<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\DocumentTemplate;
use App\Models\Resident;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;
use DOMDocument;
use DOMXPath;
use DOMElement;
use DOMNode;

class OfficialDocumentTemplateService
{
    /**
     * Generate a populated copy of the official DOCX template.
     *
     * The original official template is never modified.
     */
    public function generate(DocumentRequest $document): string
    {
        $template = DocumentTemplate::query()
            ->where('document_type', $document->document_type)
            ->where('is_active', true)
            ->first();

        if (! $template) {
            throw new RuntimeException(
                "No active official template is registered for {$document->document_type}."
            );
        }

       $templateStoragePath = ltrim(
    str_replace('\\', '/', $template->file_path),
    '/'
);

try {
    $templateContents = Storage::disk('s3')->get(
        $templateStoragePath
    );
} catch (\Throwable $exception) {
    throw new RuntimeException(
        'The registered official document template could not be retrieved from storage.',
        0,
        $exception
    );
}

if (
    ! is_string($templateContents) ||
    $templateContents === ''
) {
    throw new RuntimeException(
        'The registered official document template file is empty or unavailable.'
    );
}

        $document->loadMissing('resident');

        $resident = $document->resident;

        if (! $resident) {
            throw new RuntimeException(
                'The resident associated with this document request could not be found.'
            );
        }

        $directory = storage_path(
            'app/private/generated-documents'
        );

        if (! is_dir($directory)) {
            if (
                ! mkdir($directory, 0755, true) &&
                ! is_dir($directory)
            ) {
                throw new RuntimeException(
                    'The generated-document directory could not be created.'
                );
            }
        }

        $fileName =
            $document->request_code .
            '-' .
            Str::slug($document->document_type) .
            '.docx';

        $outputPath =
            $directory .
            DIRECTORY_SEPARATOR .
            $fileName;

       $writtenBytes = file_put_contents(
    $outputPath,
    $templateContents,
    LOCK_EX
);

unset($templateContents);

if ($writtenBytes === false || $writtenBytes === 0) {
    throw new RuntimeException(
        'The official document template could not be written to the generated-document directory.'
    );
}

        $values = $this->buildValues(
            $document,
            $resident
        );

        $this->populateTemplate(
            $outputPath,
            $document,
            $values
        );

        return $outputPath;
    }

    /**
     * Build common resident/request values.
     */
    protected function buildValues(
        DocumentRequest $document,
        object $resident
    ): array {
        $fullName = trim(
            implode(
                ' ',
                array_filter([
                    $resident->first_name ?? null,
                    $resident->middle_name ?? null,
                    $resident->last_name ?? null,
                    $resident->suffix ?? null,
                ])
            )
        );

        $address = $this->buildResidentAddress(
            $resident
        );

        /*
         * Document-specific values are stored inside the
         * document_details JSON column.
         */
        $documentDetails = is_array($document->document_details)
            ? $document->document_details
            : [];

        /*
         * Barangay Clearance CTC number.
         *
         * The Create form submits this as:
         * document_details[ctc_number]
         *
         * Keep it as a string because CTC numbers may contain
         * leading zeroes or formatting characters.
         */
        $ctcNumber = trim((string) (
            $documentDetails['ctc_number'] ?? ''
        ));

        /*
         * Compute resident age from birth date.
         */
        $age = null;

        if (! empty($resident->birth_date)) {
            try {
                $birthDate = \Carbon\Carbon::parse(
                    $resident->birth_date
                );

                $age = $birthDate->age;
            } catch (\Throwable) {
                $age = null;
            }
        }

        /*
         * Determine resident sex/gender.
         *
         * Different Resident model versions may use either
         * "sex" or "gender", so both are supported.
         */
        $residentSex = '';

        if (
            isset($resident->sex) &&
            trim((string) $resident->sex) !== ''
        ) {
            $residentSex = strtolower(
                trim((string) $resident->sex)
            );
        } elseif (
            isset($resident->gender) &&
            trim((string) $resident->gender) !== ''
        ) {
            $residentSex = strtolower(
                trim((string) $resident->gender)
            );
        }

        $lateRegisterChildTerm = '';

        if (in_array($residentSex, [
            'female',
            'f',
            'woman',
            'girl',
        ], true)) {
            $lateRegisterChildTerm = 'daughter';
        } elseif (in_array($residentSex, [
            'male',
            'm',
            'man',
            'boy',
        ], true)) {
            $lateRegisterChildTerm = 'son';
        }

        /*
         * Certificate of Residency year.
         *
         * Prefer the value stored with the request when available, then
         * fall back to the Resident record. The aliases below keep this
         * document compatible with the existing resident/request data.
         */
        $residencyStartYear = trim((string) (
            $documentDetails['residency_start_year']
                ?? $documentDetails['residency_year']
                ?? $documentDetails['year_of_residency']
                ?? $resident->residency_start_year
                ?? $resident->residency_year
                ?? $resident->year_of_residency
                ?? ''
        ));

        $residencyYearEstimated = filter_var(
            $documentDetails['residency_year_estimated']
                ?? $documentDetails['residency_estimated']
                ?? $resident->residency_year_estimated
                ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        $childResidentIds = $documentDetails['child_resident_ids'] ?? [];

        if (! is_array($childResidentIds)) {
            $childResidentIds = [$childResidentIds];
        }

        $childResidentIds = collect($childResidentIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $soloParentChildren = collect();

        if ($childResidentIds->isNotEmpty()) {
            $childrenById = Resident::query()
                ->whereIn('id', $childResidentIds->all())
                ->where('status', 'Active')
                ->get()
                ->keyBy('id');

            foreach ($childResidentIds as $childId) {
                $child = $childrenById->get($childId);

                if (! $child) {
                    continue;
                }

                $childName = trim(implode(' ', array_filter([
                    $child->first_name ?? null,
                    $child->middle_name ?? null,
                    $child->last_name ?? null,
                    $child->suffix ?? null,
                ])));

                if ($childName === '') {
                    continue;
                }

                $childSex = strtolower(trim((string) ($child->sex ?? '')));
                $childTerm = in_array($childSex, ['female', 'f', 'woman', 'girl'], true)
                    ? 'daughter'
                    : (in_array($childSex, ['male', 'm', 'man', 'boy'], true)
                        ? 'son'
                        : 'child');

                $childBirthDate = '';

                if (! empty($child->birth_date)) {
                    try {
                        $childBirthDate = $this->formatBirthDate($child->birth_date);
                    } catch (\Throwable) {
                        $childBirthDate = '';
                    }
                }

                $soloParentChildren->push([
                    'name' => $childName,
                    'term' => $childTerm,
                    'birth_date' => $childBirthDate,
                ]);
            }
        }

        $soloParentChildrenPhrase = '';

        if ($soloParentChildren->isNotEmpty()) {
            $parts = $soloParentChildren->map(function (array $child) {
                $sentence = $child['name'];

                if ($child['birth_date'] !== '') {
                    $sentence .= ', born on ' . $child['birth_date'];
                }

                return $sentence;
            })->values()->all();

            $soloParentChildrenPhrase = implode('; and ', $parts);
        }

        $requestedDate = $document->requested_at
            ? \Carbon\Carbon::parse(
                $document->requested_at
            )
            : now();

        return [
            'request_code' => (string) (
                $document->request_code ?? ''
            ),

            'document_type' => (string) (
                $document->document_type ?? ''
            ),

            'resident_name' => $fullName,

            'resident_name_upper' => strtoupper(
                $fullName
            ),

            'first_name' => (string) (
                $resident->first_name ?? ''
            ),

            'middle_name' => (string) (
                $resident->middle_name ?? ''
            ),

            'last_name' => (string) (
                $resident->last_name ?? ''
            ),

            'suffix' => (string) (
                $resident->suffix ?? ''
            ),

            'birth_date' => $this->formatBirthDate(
                $resident->birth_date ?? null
            ),

            'age' => $age !== null
                ? (string) $age
                : '',

            'civil_status' => $this->formatCivilStatus(
                $resident->civil_status ?? null
            ),

            'address' => $address,

            'purpose' => (string) (
                $document->purpose ?? ''
            ),

            'purpose_upper' => strtoupper(
                trim((string) (
                    $document->purpose ?? ''
                ))
            ),

            'remarks' => (string) (
                $document->remarks ?? ''
            ),

            /*
             * Barangay Clearance.
             */
            'ctc_number' => $ctcNumber,

            /*
             * Certificate of Residency.
             */
            'residency_start_year' => $residencyStartYear,

            'residency_year_estimated' => $residencyYearEstimated,

            'residency_year_label' => $residencyStartYear !== ''
                ? $residencyStartYear . ($residencyYearEstimated ? ' (estimated)' : '')
                : '',

            'solo_parent_children' => $soloParentChildrenPhrase,

            /*
             * Certificate of Late Register.
             */
            'birthplace' => trim(
                (string) (
                    $documentDetails['birthplace'] ?? ''
                )
            ),

            'father_name' => trim(
                (string) (
                    $documentDetails['father_name'] ?? ''
                )
            ),

            'mother_name' => trim(
                (string) (
                    $documentDetails['mother_name'] ?? ''
                )
            ),

            /*
             * Cutting Trees.
             */
            'permit_to_cut_no' => trim(
                (string) (
                    $documentDetails['permit_to_cut_no'] ?? ''
                )
            ),

            'permit_to_cut_date' => trim(
                (string) (
                    $documentDetails['permit_to_cut_date'] ?? ''
                )
            ),

            'permit_issued_by' => trim(
                (string) (
                    $documentDetails['permit_issued_by'] ?? 'DENR'
                )
            ),

            'tree_count' => (int) (
                $documentDetails['tree_count'] ?? 0
            ),

            'tree_type' => trim(
                (string) (
                    $documentDetails['tree_type'] ?? ''
                )
            ),

            'location' => trim(
                (string) (
                    $documentDetails['location'] ?? ''
                )
            ),

            'location_description' => trim(
                (string) (
                    $documentDetails['location_description'] ?? ''
                )
            ),

            'tree_owner' => trim(
                (string) (
                    $documentDetails['tree_owner'] ?? ''
                )
            ),

            'cutter_name' => trim(
                (string) (
                    $documentDetails['cutter_name'] ?? ''
                )
            ),

            /*
             * Late Register gender wording.
             */
            'late_register_child_term' =>
                $lateRegisterChildTerm,

            'late_register_pronoun' =>
                in_array($residentSex, [
                    'female',
                    'f',
                    'woman',
                    'girl',
                ], true)
                    ? 'her'
                    : (
                        in_array($residentSex, [
                            'male',
                            'm',
                            'man',
                            'boy',
                        ], true)
                            ? 'his'
                            : ''
                    ),

            /*
             * Request/issuance dates.
             */
            'date_requested' => $requestedDate->format(
                'F j, Y'
            ),

            'date_issued' => now()->format(
                'F j, Y'
            ),

            'day' => now()->format('j'),

            'day_ordinal' => $this->ordinalDay(
                (int) now()->format('j')
            ),

            'month' => now()->format('F'),

            'month_upper' => strtoupper(
                now()->format('F')
            ),

            'year' => now()->format('Y'),
        ];
    }

    /**
     * Populate the copied DOCX without rebuilding the document.
     */
    protected function populateTemplate(
        string $docxPath,
        DocumentRequest $document,
        array $values
    ): void {
        $zip = new ZipArchive();

        $openResult = $zip->open($docxPath);

        if ($openResult !== true) {
            throw new RuntimeException(
                'The generated DOCX package could not be opened.'
            );
        }

        try {
            $parts = $this->getXmlParts($zip);

            foreach ($parts as $part) {
                $xml = $zip->getFromName($part);

                if ($xml === false) {
                    continue;
                }

                $updatedXml = $this->populateXmlPart(
                    $xml,
                    $document,
                    $values,
                    $part
                );

                if ($updatedXml === $xml) {
                    continue;
                }

                if (! $zip->deleteName($part)) {
                    throw new RuntimeException(
                        "The existing Word XML part could not be replaced: {$part}"
                    );
                }

                if (! $zip->addFromString(
                    $part,
                    $updatedXml
                )) {
                    throw new RuntimeException(
                        "The generated Word XML part could not be written: {$part}"
                    );
                }
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Get editable Word XML parts.
     */
    protected function getXmlParts(
        ZipArchive $zip
    ): array {
        $parts = [];

        for (
            $index = 0;
            $index < $zip->numFiles;
            $index++
        ) {
            $name = $zip->getNameIndex($index);

            if (
                $name === 'word/document.xml' ||
                preg_match(
                    '/^word\/header\d+\.xml$/',
                    $name
                ) ||
                preg_match(
                    '/^word\/footer\d+\.xml$/',
                    $name
                )
            ) {
                $parts[] = $name;
            }
        }

        return $parts;
    }

    /**
     * Populate one Word XML part.
     */
    protected function populateXmlPart(
        string $xml,
        DocumentRequest $document,
        array $values,
        string $partName
    ): string {
        $dom = new DOMDocument();

        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        if (! @$dom->loadXML($xml)) {
            return $xml;
        }

        $xpath = new DOMXPath($dom);

        $xpath->registerNamespace(
            'w',
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
        );

        /*
         * Real placeholders.
         */
        $placeholderMap = [
            '{{resident_name}}' =>
                $values['resident_name'],

            '{{resident_name_upper}}' =>
                $values['resident_name_upper'],

            '{{first_name}}' =>
                $values['first_name'],

            '{{middle_name}}' =>
                $values['middle_name'],

            '{{last_name}}' =>
                $values['last_name'],

            '{{suffix}}' =>
                $values['suffix'],

            '{{birth_date}}' =>
                $values['birth_date'],

            '{{age}}' =>
                $values['age'],

            '{{civil_status}}' =>
                $values['civil_status'],

            '{{address}}' =>
                $values['address'],

            '{{purpose}}' =>
                $values['purpose'],

            '{{remarks}}' =>
                $values['remarks'],

            '{{date_requested}}' =>
                $values['date_requested'],

            '{{date_issued}}' =>
                $values['date_issued'],

            '{{day}}' =>
                $values['day'],

            '{{day_ordinal}}' =>
                $values['day_ordinal'],

            '{{month}}' =>
                $values['month'],

            '{{year}}' =>
                $values['year'],

            '{{residency_start_year}}' =>
                $values['residency_year_label'],

            '{{residency_year_label}}' =>
                $values['residency_year_label'],

            '{{solo_parent_children}}' =>
                $values['solo_parent_children'],

            '{{birthplace}}' =>
                $values['birthplace'],

            '{{father_name}}' =>
                $values['father_name'],

            '{{mother_name}}' =>
                $values['mother_name'],

            '{{child_term}}' =>
                $values['late_register_child_term'],
        ];

        foreach ($placeholderMap as $search => $replacement) {
            $this->replaceAcrossRuns(
                $xpath,
                $search,
                (string) $replacement
            );
        }

        /*
         * Existing official templates contain sample values.
         */
        $this->applyOfficialTemplateMappings(
            $xpath,
            $document,
            $values,
            $partName
        );

        return $dom->saveXML();
    }

    /**
     * Replace text even when Microsoft Word has split the
     * visible text across multiple w:t runs.
     */
    protected function replaceAcrossRuns(
        DOMXPath $xpath,
        string $search,
        string $replacement
    ): bool {
        if ($search === '') {
            return false;
        }

        $paragraphs = $xpath->query(
            '//w:p'
        );

        if (! $paragraphs) {
            return false;
        }

        foreach ($paragraphs as $paragraph) {
            $textNodes = $xpath->query(
                './/w:t',
                $paragraph
            );

            if (! $textNodes || $textNodes->length === 0) {
                continue;
            }

            $nodes = [];

            foreach ($textNodes as $textNode) {
                $nodes[] = [
                    'node' => $textNode,
                    'text' => $textNode->textContent,
                ];
            }

            /*
             * Exact concatenated text.
             */
            $combined = '';

            foreach ($nodes as $item) {
                $combined .= $item['text'];
            }

            $position = strpos(
                $combined,
                $search
            );

            if ($position !== false) {
                $this->replaceMatchInsideParagraph(
                    $nodes,
                    $position,
                    strlen($search),
                    $replacement
                );

                return true;
            }

            /*
             * Word may have inserted whitespace between runs.
             */
            $logicalText = '';
            $characterMap = [];

            foreach ($nodes as $nodeIndex => $item) {
                $text = $item['text'];
                $length = strlen($text);

                for ($i = 0; $i < $length; $i++) {
                    $character = $text[$i];

                    if (ctype_space($character)) {
                        continue;
                    }

                    $characterMap[] = [
                        'node_index' => $nodeIndex,
                        'offset' => $i,
                        'character' => $character,
                    ];

                    $logicalText .= $character;
                }
            }

            $normalizedSearch = preg_replace(
                '/\s+/u',
                '',
                $search
            );

            if ($normalizedSearch === '') {
                continue;
            }

            $logicalPosition = strpos(
                $logicalText,
                $normalizedSearch
            );

            if ($logicalPosition === false) {
                continue;
            }

            $matchStartMap = $characterMap[$logicalPosition];

            $matchEndMap = $characterMap[
                $logicalPosition +
                strlen($normalizedSearch) -
                1
            ];

            $this->replaceLogicalMatch(
                $nodes,
                $matchStartMap,
                $matchEndMap,
                $replacement
            );

            return true;
        }

        return false;
    }

    /**
     * Replace an exact match inside paragraph runs.
     *
     * textContent is used instead of nodeValue so replacement values
     * containing XML-sensitive characters such as & are safely escaped
     * by DOMDocument when the Word XML is serialized.
     */
    protected function replaceMatchInsideParagraph(
        array $nodes,
        int $start,
        int $length,
        string $replacement
    ): void {
        $end = $start + $length;

        $cursor = 0;
        $affected = [];

        foreach ($nodes as $index => $item) {
            $text = $item['text'];

            $nodeStart = $cursor;
            $nodeEnd = $cursor + strlen($text);

            if (
                $start < $nodeEnd &&
                $end > $nodeStart
            ) {
                $localStart = max(
                    0,
                    $start - $nodeStart
                );

                $localEnd = min(
                    strlen($text),
                    $end - $nodeStart
                );

                $affected[] = [
                    'index' => $index,
                    'localStart' => $localStart,
                    'localEnd' => $localEnd,
                    'text' => $text,
                ];
            }

            $cursor = $nodeEnd;
        }

        if (empty($affected)) {
            return;
        }

        $first = $affected[0];

        $firstNode = $nodes[
            $first['index']
        ]['node'];

        $firstText = $first['text'];

        $before = substr(
            $firstText,
            0,
            $first['localStart']
        );

        if (count($affected) === 1) {
            $after = substr(
                $firstText,
                $first['localEnd']
            );

            $firstNode->textContent =
                $before .
                $replacement .
                $after;

            return;
        }

        $firstNode->textContent =
            $before .
            $replacement;

        $lastIndex = count($affected) - 1;

        foreach ($affected as $position => $item) {
            if ($position === 0) {
                continue;
            }

            $node = $nodes[
                $item['index']
            ]['node'];

            if ($position === $lastIndex) {
                $after = substr(
                    $item['text'],
                    $item['localEnd']
                );

                $node->textContent = $after;
            } else {
                $node->textContent = '';
            }
        }
    }

    /**
     * Replace a visually continuous match when Word inserted
     * whitespace between individual XML text runs.
     */
    protected function replaceLogicalMatch(
        array $nodes,
        array $startMap,
        array $endMap,
        string $replacement
    ): void {
        $startNodeIndex = $startMap['node_index'];
        $startOffset = $startMap['offset'];

        $endNodeIndex = $endMap['node_index'];
        $endOffset = $endMap['offset'];

        /*
         * Same w:t node.
         */
        if ($startNodeIndex === $endNodeIndex) {
            $node = $nodes[
                $startNodeIndex
            ]['node'];

            $text = $nodes[
                $startNodeIndex
            ]['text'];

            $before = substr(
                $text,
                0,
                $startOffset
            );

            $after = substr(
                $text,
                $endOffset + 1
            );

            $node->textContent =
                $before .
                $replacement .
                $after;

            return;
        }

        /*
         * First affected node.
         */
        $firstNode = $nodes[
            $startNodeIndex
        ]['node'];

        $firstText = $nodes[
            $startNodeIndex
        ]['text'];

        $before = substr(
            $firstText,
            0,
            $startOffset
        );

        $firstNode->textContent =
            $before .
            $replacement;

        /*
         * Clear nodes between first and last.
         */
        for (
            $index = $startNodeIndex + 1;
            $index < $endNodeIndex;
            $index++
        ) {
            $nodes[$index]['node']->textContent = '';
        }

        /*
         * Last affected node keeps anything after the match.
         */
        $lastNode = $nodes[
            $endNodeIndex
        ]['node'];

        $lastText = $nodes[
            $endNodeIndex
        ]['text'];

        $after = substr(
            $lastText,
            $endOffset + 1
        );

        $lastNode->textContent = $after;
    }

    /**
     * Apply mappings for existing official templates.
     */
    protected function applyOfficialTemplateMappings(
        DOMXPath $xpath,
        DocumentRequest $document,
        array $values,
        string $partName = 'word/document.xml'
    ): void {
        switch ($document->document_type) {
            case 'Barangay Clearance':
                $this->populateBarangayClearance(
                    $xpath,
                    $values
                );
                break;

            case 'Certificate of Residency':
                $this->populateCertificateOfResidency(
                    $xpath,
                    $values
                );
                break;

            case 'Certificate of Indigency':
                $this->populateCertificateOfIndigency(
                    $xpath,
                    $values
                );
                break;

            case 'Certificate of Late Register':
                $this->populateCertificateOfLateRegister(
                    $xpath,
                    $values
                );
                break;

            case 'Cutting Trees':
                $this->populateCuttingTrees(
                    $xpath,
                    $values
                );
                break;

            case 'First Time Job Seeker':
                $this->populateFirstTimeJobSeeker(
                    $xpath,
                    $values
                );
                break;

            case 'Oath of Undertaking':
                $this->populateOathOfUndertaking(
                    $xpath,
                    $values
                );
                break;

            case 'Solo Parent':
                if ($partName !== 'word/document.xml') {
                    break;
                }

                $this->populateSoloParent(
                    $xpath,
                    $values
                );
                break;
        }
    }

    /**
     * Barangay Clearance.
     */
    protected function populateBarangayClearance(
        DOMXPath $xpath,
        array $values
    ): void {
        $this->replaceAcrossRuns(
            $xpath,
            'JOHN DAVE C. MENDOZA',
            $values['resident_name_upper']
        );

        $this->replaceAcrossRuns(
            $xpath,
            'LOCAL EMPLOYMENT PURPOSES',
            $values['purpose_upper']
        );

        $this->replaceAcrossRuns(
            $xpath,
            '7th day of September 2026',
            $this->ordinalDatePhrase()
        );

        /*
         * CTC number printed on page 2 of the official template.
         *
         * The label is split across Word runs ("CTC NUMBER" and ":"),
         * so replaceAcrossRuns() is used to match the visible label
         * without rebuilding the paragraph or changing the layout.
         *
         * If the field is blank, leave the original label unchanged.
         */
        if (trim((string) ($values['ctc_number'] ?? '')) !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                'CTC NUMBER:',
                'CTC NUMBER: ' . trim((string) $values['ctc_number'])
            );
        }

        $this->setParagraphAlignmentContaining(
            $xpath,
            'This is to certify that,',
            'both'
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'This certification is issued upon request',
            'both'
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'Given this',
            'both'
        );
    }

    /**
     * Certificate of Residency.
     */
    protected function populateCertificateOfResidency(
        DOMXPath $xpath,
        array $values
    ): void {
        $this->replaceAcrossRuns(
            $xpath,
            'GENEVA A. GATBUNTON',
            $values['resident_name_upper']
        );

        /*
         * Replace the official template's sample residency year.
         *
         * If the resident has a residency_start_year, that year
         * replaces 1992.
         *
         * If the resident does not have a residency_start_year,
         * the sample 1992 is removed instead of remaining in the
         * generated Certificate of Residency.
         */
        $this->replaceAcrossRuns(
            $xpath,
            '1992',
            $values['residency_year_label']
        );

        $this->replaceAcrossRuns(
            $xpath,
            '1st day of SEPTEMBER 2026',
            $this->ordinalDatePhrase(
                strtoupper(
                    now()->format('F')
                )
            )
        );

        $this->replaceAcrossRuns(
            $xpath,
            '1st day of September 2026',
            $this->ordinalDatePhrase()
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'This is to certify that,',
            'both'
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'This is to further certify that',
            'both'
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'This certification is being issued',
            'both'
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'Issued this',
            'both'
        );
    }

    /**
     * Certificate of Indigency.
     */
    protected function populateCertificateOfIndigency(
        DOMXPath $xpath,
        array $values
    ): void {
        $this->replaceAcrossRuns(
            $xpath,
            'PRINCESS BUSTRIA MENDOZA',
            $values['resident_name_upper']
        );

        $this->replaceAcrossRuns(
            $xpath,
            '4th day of SEPTEMBER 2026',
            $this->ordinalDatePhrase(
                strtoupper(
                    now()->format('F')
                )
            )
        );

        $this->replaceAcrossRuns(
            $xpath,
            '4th day of September 2026',
            $this->ordinalDatePhrase()
        );

        if ($values['civil_status'] !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                'single',
                strtolower(
                    $values['civil_status']
                )
            );
        }

        $this->replaceAcrossRuns(
            $xpath,
            'Financial Assistance',
            $values['purpose']
        );
    }

    /**
     * Certificate of Late Register.
     */
    protected function populateCertificateOfLateRegister(
        DOMXPath $xpath,
        array $values
    ): void {
        $this->replaceAcrossRuns(
            $xpath,
            'LUZ S. SAN JUAN',
            $values['resident_name_upper']
        );

        if (
            $values['birthplace'] !== '' &&
            $values['father_name'] !== '' &&
            $values['mother_name'] !== '' &&
            $values['late_register_child_term'] !== ''
        ) {
            $replacement =
                'allegedly been born on ' .
                $values['birthplace'] .
                '. ' .
                $values['late_register_child_term'] .
                ' of ' .
                $values['father_name'] .
                ' & ' .
                $values['mother_name'];

            $this->replaceAcrossRuns(
                $xpath,
                'allegedly been born on Candon, Ilocos Sur. daughter of SIMMEON SANTIAGO & AURORA GUERERRO',
                $replacement
            );

            $this->replaceAcrossRuns(
                $xpath,
                'allegedly been born on Candon Ilocos Sur. daughter of SIMMEON SANTIAGO & AURORA GUERERRO',
                $replacement
            );

            $this->replaceAcrossRuns(
                $xpath,
                'allegedly been born on Candon, Ilocos Sur. son of SIMMEON SANTIAGO & AURORA GUERERRO',
                $replacement
            );

            $this->replaceAcrossRuns(
                $xpath,
                'allegedly been born on Candon Ilocos Sur. son of SIMMEON SANTIAGO & AURORA GUERERRO',
                $replacement
            );
        }

        if ($values['birthplace'] !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                'Candon, Ilocos Sur',
                $values['birthplace']
            );

            $this->replaceAcrossRuns(
                $xpath,
                'Candon Ilocos Sur',
                $values['birthplace']
            );

            $this->replaceAcrossRuns(
                $xpath,
                'CANDON, ILOCOS SUR',
                strtoupper($values['birthplace'])
            );

            $this->replaceAcrossRuns(
                $xpath,
                'CANDON ILOCOS SUR',
                strtoupper($values['birthplace'])
            );
        }

        if ($values['father_name'] !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                'SIMMEON SANTIAGO',
                $values['father_name']
            );
        }

        if ($values['mother_name'] !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                'AURORA GUERERRO',
                $values['mother_name']
            );
        }

        if (
            $values['father_name'] !== '' &&
            $values['mother_name'] !== ''
        ) {
            $this->replaceAcrossRuns(
                $xpath,
                'SIMMEON SANTIAGO & AURORA GUERERRO',
                $values['father_name'] .
                ' & ' .
                $values['mother_name']
            );
        }

        $registrationPronoun =
            $values['late_register_pronoun'];

        if ($registrationPronoun !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                'for his LATE REGISTRATION',
                'for ' .
                $registrationPronoun .
                ' LATE REGISTRATION'
            );

            $this->replaceAcrossRuns(
                $xpath,
                'for her LATE REGISTRATION',
                'for ' .
                $registrationPronoun .
                ' LATE REGISTRATION'
            );
        }

        $this->replaceAcrossRuns(
            $xpath,
            '22nd day of July 2026',
            $this->ordinalDatePhrase()
        );

        $this->replaceAcrossRuns(
            $xpath,
            '22nd day of JULY 2026',
            $this->ordinalDatePhrase(
                strtoupper(
                    now()->format('F')
                )
            )
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'Based on the record filed in this office',
            'both'
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'This certification is issued upon the request',
            'both'
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'Issued this',
            'both'
        );
    }

    /**
     * Cutting Trees.
     */
    protected function populateCuttingTrees(
        DOMXPath $xpath,
        array $values
    ): void {
        if ($values['permit_to_cut_no'] !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                '003204',
                $values['permit_to_cut_no']
            );
        }

        $permitDatePhrase =
            $this->ordinalDatePhraseFromDate(
                $values['permit_to_cut_date']
            );

        if ($permitDatePhrase !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                '24th day of July 2026',
                $permitDatePhrase
            );
        }

        if (
            $values['tree_count'] > 0 &&
            $values['tree_type'] !== ''
        ) {
            $treePhrase =
                '(' .
                $values['tree_count'] .
                ') ' .
                $values['tree_type'];

            $this->replaceAcrossRuns(
                $xpath,
                '(1) Acacia Tree',
                $treePhrase
            );
        }

        $locationPhrase = trim(
            $values['location']
        );

        /*
         * If a Tree Owner / Property Owner is provided,
         * use the owner as the property reference.
         *
         * The separate Location Description is intentionally
         * NOT appended in this case so the generated sentence
         * does not duplicate the resident/property location.
         */
        $treeOwner = trim(
            (string) ($values['tree_owner'] ?? '')
        );

        if ($treeOwner !== '') {
            if ($locationPhrase !== '') {
                $replacement =
                    'located at ' .
                    $locationPhrase .
                    ' inside of the compound of ' .
                    $treeOwner .
                    ' of this Barangay';
            } else {
                $replacement =
                    'located inside of the compound of ' .
                    $treeOwner .
                    ' of this Barangay';
            }

            $this->replaceAcrossRuns(
                $xpath,
                'located at Zone 7 Barangay Poblacion, Bani, Pangasinan inside of the compound of GRACE MARY O. TABLANG of this Barangay',
                $replacement
            );
        } else {
            /*
             * No Tree Owner was supplied.
             * Use Location Description as the additional
             * landmark/location detail.
             */
            if (
                $values['location_description'] !== ''
            ) {
                if ($locationPhrase !== '') {
                    $locationPhrase .= ', ';
                }

                $locationPhrase .=
                    $values['location_description'];
            }

            if ($locationPhrase !== '') {
                $this->replaceAcrossRuns(
                    $xpath,
                    'located at Zone 7 Barangay Poblacion, Bani, Pangasinan inside of the compound of GRACE MARY O. TABLANG of this Barangay',
                    'located at ' .
                    $locationPhrase
                );
            }
        }

        if ($values['cutter_name'] !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                'RAFFY OLIVEROS',
                $values['cutter_name']
            );
        }
    }

    /**
     * First Time Job Seeker.
     *
     * IMPORTANT:
     * The validity date is calculated dynamically as exactly
     * one year after the issuance date.
     */
    protected function populateFirstTimeJobSeeker(
        DOMXPath $xpath,
        array $values
    ): void {
        /*
         * ========================================================
         * RESIDENT NAME
         * ========================================================
         */
        $this->replaceAcrossRuns(
            $xpath,
            'HEIDY NABOR PAUMBA',
            $values['resident_name_upper']
        );

        /*
         * ========================================================
         * AGE
         * ========================================================
         */
        if ($values['age'] !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                '19 years of age',
                $values['age'] .
                ' years of age'
            );
        }

        /*
         * ========================================================
         * ISSUANCE DATE
         * ========================================================
         *
         * Current date is used as the date the document is issued.
         *
         * Example:
         *
         * 28th day of SEPTEMBER 2026
         */
        $issuedDateUpper =
            $this->ordinalDatePhrase(
                strtoupper(
                    now()->format('F')
                )
            );

        $issuedDateNormal =
            $this->ordinalDatePhrase();

        /*
         * Support all known capitalization variations of the
         * sample date in the official DOCX.
         */
        $this->replaceAcrossRuns(
            $xpath,
            '1ST day of SEPTEMBER 2026',
            $issuedDateUpper
        );

        $this->replaceAcrossRuns(
            $xpath,
            '1st day of SEPTEMBER 2026',
            $issuedDateUpper
        );

        $this->replaceAcrossRuns(
            $xpath,
            '1st day of September 2026',
            $issuedDateNormal
        );

        $this->replaceAcrossRuns(
            $xpath,
            '1ST DAY OF SEPTEMBER 2026',
            $issuedDateUpper
        );

        /*
         * ========================================================
         * VALID UNTIL
         * ========================================================
         *
         * Exactly one year after issuance.
         *
         * If issued:
         *     September 28, 2026
         *
         * Then:
         *     September 28, 2027
         *
         * We intentionally calculate this instead of hard-coding
         * 2027 so the certificate continues to work in future years.
         */
        $validUntil = now()->copy()->addYear();

        $validUntilUpper =
            $this->ordinalDay(
                (int) $validUntil->format('j')
            ) .
            ' day of ' .
            strtoupper(
                $validUntil->format('F')
            ) .
            ' ' .
            $validUntil->format('Y');

        $validUntilNormal =
            $this->ordinalDay(
                (int) $validUntil->format('j')
            ) .
            ' day of ' .
            $validUntil->format('F') .
            ' ' .
            $validUntil->format('Y');

        /*
         * Replace the official sample validity date.
         */
        $validUntilSampleDates = [
            '1st day of SEPTEMBER 2027',
            '1st day of September 2027',
            '1ST day of SEPTEMBER 2027',
            '1ST DAY OF SEPTEMBER 2027',
        ];

        foreach ($validUntilSampleDates as $sampleDate) {
            $this->replaceAcrossRuns(
                $xpath,
                $sampleDate,
                str_contains(
                    $sampleDate,
                    'SEPTEMBER'
                )
                    ? $validUntilUpper
                    : $validUntilNormal
            );
        }

        /*
         * ========================================================
         * ADDITIONAL FALLBACKS
         * ========================================================
         *
         * Some official templates may have the sample validity
         * date written without the ordinal capitalization.
         */
        $this->replaceAcrossRuns(
            $xpath,
            '1st day of september 2027',
            $validUntilNormal
        );

        /*
         * ========================================================
         * BODY ALIGNMENT
         * ========================================================
         */
        $this->setParagraphAlignmentContaining(
            $xpath,
            'This is to certify that,',
            'both'
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'I further certify that',
            'both'
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'Signed this',
            'both'
        );

        $this->setParagraphAlignmentContaining(
            $xpath,
            'This certification is valid until',
            'both'
        );
    }

    /**
     * Oath of Undertaking.
     */
    protected function populateOathOfUndertaking(
        DOMXPath $xpath,
        array $values
    ): void {
        $this->replaceAcrossRuns(
            $xpath,
            'HEIDY NABOR PAUMBA',
            $values['resident_name_upper']
        );

        /*
         * The oath's second resident-name occurrence contains only
         * first name + middle name in the official sample. Replace
         * the surname separately so the ORIGINAL resident surname
         * from the Residents record is never left behind.
         */
        $this->replaceAcrossRuns(
            $xpath,
            'HEIDY NABOR',
            strtoupper(
                trim(
                    $values['first_name'] .
                    ' ' .
                    $values['middle_name']
                )
            )
        );

        $this->replaceAcrossRuns(
            $xpath,
            'PAUMBA',
            strtoupper(
                trim($values['last_name'])
            )
        );

        $this->replaceAcrossRuns(
            $xpath,
            '1st day of SEPTEMBER 2026',
            $this->ordinalDatePhrase(
                strtoupper(
                    now()->format('F')
                )
            )
        );

        $this->replaceAcrossRuns(
            $xpath,
            '1st day of September 2026',
            $this->ordinalDatePhrase()
        );

        if ($values['age'] !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                '19 years',
                $values['age'] . ' years'
            );
        }

        $this->fixOathSignatureBlock($xpath);
    }

    /**
     * Solo Parent.
     */
    protected function populateSoloParent(
        DOMXPath $xpath,
        array $values
    ): void {
        $this->replaceAcrossRuns(
            $xpath,
            'VIVIAN RECOPELACION ROSETE',
            $values['resident_name_upper']
        );

        /*
         * Generate the children directly from the selected registered
         * resident IDs saved in document_details. This replaces the
         * template's sample child instead of leaving sample data.
         */
        if ($values['solo_parent_children'] !== '') {
            $this->replaceAcrossRuns(
                $xpath,
                'MARIANE R. ROSETE, born on August 3, 2007 at Poblacion, Bani, Pangasinan; and [CHILD 2 FULL NAME], born on [BIRTHDATE], at [BIRTHPLACE]',
                $values['solo_parent_children']
            );

            $this->replaceAcrossRuns(
                $xpath,
                'MARIANE R. ROSETE born on August 3, 2007 at Poblacion, Bani, Pangasinan',
                $values['solo_parent_children']
            );

            $this->replaceAcrossRuns(
                $xpath,
                'MARIANE R. ROSETE, born on August 3, 2007 at Poblacion, Bani, Pangasinan',
                $values['solo_parent_children']
            );
        }

        $this->replaceAcrossRuns(
            $xpath,
            '26th day of August 2026',
            $this->ordinalDatePhrase()
        );
    }

    protected function fixOathSignatureBlock(DOMXPath $xpath): void
    {
        /*
         * IMPORTANT:
         *
         * The official Oath template does NOT use a Word table for the
         * signature block. It uses ordinary paragraphs + tab characters.
         *
         * The previous table-based fix therefore had no effect.
         * This version works directly with the actual paragraph structure
         * of OATH OF UNDERTAKING.docx.
         */
        $paragraphs = $xpath->query('//w:body/w:p');

        if (! $paragraphs) {
            return;
        }

        $heading = null;
        $signerParagraph = null;
        $witnessParagraph = null;
        $secretaryParagraph = null;
        $certifiedParagraph = null;
        $captainParagraph = null;
        $captainTitleParagraph = null;

        foreach ($paragraphs as $paragraph) {
            if (! $paragraph instanceof DOMElement) {
                continue;
            }

            $text = trim(
                preg_replace(
                    '/\s+/u',
                    ' ',
                    (string) $paragraph->textContent
                )
            );

            if (
                $heading === null &&
                str_contains($text, 'Signed by:') &&
                str_contains($text, 'Witnessed by:')
            ) {
                $heading = $paragraph;
                continue;
            }

            if (
                $witnessParagraph === null &&
                str_contains($text, 'RIEMAR DIRECK H. ORILLA')
            ) {
                $witnessParagraph = $paragraph;
                continue;
            }

            if (
                $secretaryParagraph === null &&
                str_contains($text, 'BARANGAY SECRETARY')
            ) {
                $secretaryParagraph = $paragraph;
                continue;
            }

            if (
                $certifiedParagraph === null &&
                str_contains($text, 'CERTIFIED BY:')
            ) {
                $certifiedParagraph = $paragraph;
                continue;
            }

            if (
                $captainParagraph === null &&
                str_contains($text, 'BRYANNE PAUL B. AMPLER')
            ) {
                $captainParagraph = $paragraph;
                continue;
            }

            if (
                $captainTitleParagraph === null &&
                str_contains($text, 'PUNONG BARANGAY')
            ) {
                $captainTitleParagraph = $paragraph;
            }
        }

        if (! $heading instanceof DOMElement) {
            return;
        }

        /*
         * The signer is always the paragraph immediately after the
         * Signed by / Witnessed by heading in the official template.
         * We deliberately do NOT search for the old sample name because
         * it has already been replaced by the resident's actual name.
         */
        $body = $heading->parentNode;
        if ($body instanceof DOMElement) {
            $next = $heading->nextSibling;

            while ($next !== null) {
                if (
                    $next instanceof DOMElement &&
                    $next->localName === 'p'
                ) {
                    $signerParagraph = $next;
                    break;
                }

                $next = $next->nextSibling;
            }
        }

        if (! $signerParagraph instanceof DOMElement) {
            return;
        }

        /*
         * Read the already-populated resident name before rebuilding the
         * signature paragraph.
         */
        $signerName = trim(
            preg_replace(
                '/\s+/u',
                ' ',
                (string) $signerParagraph->textContent
            )
        );

        if ($signerName === '') {
            return;
        }

        /*
         * Save the official run formatting before clearing the paragraph.
         */
        $signerRunProperties = null;

        foreach ($signerParagraph->childNodes as $child) {
            if (! $child instanceof DOMElement || $child->localName !== 'r') {
                continue;
            }

            $runText = trim(
                preg_replace(
                    '/\s+/u',
                    ' ',
                    (string) $child->textContent
                )
            );

            if ($runText !== '') {
                $runProperties = $this->findOathRunProperties($child);

                if ($runProperties instanceof DOMElement) {
                    $signerRunProperties = $runProperties->cloneNode(true);
                }

                break;
            }
        }

        /*
         * Rebuild the upper signature row as ONE paragraph:
         *
         * Signed by:        [same line]        Witnessed by:
         * RESIDENT NAME                         SECRETARY NAME
         *                                         BARANGAY SECRETARY
         *
         * The right tab is what makes the witness column independent from
         * the length of the resident's name.
         */
        $this->rebuildOathHeadingParagraph($heading);

        $witnessName = 'RIEMAR DIRECK H. ORILLA';

        if ($witnessParagraph instanceof DOMElement) {
            $candidate = trim(
                preg_replace(
                    '/\s+/u',
                    ' ',
                    (string) $witnessParagraph->textContent
                )
            );

            if ($candidate !== '') {
                $witnessName = $candidate;
            }
        }

        $witnessRunProperties = null;

        if ($witnessParagraph instanceof DOMElement) {
            foreach ($witnessParagraph->childNodes as $child) {
                if (! $child instanceof DOMElement || $child->localName !== 'r') {
                    continue;
                }

                $runText = trim(
                    preg_replace(
                        '/\s+/u',
                        ' ',
                        (string) $child->textContent
                    )
                );

                if ($runText === '') {
                    continue;
                }

                $runProperties = $this->findOathRunProperties($child);

                if ($runProperties instanceof DOMElement) {
                    $witnessRunProperties = $runProperties->cloneNode(true);
                }

                break;
            }
        }

        $this->rebuildOathSignerParagraph(
            $signerParagraph,
            $signerName,
            $signerRunProperties,
            $witnessName,
            $witnessRunProperties
        );

        /*
         * Use the existing empty paragraph directly after the signer row
         * for BARANGAY SECRETARY. This puts the title exactly underneath
         * the witness name instead of creating an extra vertical gap.
         */
        $secretaryTarget = $signerParagraph->nextSibling;

        while (
            $secretaryTarget !== null &&
            (! $secretaryTarget instanceof DOMElement ||
             $secretaryTarget->localName !== 'p')
        ) {
            $secretaryTarget = $secretaryTarget->nextSibling;
        }

        if ($secretaryTarget instanceof DOMElement) {
            $this->rebuildOathSecretaryParagraph(
                $secretaryTarget,
                $secretaryParagraph
            );
        }

        /*
         * Remove the old witness-name/title paragraphs and the extra blank
         * paragraphs that caused the secretary block to sit too low.
         */
        foreach ($paragraphs as $paragraph) {
            if (! $paragraph instanceof DOMElement) {
                continue;
            }

            if (
                $paragraph === $signerParagraph ||
                $paragraph === $heading ||
                $paragraph === $secretaryTarget ||
                $paragraph === $certifiedParagraph ||
                $paragraph === $captainParagraph ||
                $paragraph === $captainTitleParagraph
            ) {
                continue;
            }

            $text = trim(
                preg_replace(
                    '/\s+/u',
                    ' ',
                    (string) $paragraph->textContent
                )
            );

            if (
                $text === 'RIEMAR DIRECK H. ORILLA' ||
                str_contains($text, 'BARANGAY SECRETARY')
            ) {
                $paragraph->parentNode?->removeChild($paragraph);
            }
        }

        /*
         * Remove the remaining blank paragraphs between the secretary row
         * and CERTIFIED BY. Keep only one clean separation paragraph.
         */
        if ($secretaryTarget instanceof DOMElement) {
            $node = $secretaryTarget->nextSibling;

            while ($node !== null && $node !== $certifiedParagraph) {
                $next = $node->nextSibling;

                if ($node instanceof DOMElement && $node->localName === 'p') {
                    $node->parentNode?->removeChild($node);
                }

                $node = $next;
            }
        }

        /*
         * Center the certification block and remove its old manual spaces.
         */
        $this->cleanAndCenterOathParagraph(
            $certifiedParagraph,
            'CERTIFIED BY:'
        );

        $this->cleanAndCenterOathParagraph(
            $captainParagraph,
            'BRYANNE PAUL B. AMPLER'
        );

        /*
         * Captain's name is the signature name: bold + underlined.
         * Keep PUNONG BARANGAY as the identity/title underneath it.
         */
        $this->makeOathParagraphBoldAndUnderlined(
            $captainParagraph
        );

        $this->cleanAndCenterOathParagraph(
            $captainTitleParagraph,
            'PUNONG BARANGAY'
        );
    }

    protected function findOathRunProperties(
        DOMElement $run
    ): ?DOMElement {
        foreach ($run->childNodes as $child) {
            if (
                $child instanceof DOMElement &&
                $child->localName === 'rPr'
            ) {
                return $child;
            }
        }

        return null;
    }

    protected function clearOathParagraph(DOMElement $paragraph): void
    {
        while ($paragraph->firstChild !== null) {
            $paragraph->removeChild($paragraph->firstChild);
        }
    }

    protected function getOathParagraphProperties(
        DOMElement $paragraph
    ): DOMElement {
        foreach ($paragraph->childNodes as $child) {
            if (
                $child instanceof DOMElement &&
                $child->localName === 'pPr'
            ) {
                return $child;
            }
        }

        $pPr = $paragraph->ownerDocument->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:pPr'
        );

        $paragraph->insertBefore($pPr, $paragraph->firstChild);

        return $pPr;
    }

    protected function rebuildOathHeadingParagraph(
        DOMElement $paragraph
    ): void {
        $document = $paragraph->ownerDocument;
        $pPr = $this->getOathParagraphProperties($paragraph);

        $this->setOathParagraphIndent(
            $pPr,
            '360',
            null
        );

        $this->setOathParagraphTabs(
            $pPr,
            'right',
            '8666'
        );

        $this->removeOathParagraphAlignment($pPr);

        $oldRun = null;

        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'r') {
                $oldRun = $child;
                break;
            }
        }

        $runProperties = null;

        if ($oldRun instanceof DOMElement) {
            $existing = $this->findOathRunProperties($oldRun);
            if ($existing instanceof DOMElement) {
                $runProperties = $existing->cloneNode(true);
            }
        }

        $this->clearOathParagraph($paragraph);
        $paragraph->appendChild($pPr);

        $this->appendOathTextRun(
            $paragraph,
            'Signed by:',
            $runProperties
        );

        $this->appendOathTabRun($paragraph, $runProperties);

        $this->appendOathTextRun(
            $paragraph,
            'Witnessed by:',
            $runProperties
        );
    }

    protected function rebuildOathSignerParagraph(
        DOMElement $paragraph,
        string $signerName,
        ?DOMNode $signerRunProperties,
        string $witnessName,
        ?DOMNode $witnessRunProperties
    ): void {
        $document = $paragraph->ownerDocument;
        $pPr = $this->getOathParagraphProperties($paragraph);

        $this->setOathParagraphIndent(
            $pPr,
            '360',
            null
        );

        $this->setOathParagraphTabs(
            $pPr,
            'right',
            '8666'
        );

        $this->removeOathParagraphAlignment($pPr);

        $this->clearOathParagraph($paragraph);
        $paragraph->appendChild($pPr);

        $this->appendOathTextRun(
            $paragraph,
            $signerName,
            $signerRunProperties
        );

        $this->appendOathTabRun(
            $paragraph,
            $signerRunProperties
        );

        $witnessProperties = $witnessRunProperties instanceof DOMNode
            ? $witnessRunProperties->cloneNode(true)
            : null;

        $this->forceOathBoldAndUnderlineRunProperties($witnessProperties);

        $this->appendOathTextRun(
            $paragraph,
            $witnessName,
            $witnessProperties
        );
    }

    protected function rebuildOathSecretaryParagraph(
        DOMElement $paragraph,
        ?DOMElement $sourceParagraph
    ): void {
        $pPr = $this->getOathParagraphProperties($paragraph);

        $this->setOathParagraphIndent(
            $pPr,
            '360',
            null
        );

        $this->setOathParagraphTabs(
            $pPr,
            'right',
            '8666'
        );

        $this->removeOathParagraphAlignment($pPr);

        $runProperties = null;

        if ($sourceParagraph instanceof DOMElement) {
            foreach ($sourceParagraph->childNodes as $child) {
                if (! $child instanceof DOMElement || $child->localName !== 'r') {
                    continue;
                }

                $text = trim(
                    preg_replace(
                        '/\s+/u',
                        ' ',
                        (string) $child->textContent
                    )
                );

                if ($text === '') {
                    continue;
                }

                $existing = $this->findOathRunProperties($child);

                if ($existing instanceof DOMElement) {
                    $runProperties = $existing->cloneNode(true);
                }

                break;
            }
        }

        $this->clearOathParagraph($paragraph);
        $paragraph->appendChild($pPr);

        $this->appendOathTabRun($paragraph, $runProperties);

        $this->appendOathTextRun(
            $paragraph,
            'BARANGAY SECRETARY',
            $runProperties
        );
    }

    protected function appendOathTextRun(
        DOMElement $paragraph,
        string $text,
        ?DOMNode $runProperties = null
    ): void {
        $document = $paragraph->ownerDocument;

        $run = $document->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:r'
        );

        if ($runProperties instanceof DOMNode) {
            $run->appendChild($runProperties->cloneNode(true));
        }

        $textNode = $document->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:t'
        );

        $textNode->appendChild(
            $document->createTextNode($text)
        );

        $run->appendChild($textNode);
        $paragraph->appendChild($run);
    }

    protected function appendOathTabRun(
        DOMElement $paragraph,
        ?DOMNode $runProperties = null
    ): void {
        $document = $paragraph->ownerDocument;

        $run = $document->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:r'
        );

        if ($runProperties instanceof DOMNode) {
            $run->appendChild($runProperties->cloneNode(true));
        }

        $run->appendChild(
            $document->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:tab'
            )
        );

        $paragraph->appendChild($run);
    }

    protected function setOathParagraphTabs(
        DOMElement $pPr,
        string $alignment,
        string $position
    ): void {
        $document = $pPr->ownerDocument;
        $tabs = null;

        foreach ($pPr->childNodes as $child) {
            if (
                $child instanceof DOMElement &&
                $child->localName === 'tabs'
            ) {
                $tabs = $child;
                break;
            }
        }

        if (! $tabs) {
            $tabs = $document->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:tabs'
            );
            $pPr->appendChild($tabs);
        }

        while ($tabs->firstChild !== null) {
            $tabs->removeChild($tabs->firstChild);
        }

        $tab = $document->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:tab'
        );

        $tab->setAttribute('w:val', $alignment);
        $tab->setAttribute('w:pos', $position);

        $tabs->appendChild($tab);
    }

    protected function setOathParagraphIndent(
        DOMElement $pPr,
        string $left,
        ?string $firstLine
    ): void {
        $document = $pPr->ownerDocument;
        $indent = null;

        foreach ($pPr->childNodes as $child) {
            if (
                $child instanceof DOMElement &&
                $child->localName === 'ind'
            ) {
                $indent = $child;
                break;
            }
        }

        if (! $indent) {
            $indent = $document->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:ind'
            );
            $pPr->appendChild($indent);
        }

        $indent->setAttribute('w:left', $left);

        if ($firstLine === null) {
            $indent->removeAttribute('w:firstLine');
        } else {
            $indent->setAttribute('w:firstLine', $firstLine);
        }
    }

    protected function removeOathParagraphAlignment(
        DOMElement $pPr
    ): void {
        foreach ($pPr->childNodes as $child) {
            if (
                $child instanceof DOMElement &&
                $child->localName === 'jc'
            ) {
                $pPr->removeChild($child);
                break;
            }
        }
    }

    protected function forceOathBoldRunProperties(
        ?DOMNode $properties
    ): void {
        if (! $properties instanceof DOMElement) {
            return;
        }

        $document = $properties->ownerDocument;
        $bold = null;

        foreach ($properties->childNodes as $child) {
            if (
                $child instanceof DOMElement &&
                $child->localName === 'b'
            ) {
                $bold = $child;
                break;
            }
        }

        if (! $bold) {
            $bold = $document->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:b'
            );
            $properties->appendChild($bold);
        }
    }

    /**
     * Make a signature name bold and underlined while preserving the
     * official template's other run formatting.
     */
    protected function forceOathBoldAndUnderlineRunProperties(
        ?DOMNode $properties
    ): void {
        if (! $properties instanceof DOMElement) {
            return;
        }

        $this->forceOathBoldRunProperties($properties);

        $document = $properties->ownerDocument;
        $underline = null;

        foreach ($properties->childNodes as $child) {
            if (
                $child instanceof DOMElement &&
                $child->localName === 'u'
            ) {
                $underline = $child;
                break;
            }
        }

        if (! $underline) {
            $underline = $document->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:u'
            );
            $properties->appendChild($underline);
        }

        $underline->setAttribute('w:val', 'single');
    }

    protected function cleanAndCenterOathParagraph(
        ?DOMElement $paragraph,
        string $text
    ): void {
        if (! $paragraph instanceof DOMElement) {
            return;
        }

        $pPr = $this->getOathParagraphProperties($paragraph);

        $this->removeOathParagraphAlignment($pPr);

        $jc = $paragraph->ownerDocument->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:jc'
        );
        $jc->setAttribute('w:val', 'center');
        $pPr->appendChild($jc);

        $this->setOathParagraphIndent($pPr, '0', null);

        $this->clearOathParagraph($paragraph);
        $paragraph->appendChild($pPr);

        /*
         * Preserve the official paragraph font by using a clean run with
         * the same 26pt formatting as the original template.
         */
        $runProperties = $paragraph->ownerDocument->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:rPr'
        );

        $fonts = $paragraph->ownerDocument->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:rFonts'
        );
        $fonts->setAttribute('w:asciiTheme', 'majorBidi');
        $fonts->setAttribute('w:hAnsiTheme', 'majorBidi');
        $fonts->setAttribute('w:cstheme', 'majorBidi');
        $runProperties->appendChild($fonts);

        $size = $paragraph->ownerDocument->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:sz'
        );
        $size->setAttribute('w:val', '26');
        $runProperties->appendChild($size);

        $sizeCs = $paragraph->ownerDocument->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:szCs'
        );
        $sizeCs->setAttribute('w:val', '26');
        $runProperties->appendChild($sizeCs);

        $this->appendOathTextRun(
            $paragraph,
            $text,
            $runProperties
        );
    }

    /**
     * Format one of the two upper signature columns.
     *
     * The expected order is:
     *   Signed/Witnessed by:
     *   NAME
     *   POSITION
     *
     * The name and position are centered and their paragraph spacing is
     * normalized so both columns sit on the same horizontal level.
     */
    protected function formatOathSignatureCell(
        DOMXPath $xpath,
        DOMElement $cell,
        string $name,
        bool $boldName
    ): void {
        $this->setOathCellVerticalAlignment($cell, 'top');

        $paragraphs = $xpath->query('./w:p', $cell);
        if (! $paragraphs) {
            return;
        }

        foreach ($paragraphs as $paragraph) {
            if (! $paragraph instanceof DOMElement) {
                continue;
            }

            $text = trim(preg_replace('/\s+/u', ' ', (string) $paragraph->textContent));
            if ($text === '') {
                continue;
            }

            if (
                str_contains($text, 'Signed by:') ||
                str_contains($text, 'Witnessed by:') ||
                str_contains($text, $name) ||
                str_contains($text, 'BARANGAY SECRETARY')
            ) {
                $this->setOathParagraphAlignment($paragraph, 'center');
            }

            if (str_contains($text, $name)) {
                $this->normalizeOathParagraphSpacing($paragraph);

                if ($boldName) {
                    $this->makeOathParagraphBold($paragraph);
                }
            }

            if (str_contains($text, 'BARANGAY SECRETARY')) {
                $this->normalizeOathParagraphSpacing($paragraph);
            }
        }
    }

    /**
     * Move an existing Word paragraph into the requested signature cell.
     */
    protected function moveOathParagraphContainingToCell(
        DOMXPath $xpath,
        DOMElement $table,
        string $textFragment,
        DOMElement $targetCell
    ): void {
        if ($textFragment === '') {
            return;
        }

        $paragraphs = $xpath->query('.//w:p', $table);
        if (! $paragraphs) {
            return;
        }

        foreach ($paragraphs as $paragraph) {
            if (! $paragraph instanceof DOMElement) {
                continue;
            }

            $paragraphText = trim(preg_replace('/\s+/u', ' ', (string) $paragraph->textContent));

            if (
                $paragraphText === '' ||
                ! str_contains($paragraphText, $textFragment)
            ) {
                continue;
            }

            $currentCell = $paragraph->parentNode;

            while (
                $currentCell instanceof DOMElement &&
                $currentCell->localName !== 'tc'
            ) {
                $currentCell = $currentCell->parentNode;
            }

            if (
                $currentCell instanceof DOMElement &&
                $currentCell->isSameNode($targetCell)
            ) {
                return;
            }

            $targetCell->appendChild($paragraph);
            return;
        }
    }

    /**
     * Set all relevant paragraphs in a signature cell to centered alignment.
     */
    protected function setOathCellParagraphAlignment(
        DOMXPath $xpath,
        DOMElement $cell,
        string $alignment
    ): void {
        $paragraphs = $xpath->query('.//w:p', $cell);

        if (! $paragraphs) {
            return;
        }

        foreach ($paragraphs as $paragraph) {
            if (! $paragraph instanceof DOMElement) {
                continue;
            }

            $this->setOathParagraphAlignment($paragraph, $alignment);
        }
    }

    /**
     * Set a specific paragraph's Word alignment.
     */
    protected function setOathParagraphAlignment(
        DOMElement $paragraph,
        string $alignment
    ): void {
        $paragraphProperties = $this->getOrCreateOathParagraphProperties($paragraph);

        $jc = null;

        foreach ($paragraphProperties->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'jc') {
                $jc = $child;
                break;
            }
        }

        if (! $jc) {
            $jc = $paragraph->ownerDocument->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:jc'
            );
            $paragraphProperties->appendChild($jc);
        }

        $jc->setAttribute('w:val', $alignment);
    }

    /**
     * Remove inherited paragraph spacing that can push the secretary name
     * below the signer name.
     */
    protected function normalizeOathParagraphSpacing(DOMElement $paragraph): void
    {
        $paragraphProperties = $this->getOrCreateOathParagraphProperties($paragraph);

        $spacing = null;

        foreach ($paragraphProperties->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'spacing') {
                $spacing = $child;
                break;
            }
        }

        if (! $spacing) {
            $spacing = $paragraph->ownerDocument->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:spacing'
            );
            $paragraphProperties->appendChild($spacing);
        }

        $spacing->setAttribute('w:before', '0');
        $spacing->setAttribute('w:after', '0');
        $spacing->setAttribute('w:line', '240');
        $spacing->setAttribute('w:lineRule', 'auto');
    }

    /**
     * Make every text run in a paragraph bold.
     */
    protected function makeOathParagraphBoldAndUnderlined(
        ?DOMElement $paragraph
    ): void {
        if (! $paragraph instanceof DOMElement) {
            return;
        }

        $this->makeOathParagraphBold($paragraph);

        foreach ($paragraph->getElementsByTagNameNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'r'
        ) as $run) {
            if (! $run instanceof DOMElement) {
                continue;
            }

            $runProperties = null;

            foreach ($run->childNodes as $child) {
                if ($child instanceof DOMElement && $child->localName === 'rPr') {
                    $runProperties = $child;
                    break;
                }
            }

            if (! $runProperties) {
                $runProperties = $paragraph->ownerDocument->createElementNS(
                    'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                    'w:rPr'
                );
                $run->insertBefore($runProperties, $run->firstChild);
            }

            $underline = null;

            foreach ($runProperties->childNodes as $child) {
                if ($child instanceof DOMElement && $child->localName === 'u') {
                    $underline = $child;
                    break;
                }
            }

            if (! $underline) {
                $underline = $paragraph->ownerDocument->createElementNS(
                    'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                    'w:u'
                );
                $runProperties->appendChild($underline);
            }

            $underline->setAttribute('w:val', 'single');
        }
    }

    protected function makeOathParagraphBold(DOMElement $paragraph): void
    {
        foreach ($paragraph->getElementsByTagNameNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'r'
        ) as $run) {
            if (! $run instanceof DOMElement) {
                continue;
            }

            $runProperties = null;

            foreach ($run->childNodes as $child) {
                if ($child instanceof DOMElement && $child->localName === 'rPr') {
                    $runProperties = $child;
                    break;
                }
            }

            if (! $runProperties) {
                $runProperties = $paragraph->ownerDocument->createElementNS(
                    'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                    'w:rPr'
                );
                $run->insertBefore($runProperties, $run->firstChild);
            }

            $bold = null;

            foreach ($runProperties->childNodes as $child) {
                if ($child instanceof DOMElement && $child->localName === 'b') {
                    $bold = $child;
                    break;
                }
            }

            if (! $bold) {
                $bold = $paragraph->ownerDocument->createElementNS(
                    'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                    'w:b'
                );
                $runProperties->appendChild($bold);
            }

            $bold->removeAttribute('w:val');
        }
    }

    /**
     * Vertically align a signature cell to the top of its row.
     */
    protected function setOathCellVerticalAlignment(
        DOMElement $cell,
        string $alignment
    ): void {
        $cellProperties = null;

        foreach ($cell->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'tcPr') {
                $cellProperties = $child;
                break;
            }
        }

        if (! $cellProperties) {
            $cellProperties = $cell->ownerDocument->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:tcPr'
            );
            $cell->insertBefore($cellProperties, $cell->firstChild);
        }

        $vAlign = null;

        foreach ($cellProperties->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'vAlign') {
                $vAlign = $child;
                break;
            }
        }

        if (! $vAlign) {
            $vAlign = $cell->ownerDocument->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:vAlign'
            );
            $cellProperties->appendChild($vAlign);
        }

        $vAlign->setAttribute('w:val', $alignment);
    }

    /**
     * Align a paragraph inside the Oath signature table by its text.
     */
    protected function setOathParagraphContainingAlignment(
        DOMXPath $xpath,
        DOMElement $table,
        string $textFragment,
        string $alignment
    ): void {
        $paragraphs = $xpath->query('.//w:p', $table);

        if (! $paragraphs) {
            return;
        }

        foreach ($paragraphs as $paragraph) {
            if (! $paragraph instanceof DOMElement) {
                continue;
            }

            $text = trim(preg_replace('/\s+/u', ' ', (string) $paragraph->textContent));

            if (str_contains($text, $textFragment)) {
                $this->setOathParagraphAlignment($paragraph, $alignment);
            }
        }
    }

    /**
     * Get or create Word paragraph properties.
     */
    protected function getOrCreateOathParagraphProperties(
        DOMElement $paragraph
    ): DOMElement {
        foreach ($paragraph->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'pPr') {
                return $child;
            }
        }

        $paragraphProperties = $paragraph->ownerDocument->createElementNS(
            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'w:pPr'
        );

        $paragraph->insertBefore($paragraphProperties, $paragraph->firstChild);

        return $paragraphProperties;
    }

    /**
     * Center the Oath signature table while preserving its existing structure.
     */
    protected function centerOathTable(DOMElement $table): void
    {
        $tableProperties = null;

        foreach ($table->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'tblPr') {
                $tableProperties = $child;
                break;
            }
        }

        if (! $tableProperties) {
            $tableProperties = $table->ownerDocument->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:tblPr'
            );
            $table->insertBefore($tableProperties, $table->firstChild);
        }

        $tableAlignment = null;

        foreach ($tableProperties->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'jc') {
                $tableAlignment = $child;
                break;
            }
        }

        if (! $tableAlignment) {
            $tableAlignment = $table->ownerDocument->createElementNS(
                'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                'w:jc'
            );
            $tableProperties->appendChild($tableAlignment);
        }

        $tableAlignment->setAttribute('w:val', 'center');
    }

    /**
     * Set paragraph alignment for a paragraph containing
     * a specific text fragment.
     */
    protected function setParagraphAlignmentContaining(
        DOMXPath $xpath,
        string $textFragment,
        string $alignment
    ): bool {
        if ($textFragment === '') {
            return false;
        }

        $paragraphs = $xpath->query('//w:p');

        if (! $paragraphs) {
            return false;
        }

        foreach ($paragraphs as $paragraph) {
            $text = trim(
                (string) $paragraph->textContent
            );

            if (
                $text === '' ||
                ! str_contains(
                    $text,
                    $textFragment
                )
            ) {
                continue;
            }

            $paragraphProperties = null;

            foreach ($paragraph->childNodes as $child) {
                if (
                    $child instanceof \DOMElement &&
                    $child->localName === 'pPr'
                ) {
                    $paragraphProperties = $child;
                    break;
                }
            }

            if (! $paragraphProperties) {
                $paragraphProperties =
                    $paragraph->ownerDocument
                        ->createElementNS(
                            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                            'w:pPr'
                        );

                $paragraph->insertBefore(
                    $paragraphProperties,
                    $paragraph->firstChild
                );
            }

            $jc = null;

            foreach (
                $paragraphProperties->childNodes as $child
            ) {
                if (
                    $child instanceof \DOMElement &&
                    $child->localName === 'jc'
                ) {
                    $jc = $child;
                    break;
                }
            }

            if (! $jc) {
                $jc =
                    $paragraph->ownerDocument
                        ->createElementNS(
                            'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                            'w:jc'
                        );

                $paragraphProperties->appendChild(
                    $jc
                );
            }

            $jc->setAttribute(
                'w:val',
                $alignment
            );

            return true;
        }

        return false;
    }

    /**
     * Build the resident's address.
     */
    protected function buildResidentAddress(
        object $resident
    ): string {
        foreach ([
            'full_address',
            'address',
            'complete_address',
        ] as $field) {
            if (
                isset($resident->{$field}) &&
                trim((string) $resident->{$field}) !== ''
            ) {
                return trim(
                    (string) $resident->{$field}
                );
            }
        }

        $parts = [];

        foreach ([
            'house_number',
            'street',
            'street_name',
            'sitio',
            'purok',
            'barangay',
            'municipality',
            'province',
        ] as $field) {
            if (
                isset($resident->{$field}) &&
                trim((string) $resident->{$field}) !== ''
            ) {
                $parts[] = trim(
                    (string) $resident->{$field}
                );
            }
        }

        if (! empty($parts)) {
            return implode(
                ', ',
                $parts
            );
        }

        return 'Barangay Poblacion, Bani, Pangasinan';
    }

    /**
     * Format resident birth date.
     */
    protected function formatBirthDate(
        mixed $birthDate
    ): string {
        if (empty($birthDate)) {
            return '';
        }

        try {
            return \Carbon\Carbon::parse(
                $birthDate
            )->format('F j, Y');
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Format civil status.
     */
    protected function formatCivilStatus(
        mixed $civilStatus
    ): string {
        if (
            $civilStatus === null ||
            trim((string) $civilStatus) === ''
        ) {
            return '';
        }

        return ucwords(
            strtolower(
                trim((string) $civilStatus)
            )
        );
    }

    /**
     * Generate an ordinal date phrase.
     *
     * Example:
     * 25th day of September 2026
     */
    protected function ordinalDatePhraseFromDate(
        ?string $date
    ): string {
        if (! $date) {
            return '';
        }

        try {
            $carbon = \Carbon\Carbon::parse(
                $date
            );
        } catch (\Throwable) {
            return '';
        }

        return $this->ordinalDay(
            (int) $carbon->format('j')
        ) .
            ' day of ' .
            $carbon->format('F') .
            ' ' .
            $carbon->format('Y');
    }

    /**
     * Existing date helper used by the official templates.
     */
    protected function ordinalDatePhrase(
        ?string $month = null
    ): string {
        $day = (int) now()->format('j');

        $month = $month ??
            now()->format('F');

        return $this->ordinalDay($day) .
            ' day of ' .
            $month .
            ' ' .
            now()->format('Y');
    }

    /**
     * Generate ordinal day suffix.
     */
    protected function ordinalDay(
        int $day
    ): string {
        $suffix = 'th';

        if (
            $day % 100 < 11 ||
            $day % 100 > 13
        ) {
            $suffix = match ($day % 10) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th',
            };
        }

        return $day . $suffix;
    }
}

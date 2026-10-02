<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use ZipArchive;
use DOMDocument;
use DOMElement;
use DOMXPath;

class DocumentConversionService
{
    /**
     * Convert a generated DOCX file into PDF using the
     * local Gotenberg API.
     *
     * IMPORTANT:
     *
     * The official DOCX template is never modified.
     * Only the generated copy is processed.
     *
     * The conversion process intentionally preserves the
     * original horizontal page layout of the official template.
     *
     * We DO NOT change the left/right page margins because
     * those margins can affect:
     *
     * - logos
     * - header positioning
     * - centered content
     * - tables
     * - text boxes
     * - floating objects
     * - official document layout
     *
     * If the generated document exceeds one page, only
     * vertical spacing and font size are progressively reduced.
     *
     * @param string $docxPath Absolute path to generated DOCX.
     * @return string Absolute path to generated PDF.
     */
    public function convertDocxToPdf(
        string $docxPath
    ): string {
        if (! is_file($docxPath)) {
            throw new RuntimeException(
                'The DOCX file to be converted could not be found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generated PDF directory
        |--------------------------------------------------------------------------
        */

        $pdfDirectory = storage_path(
            'app/private/generated-documents'
        );

        if (
            ! is_dir($pdfDirectory) &&
            ! mkdir(
                $pdfDirectory,
                0755,
                true
            ) &&
            ! is_dir($pdfDirectory)
        ) {
            throw new RuntimeException(
                'The generated-document directory could not be created.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PDF filename
        |--------------------------------------------------------------------------
        */

        $pdfFileName =
            pathinfo(
                basename($docxPath),
                PATHINFO_FILENAME
            ) . '.pdf';

        $pdfPath =
            $pdfDirectory .
            DIRECTORY_SEPARATOR .
            $pdfFileName;

        /*
        |--------------------------------------------------------------------------
        | FIRST CONVERSION
        |--------------------------------------------------------------------------
        |
        | Always convert the generated DOCX exactly as it currently
        | exists before applying any fitting.
        |
        | This is extremely important because the official template's
        | original layout should be preserved whenever possible.
        |
        */

        $response = $this->sendToGotenberg(
            $docxPath
        );

        $this->savePdf(
            $response,
            $pdfPath
        );

        /*
        |--------------------------------------------------------------------------
        | Check page count
        |--------------------------------------------------------------------------
        */

        $pageCount = $this->countPdfPages(
            $response
        );

        /*
        |--------------------------------------------------------------------------
        | Already one page
        |--------------------------------------------------------------------------
        |
        | If the original generated DOCX already fits on one page,
        | STOP HERE.
        |
        | No margins.
        | No font changes.
        | No layout changes.
        |
        */

        if ($pageCount <= 1) {
            return $pdfPath;
        }

        /*
        |--------------------------------------------------------------------------
        | One-page fitting
        |--------------------------------------------------------------------------
        |
        | The document is larger than one page.
        |
        | We now make small vertical/typographic adjustments.
        |
        | IMPORTANT:
        |
        | We intentionally DO NOT change left/right page margins.
        |
        */

        $maxAttempts = 6;

        for (
            $attempt = 1;
            $attempt <= $maxAttempts;
            $attempt++
        ) {
            $this->compactGeneratedDocx(
                $docxPath,
                $attempt
            );

            $response = $this->sendToGotenberg(
                $docxPath
            );

            $this->savePdf(
                $response,
                $pdfPath
            );

            $pageCount = $this->countPdfPages(
                $response
            );

            if ($pageCount <= 1) {
                return $pdfPath;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Final fallback
        |--------------------------------------------------------------------------
        |
        | Return the last generated PDF even if it still contains
        | more than one page.
        |
        */

        return $pdfPath;
    }

    /**
     * Send generated DOCX to Gotenberg.
     */
    protected function sendToGotenberg(
        string $docxPath
    ): Response {
        if (! is_file($docxPath)) {
            throw new RuntimeException(
                'The DOCX file to be converted could not be found.'
            );
        }

        $gotenbergUrl = rtrim(
            (string) config(
                'services.gotenberg.url',
                'http://127.0.0.1:3000'
            ),
            '/'
        );

        $handle = fopen(
            $docxPath,
            'r'
        );

        if ($handle === false) {
            throw new RuntimeException(
                'The generated DOCX file could not be opened for conversion.'
            );
        }

        try {
            $response = Http::timeout(120)
                ->attach(
                    'files',
                    $handle,
                    basename($docxPath)
                )
                ->post(
                    $gotenbergUrl .
                    '/forms/libreoffice/convert'
                );
        } finally {
            fclose($handle);
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                'Gotenberg failed to convert the DOCX document. ' .
                'HTTP status: ' .
                $response->status() .
                '.'
            );
        }

        return $response;
    }

    /**
     * Save generated PDF.
     */
    protected function savePdf(
        Response $response,
        string $pdfPath
    ): void {
        $written = file_put_contents(
            $pdfPath,
            $response->body()
        );

        if ($written === false) {
            throw new RuntimeException(
                'The generated PDF could not be saved.'
            );
        }
    }

    /**
     * Count PDF pages.
     *
     * LibreOffice-generated PDFs contain page objects using:
     *
     * /Type /Page
     *
     * This deliberately does not count:
     *
     * /Type /Pages
     */
    protected function countPdfPages(
        Response $response
    ): int {
        $pdf = $response->body();

        if (
            $pdf === '' ||
            ! str_starts_with(
                $pdf,
                '%PDF-'
            )
        ) {
            return 0;
        }

        preg_match_all(
            '/\/Type\s*\/Page\b/',
            $pdf,
            $matches
        );

        return count(
            $matches[0] ?? []
        );
    }

    /**
     * Compact only the generated DOCX copy.
     *
     * IMPORTANT:
     *
     * We only process word/document.xml.
     *
     * Headers, footers, logos, images and other official
     * template structures are intentionally left untouched.
     */
    protected function compactGeneratedDocx(
        string $docxPath,
        int $attempt
    ): void {
        $zip = new ZipArchive();

        if (
            $zip->open(
                $docxPath
            ) !== true
        ) {
            throw new RuntimeException(
                'The generated DOCX package could not be opened for one-page fitting.'
            );
        }

        try {
            $parts = [];

            for (
                $index = 0;
                $index < $zip->numFiles;
                $index++
            ) {
                $name =
                    $zip->getNameIndex($index);

                /*
                |--------------------------------------------------------------------------
                | Only main document
                |--------------------------------------------------------------------------
                |
                | DO NOT modify:
                |
                | word/header1.xml
                | word/header2.xml
                | word/footer1.xml
                |
                | because official logos and header structures may
                | be located there.
                |
                */

                if (
                    $name === 'word/document.xml'
                ) {
                    $parts[] = $name;
                }
            }

            foreach ($parts as $part) {
                $xml =
                    $zip->getFromName($part);

                if ($xml === false) {
                    continue;
                }

                $updatedXml =
                    $this->compactWordDocumentXml(
                        $xml,
                        $attempt
                    );

                if ($updatedXml === $xml) {
                    continue;
                }

                if (! $zip->deleteName($part)) {
                    throw new RuntimeException(
                        "The generated Word XML part could not be replaced: {$part}"
                    );
                }

                if (! $zip->addFromString(
                    $part,
                    $updatedXml
                )) {
                    throw new RuntimeException(
                        "The compacted Word XML part could not be written: {$part}"
                    );
                }
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Compact main document XML.
     */
    protected function compactWordDocumentXml(
        string $xml,
        int $attempt
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
        |--------------------------------------------------------------------------
        | Progressive fitting settings
        |--------------------------------------------------------------------------
        |
        | Notice that there is NO left/right margin setting.
        |
        | This is intentional.
        |
        */

        $settings = match (true) {
            $attempt <= 1 => [
                'fontReduction' => 1,
                'minimumFont' => 10,
                'paragraphSpacing' => 0,
                'lineSpacing' => 240,
            ],

            $attempt === 2 => [
                'fontReduction' => 2,
                'minimumFont' => 10,
                'paragraphSpacing' => 0,
                'lineSpacing' => 230,
            ],

            $attempt === 3 => [
                'fontReduction' => 2,
                'minimumFont' => 9,
                'paragraphSpacing' => 0,
                'lineSpacing' => 220,
            ],

            $attempt === 4 => [
                'fontReduction' => 3,
                'minimumFont' => 9,
                'paragraphSpacing' => 0,
                'lineSpacing' => 210,
            ],

            $attempt === 5 => [
                'fontReduction' => 3,
                'minimumFont' => 8,
                'paragraphSpacing' => 0,
                'lineSpacing' => 200,
            ],

            default => [
                'fontReduction' => 4,
                'minimumFont' => 8,
                'paragraphSpacing' => 0,
                'lineSpacing' => 190,
            ],
        };

        /*
        |--------------------------------------------------------------------------
        | DO NOT MODIFY SECTION MARGINS
        |--------------------------------------------------------------------------
        |
        | This is the main fix.
        |
        | The official templates already contain the correct:
        |
        | - left margin
        | - right margin
        | - logo positioning
        | - header positioning
        | - table positioning
        | - document width
        |
        | Changing these values was causing the logos and other
        | horizontal elements to move.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Compact paragraphs vertically
        |--------------------------------------------------------------------------
        */

        $paragraphs = $xpath->query(
            '//w:p'
        );

        if ($paragraphs) {
            foreach ($paragraphs as $paragraph) {
                if (
                    ! $paragraph instanceof DOMElement
                ) {
                    continue;
                }

                $this->compactParagraph(
                    $paragraph,
                    $settings['paragraphSpacing'],
                    $settings['lineSpacing']
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Compact font sizes
        |--------------------------------------------------------------------------
        */

        $runs = $xpath->query(
            '//w:r'
        );

        if ($runs) {
            foreach ($runs as $run) {
                if (
                    ! $run instanceof DOMElement
                ) {
                    continue;
                }

                $this->compactRunFont(
                    $run,
                    $settings['fontReduction'],
                    $settings['minimumFont']
                );
            }
        }

        return $dom->saveXML();
    }

    /**
     * Compact paragraph spacing and line spacing.
     *
     * This only affects vertical document flow.
     *
     * It does NOT modify:
     *
     * - paragraph alignment
     * - indentation
     * - left/right margins
     * - table width
     * - image position
     * - logo position
     */
    protected function compactParagraph(
        DOMElement $paragraph,
        int $paragraphSpacing,
        int $lineSpacing
    ): void {
        $paragraphProperties = null;

        foreach (
            $paragraph->childNodes as $child
        ) {
            if (
                $child instanceof DOMElement &&
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

        $spacing = null;

        foreach (
            $paragraphProperties->childNodes as $child
        ) {
            if (
                $child instanceof DOMElement &&
                $child->localName === 'spacing'
            ) {
                $spacing = $child;
                break;
            }
        }

        if (! $spacing) {
            $spacing =
                $paragraph->ownerDocument
                    ->createElementNS(
                        'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
                        'w:spacing'
                    );

            $paragraphProperties->appendChild(
                $spacing
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Remove extra paragraph spacing
        |--------------------------------------------------------------------------
        */

        $spacing->setAttribute(
            'w:before',
            (string) $paragraphSpacing
        );

        $spacing->setAttribute(
            'w:after',
            (string) $paragraphSpacing
        );

        /*
        |--------------------------------------------------------------------------
        | Compact line spacing
        |--------------------------------------------------------------------------
        */

        $spacing->setAttribute(
            'w:line',
            (string) $lineSpacing
        );

        $spacing->setAttribute(
            'w:lineRule',
            'auto'
        );
    }

    /**
     * Reduce font size progressively.
     *
     * The font family itself is never changed.
     */
    protected function compactRunFont(
        DOMElement $run,
        int $fontReduction,
        int $minimumFont
    ): void {
        $runProperties = null;

        foreach (
            $run->childNodes as $child
        ) {
            if (
                $child instanceof DOMElement &&
                $child->localName === 'rPr'
            ) {
                $runProperties = $child;
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Do not invent run properties
        |--------------------------------------------------------------------------
        |
        | This preserves the original official template's
        | font configuration.
        |
        */

        if (! $runProperties) {
            return;
        }

        foreach ([
            'sz',
            'szCs',
        ] as $fontSizeElementName) {
            $fontSizeElement = null;

            foreach (
                $runProperties->childNodes as $child
            ) {
                if (
                    $child instanceof DOMElement &&
                    $child->localName ===
                    $fontSizeElementName
                ) {
                    $fontSizeElement = $child;
                    break;
                }
            }

            if (! $fontSizeElement) {
                continue;
            }

            $currentHalfPoints =
                (int) $fontSizeElement->getAttribute(
                    'w:val'
                );

            if ($currentHalfPoints <= 0) {
                continue;
            }

            $currentPoints =
                $currentHalfPoints / 2;

            /*
            |--------------------------------------------------------------------------
            | Reduce only when necessary.
            |--------------------------------------------------------------------------
            */

            $newPoints = max(
                $minimumFont,
                $currentPoints -
                $fontReduction
            );

            /*
            |--------------------------------------------------------------------------
            | Do not enlarge text.
            |--------------------------------------------------------------------------
            */

            if ($newPoints >= $currentPoints) {
                continue;
            }

            $newHalfPoints =
                (string) round(
                    $newPoints * 2
                );

            $fontSizeElement->setAttribute(
                'w:val',
                $newHalfPoints
            );
        }
    }
}

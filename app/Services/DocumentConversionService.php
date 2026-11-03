<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use ZipArchive;
use DOMDocument;
use DOMElement;
use DOMXPath;

class DocumentConversionService
{
    /**
     * Maximum number of extra fitting attempts after the first conversion.
     */
    protected const MAX_FIT_ATTEMPTS = 3;

    /**
     * Convert a generated DOCX file into PDF using the
     * local Gotenberg API.
     *
     * IMPORTANT:
     *
     * The official DOCX template is never modified.
     * Only the generated copy is processed.
     *
     * Conversion strategy:
     *
     * 1. Convert the generated DOCX once.
     * 2. If it already fits on one page, return immediately.
     * 3. Otherwise, up to MAX_FIT_ATTEMPTS times, restore the pristine
     *    generated DOCX, apply a slightly stronger compaction and convert
     *    again, stopping as soon as the PDF has one page.
     * 4. The actual PDF page count and page size are verified at the end.
     *
     * The paper size and orientation of the DOCX are preserved; the PDF
     * is produced at that same size. Text is never deleted, and fonts are
     * never reduced below the readable minimum.
     *
     * NOTE: this service only controls the generated PDF. The physical
     * printout is also affected by the printer driver, the paper size set
     * in the printer, and options such as "Fit to page". For a faithful
     * printout, print the PDF at "Actual size" / 100% on the same paper size.
     *
     * The original horizontal page layout is preserved.
     *
     * We DO NOT change:
     *
     * - left margins
     * - right margins
     * - logo positioning
     * - header positioning
     * - tables
     * - floating objects
     * - document width
     *
     * @param string $docxPath Absolute path to generated DOCX.
     * @return string Absolute path to generated PDF.
     */
    public function convertDocxToPdf(
        string $docxPath,
        ?string $fitKey = null
    ): string {
        $startedAt = microtime(true);
        $conversions = 0;
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
        | Page size / orientation of the generated DOCX
        |--------------------------------------------------------------------------
        |
        | The DOCX page size and orientation are never changed. They are read
        | only so the finished PDF can be verified against them.
        |
        */

        $expectedPageSize = $this->readDocxPageSize(
            $docxPath
        );

        /*
        |--------------------------------------------------------------------------
        | Pristine copy of the generated DOCX
        |--------------------------------------------------------------------------
        |
        | Every fitting attempt starts again from this copy instead of
        | stacking reductions on top of the previous attempt, so the result
        | uses the LEAST compaction that is enough to fit one page.
        |
        */

        $originalDocx = file_get_contents(
            $docxPath
        );

        if ($originalDocx === false) {
            throw new RuntimeException(
                'The generated DOCX file could not be read for conversion.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FIRST CONVERSION
        |--------------------------------------------------------------------------
        |
        | Always convert the generated DOCX exactly as it currently exists.
        |
        | If it already fits on one page, we immediately return.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | PERFORMANCE: remember the fitting level per document type
        |--------------------------------------------------------------------------
        |
        | Each Gotenberg/LibreOffice conversion costs seconds. If this document
        | type needed compaction level N last time, start at level N instead of
        | wasting 1..N conversions rediscovering it on every request.
        |
        */

        $fitCacheKey = $fitKey
            ? 'docfit:' . md5($fitKey)
            : null;

        $attempt = $fitCacheKey
            ? min((int) Cache::get($fitCacheKey, 0), self::MAX_FIT_ATTEMPTS)
            : 0;

        if ($attempt > 0) {
            $this->compactGeneratedDocx($docxPath, $attempt);
        }

        $response = $this->sendToGotenberg($docxPath);
        $conversions++;

        $this->savePdf($response, $pdfPath);

        $pageCount = $this->countPdfPages($response);

        /*
        |--------------------------------------------------------------------------
        | AUTOMATIC ONE-PAGE FITTING (escalates only when still > 1 page)
        |--------------------------------------------------------------------------
        */

        while (
            $pageCount > 1 &&
            $attempt < self::MAX_FIT_ATTEMPTS
        ) {
            $attempt++;

            if (file_put_contents($docxPath, $originalDocx) === false) {
                throw new RuntimeException(
                    'The generated DOCX could not be restored for one-page fitting.'
                );
            }

            $this->compactGeneratedDocx($docxPath, $attempt);

            $response = $this->sendToGotenberg($docxPath);
            $conversions++;

            $this->savePdf($response, $pdfPath);

            $pageCount = $this->countPdfPages($response);
        }

        if ($fitCacheKey && $pageCount === 1) {
            Cache::forever($fitCacheKey, $attempt);
        }

        Log::info('DOCX to PDF conversion finished.', [
            'pdf' => basename($pdfPath),
            'conversions' => $conversions,
            'fit_level' => $attempt,
            'pages' => $pageCount,
            'seconds' => round(microtime(true) - $startedAt, 2),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Final verification of the actual PDF
        |--------------------------------------------------------------------------
        |
        | The valid PDF is always returned, even when the content truly
        | cannot fit on one page at a readable size. The text is never
        | deleted and never made smaller than the readable minimum.
        |
        */

        if ($pageCount > 1) {
            Log::warning(
                'Generated PDF could not be fitted on one readable page.',
                [
                    'pdf' => basename($pdfPath),
                    'pages' => $pageCount,
                ]
            );
        }

        $actualPageSize = $this->readPdfPageSize(
            $response->body()
        );

        if (
            $expectedPageSize !== null &&
            $actualPageSize !== null &&
            (
                abs($expectedPageSize['width'] - $actualPageSize['width']) > 3 ||
                abs($expectedPageSize['height'] - $actualPageSize['height']) > 3
            )
        ) {
            Log::warning(
                'Generated PDF page size differs from the DOCX page size.',
                [
                    'pdf' => basename($pdfPath),
                    'docx_points' => $expectedPageSize,
                    'pdf_points' => $actualPageSize,
                ]
            );
        }

        return $pdfPath;
    }

    /**
     * Read the first page size from the DOCX, in points.
     *
     * Width and height are returned exactly as stored, so landscape
     * documents report width > height.
     */
    protected function readDocxPageSize(
        string $docxPath
    ): ?array {
        $zip = new ZipArchive();

        if ($zip->open($docxPath) !== true) {
            return null;
        }

        $xml = $zip->getFromName(
            'word/document.xml'
        );

        $zip->close();

        if (
            $xml === false ||
            ! preg_match(
                '/<w:pgSz\b[^>]*>/',
                $xml,
                $tag
            ) ||
            ! preg_match('/\bw:w="(\d+)"/', $tag[0], $width) ||
            ! preg_match('/\bw:h="(\d+)"/', $tag[0], $height)
        ) {
            return null;
        }

        return [
            'width' => (int) $width[1] / 20,
            'height' => (int) $height[1] / 20,
        ];
    }

    /**
     * Read the first page size from the PDF, in points.
     */
    protected function readPdfPageSize(
        string $pdf
    ): ?array {
        if (
            ! preg_match(
                '/\/MediaBox\s*\[\s*(-?[\d.]+)\s+(-?[\d.]+)\s+(-?[\d.]+)\s+(-?[\d.]+)\s*\]/',
                $pdf,
                $box
            )
        ) {
            return null;
        }

        return [
            'width' => abs((float) $box[3] - (float) $box[1]),
            'height' => abs((float) $box[4] - (float) $box[2]),
        ];
    }

    /**
     * Send generated DOCX to Gotenberg.
     *
     * The timeout is intentionally limited so a broken or unavailable
     * Gotenberg service cannot make Preview / Print hang for many minutes.
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
            /*
            |--------------------------------------------------------------------------
            | Gotenberg HTTP timeout
            |--------------------------------------------------------------------------
            |
            | Connection timeout:
            | 5 seconds
            |
            | Conversion/request timeout:
            | 45 seconds
            |
            | This prevents a broken Gotenberg service from causing
            | a 10+ minute Preview / Print wait.
            |
            */

            $response = Http::connectTimeout(5)
                ->timeout(45)
                ->attach(
                    'files',
                    $handle,
                    basename($docxPath)
                )
                ->post(
                    $gotenbergUrl .
                    '/forms/libreoffice/convert'
                );
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                'Unable to connect to the local Gotenberg PDF conversion service. ' .
                'Please make sure Gotenberg is running on ' .
                $gotenbergUrl .
                '. Error: ' .
                $exception->getMessage(),
                0,
                $exception
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

        /*
        |--------------------------------------------------------------------------
        | Validate response body
        |--------------------------------------------------------------------------
        */

        $body = $response->body();

        if (
            $body === '' ||
            ! str_starts_with(
                $body,
                '%PDF-'
            )
        ) {
            throw new RuntimeException(
                'Gotenberg returned an invalid PDF response.'
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

        $pageObjects = count(
            $matches[0] ?? []
        );

        /*
         * Fallback for PDFs whose page objects are not plainly readable:
         * use the /Count value of the page tree root.
         */
        preg_match_all(
            '/\/Type\s*\/Pages\b[^>]*?\/Count\s+(\d+)|\/Count\s+(\d+)[^>]*?\/Type\s*\/Pages\b/s',
            $pdf,
            $countMatches
        );

        $treeCount = 0;

        foreach (array_merge(
            $countMatches[1] ?? [],
            $countMatches[2] ?? []
        ) as $value) {
            $treeCount = max(
                $treeCount,
                (int) $value
            );
        }

        return max(
            $pageObjects,
            $treeCount
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
        | One safe compaction profile
        |--------------------------------------------------------------------------
        |
        | Only vertical spacing and font size are reduced.
        |
        | Horizontal layout remains untouched.
        |
        */

        $settings = match (true) {
            $attempt <= 1 => [
                'fontReduction' => 1,
                'minimumFont' => 10,
                'paragraphSpacing' => 0,
                'lineSpacing' => 240,
                'verticalMargin' => null,
            ],

            $attempt === 2 => [
                'fontReduction' => 2,
                'minimumFont' => 9,
                'paragraphSpacing' => 0,
                'lineSpacing' => 240,
                'verticalMargin' => null,
            ],

            default => [
                'fontReduction' => 3,
                'minimumFont' => 9,
                'paragraphSpacing' => 0,
                'lineSpacing' => 230,
                'verticalMargin' => 720,
            ],
        };

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

        /*
        |--------------------------------------------------------------------------
        | Smaller top / bottom page margins (last attempt only)
        |--------------------------------------------------------------------------
        |
        | Margins are only ever reduced, never enlarged. Left and right
        | margins, header/footer distance, paper size and orientation
        | are not touched.
        |
        */

        if ($settings['verticalMargin'] !== null) {
            $pageMargins = $xpath->query(
                '//w:sectPr/w:pgMar'
            );

            if ($pageMargins) {
                foreach ($pageMargins as $pageMargin) {
                    if (
                        ! $pageMargin instanceof DOMElement
                    ) {
                        continue;
                    }

                    foreach (['w:top', 'w:bottom'] as $side) {
                        $current = (int) $pageMargin->getAttribute(
                            $side
                        );

                        if ($current > $settings['verticalMargin']) {
                            $pageMargin->setAttribute(
                                $side,
                                (string) $settings['verticalMargin']
                            );
                        }
                    }
                }
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

<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\DocumentTemplate;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

class DocumentTemplateService
{
    public function __construct(
        protected DocumentConversionService $documentConversionService
    ) {
    }

    /**
     * Generate a populated DOCX document from the
     * registered official barangay template.
     *
     * This method preserves the existing DOCX generation
     * workflow and returns the absolute DOCX file path.
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

        $templatePath = storage_path(
            'app/private/' . $template->file_path
        );

        if (! is_file($templatePath)) {
            throw new RuntimeException(
                'The registered official document template file could not be found.'
            );
        }

        $document->loadMissing('resident');

        $resident = $document->resident;

        if (! $resident) {
            throw new RuntimeException(
                'The resident associated with this document request could not be found.'
            );
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        /*
         * Resident information
         */
        $fullName = trim(implode(' ', array_filter([
            $resident->first_name,
            $resident->middle_name,
            $resident->last_name,
            $resident->suffix,
        ])));

        $civilStatus = $resident->civil_status ?? '';

        /*
         * Common document/request information
         */
        $values = [
            'request_code' => $document->request_code,
            'document_type' => $document->document_type,
            'resident_name' => $fullName,
            'first_name' => $resident->first_name ?? '',
            'middle_name' => $resident->middle_name ?? '',
            'last_name' => $resident->last_name ?? '',
            'suffix' => $resident->suffix ?? '',
            'civil_status' => $civilStatus,
            'purpose' => $document->purpose ?? '',
            'remarks' => $document->remarks ?? '',
            'date_requested' => optional($document->requested_at)->format('F j, Y') ?? '',
            'date_issued' => now()->format('F j, Y'),
        ];

        foreach ($values as $placeholder => $value) {
            $templateProcessor->setValue(
                $placeholder,
                $value
            );
        }

        /*
         * Create the private generated-document directory.
         */
        $directory = storage_path(
            'app/private/generated-documents'
        );

        if (! is_dir($directory)) {
            mkdir(
                $directory,
                0755,
                true
            );
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

        $templateProcessor->saveAs($outputPath);

        return $outputPath;
    }

    /**
     * Generate a populated DOCX document and convert it
     * into a printable PDF using the local Gotenberg API.
     *
     * Returns the absolute PDF file path.
     */
    public function generatePdf(DocumentRequest $document): string
    {
        $docxPath = $this->generate($document);

        return $this->documentConversionService
            ->convertDocxToPdf($docxPath);
    }
}

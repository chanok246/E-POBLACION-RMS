<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentTemplateController extends Controller
{
    /**
     * Display the registered official document templates.
     */
    public function index(): View
    {
        $templates = DocumentTemplate::query()
            ->orderBy('document_type')
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->get();

        return view(
            'document-templates.index',
            compact('templates')
        );
    }


    /**
     * Display the template upload form.
     */
    public function create(): View
    {
        $documentTypes = [
            'Barangay Clearance',
            'Certificate of Residency',
            'Certificate of Indigency',
            'Business Permit',
        ];

        return view(
            'document-templates.create',
            compact('documentTypes')
        );
    }


    /**
     * Store a new official document template.
     */
    public function store(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $request->validate([
            'document_type' => [
                'required',
                Rule::in([
                    'Barangay Clearance',
                    'Certificate of Residency',
                    'Certificate of Indigency',
                    'Business Permit',
                ]),
            ],

            'template_name' => [
                'required',
                'string',
                'max:255',
            ],

            'template_file' => [
                'required',
                'file',
                'mimes:docx',
                'max:5120',
            ],
        ]);

        /*
         * Deactivate any currently active template for the
         * same document type.
         *
         * This ensures only one official template is active
         * for a document type at a time.
         */
        DocumentTemplate::query()
            ->where(
                'document_type',
                $validated['document_type']
            )
            ->where('is_active', true)
            ->update([
                'is_active' => false,
            ]);

        /*
         * Store the official template in Laravel's
         * private application storage.
         */
        $path = $request->file('template_file')->store(
            'document-templates',
            'local'
        );

        $template = DocumentTemplate::create([
            'document_type' => $validated['document_type'],
            'template_name' => $validated['template_name'],
            'file_path' => $path,
            'file_type' => 'docx',
            'is_active' => true,
        ]);

        /*
         * Record the template upload in the
         * system-wide Audit Trail.
         */
        $auditLog->log(
            action: 'Uploaded',
            module: 'Document Templates',
            record: $template,
            newValues: [
                'document_type' => $template->document_type,
                'template_name' => $template->template_name,
                'file_path' => $template->file_path,
                'file_type' => $template->file_type,
                'is_active' => $template->is_active,
            ],
            description:
                'Uploaded and activated an official barangay document template.'
        );

        return redirect()
            ->route('document-templates.index')
            ->with(
                'success',
                'Official document template uploaded successfully.'
            );
    }


    /**
     * Deactivate an official template.
     */
    public function deactivate(
        DocumentTemplate $documentTemplate,
        AuditLogService $auditLog
    ): RedirectResponse {
        if (! $documentTemplate->is_active) {
            return back()->with(
                'info',
                'This document template is already inactive.'
            );
        }

        $oldValues = [
            'is_active' => $documentTemplate->is_active,
        ];

        $documentTemplate->update([
            'is_active' => false,
        ]);

        $auditLog->log(
            action: 'Deactivated',
            module: 'Document Templates',
            record: $documentTemplate,
            oldValues: $oldValues,
            newValues: [
                'is_active' => $documentTemplate->is_active,
            ],
            description:
                'Deactivated an official barangay document template.'
        );

        return back()->with(
            'success',
            'Document template deactivated successfully.'
        );
    }
}

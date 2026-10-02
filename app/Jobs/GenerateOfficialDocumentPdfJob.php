<?php

namespace App\Jobs;

use App\Models\DocumentRequest;
use App\Services\DocumentConversionService;
use App\Services\OfficialDocumentTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

/**
 * Pre-generates ("warms up") the official DOCX -> PDF document for a
 * document request right after it is approved, so that by the time the
 * officer actually clicks "Preview and print document", the PDF is
 * already sitting in cache and DocumentRequestController::advance()/
 * print()/preview() just serve the existing file instead of generating
 * it on the spot.
 *
 * IMPORTANT - nothing about the existing on-demand generation was
 * removed or changed. This job is purely "best effort": if it fails for
 * any reason, it fails silently (after reporting the exception) and the
 * existing getOrGeneratePdf() logic in DocumentRequestController remains
 * the safety net that generates the PDF synchronously, exactly as it did
 * before this job existed.
 *
 * This is dispatched with ->afterResponse() at the call site, which
 * means it runs correctly whether QUEUE_CONNECTION is "sync" (no queue
 * worker needed at all - it simply runs right after the HTTP response
 * has already been sent to the browser) or a real queue driver such as
 * "database"/"redis" (in which case a `php artisan queue:work` worker
 * will pick it up and run it fully in the background). No code changes
 * are needed later if a real queue worker is added.
 */
class GenerateOfficialDocumentPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Only try once - this is a "warm up" optimization, not a critical
     * operation. The on-demand path in the controller is the real
     * source of truth and will always regenerate if needed.
     */
    public int $tries = 1;

    /**
     * DOCX -> PDF conversion can be slow; give it enough room.
     */
    public int $timeout = 120;

    public function __construct(public int $documentId)
    {
    }

    public function handle(
        OfficialDocumentTemplateService $templateService,
        DocumentConversionService $conversionService
    ): void {
        $document = DocumentRequest::find($this->documentId);

        if (! $document) {
            return;
        }

        // Someone (e.g. the officer clicking print immediately) may have
        // already generated it before this job got a chance to run.
        if ($document->generatedPdfExists()) {
            return;
        }

        try {
            $generatedDocumentPath = $templateService->generate($document);

            $generatedPdfPath = $conversionService->convertDocxToPdf(
                $generatedDocumentPath
            );

            if (! is_file($generatedPdfPath)) {
                return;
            }

            $document->update([
                'generated_pdf_path' => $this->relativePrivateStoragePath(
                    $generatedPdfPath
                ),
            ]);
        } catch (Throwable $exception) {
            // Swallow - do not let a warm-up failure surface as a queue
            // failure/retry storm. The controller's on-demand generation
            // remains the safety net.
            report($exception);
        }
    }

    private function relativePrivateStoragePath(string $absolutePath): string
    {
        $privateStoragePath = storage_path('app/private');

        $normalizedAbsolutePath = str_replace('\\', '/', $absolutePath);
        $normalizedPrivateStoragePath = str_replace('\\', '/', $privateStoragePath);

        if (
            Str::startsWith(
                $normalizedAbsolutePath,
                $normalizedPrivateStoragePath . '/'
            )
        ) {
            return ltrim(
                Str::after($normalizedAbsolutePath, $normalizedPrivateStoragePath),
                '/'
            );
        }

        return ltrim($normalizedAbsolutePath, '/');
    }
}
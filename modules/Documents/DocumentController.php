<?php

declare(strict_types=1);

namespace Modules\Documents;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\DocumentAccessService;
use App\Services\DocumentUploadService;
use RuntimeException;
use Throwable;

final class DocumentController
{
    private DocumentAccessService $documents;
    private DocumentUploadService $uploads;

    public function __construct()
    {
        $this->documents = new DocumentAccessService();
        $this->uploads = new DocumentUploadService();
    }

    public function download(Request $request): void
    {
        $documentId = (int) $request->input('id', 0);

        try {
            $context = $this->documents->downloadContext($documentId, $request);
            Response::download(
                $context['absolute_path'],
                $context['download_name'],
                $context['mime_type']
            );
        } catch (RuntimeException $exception) {
            $message = $exception->getMessage();
            $status = in_array($message, ['Document not found.', 'Document file is not available.'], true) ? 404 : 403;
            Response::abort($status, $message);
        }
    }

    public function show(Request $request): void
    {
        $documentId = (int) $request->input('id', 0);

        try {
            $context = $this->documents->viewContext($documentId, $request);
            Response::html(View::render(base_path('modules/Documents/views/show.php'), [
                'title' => 'Document Workspace',
                'activeMenu' => $this->activeMenu(),
                'document' => $context['document'],
                'versions' => $context['versions'],
                'previewable' => $context['previewable'],
                'replaceAllowed' => $context['replace_allowed'],
                'success' => Session::pullFlash('success'),
                'error' => Session::pullFlash('error'),
            ]));
        } catch (RuntimeException $exception) {
            $message = $exception->getMessage();
            $status = in_array($message, ['Document not found.', 'Document file is not available.'], true) ? 404 : 403;
            Response::abort($status, $message);
        }
    }

    public function preview(Request $request): void
    {
        $documentId = (int) $request->input('id', 0);

        try {
            $context = $this->documents->previewContext($documentId, $request);
            Response::inlineFile(
                $context['absolute_path'],
                $context['display_name'],
                $context['mime_type']
            );
        } catch (RuntimeException $exception) {
            $message = $exception->getMessage();
            $status = in_array($message, ['Document not found.', 'Document file is not available.'], true) ? 404 : 403;
            Response::abort($status, $message);
        }
    }

    public function replace(Request $request): void
    {
        $documentId = (int) $request->input('document_id', 0);

        try {
            $this->documents->replaceContext($documentId, $request);
            $file = $request->file('replacement_file');
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Choose a replacement document to continue.');
            }

            $versionNo = $this->uploads->replaceDocumentVersion(
                $documentId,
                $file,
                (int) (\App\Core\Auth::id() ?? 0),
                trim((string) $request->input('change_note', '')) ?: null
            );
            Session::flash('success', 'Document updated successfully. Current version: V' . $versionNo . '.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/documents/show?id=' . $documentId);
    }

    private function activeMenu(): string
    {
        if (\App\Core\Auth::isPortalUser()) {
            return 'client_portal';
        }

        return 'reports';
    }
}

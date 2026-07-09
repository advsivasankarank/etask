<?php

declare(strict_types=1);

namespace Modules\Documents;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\ClientRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ServiceOrderRepository;
use App\Services\DocumentAccessService;
use App\Services\DocumentUploadService;
use RuntimeException;
use Throwable;

final class DocumentController
{
    private DocumentAccessService $documents;
    private DocumentUploadService $uploads;
    private DocumentRepository $documentRepo;
    private ClientRepository $clients;
    private ServiceOrderRepository $serviceOrders;

    public function __construct()
    {
        $this->documents = new DocumentAccessService();
        $this->uploads = new DocumentUploadService();
        $this->documentRepo = new DocumentRepository();
        $this->clients = new ClientRepository();
        $this->serviceOrders = new ServiceOrderRepository();
    }

    public function index(Request $request): void
    {
        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'search' => $search,
            'client_id' => $request->input('client_id', ''),
            'verification_status' => $request->input('verification_status', ''),
            'document_category' => $request->input('document_category', ''),
        ];

        $content = View::render(base_path('modules/Documents/views/index.php'), [
            'title' => 'Document Register',
            'activeMenu' => 'documents',
            'documents' => $this->documentRepo->paginateRegister($filters, $page),
            'summary' => $this->documentRepo->registerSummary(),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
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
                'movements' => $this->documentRepo->movementsForDocument($documentId),
                'success' => Session::pullFlash('success'),
                'error' => Session::pullFlash('error'),
            ]));
        } catch (RuntimeException $exception) {
            $message = $exception->getMessage();
            $status = in_array($message, ['Document not found.', 'Document file is not available.'], true) ? 404 : 403;
            Response::abort($status, $message);
        }
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
                (int) (Auth::id() ?? 0),
                trim((string) $request->input('change_note', '')) ?: null
            );
            Session::flash('success', 'Document updated successfully. Current version: V' . $versionNo . '.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/documents/show?id=' . $documentId);
    }

    public function verify(Request $request): void
    {
        $documentId = (int) $request->input('document_id', 0);
        $status = strtoupper((string) $request->input('verification_status', ''));
        $remarks = trim((string) $request->input('remarks', ''));

        if (!in_array($status, ['VERIFIED', 'REJECTED'], true)) {
            Session::flash('error', 'Invalid verification status.');
            redirect('/documents/show?id=' . $documentId);
        }

        try {
            $this->documentRepo->verifyDocument($documentId, (int) Auth::id(), $status, $remarks ?: null);
            Session::flash('success', 'Document ' . strtolower($status) . ' successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/documents/show?id=' . $documentId);
    }

    public function requests(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'status' => $request->input('status', ''),
            'client_id' => $request->input('client_id', ''),
        ];

        $content = View::render(base_path('modules/Documents/views/requests.php'), [
            'title' => 'Document Requests',
            'activeMenu' => 'documents',
            'requests' => $this->documentRepo->paginateRequests($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function requestForm(): void
    {
        $content = View::render(base_path('modules/Documents/views/request_form.php'), [
            'title' => 'Create Document Request',
            'activeMenu' => 'documents',
            'clients' => $this->clients->allActive(),
            'serviceOrders' => [],
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function createRequest(Request $request): void
    {
        $payload = [
            'client_id' => (int) $request->input('client_id', 0),
            'service_order_id' => (int) $request->input('service_order_id', 0) ?: null,
            'requested_by' => (int) Auth::id(),
            'assigned_to' => (int) $request->input('assigned_to', 0) ?: null,
            'document_title' => trim((string) $request->input('document_title', '')),
            'document_category' => trim((string) $request->input('document_category', '')),
            'description' => trim((string) $request->input('description', '')),
            'due_date' => trim((string) $request->input('due_date', '')),
            'remarks' => trim((string) $request->input('remarks', '')),
        ];

        Session::flash('old', $payload);

        if ($payload['client_id'] <= 0 || $payload['document_title'] === '') {
            Session::flash('error', 'Client and document title are required.');
            redirect('/documents/requests/create');
        }

        try {
            $requestId = $this->documentRepo->createRequest($payload);
            Session::flash('success', 'Document request created successfully.');
            redirect('/documents/requests');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/documents/requests/create');
        }
    }

    public function markReceived(Request $request): void
    {
        $requestId = (int) $request->input('request_id', 0);
        $remarks = trim((string) $request->input('remarks', ''));

        try {
            $this->documentRepo->updateRequestStatus($requestId, 'RECEIVED', null, $remarks ?: null);
            Session::flash('success', 'Document request marked as received.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/documents/requests');
    }

    public function cancelRequest(Request $request): void
    {
        $requestId = (int) $request->input('request_id', 0);
        $remarks = trim((string) $request->input('remarks', ''));

        try {
            $this->documentRepo->updateRequestStatus($requestId, 'CANCELLED', null, $remarks ?: null);
            Session::flash('success', 'Document request cancelled.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/documents/requests');
    }

    public function movement(Request $request): void
    {
        $page = max(1, (int) $request->input('page', 1));
        $filters = [
            'status' => $request->input('status', ''),
            'movement_type' => $request->input('movement_type', ''),
        ];

        $content = View::render(base_path('modules/Documents/views/movement.php'), [
            'title' => 'Document Movement Register',
            'activeMenu' => 'documents',
            'movements' => $this->documentRepo->paginateMovements($filters, $page),
            'filters' => $filters,
            'success' => Session::pullFlash('success'),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function movementForm(): void
    {
        $content = View::render(base_path('modules/Documents/views/movement_form.php'), [
            'title' => 'Record Document Movement',
            'activeMenu' => 'documents',
            'documents' => $this->documentRepo->allActive(),
            'old' => Session::pullFlash('old', []),
            'error' => Session::pullFlash('error'),
        ]);

        Response::html($content);
    }

    public function createMovement(Request $request): void
    {
        $payload = [
            'document_id' => (int) $request->input('document_id', 0),
            'client_id' => (int) $request->input('client_id', 0) ?: null,
            'service_order_id' => (int) $request->input('service_order_id', 0) ?: null,
            'from_user_id' => (int) $request->input('from_user_id', 0) ?: null,
            'to_user_id' => (int) $request->input('to_user_id', 0) ?: null,
            'from_location' => trim((string) $request->input('from_location', '')),
            'to_location' => trim((string) $request->input('to_location', '')),
            'movement_type' => strtoupper((string) $request->input('movement_type', 'TRANSFERRED')),
            'purpose' => trim((string) $request->input('purpose', '')),
            'expected_return_date' => trim((string) $request->input('expected_return_date', '')),
            'remarks' => trim((string) $request->input('remarks', '')),
            'created_by' => (int) Auth::id(),
        ];

        Session::flash('old', $payload);

        if ($payload['document_id'] <= 0) {
            Session::flash('error', 'Document is required.');
            redirect('/documents/movement/create');
        }

        try {
            $this->documentRepo->createMovement($payload);
            Session::flash('success', 'Document movement recorded successfully.');
            redirect('/documents/movement');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
            redirect('/documents/movement/create');
        }
    }

    public function returnMovement(Request $request): void
    {
        $movementId = (int) $request->input('movement_id', 0);

        try {
            $this->documentRepo->returnMovement($movementId);
            Session::flash('success', 'Document return recorded successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/documents/movement');
    }

    public function archiveMovement(Request $request): void
    {
        $movementId = (int) $request->input('movement_id', 0);

        try {
            $this->documentRepo->archiveMovement($movementId);
            Session::flash('success', 'Document archived successfully.');
        } catch (Throwable $throwable) {
            Session::flash('error', $throwable->getMessage());
        }

        redirect('/documents/movement');
    }

    private function activeMenu(): string
    {
        if (Auth::isPortalUser()) {
            return 'client_portal';
        }

        return 'documents';
    }
}

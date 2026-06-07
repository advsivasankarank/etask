<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Request;
use App\Repositories\DocumentRepository;
use RuntimeException;

final class DocumentAccessService
{
    public function __construct(
        private readonly DocumentRepository $documents = new DocumentRepository()
    ) {
    }

    public function downloadContext(int $documentId, Request $request): array
    {
        $document = $this->documents->findById($documentId);
        if ($document === null || (int) ($document['is_active'] ?? 0) !== 1) {
            throw new RuntimeException('Document not found.');
        }

        $user = Auth::user();
        if ($user === null) {
            $this->documents->recordAccess(null, $documentId, 'DOWNLOAD_DENIED', 'Unauthenticated document access attempt.', $request->ip(), $request->userAgent());
            throw new RuntimeException('Authentication required.');
        }

        if (!$this->canAccessDocument($document, $user)) {
            $this->documents->recordAccess((int) ($user['id'] ?? 0) ?: null, $documentId, 'DOWNLOAD_DENIED', 'Unauthorized document access attempt.', $request->ip(), $request->userAgent());
            throw new RuntimeException('You are not allowed to access this document.');
        }

        $absolutePath = $this->resolveAbsolutePath((string) $document['latest_file_path']);
        if (!is_file($absolutePath)) {
            $this->documents->recordAccess((int) ($user['id'] ?? 0) ?: null, $documentId, 'DOWNLOAD_MISSING', 'Document file missing on disk.', $request->ip(), $request->userAgent());
            throw new RuntimeException('Document file is not available.');
        }

        $this->documents->recordAccess(
            (int) ($user['id'] ?? 0) ?: null,
            $documentId,
            'DOWNLOAD_SUCCESS',
            'Document downloaded securely.',
            $request->ip(),
            $request->userAgent()
        );

        return [
            'document' => $document,
            'absolute_path' => $absolutePath,
            'download_name' => (string) ($document['latest_file_name'] ?: $document['document_name']),
            'mime_type' => (string) ($document['mime_type'] ?: 'application/octet-stream'),
        ];
    }

    private function canAccessDocument(array $document, array $user): bool
    {
        $documentClientId = (int) ($document['client_id'] ?? 0);

        if (Auth::isPortalUser()) {
            if ((int) ($user['client_id'] ?? 0) !== $documentClientId) {
                return false;
            }

            return in_array((string) ($document['linked_module'] ?? ''), ['CLIENT', 'PSO', 'SO', 'BILLING'], true);
        }

        if (Auth::isConsultantUser()) {
            return (string) ($document['linked_module'] ?? '') === 'CONSULTANT'
                && (int) ($document['consultant_user_id'] ?? 0) === (int) ($user['id'] ?? 0);
        }

        if (!Auth::can('documents.download')) {
            return false;
        }

        return match ((string) ($document['linked_module'] ?? '')) {
            'CLIENT' => Auth::canAny('clients.view', 'clients.edit', 'clients.credentials.manage'),
            'PSO' => Auth::canAny('portal.pso.review', 'portal.pso.approve', 'portal.pso.reject', 'portal.pso.create'),
            'SO' => Auth::canAny('service_orders.view', 'service_orders.create', 'workflow.advance'),
            'CONSULTANT' => Auth::canAny('consultants.view', 'consultants.deliverables.upload', 'consultants.deliverables.review', 'consultants.bills.create', 'consultants.bills.review', 'consultants.payments.record'),
            'BILLING' => Auth::canAny('billing.view', 'billing.disbursements.manage', 'billing.invoices.manage', 'billing.payments.manage'),
            'GENERAL' => Auth::can('documents.download'),
            default => false,
        };
    }

    private function resolveAbsolutePath(string $relativePath): string
    {
        $basePath = $this->storageBasePathFor($relativePath);
        $baseUploadsPath = realpath($basePath);
        $resolvedPath = realpath($basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($this->normalizedRelativePath($relativePath), '/')));

        if ($baseUploadsPath === false || $resolvedPath === false) {
            return '';
        }

        $normalizedBase = str_replace('\\', '/', strtolower(rtrim($baseUploadsPath, '\\/')));
        $normalizedTarget = str_replace('\\', '/', strtolower($resolvedPath));

        if (!str_starts_with($normalizedTarget, $normalizedBase . '/')
            && $normalizedTarget !== $normalizedBase) {
            throw new RuntimeException('Invalid document path.');
        }

        return $resolvedPath;
    }

    private function storageBasePathFor(string $relativePath): string
    {
        if (str_starts_with($relativePath, 'storage/uploads/')) {
            return base_path();
        }

        return trim((string) config('app.private_storage_path', ''));
    }

    private function normalizedRelativePath(string $relativePath): string
    {
        if (str_starts_with($relativePath, 'storage/uploads/')) {
            return $relativePath;
        }

        return ltrim($relativePath, '\\/');
    }
}

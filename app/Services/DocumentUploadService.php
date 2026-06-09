<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\DocumentRepository;
use finfo;
use RuntimeException;
use Throwable;

final class DocumentUploadService
{
    private const ALLOWED_EXTENSIONS = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    private const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'phar', 'exe', 'dll', 'bat', 'cmd', 'com', 'cgi', 'pl', 'js', 'jsp', 'asp', 'aspx', 'sh',
    ];

    private const UPLOAD_HTACCESS = <<<'HTACCESS'
Options -Indexes
php_flag engine off
RemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8 .phar
RemoveType .php .phtml .php3 .php4 .php5 .php7 .php8 .phar
AddType text/plain .php .phtml .php3 .php4 .php5 .php7 .php8 .phar .cgi .pl .asp .aspx .jsp .js .sh .bat .cmd .exe
<FilesMatch "\.(php|phtml|php3|php4|php5|php7|php8|phar|cgi|pl|asp|aspx|jsp|js|sh|bat|cmd|exe)$">
    Require all denied
</FilesMatch>
HTACCESS;

    public function __construct(
        private readonly DocumentRepository $documents = new DocumentRepository()
    ) {
    }

    public function uploadLinkedDocuments(
        int $clientId,
        string $linkedModule,
        int $linkedId,
        string $documentCategory,
        array $files,
        int $uploadedBy,
        string $directoryKey
    ): array {
        if (!isset($files['name'])) {
            return [];
        }

        $uploadedDocumentIds = [];
        $normalizedFiles = $this->normalizeFilesArray($files);

        foreach ($normalizedFiles as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            $uploadedDocumentIds[] = $this->storeSingleDocument(
                clientId: $clientId,
                linkedModule: $linkedModule,
                linkedId: $linkedId,
                documentCategory: $documentCategory,
                file: $file,
                uploadedBy: $uploadedBy,
                directoryKey: $directoryKey
            );
        }

        return $uploadedDocumentIds;
    }

    public function uploadForPso(int $clientId, int $psoId, array $files, int $uploadedBy): array
    {
        return $this->uploadLinkedDocuments($clientId, 'PSO', $psoId, 'PSO_SUPPORTING_DOC', $files, $uploadedBy, 'pso');
    }

    public function replaceDocumentVersion(int $documentId, array $file, int $uploadedBy, ?string $changeNote = null): int
    {
        $document = $this->documents->findById($documentId);
        if ($document === null) {
            throw new RuntimeException('Document not found.');
        }

        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $versions = $this->documents->versions($documentId);
            $nextVersionNo = ((int) ($versions[0]['version_no'] ?? 0)) + 1;

            $stored = $this->storePreparedFile(
                clientId: (int) $document['client_id'],
                linkedModule: (string) $document['linked_module'],
                linkedId: (int) $document['linked_id'],
                documentCategory: (string) $document['document_category'],
                file: $file,
                uploadedBy: $uploadedBy,
                directoryKey: strtolower((string) $document['linked_module']) ?: 'general',
                persistDocument: false
            );

            $this->documents->addVersion(
                $documentId,
                $nextVersionNo,
                $stored['file_name'],
                $stored['file_path'],
                $stored['mime_type'],
                $stored['file_size'],
                $stored['checksum_sha256'],
                $changeNote ?: 'Document replaced',
                $uploadedBy
            );

            $this->documents->touchCurrentVersion(
                $documentId,
                $nextVersionNo,
                $stored['file_name'],
                $stored['file_path'],
                $stored['mime_type'],
                $stored['file_size'],
                $stored['checksum_sha256'],
                $uploadedBy
            );

            $connection->commit();

            Logger::info('document.replaced', [
                'document_id' => $documentId,
                'version_no' => $nextVersionNo,
                'uploaded_by' => $uploadedBy,
            ]);

            return $nextVersionNo;
        } catch (Throwable $throwable) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }
    }

    private function storeSingleDocument(
        int $clientId,
        string $linkedModule,
        int $linkedId,
        string $documentCategory,
        array $file,
        int $uploadedBy,
        string $directoryKey
    ): int {
        $stored = $this->storePreparedFile(
            clientId: $clientId,
            linkedModule: $linkedModule,
            linkedId: $linkedId,
            documentCategory: $documentCategory,
            file: $file,
            uploadedBy: $uploadedBy,
            directoryKey: $directoryKey,
            persistDocument: true
        );

        return (int) $stored['document_id'];
    }

    private function storePreparedFile(
        int $clientId,
        string $linkedModule,
        int $linkedId,
        string $documentCategory,
        array $file,
        int $uploadedBy,
        string $directoryKey,
        bool $persistDocument
    ): array {
        $originalName = (string) ($file['name'] ?? '');
        $tmpName = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);

        if ($originalName === '' || $tmpName === '') {
            throw new RuntimeException('Invalid upload payload received.');
        }

        if (!is_uploaded_file($tmpName)) {
            throw new RuntimeException('Invalid uploaded file source.');
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName) ?: 'document.bin';
        $extension = strtolower((string) pathinfo($safeName, PATHINFO_EXTENSION));
        $mimeType = $this->detectMimeType($tmpName);

        $this->assertSecureUpload($safeName, $extension, $mimeType, $size);

        $generatedName = uniqid($directoryKey . '_', true) . ($extension !== '' ? '.' . $extension : '');

        $storageRoot = $this->privateStorageRoot();
        $relativeDirectory = 'uploads/' . $directoryKey . '/' . date('Y/m');
        $absoluteDirectory = $storageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);

        if (!is_dir($absoluteDirectory) && !mkdir($absoluteDirectory, 0775, true) && !is_dir($absoluteDirectory)) {
            throw new RuntimeException('Unable to create upload directory.');
        }

        $this->ensureUploadProtection(base_path('storage'));

        $absolutePath = $absoluteDirectory . DIRECTORY_SEPARATOR . $generatedName;
        if (!move_uploaded_file($tmpName, $absolutePath)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        $relativePath = str_replace('\\', '/', $relativeDirectory . '/' . $generatedName);
        $checksum = hash_file('sha256', $absolutePath) ?: null;

        $result = [
            'document_id' => null,
            'file_name' => $safeName,
            'file_path' => $relativePath,
            'mime_type' => $mimeType,
            'file_size' => $size,
            'checksum_sha256' => $checksum,
        ];

        if (!$persistDocument) {
            return $result;
        }

        $connection = Database::connection();
        $documentStatement = $connection->prepare(
            "INSERT INTO documents (
                client_id, linked_module, linked_id, document_category, document_name, current_version_no,
                latest_file_name, latest_file_path, mime_type, file_size, checksum_sha256, uploaded_by, uploaded_at, is_active
            ) VALUES (
                :client_id, :linked_module, :linked_id, :document_category, :document_name, 1,
                :latest_file_name, :latest_file_path, :mime_type, :file_size, :checksum_sha256, :uploaded_by, NOW(), 1
            )"
        );
        $documentStatement->execute([
            'client_id' => $clientId,
            'linked_module' => $linkedModule,
            'linked_id' => $linkedId,
            'document_category' => $documentCategory,
            'document_name' => $safeName,
            'latest_file_name' => $safeName,
            'latest_file_path' => $relativePath,
            'mime_type' => $mimeType,
            'file_size' => $size,
            'checksum_sha256' => $checksum,
            'uploaded_by' => $uploadedBy,
        ]);

        $documentId = (int) $connection->lastInsertId();

        $this->documents->addVersion(
            $documentId,
            1,
            $safeName,
            $relativePath,
            $mimeType,
            $size,
            $checksum,
            'Initial upload',
            $uploadedBy
        );

        Logger::info('document.uploaded', [
            'document_id' => $documentId,
            'client_id' => $clientId,
            'linked_module' => $linkedModule,
            'linked_id' => $linkedId,
            'document_category' => $documentCategory,
            'uploaded_by' => $uploadedBy,
            'stored_path' => $relativePath,
            'mime_type' => $mimeType,
            'file_size' => $size,
        ]);

        $result['document_id'] = $documentId;

        return $result;
    }

    private function normalizeFilesArray(array $files): array
    {
        if (!is_array($files['name'])) {
            return [$files];
        }

        $normalized = [];
        foreach ($files['name'] as $index => $name) {
            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
            ];
        }

        return $normalized;
    }

    private function detectMimeType(string $tmpName): string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($tmpName);

        if (!is_string($mimeType) || trim($mimeType) === '') {
            throw new RuntimeException('Unable to determine uploaded file type.');
        }

        return strtolower(trim($mimeType));
    }

    private function assertSecureUpload(string $safeName, string $extension, string $mimeType, int $size): void
    {
        if ($size <= 0) {
            throw new RuntimeException('Uploaded file is empty.');
        }

        $maxBytes = (int) config('app.upload_max_bytes', 5242880);
        if ($size > $maxBytes) {
            throw new RuntimeException('Uploaded file exceeds the maximum allowed size.');
        }

        if ($extension === '') {
            throw new RuntimeException('Uploaded file must include a valid extension.');
        }

        if (str_contains($safeName, "\0")) {
            throw new RuntimeException('Invalid file name supplied.');
        }

        if (in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            throw new RuntimeException('Executable or script uploads are not allowed.');
        }

        $nameParts = explode('.', strtolower($safeName));
        if (count($nameParts) > 2) {
            foreach (array_slice($nameParts, 0, -1) as $innerPart) {
                if (in_array($innerPart, self::BLOCKED_EXTENSIONS, true)) {
                    throw new RuntimeException('Suspicious double-extension uploads are not allowed.');
                }
            }
        }

        $allowedMimeTypes = self::ALLOWED_EXTENSIONS[$extension] ?? null;
        if ($allowedMimeTypes === null) {
            throw new RuntimeException('This file type is not allowed.');
        }

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new RuntimeException('Uploaded file type does not match the file extension.');
        }

        if (str_contains($safeName, '..')) {
            throw new RuntimeException('Invalid file name supplied.');
        }
    }

    private function ensureUploadProtection(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $htaccessPath = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.htaccess';
        if (is_file($htaccessPath)) {
            return;
        }

        @file_put_contents($htaccessPath, self::UPLOAD_HTACCESS, LOCK_EX);
    }

    private function privateStorageRoot(): string
    {
        $configured = trim((string) config('app.private_storage_path', ''));
        if ($configured === '') {
            throw new RuntimeException('Private storage path is not configured.');
        }

        return $configured;
    }
}

<?php

declare(strict_types=1);

namespace Modules\Documents;

use App\Core\Request;
use App\Core\Response;
use App\Services\DocumentAccessService;
use RuntimeException;

final class DocumentController
{
    public function __construct(
        private readonly DocumentAccessService $documents = new DocumentAccessService()
    ) {
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
}

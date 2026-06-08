<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\ClientRepository;
use RuntimeException;
use Throwable;

final class ClientService
{
    public function __construct(
        private readonly ClientRepository $clients = new ClientRepository(),
        private readonly DocumentUploadService $documentUploads = new DocumentUploadService(),
        private readonly EncryptionService $encryption = new EncryptionService(),
        private readonly UserService $users = new UserService()
    ) {
    }

    public function create(array $input, array $files = [], ?int $uploadedBy = null): int
    {
        $pan = strtoupper(trim((string) ($input['pan'] ?? '')));
        $legalName = trim((string) ($input['legal_name'] ?? ''));
        $aadhaar = preg_replace('/\D+/', '', (string) ($input['aadhaar_no'] ?? ''));

        if ($pan === '' || $legalName === '') {
            throw new RuntimeException('PAN and legal name are required.');
        }

        if ($aadhaar !== '' && strlen($aadhaar) !== 12) {
            throw new RuntimeException('Aadhaar number must be 12 digits.');
        }

        if ($this->clients->panExists($pan)) {
            throw new RuntimeException('A client with this PAN already exists.');
        }

        $clientId = $this->runInTransaction(function () use ($input, $pan, $legalName, $aadhaar): int {
            $clientCode = 'CLT/' . date('Y') . '/' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
            $encryptedAadhaar = $aadhaar !== '' ? $this->encryption->encrypt($aadhaar) : null;

            $clientId = $this->clients->create([
                'client_code' => $clientCode,
                'client_type' => (string) ($input['client_type'] ?? 'INDIVIDUAL'),
                'legal_name' => $legalName,
                'trade_name' => trim((string) ($input['trade_name'] ?? '')) ?: null,
                'pan' => $pan,
                'tan' => strtoupper(trim((string) ($input['tan'] ?? ''))) ?: null,
                'gstin' => strtoupper(trim((string) ($input['gstin'] ?? ''))) ?: null,
                'aadhaar_no' => null,
                'aadhaar_ciphertext' => $encryptedAadhaar['ciphertext'] ?? null,
                'aadhaar_iv' => $encryptedAadhaar['iv'] ?? null,
                'aadhaar_last4' => $aadhaar !== '' ? substr($aadhaar, -4) : null,
                'email' => trim((string) ($input['email'] ?? '')) ?: null,
                'mobile' => trim((string) ($input['mobile'] ?? '')) ?: null,
                'alternate_mobile' => trim((string) ($input['alternate_mobile'] ?? '')) ?: null,
                'landline' => trim((string) ($input['landline'] ?? '')) ?: null,
                'address_line1' => trim((string) ($input['address_line1'] ?? '')) ?: null,
                'address_line2' => trim((string) ($input['address_line2'] ?? '')) ?: null,
                'city' => trim((string) ($input['city'] ?? '')) ?: null,
                'state_name' => trim((string) ($input['state_name'] ?? '')) ?: null,
                'postal_code' => trim((string) ($input['postal_code'] ?? '')) ?: null,
                'default_company_id' => null,
                'assigned_crm_id' => (int) ($input['assigned_crm_id'] ?? 0) ?: null,
            ]);

            $this->clients->createContact([
                'client_id' => $clientId,
                'contact_name' => trim((string) ($input['contact_name'] ?? $legalName)),
                'designation' => trim((string) ($input['designation'] ?? '')) ?: null,
                'email' => trim((string) ($input['contact_email'] ?? $input['email'] ?? '')) ?: null,
                'mobile' => trim((string) ($input['contact_mobile'] ?? $input['mobile'] ?? '')) ?: null,
                'can_login' => 0,
            ]);

            return $clientId;
        });

        $this->uploadIdentityDocuments($clientId, $files, $uploadedBy);
        Logger::info('client.created', [
            'client_id' => $clientId,
            'pan' => $pan,
            'assigned_crm_id' => (int) ($input['assigned_crm_id'] ?? 0) ?: null,
            'uploaded_by' => $uploadedBy,
        ]);

        return $clientId;
    }

    public function update(int $clientId, array $input, array $files = [], ?int $uploadedBy = null): void
    {
        $pan = strtoupper(trim((string) ($input['pan'] ?? '')));
        $legalName = trim((string) ($input['legal_name'] ?? ''));
        $aadhaar = preg_replace('/\D+/', '', (string) ($input['aadhaar_no'] ?? ''));

        if ($pan === '' || $legalName === '') {
            throw new RuntimeException('PAN and legal name are required.');
        }

        if ($aadhaar !== '' && strlen($aadhaar) !== 12) {
            throw new RuntimeException('Aadhaar number must be 12 digits.');
        }

        if ($this->clients->panExists($pan, $clientId)) {
            throw new RuntimeException('Another client already uses this PAN.');
        }

        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new RuntimeException('Client not found.');
        }

        $primaryContact = $this->clients->primaryContact($clientId);

        $this->runInTransaction(function () use ($clientId, $input, $pan, $legalName, $primaryContact, $aadhaar): void {
            $encryptedAadhaar = $aadhaar !== '' ? $this->encryption->encrypt($aadhaar) : null;

            $this->clients->update($clientId, [
                'client_type' => (string) ($input['client_type'] ?? 'INDIVIDUAL'),
                'legal_name' => $legalName,
                'trade_name' => trim((string) ($input['trade_name'] ?? '')) ?: null,
                'pan' => $pan,
                'tan' => strtoupper(trim((string) ($input['tan'] ?? ''))) ?: null,
                'gstin' => strtoupper(trim((string) ($input['gstin'] ?? ''))) ?: null,
                'aadhaar_no' => null,
                'aadhaar_ciphertext' => $encryptedAadhaar['ciphertext'] ?? null,
                'aadhaar_iv' => $encryptedAadhaar['iv'] ?? null,
                'aadhaar_last4' => $aadhaar !== '' ? substr($aadhaar, -4) : null,
                'email' => trim((string) ($input['email'] ?? '')) ?: null,
                'mobile' => trim((string) ($input['mobile'] ?? '')) ?: null,
                'alternate_mobile' => trim((string) ($input['alternate_mobile'] ?? '')) ?: null,
                'landline' => trim((string) ($input['landline'] ?? '')) ?: null,
                'address_line1' => trim((string) ($input['address_line1'] ?? '')) ?: null,
                'address_line2' => trim((string) ($input['address_line2'] ?? '')) ?: null,
                'city' => trim((string) ($input['city'] ?? '')) ?: null,
                'state_name' => trim((string) ($input['state_name'] ?? '')) ?: null,
                'postal_code' => trim((string) ($input['postal_code'] ?? '')) ?: null,
                'default_company_id' => null,
                'assigned_crm_id' => (int) ($input['assigned_crm_id'] ?? 0) ?: null,
            ]);

            $contactPayload = [
                'contact_name' => trim((string) ($input['contact_name'] ?? $legalName)),
                'designation' => trim((string) ($input['designation'] ?? '')) ?: null,
                'email' => trim((string) ($input['contact_email'] ?? $input['email'] ?? '')) ?: null,
                'mobile' => trim((string) ($input['contact_mobile'] ?? $input['mobile'] ?? '')) ?: null,
            ];

            if ($primaryContact === null) {
                $this->clients->createContact(['client_id' => $clientId] + $contactPayload);
            } else {
                $this->clients->updatePrimaryContact((int) $primaryContact['id'], $contactPayload);
            }
        });

        $this->uploadIdentityDocuments($clientId, $files, $uploadedBy);
        Logger::info('client.updated', [
            'client_id' => $clientId,
            'pan' => $pan,
            'updated_by' => $uploadedBy,
        ]);
    }

    public function archive(int $clientId, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('Archive reason is required.');
        }

        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new RuntimeException('Client not found.');
        }

        $this->clients->archive($clientId, $reason);
        Logger::info('client.archived', [
            'client_id' => $clientId,
            'reason' => $reason,
        ]);
    }

    public function savePortalCredentials(int $clientId, array $input, int $updatedBy): void
    {
        $client = $this->clients->findById($clientId);
        if ($client === null) {
            throw new RuntimeException('Client not found.');
        }

        $this->runInTransaction(function () use ($clientId, $input, $updatedBy): void {
            $existingCredentials = $this->clients->portalCredentials($clientId);
            $nextCustomSequence = $this->nextCustomPortalSequence($existingCredentials);

            foreach (self::portalDefinitions() as $portalCode => $definition) {
                $userIdentifier = trim((string) ($input[$portalCode . '_user_identifier'] ?? ''));
                $password = trim((string) ($input[$portalCode . '_password'] ?? ''));
                $existing = $this->clients->portalCredentialByCode($clientId, $portalCode);
                $encrypted = $password !== '' ? $this->encryption->encrypt($password) : null;

                if ($existing === null && $userIdentifier === '' && $password === '') {
                    continue;
                }

                if ($existing === null) {
                    $this->clients->createPortalCredential([
                        'client_id' => $clientId,
                        'portal_code' => $portalCode,
                        'portal_label' => $definition['label'],
                        'user_identifier' => $userIdentifier !== '' ? $userIdentifier : null,
                        'password_ciphertext' => $encrypted['ciphertext'] ?? null,
                        'password_iv' => $encrypted['iv'] ?? null,
                        'portal_url' => null,
                        'remarks' => null,
                        'last_verified_at' => null,
                        'created_by' => $updatedBy,
                        'updated_by' => $updatedBy,
                    ]);
                    continue;
                }

                $this->clients->updatePortalCredential((int) $existing['id'], [
                    'portal_label' => $existing['portal_label'] ?: $definition['label'],
                    'user_identifier' => $userIdentifier !== '' ? $userIdentifier : ($existing['user_identifier'] ?: null),
                    'password_ciphertext' => $encrypted['ciphertext'] ?? $existing['password_ciphertext'],
                    'password_iv' => $encrypted['iv'] ?? $existing['password_iv'],
                    'portal_url' => $existing['portal_url'] ?: null,
                    'remarks' => $existing['remarks'] ?: null,
                    'last_verified_at' => $existing['last_verified_at'] ?: null,
                    'updated_by' => $updatedBy,
                ]);
            }

            $customCodes = $input['custom_portal_code'] ?? [];
            $customLabels = $input['custom_portal_label'] ?? [];
            $customUsers = $input['custom_portal_user_identifier'] ?? [];
            $customPasswords = $input['custom_portal_password'] ?? [];

            $rowCount = max(
                is_array($customLabels) ? count($customLabels) : 0,
                is_array($customUsers) ? count($customUsers) : 0,
                is_array($customPasswords) ? count($customPasswords) : 0
            );

            for ($index = 0; $index < $rowCount; $index++) {
                $portalName = trim((string) ($customLabels[$index] ?? ''));
                $userIdentifier = trim((string) ($customUsers[$index] ?? ''));
                $password = trim((string) ($customPasswords[$index] ?? ''));
                $portalCode = trim((string) ($customCodes[$index] ?? ''));

                if ($portalName === '' && $userIdentifier === '' && $password === '') {
                    continue;
                }

                if ($portalName === '') {
                    throw new RuntimeException('Portal name is required when saving a new portal credential.');
                }

                if ($portalCode === '') {
                    $portalCode = 'CUSTOM_PORTAL_' . $nextCustomSequence;
                    $nextCustomSequence++;
                }

                $existing = $this->clients->portalCredentialByCode($clientId, $portalCode);
                $encrypted = $password !== '' ? $this->encryption->encrypt($password) : null;

                if ($existing === null) {
                    $this->clients->createPortalCredential([
                        'client_id' => $clientId,
                        'portal_code' => $portalCode,
                        'portal_label' => $portalName,
                        'user_identifier' => $userIdentifier !== '' ? $userIdentifier : null,
                        'password_ciphertext' => $encrypted['ciphertext'] ?? null,
                        'password_iv' => $encrypted['iv'] ?? null,
                        'portal_url' => null,
                        'remarks' => null,
                        'last_verified_at' => null,
                        'created_by' => $updatedBy,
                        'updated_by' => $updatedBy,
                    ]);
                    continue;
                }

                $this->clients->updatePortalCredential((int) $existing['id'], [
                    'portal_label' => $portalName,
                    'user_identifier' => $userIdentifier !== '' ? $userIdentifier : ($existing['user_identifier'] ?: null),
                    'password_ciphertext' => $encrypted['ciphertext'] ?? $existing['password_ciphertext'],
                    'password_iv' => $encrypted['iv'] ?? $existing['password_iv'],
                    'portal_url' => $existing['portal_url'] ?: null,
                    'remarks' => $existing['remarks'] ?: null,
                    'last_verified_at' => $existing['last_verified_at'] ?: null,
                    'updated_by' => $updatedBy,
                ]);
            }
        });

        Logger::info('client.credentials_saved', [
            'client_id' => $clientId,
            'updated_by' => $updatedBy,
        ]);
    }

    public function registerPortalClient(array $input, array $files = []): array
    {
        $password = (string) ($input['password'] ?? '');
        $confirmPassword = (string) ($input['confirm_password'] ?? '');
        $usernameBasis = strtoupper(trim((string) ($input['username_basis'] ?? 'PAN')));
        $email = trim((string) ($input['contact_email'] ?? $input['email'] ?? ''));

        if ($email === '') {
            throw new RuntimeException('Contact email is required for portal registration.');
        }

        if ($password === '' || $confirmPassword === '') {
            throw new RuntimeException('Password and confirmation password are required.');
        }

        if ($password !== $confirmPassword) {
            throw new RuntimeException('Password and confirmation password must match.');
        }

        $portalAccount = null;
        $clientId = $this->runInTransaction(function () use ($input, $usernameBasis, $password, &$portalAccount): int {
            $pan = strtoupper(trim((string) ($input['pan'] ?? '')));
            $legalName = trim((string) ($input['legal_name'] ?? ''));
            $aadhaar = preg_replace('/\D+/', '', (string) ($input['aadhaar_no'] ?? ''));

            if ($pan === '' || $legalName === '') {
                throw new RuntimeException('PAN and legal name are required.');
            }

            if ($aadhaar !== '' && strlen($aadhaar) !== 12) {
                throw new RuntimeException('Aadhaar number must be 12 digits.');
            }

            if ($this->clients->panExists($pan)) {
                throw new RuntimeException('A client with this PAN already exists.');
            }

            $clientCode = 'CLT/' . date('Y') . '/' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
            $encryptedAadhaar = $aadhaar !== '' ? $this->encryption->encrypt($aadhaar) : null;

            $clientId = $this->clients->create([
                'client_code' => $clientCode,
                'client_type' => (string) ($input['client_type'] ?? 'INDIVIDUAL'),
                'legal_name' => $legalName,
                'trade_name' => trim((string) ($input['trade_name'] ?? '')) ?: null,
                'pan' => $pan,
                'tan' => strtoupper(trim((string) ($input['tan'] ?? ''))) ?: null,
                'gstin' => strtoupper(trim((string) ($input['gstin'] ?? ''))) ?: null,
                'aadhaar_no' => null,
                'aadhaar_ciphertext' => $encryptedAadhaar['ciphertext'] ?? null,
                'aadhaar_iv' => $encryptedAadhaar['iv'] ?? null,
                'aadhaar_last4' => $aadhaar !== '' ? substr($aadhaar, -4) : null,
                'email' => trim((string) ($input['email'] ?? '')) ?: null,
                'mobile' => trim((string) ($input['mobile'] ?? '')) ?: null,
                'alternate_mobile' => trim((string) ($input['alternate_mobile'] ?? '')) ?: null,
                'landline' => trim((string) ($input['landline'] ?? '')) ?: null,
                'address_line1' => trim((string) ($input['address_line1'] ?? '')) ?: null,
                'address_line2' => trim((string) ($input['address_line2'] ?? '')) ?: null,
                'city' => trim((string) ($input['city'] ?? '')) ?: null,
                'state_name' => trim((string) ($input['state_name'] ?? '')) ?: null,
                'postal_code' => trim((string) ($input['postal_code'] ?? '')) ?: null,
                'default_company_id' => null,
                'assigned_crm_id' => null,
            ]);

            $contactId = $this->clients->createContact([
                'client_id' => $clientId,
                'contact_name' => trim((string) ($input['contact_name'] ?? $legalName)),
                'designation' => trim((string) ($input['designation'] ?? '')) ?: null,
                'email' => trim((string) ($input['contact_email'] ?? $input['email'] ?? '')) ?: null,
                'mobile' => trim((string) ($input['contact_mobile'] ?? $input['mobile'] ?? '')) ?: null,
                'can_login' => 1,
            ]);

            $portalAccount = $this->users->createPortalUserForClientContact(
                $contactId,
                $usernameBasis,
                $password,
                trim((string) ($input['contact_name'] ?? $legalName)),
                trim((string) ($input['contact_email'] ?? $input['email'] ?? '')),
                trim((string) ($input['contact_mobile'] ?? $input['mobile'] ?? '')) ?: null,
                null
            );

            return $clientId;
        });

        $this->uploadIdentityDocuments($clientId, $files, (int) ($portalAccount['user_id'] ?? 0));

        Logger::info('client.portal_registered', [
            'client_id' => $clientId,
            'username' => $portalAccount['username'] ?? null,
            'user_id' => $portalAccount['user_id'] ?? null,
        ]);

        return [
            'client_id' => $clientId,
            'user_id' => (int) ($portalAccount['user_id'] ?? 0),
            'username' => (string) ($portalAccount['username'] ?? ''),
        ];
    }

    public static function portalDefinitions(): array
    {
        return [
            'INCOME_TAX' => ['label' => 'Income Tax Portal'],
            'GST' => ['label' => 'GST Portal'],
            'TRACES' => ['label' => 'TDS TRACES Portal'],
            'GST_EINVOICE' => ['label' => 'GST E-Invoice Portal'],
            'GST_EWAY_BILL' => ['label' => 'GST E-Way Bill Portal'],
            'UDYAM' => ['label' => 'UDYAM Portal'],
            'MCA' => ['label' => 'MCA Portal'],
            'EPFO' => ['label' => 'EPFO Portal'],
            'ESIC' => ['label' => 'ESIC Portal'],
        ];
    }

    private function runInTransaction(callable $callback): mixed
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $result = $callback();
            $connection->commit();

            return $result;
        } catch (Throwable $throwable) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $throwable;
        }
    }

    private function uploadIdentityDocuments(int $clientId, array $files, ?int $uploadedBy): void
    {
        if (($uploadedBy ?? 0) <= 0) {
            return;
        }

        if (!empty($files['pan_document'])) {
            $this->documentUploads->uploadLinkedDocuments(
                $clientId,
                'CLIENT',
                $clientId,
                'CLIENT_PAN_CARD_IMAGE',
                $files['pan_document'],
                $uploadedBy,
                'client_identity'
            );
        }

        if (!empty($files['aadhaar_document'])) {
            $this->documentUploads->uploadLinkedDocuments(
                $clientId,
                'CLIENT',
                $clientId,
                'CLIENT_AADHAAR_CARD_IMAGE',
                $files['aadhaar_document'],
                $uploadedBy,
                'client_identity'
            );
        }
    }

    private function nextCustomPortalSequence(array $credentials): int
    {
        $max = 0;

        foreach ($credentials as $credential) {
            $portalCode = (string) ($credential['portal_code'] ?? '');
            if (preg_match('/^CUSTOM_PORTAL_(\d+)$/', $portalCode, $matches) === 1) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max + 1;
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class BillingRepository
{
    public function paginateServiceOrdersForBilling(int $page = 1, int $perPage = 12, string $search = ''): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(*)
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             INNER JOIN companies comp ON comp.id = so.company_id
             INNER JOIN service_types st ON st.id = so.service_type_id
             WHERE 1 = 1";

        $dataSql = "SELECT so.id,
                    so.so_no,
                    so.title,
                    so.current_stage_code,
                    c.legal_name AS client_name,
                    comp.display_name AS company_name,
                    st.name AS service_type_name
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             INNER JOIN companies comp ON comp.id = so.company_id
             INNER JOIN service_types st ON st.id = so.service_type_id
             WHERE 1 = 1";

        $params = [];
        if (trim($search) !== '') {
            $filterSql = " AND (
                so.so_no LIKE :search_so_no
                OR c.legal_name LIKE :search_client_name
                OR st.name LIKE :search_service_type
            )";
            $countSql .= $filterSql;
            $dataSql .= $filterSql;
            $searchTerm = '%' . trim($search) . '%';
            $params['search_so_no'] = $searchTerm;
            $params['search_client_name'] = $searchTerm;
            $params['search_service_type'] = $searchTerm;
        }

        $countStatement = Database::connection()->prepare($countSql);
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataSql .= " ORDER BY so.id DESC LIMIT :limit OFFSET :offset";

        $statement = Database::connection()->prepare($dataSql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $statement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function serviceOrderBillingContext(int $serviceOrderId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT so.*,
                    c.legal_name AS client_name,
                    c.client_code,
                    c.pan,
                    c.mobile,
                    comp.display_name AS company_name,
                    comp.code AS company_code,
                    fy.code AS financial_year_code,
                    fy.label AS financial_year_label,
                    st.name AS service_type_name,
                    ssf.is_client_paid
             FROM service_orders so
             INNER JOIN clients c ON c.id = so.client_id
             INNER JOIN companies comp ON comp.id = so.company_id
             INNER JOIN financial_years fy ON fy.id = so.financial_year_id
             INNER JOIN service_types st ON st.id = so.service_type_id
             LEFT JOIN service_order_status_flags ssf ON ssf.service_order_id = so.id
             WHERE so.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $serviceOrderId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function lockServiceOrderForUpdate(int $serviceOrderId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT so.id, so.company_id, so.financial_year_id, so.client_id, so.is_locked
             FROM service_orders so
             WHERE so.id = :id
             LIMIT 1
             FOR UPDATE"
        );
        $statement->execute(['id' => $serviceOrderId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function unrecoveredDisbursements(int $serviceOrderId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, expense_date, expense_type, amount, is_recoverable, proof_document_id, notes
             FROM disbursements
             WHERE service_order_id = :service_order_id
               AND is_recoverable = 1
               AND invoiced_at IS NULL
             ORDER BY expense_date ASC, id ASC"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allDisbursements(int $serviceOrderId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, expense_date, expense_type, amount, is_recoverable, proof_document_id, paid_to, notes, invoiced_at
             FROM disbursements
             WHERE service_order_id = :service_order_id
             ORDER BY id DESC"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function invoicesForServiceOrder(int $serviceOrderId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, invoice_no, invoice_date, invoice_type, gross_total, advance_adjusted, net_payable, payment_status, accounting_status
             FROM invoices
             WHERE service_order_id = :service_order_id
             ORDER BY id DESC"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paymentsForServiceOrder(int $serviceOrderId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT p.id, p.payment_date, p.amount, p.payment_mode, p.transaction_type, p.reference_no, p.status, r.id AS receipt_id, r.receipt_no
             FROM payments p
             LEFT JOIN receipts r ON r.payment_id = p.id
             WHERE p.service_order_id = :service_order_id
             ORDER BY p.id DESC"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createDisbursement(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO disbursements (
                service_order_id, expense_date, expense_type, amount, is_recoverable, proof_document_id, paid_to, notes, added_by, created_at, updated_at
             ) VALUES (
                :service_order_id, :expense_date, :expense_type, :amount, :is_recoverable, :proof_document_id, :paid_to, :notes, :added_by, NOW(), NOW()
             )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function updateDisbursementProof(int $disbursementId, int $documentId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE disbursements
             SET proof_document_id = :proof_document_id,
                 updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'proof_document_id' => $documentId,
            'id' => $disbursementId,
        ]);
    }

    public function createInvoice(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO invoices (
                invoice_no, company_id, financial_year_id, client_id, service_order_id, invoice_date, due_date,
                invoice_type, service_fee, disbursement_total, tax_total, gross_total, advance_adjusted, net_payable,
                payment_status, accounting_status, approved_by, approved_at, issued_at, notes, created_by, created_at, updated_at
            ) VALUES (
                :invoice_no, :company_id, :financial_year_id, :client_id, :service_order_id, :invoice_date, :due_date,
                :invoice_type, :service_fee, :disbursement_total, :tax_total, :gross_total, :advance_adjusted, :net_payable,
                :payment_status, 'ISSUED', :approved_by, NOW(), NOW(), :notes, :created_by, NOW(), NOW()
            )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function addInvoiceItem(array $payload): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO invoice_items (
                invoice_id, line_type, reference_type, reference_id, description, quantity, unit_price, line_total, created_at
             ) VALUES (
                :invoice_id, :line_type, :reference_type, :reference_id, :description, :quantity, :unit_price, :line_total, NOW()
             )"
        );
        $statement->execute($payload);
    }

    public function markDisbursementInvoiced(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));
        $statement = Database::connection()->prepare(
            "UPDATE disbursements
             SET invoiced_at = NOW(), updated_at = NOW()
             WHERE id IN ({$placeholders})"
        );
        $statement->execute($ids);
    }

    public function successfulAdvanceBalance(int $serviceOrderId): float
    {
        $statement = Database::connection()->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM payments
             WHERE service_order_id = :service_order_id
               AND transaction_type = 'ADVANCE'
               AND status = 'SUCCESS'"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return (float) ($statement->fetchColumn() ?: 0.0);
    }

    public function invoiceTotals(int $serviceOrderId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT
                COUNT(DISTINCT i.id) AS invoice_count,
                COALESCE(SUM(i.net_payable), 0) AS invoice_total,
                COALESCE(SUM(pa.allocated_amount), 0) AS invoice_paid_total
             FROM invoices i
             LEFT JOIN payment_allocations pa ON pa.invoice_id = i.id
             WHERE i.service_order_id = :service_order_id
               AND i.accounting_status <> 'CANCELLED'"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetch(PDO::FETCH_ASSOC) ?: ['invoice_count' => 0, 'invoice_total' => 0, 'invoice_paid_total' => 0];
    }

    public function createPayment(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO payments (
                client_id, company_id, service_order_id, invoice_id, payment_date, amount, payment_mode, transaction_type,
                reference_no, gateway_order_id, gateway_payment_id, gateway_signature, status, received_by, notes, created_at, updated_at
             ) VALUES (
                :client_id, :company_id, :service_order_id, :invoice_id, :payment_date, :amount, :payment_mode, :transaction_type,
                :reference_no, :gateway_order_id, :gateway_payment_id, :gateway_signature, :status, :received_by, :notes, NOW(), NOW()
             )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function openInvoices(int $serviceOrderId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT i.id,
                    i.invoice_no,
                    i.net_payable,
                    i.payment_status,
                    COALESCE(SUM(pa.allocated_amount), 0) AS allocated_amount
             FROM invoices i
             LEFT JOIN payment_allocations pa ON pa.invoice_id = i.id
             WHERE i.service_order_id = :service_order_id
               AND i.accounting_status <> 'CANCELLED'
             GROUP BY i.id, i.invoice_no, i.net_payable, i.payment_status
             HAVING i.net_payable - COALESCE(SUM(pa.allocated_amount), 0) > 0
             ORDER BY i.id ASC"
        );
        $statement->execute(['service_order_id' => $serviceOrderId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPaymentAllocation(int $paymentId, int $invoiceId, float $amount, ?int $allocatedBy): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO payment_allocations (payment_id, invoice_id, allocated_amount, allocated_at, allocated_by)
             VALUES (:payment_id, :invoice_id, :allocated_amount, NOW(), :allocated_by)"
        );
        $statement->execute([
            'payment_id' => $paymentId,
            'invoice_id' => $invoiceId,
            'allocated_amount' => $amount,
            'allocated_by' => $allocatedBy,
        ]);
    }

    public function updateInvoicePaymentStatus(int $invoiceId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE invoices i
             LEFT JOIN (
                SELECT invoice_id, COALESCE(SUM(allocated_amount), 0) AS allocated_total
                FROM payment_allocations
                WHERE invoice_id = :invoice_id
                GROUP BY invoice_id
             ) pa ON pa.invoice_id = i.id
             SET i.payment_status = CASE
                    WHEN COALESCE(pa.allocated_total, 0) <= 0 THEN 'UNPAID'
                    WHEN COALESCE(pa.allocated_total, 0) >= i.net_payable THEN 'PAID'
                    ELSE 'PARTIALLY_PAID'
                 END,
                 i.updated_at = NOW()
             WHERE i.id = :invoice_id_update"
        );
        $statement->execute([
            'invoice_id' => $invoiceId,
            'invoice_id_update' => $invoiceId,
        ]);
    }

    public function createReceipt(array $payload): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO receipts (
                receipt_no, company_id, financial_year_id, client_id, payment_id, receipt_date, receipt_amount, generated_by, created_at
             ) VALUES (
                :receipt_no, :company_id, :financial_year_id, :client_id, :payment_id, :receipt_date, :receipt_amount, :generated_by, NOW()
             )"
        );
        $statement->execute($payload);

        return (int) Database::connection()->lastInsertId();
    }

    public function addReceiptItem(int $receiptId, ?int $invoiceId, float $allocatedAmount): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO payment_receipt_items (receipt_id, invoice_id, allocated_amount, created_at)
             VALUES (:receipt_id, :invoice_id, :allocated_amount, NOW())"
        );
        $statement->execute([
            'receipt_id' => $receiptId,
            'invoice_id' => $invoiceId,
            'allocated_amount' => $allocatedAmount,
        ]);
    }

    public function nextSequence(int $companyId, int $financialYearId, string $type): array
    {
        $insert = Database::connection()->prepare(
            "INSERT IGNORE INTO numbering_sequences (company_id, financial_year_id, sequence_type, last_number, updated_at)
             VALUES (:company_id, :financial_year_id, :sequence_type, 0, NOW())"
        );
        $insert->execute([
            'company_id' => $companyId,
            'financial_year_id' => $financialYearId,
            'sequence_type' => $type,
        ]);

        $select = Database::connection()->prepare(
            "SELECT ns.id, ns.last_number, c.code AS company_code, fy.code AS financial_year_code
             FROM numbering_sequences ns
             INNER JOIN companies c ON c.id = ns.company_id
             INNER JOIN financial_years fy ON fy.id = ns.financial_year_id
             WHERE ns.company_id = :company_id
               AND ns.financial_year_id = :financial_year_id
               AND ns.sequence_type = :sequence_type
             LIMIT 1
             FOR UPDATE"
        );
        $select->execute([
            'company_id' => $companyId,
            'financial_year_id' => $financialYearId,
            'sequence_type' => $type,
        ]);

        return $select->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function bumpSequence(int $sequenceId, int $number): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE numbering_sequences
             SET last_number = :last_number, updated_at = NOW()
             WHERE id = :id"
        );
        $statement->execute([
            'last_number' => $number,
            'id' => $sequenceId,
        ]);
    }

    public function updateClientPaidFlag(int $serviceOrderId, bool $paid): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE service_order_status_flags
             SET is_client_paid = :is_client_paid, updated_at = NOW()
             WHERE service_order_id = :service_order_id"
        );
        $statement->execute([
            'is_client_paid' => $paid ? 1 : 0,
            'service_order_id' => $serviceOrderId,
        ]);
    }

    public function recordActivity(int $userId, string $actionCode, string $entityType, int $entityId, string $description): void
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO activity_logs (
                user_id, module_code, action_code, entity_type, entity_id, description, created_at
             ) VALUES (
                :user_id, 'BILLING', :action_code, :entity_type, :entity_id, :description, NOW()
             )"
        );
        $statement->execute([
            'user_id' => $userId,
            'action_code' => $actionCode,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
        ]);
    }

    public function portalInvoices(int $clientId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT i.id,
                    i.service_order_id,
                    i.invoice_no,
                    i.invoice_date,
                    i.due_date,
                    i.invoice_type,
                    i.gross_total,
                    i.net_payable,
                    i.payment_status,
                    so.so_no,
                    so.title,
                    st.name AS service_type_name,
                    comp.display_name AS company_name,
                    GREATEST(i.net_payable - COALESCE(SUM(pa.allocated_amount), 0), 0) AS outstanding_amount
             FROM invoices i
             INNER JOIN service_orders so ON so.id = i.service_order_id
             INNER JOIN service_types st ON st.id = so.service_type_id
             INNER JOIN companies comp ON comp.id = i.company_id
             LEFT JOIN payment_allocations pa ON pa.invoice_id = i.id
             WHERE i.client_id = :client_id
               AND i.accounting_status <> 'CANCELLED'
             GROUP BY i.id, i.service_order_id, i.invoice_no, i.invoice_date, i.due_date, i.invoice_type, i.gross_total, i.net_payable, i.payment_status, so.so_no, so.title, st.name, comp.display_name
             ORDER BY i.invoice_date DESC, i.id DESC"
        );
        $statement->execute(['client_id' => $clientId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function portalPayments(int $clientId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT p.id,
                    p.service_order_id,
                    p.payment_date,
                    p.amount,
                    p.payment_mode,
                    p.transaction_type,
                    p.reference_no,
                    p.status,
                    so.so_no,
                    r.id AS receipt_id,
                    r.receipt_no,
                    r.receipt_date
             FROM payments p
             LEFT JOIN service_orders so ON so.id = p.service_order_id
             LEFT JOIN receipts r ON r.payment_id = p.id
             WHERE p.client_id = :client_id
             ORDER BY p.payment_date DESC, p.id DESC"
        );
        $statement->execute(['client_id' => $clientId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function portalInvoiceById(int $invoiceId, int $clientId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT i.id,
                    i.client_id,
                    i.service_order_id,
                    i.invoice_no,
                    i.net_payable,
                    i.payment_status,
                    GREATEST(i.net_payable - COALESCE(SUM(pa.allocated_amount), 0), 0) AS outstanding_amount
             FROM invoices i
             LEFT JOIN payment_allocations pa ON pa.invoice_id = i.id
             WHERE i.id = :id
               AND i.client_id = :client_id
               AND i.accounting_status <> 'CANCELLED'
             GROUP BY i.id, i.client_id, i.service_order_id, i.invoice_no, i.net_payable, i.payment_status
             LIMIT 1"
        );
        $statement->execute([
            'id' => $invoiceId,
            'client_id' => $clientId,
        ]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function invoiceDetail(int $invoiceId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT i.*,
                    c.legal_name AS client_name,
                    c.pan,
                    comp.display_name AS company_name,
                    fy.label AS financial_year_label,
                    so.so_no,
                    so.title AS service_order_title,
                    st.name AS service_type_name
             FROM invoices i
             INNER JOIN clients c ON c.id = i.client_id
             INNER JOIN companies comp ON comp.id = i.company_id
             INNER JOIN financial_years fy ON fy.id = i.financial_year_id
             INNER JOIN service_orders so ON so.id = i.service_order_id
             INNER JOIN service_types st ON st.id = so.service_type_id
             WHERE i.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $invoiceId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function invoiceItems(int $invoiceId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, line_type, description, quantity, unit_price, line_total
             FROM invoice_items
             WHERE invoice_id = :invoice_id
             ORDER BY id ASC"
        );
        $statement->execute(['invoice_id' => $invoiceId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function receiptDetail(int $receiptId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT r.*,
                    c.legal_name AS client_name,
                    c.pan,
                    comp.display_name AS company_name,
                    fy.label AS financial_year_label,
                    p.payment_mode,
                    p.transaction_type,
                    p.reference_no,
                    p.service_order_id,
                    so.so_no
             FROM receipts r
             INNER JOIN clients c ON c.id = r.client_id
             INNER JOIN companies comp ON comp.id = r.company_id
             INNER JOIN financial_years fy ON fy.id = r.financial_year_id
             INNER JOIN payments p ON p.id = r.payment_id
             LEFT JOIN service_orders so ON so.id = p.service_order_id
             WHERE r.id = :id
             LIMIT 1"
        );
        $statement->execute(['id' => $receiptId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }

    public function receiptItems(int $receiptId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT pri.id,
                    pri.invoice_id,
                    pri.allocated_amount,
                    i.invoice_no
             FROM payment_receipt_items pri
             LEFT JOIN invoices i ON i.id = pri.invoice_id
             WHERE pri.receipt_id = :receipt_id
             ORDER BY pri.id ASC"
        );
        $statement->execute(['receipt_id' => $receiptId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}

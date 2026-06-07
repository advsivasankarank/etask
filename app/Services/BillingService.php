<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Repositories\BillingRepository;
use RuntimeException;
use Throwable;

final class BillingService
{
    public function __construct(
        private readonly BillingRepository $billing = new BillingRepository()
    ) {
    }

    public function createDisbursement(array $input, int $userId): int
    {
        $serviceOrderId = (int) ($input['service_order_id'] ?? 0);
        if ($serviceOrderId <= 0) {
            throw new RuntimeException('Service order is required.');
        }

        $amount = round((float) ($input['amount'] ?? 0), 2);
        if ($amount <= 0) {
            throw new RuntimeException('Disbursement amount must be greater than zero.');
        }

        $context = $this->billing->serviceOrderBillingContext($serviceOrderId);
        if ($context === null) {
            throw new RuntimeException('Service order not found for disbursement.');
        }

        $disbursementId = $this->billing->createDisbursement([
            'service_order_id' => $serviceOrderId,
            'expense_date' => (string) ($input['expense_date'] ?? date('Y-m-d')),
            'expense_type' => trim((string) ($input['expense_type'] ?? 'General')),
            'amount' => $amount,
            'is_recoverable' => !empty($input['is_recoverable']) ? 1 : 0,
            'proof_document_id' => null,
            'paid_to' => trim((string) ($input['paid_to'] ?? '')) ?: null,
            'notes' => trim((string) ($input['notes'] ?? '')) ?: null,
            'added_by' => $userId,
        ]);

        Logger::info('billing.disbursement_created', [
            'disbursement_id' => $disbursementId,
            'service_order_id' => $serviceOrderId,
            'amount' => $amount,
            'user_id' => $userId,
        ]);

        return $disbursementId;
    }

    public function createInvoice(array $input, int $userId): int
    {
        $serviceOrderId = (int) ($input['service_order_id'] ?? 0);
        $serviceFee = round((float) ($input['service_fee'] ?? 0), 2);
        $taxTotal = round((float) ($input['tax_total'] ?? 0), 2);
        $invoiceType = (string) ($input['invoice_type'] ?? 'FINAL');

        if ($serviceOrderId <= 0) {
            throw new RuntimeException('Service order is required.');
        }

        if ($serviceFee < 0 || $taxTotal < 0) {
            throw new RuntimeException('Service fee and tax cannot be negative.');
        }

        return $this->runInTransaction(function () use ($serviceOrderId, $serviceFee, $taxTotal, $invoiceType, $input, $userId): int {
            $locked = $this->billing->lockServiceOrderForUpdate($serviceOrderId);
            $context = $this->billing->serviceOrderBillingContext($serviceOrderId);

            if ($locked === null || $context === null) {
                throw new RuntimeException('Service order not found for invoicing.');
            }

            $sequence = $this->billing->nextSequence((int) $locked['company_id'], (int) $locked['financial_year_id'], 'INV');
            if ($sequence === []) {
                throw new RuntimeException('Unable to prepare invoice sequence.');
            }

            $nextNumber = (int) $sequence['last_number'] + 1;
            $invoiceNo = sprintf('INV/%s/%s/%04d', $sequence['company_code'], $sequence['financial_year_code'], $nextNumber);
            $this->billing->bumpSequence((int) $sequence['id'], $nextNumber);

            $disbursements = $invoiceType === 'FINAL' ? $this->billing->unrecoveredDisbursements($serviceOrderId) : [];
            $disbursementTotal = array_reduce($disbursements, static fn (float $carry, array $row): float => $carry + (float) $row['amount'], 0.0);
            $grossTotal = round($serviceFee + $taxTotal + $disbursementTotal, 2);

            $availableAdvance = $this->billing->successfulAdvanceBalance($serviceOrderId);
            $advanceAdjusted = $invoiceType === 'FINAL' ? round(min($availableAdvance, $grossTotal), 2) : 0.0;
            $netPayable = round(max($grossTotal - $advanceAdjusted, 0), 2);

            $invoiceId = $this->billing->createInvoice([
                'invoice_no' => $invoiceNo,
                'company_id' => (int) $locked['company_id'],
                'financial_year_id' => (int) $locked['financial_year_id'],
                'client_id' => (int) $locked['client_id'],
                'service_order_id' => $serviceOrderId,
                'invoice_date' => (string) ($input['invoice_date'] ?? date('Y-m-d')),
                'due_date' => trim((string) ($input['due_date'] ?? '')) ?: null,
                'invoice_type' => $invoiceType,
                'service_fee' => $serviceFee,
                'disbursement_total' => round($disbursementTotal, 2),
                'tax_total' => $taxTotal,
                'gross_total' => $grossTotal,
                'advance_adjusted' => $advanceAdjusted,
                'net_payable' => $netPayable,
                'payment_status' => $netPayable <= 0 ? 'PAID' : 'UNPAID',
                'approved_by' => $userId,
                'notes' => trim((string) ($input['notes'] ?? '')) ?: null,
                'created_by' => $userId,
            ]);

            if ($serviceFee > 0) {
                $this->billing->addInvoiceItem([
                    'invoice_id' => $invoiceId,
                    'line_type' => 'SERVICE_FEE',
                    'reference_type' => 'SERVICE_ORDER',
                    'reference_id' => $serviceOrderId,
                    'description' => 'Professional service fee',
                    'quantity' => 1,
                    'unit_price' => $serviceFee,
                    'line_total' => $serviceFee,
                ]);
            }

            foreach ($disbursements as $disbursement) {
                $this->billing->addInvoiceItem([
                    'invoice_id' => $invoiceId,
                    'line_type' => 'DISBURSEMENT',
                    'reference_type' => 'DISBURSEMENT',
                    'reference_id' => (int) $disbursement['id'],
                    'description' => 'Recoverable disbursement - ' . $disbursement['expense_type'],
                    'quantity' => 1,
                    'unit_price' => (float) $disbursement['amount'],
                    'line_total' => (float) $disbursement['amount'],
                ]);
            }

            if ($taxTotal > 0) {
                $this->billing->addInvoiceItem([
                    'invoice_id' => $invoiceId,
                    'line_type' => 'TAX',
                    'reference_type' => 'OTHER',
                    'reference_id' => null,
                    'description' => 'Tax component',
                    'quantity' => 1,
                    'unit_price' => $taxTotal,
                    'line_total' => $taxTotal,
                ]);
            }

            if ($advanceAdjusted > 0) {
                $this->billing->addInvoiceItem([
                    'invoice_id' => $invoiceId,
                    'line_type' => 'ADJUSTMENT',
                    'reference_type' => 'PAYMENT',
                    'reference_id' => null,
                    'description' => 'Advance adjustment',
                    'quantity' => 1,
                    'unit_price' => -1 * $advanceAdjusted,
                    'line_total' => -1 * $advanceAdjusted,
                ]);
            }

            $this->billing->markDisbursementInvoiced(array_map(static fn (array $row): int => (int) $row['id'], $disbursements));
            $this->syncServiceOrderClientPaid($serviceOrderId);
            $this->billing->recordActivity($userId, 'INVOICE_CREATE', 'invoices', $invoiceId, 'Invoice created: ' . $invoiceNo);
            Logger::info('billing.invoice_created', [
                'invoice_id' => $invoiceId,
                'invoice_no' => $invoiceNo,
                'service_order_id' => $serviceOrderId,
                'gross_total' => $grossTotal,
                'net_payable' => $netPayable,
                'user_id' => $userId,
            ]);

            return $invoiceId;
        });
    }

    public function recordPayment(array $input, int $userId): int
    {
        $serviceOrderId = (int) ($input['service_order_id'] ?? 0);
        $amount = round((float) ($input['amount'] ?? 0), 2);
        $transactionType = (string) ($input['transaction_type'] ?? 'INVOICE_PAYMENT');

        if ($serviceOrderId <= 0) {
            throw new RuntimeException('Service order is required.');
        }

        if ($amount <= 0) {
            throw new RuntimeException('Payment amount must be greater than zero.');
        }

        return $this->runInTransaction(function () use ($serviceOrderId, $amount, $transactionType, $input, $userId): int {
            $locked = $this->billing->lockServiceOrderForUpdate($serviceOrderId);
            $context = $this->billing->serviceOrderBillingContext($serviceOrderId);

            if ($locked === null || $context === null) {
                throw new RuntimeException('Service order not found for payment.');
            }

            $paymentId = $this->billing->createPayment([
                'client_id' => (int) $locked['client_id'],
                'company_id' => (int) $locked['company_id'],
                'service_order_id' => $serviceOrderId,
                'invoice_id' => null,
                'payment_date' => (string) ($input['payment_date'] ?? date('Y-m-d')),
                'amount' => $amount,
                'payment_mode' => (string) ($input['payment_mode'] ?? 'BANK_TRANSFER'),
                'transaction_type' => $transactionType,
                'reference_no' => trim((string) ($input['reference_no'] ?? '')) ?: null,
                'gateway_order_id' => trim((string) ($input['gateway_order_id'] ?? '')) ?: null,
                'gateway_payment_id' => trim((string) ($input['gateway_payment_id'] ?? '')) ?: null,
                'gateway_signature' => trim((string) ($input['gateway_signature'] ?? '')) ?: null,
                'status' => (string) ($input['status'] ?? 'SUCCESS'),
                'received_by' => $userId,
                'notes' => trim((string) ($input['notes'] ?? '')) ?: null,
            ]);

            $remaining = $amount;
            $openInvoices = $transactionType === 'INVOICE_PAYMENT' ? $this->billing->openInvoices($serviceOrderId) : [];
            foreach ($openInvoices as $invoice) {
                if ($remaining <= 0) {
                    break;
                }

                $balance = round((float) $invoice['net_payable'] - (float) $invoice['allocated_amount'], 2);
                if ($balance <= 0) {
                    continue;
                }

                $allocate = min($remaining, $balance);
                $this->billing->addPaymentAllocation($paymentId, (int) $invoice['id'], $allocate, $userId);
                $this->billing->updateInvoicePaymentStatus((int) $invoice['id']);
                $remaining = round($remaining - $allocate, 2);
            }

            $receiptId = $this->createReceiptForPayment(
                paymentId: $paymentId,
                companyId: (int) $locked['company_id'],
                financialYearId: (int) $locked['financial_year_id'],
                clientId: (int) $locked['client_id'],
                receiptDate: (string) ($input['payment_date'] ?? date('Y-m-d')),
                receiptAmount: $amount,
                generatedBy: $userId,
                allocationsSource: $transactionType === 'INVOICE_PAYMENT' ? $this->billing->openInvoices($serviceOrderId) : []
            );

            $this->syncServiceOrderClientPaid($serviceOrderId);
            $this->billing->recordActivity($userId, 'PAYMENT_RECORD', 'payments', $paymentId, 'Payment recorded and receipt generated.');
            Logger::info('billing.payment_recorded', [
                'payment_id' => $paymentId,
                'receipt_id' => $receiptId,
                'service_order_id' => $serviceOrderId,
                'amount' => $amount,
                'transaction_type' => $transactionType,
                'user_id' => $userId,
            ]);

            return $receiptId;
        });
    }

    public function billingDashboard(int $serviceOrderId): array
    {
        $context = $this->billing->serviceOrderBillingContext($serviceOrderId);
        if ($context === null) {
            throw new RuntimeException('Service order not found.');
        }

        return [
            'order' => $context,
            'disbursements' => $this->billing->allDisbursements($serviceOrderId),
            'invoices' => $this->billing->invoicesForServiceOrder($serviceOrderId),
            'payments' => $this->billing->paymentsForServiceOrder($serviceOrderId),
            'advance_balance' => $this->billing->successfulAdvanceBalance($serviceOrderId),
            'razorpay' => config('razorpay'),
        ];
    }

    private function createReceiptForPayment(
        int $paymentId,
        int $companyId,
        int $financialYearId,
        int $clientId,
        string $receiptDate,
        float $receiptAmount,
        int $generatedBy,
        array $allocationsSource
    ): int {
        $sequence = $this->billing->nextSequence($companyId, $financialYearId, 'RCPT');
        if ($sequence === []) {
            throw new RuntimeException('Unable to prepare receipt sequence.');
        }

        $nextNumber = (int) $sequence['last_number'] + 1;
        $receiptNo = sprintf('RCPT/%s/%s/%04d', $sequence['company_code'], $sequence['financial_year_code'], $nextNumber);
        $this->billing->bumpSequence((int) $sequence['id'], $nextNumber);

        $receiptId = $this->billing->createReceipt([
            'receipt_no' => $receiptNo,
            'company_id' => $companyId,
            'financial_year_id' => $financialYearId,
            'client_id' => $clientId,
            'payment_id' => $paymentId,
            'receipt_date' => $receiptDate,
            'receipt_amount' => $receiptAmount,
            'generated_by' => $generatedBy,
        ]);

        $allocationStatement = Database::connection()->prepare(
            "SELECT invoice_id, allocated_amount
             FROM payment_allocations
             WHERE payment_id = :payment_id"
        );
        $allocationStatement->execute(['payment_id' => $paymentId]);
        $allocations = $allocationStatement->fetchAll(\PDO::FETCH_ASSOC);

        if ($allocations === []) {
            $this->billing->addReceiptItem($receiptId, null, $receiptAmount);
        } else {
            foreach ($allocations as $allocation) {
                $this->billing->addReceiptItem($receiptId, (int) $allocation['invoice_id'], (float) $allocation['allocated_amount']);
            }
        }

        return $receiptId;
    }

    private function syncServiceOrderClientPaid(int $serviceOrderId): void
    {
        $totals = $this->billing->invoiceTotals($serviceOrderId);
        $invoiceCount = (int) ($totals['invoice_count'] ?? 0);
        $invoiceTotal = round((float) ($totals['invoice_total'] ?? 0), 2);
        $paidTotal = round((float) ($totals['invoice_paid_total'] ?? 0), 2);

        $isPaid = $invoiceCount > 0 && ($invoiceTotal <= 0 || $paidTotal >= $invoiceTotal);
        $this->billing->updateClientPaidFlag($serviceOrderId, $isPaid);
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
}

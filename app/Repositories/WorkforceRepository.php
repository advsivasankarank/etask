<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class WorkforceRepository
{
    public function summaryCounts(): array
    {
        return [
            'total_staff' => $this->scalar("SELECT COUNT(*) FROM users WHERE is_active = 1"),
            'present_today' => $this->scalar("SELECT COUNT(DISTINCT user_id) FROM attendance_sessions WHERE DATE(login_at) = CURDATE()"),
            'on_work' => $this->scalar("SELECT COUNT(DISTINCT user_id) FROM attendance_sessions WHERE DATE(login_at) = CURDATE() AND logout_at IS NULL"),
            'daily_reports_pending' => $this->scalar("SELECT COUNT(*) FROM daily_work_reports WHERE report_date = CURDATE() AND status = 'SUBMITTED'"),
            'consultants_active' => $this->scalar("SELECT COUNT(*) FROM consultants WHERE status = 'ACTIVE'"),
            'consultant_assignments_pending' => $this->scalar("SELECT COUNT(*) FROM consultant_assignments WHERE status IN ('ASSIGNED')"),
            'consultant_bills_pending' => $this->scalar("SELECT COUNT(*) FROM consultant_bills WHERE review_status IN ('PENDING')"),
        ];
    }

    public function paginateConsultants(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM consultants c WHERE c.status != 'BLACKLISTED'";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['search'] ?? '')) !== '') {
            $term = '%' . trim((string) $filters['search']) . '%';
            $whereSql .= " AND (c.name LIKE :search OR c.firm_name LIKE :search OR c.pan LIKE :search OR c.mobile LIKE :search)";
            $params['search'] = $term;
        }

        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $whereSql .= " AND c.status = :status";
            $params['status'] = trim((string) $filters['status']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT c.*
             {$baseSql}{$whereSql}
             ORDER BY c.name ASC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $dataStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStatement->execute();

        return [
            'items' => $dataStatement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function findConsultantById(int $id): ?array
    {
        $statement = Database::connection()->prepare("SELECT * FROM consultants WHERE id = :id AND status != 'BLACKLISTED' LIMIT 1");
        $statement->execute(['id' => $id]);
        $record = $statement->fetch(PDO::FETCH_ASSOC);
        return $record === false ? null : $record;
    }

    public function createConsultant(array $data): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO consultants (name, firm_name, mobile, email, pan, gstin, address, expertise, status, remarks, created_by)
             VALUES (:name, :firm_name, :mobile, :email, :pan, :gstin, :address, :expertise, :status, :remarks, :created_by)"
        );
        $statement->execute([
            'name' => $data['name'],
            'firm_name' => $data['firm_name'] ?: null,
            'mobile' => $data['mobile'] ?: null,
            'email' => $data['email'] ?: null,
            'pan' => $data['pan'] ?: null,
            'gstin' => $data['gstin'] ?: null,
            'address' => $data['address'] ?: null,
            'expertise' => $data['expertise'] ?: null,
            'status' => $data['status'] ?? 'ACTIVE',
            'remarks' => $data['remarks'] ?: null,
            'created_by' => $data['created_by'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function updateConsultant(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE consultants SET name = :name, firm_name = :firm_name, mobile = :mobile, email = :email, pan = :pan, gstin = :gstin, address = :address, expertise = :expertise, status = :status, remarks = :remarks WHERE id = :id"
        );
        $statement->execute([
            'name' => $data['name'],
            'firm_name' => $data['firm_name'] ?: null,
            'mobile' => $data['mobile'] ?: null,
            'email' => $data['email'] ?: null,
            'pan' => $data['pan'] ?: null,
            'gstin' => $data['gstin'] ?: null,
            'address' => $data['address'] ?: null,
            'expertise' => $data['expertise'] ?: null,
            'status' => $data['status'] ?? 'ACTIVE',
            'remarks' => $data['remarks'] ?: null,
            'id' => $id,
        ]);
    }

    public function archiveConsultant(int $id): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE consultants SET status = 'INACTIVE', archived_at = NOW() WHERE id = :id"
        );
        $statement->execute(['id' => $id]);
    }

    public function assignmentsForConsultant(int $consultantId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT ca.*, so.so_no
             FROM consultant_assignments ca
             LEFT JOIN service_orders so ON so.id = ca.service_order_id
             WHERE ca.consultant_user_id = :consultant_user_id
             ORDER BY ca.id DESC"
        );
        $statement->execute(['consultant_user_id' => $consultantId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function billsForConsultant(int $consultantId): array
    {
        $statement = Database::connection()->prepare(
            "SELECT cb.* FROM consultant_bills cb
             INNER JOIN consultant_assignments ca ON ca.id = cb.consultant_assignment_id
             WHERE ca.consultant_user_id = :consultant_user_id
             ORDER BY cb.id DESC"
        );
        $statement->execute(['consultant_user_id' => $consultantId]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function allActiveConsultants(): array
    {
        $statement = Database::connection()->prepare(
            "SELECT id, name, firm_name FROM consultants WHERE status = 'ACTIVE' ORDER BY name ASC"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginateAssignments(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM consultant_assignments ca
            LEFT JOIN consultants con ON con.id = ca.consultant_user_id
            LEFT JOIN service_orders so ON so.id = ca.service_order_id
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $whereSql .= " AND ca.status = :status";
            $params['status'] = trim((string) $filters['status']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT ca.*, con.name AS consultant_name, so.so_no
             {$baseSql}{$whereSql}
             ORDER BY ca.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $dataStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStatement->execute();

        return [
            'items' => $dataStatement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function createAssignment(array $data): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO consultant_assignments (consultant_user_id, service_order_id, assigned_by, status, remarks)
             VALUES (:consultant_user_id, :service_order_id, :assigned_by, :status, :remarks)"
        );
        $statement->execute([
            'consultant_user_id' => $data['consultant_id'],
            'service_order_id' => $data['service_order_id'] ?: null,
            'assigned_by' => $data['assigned_by'],
            'status' => $data['status'] ?? 'ASSIGNED',
            'remarks' => $data['remarks'] ?: null,
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function updateAssignmentStatus(int $id, string $status): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE consultant_assignments SET status = :status WHERE id = :id"
        );
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    public function paginateDeliverables(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM consultant_deliverables cd
            LEFT JOIN consultant_assignments ca ON ca.id = cd.consultant_assignment_id
            LEFT JOIN consultants con ON con.id = ca.consultant_user_id
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $whereSql .= " AND cd.review_status = :status";
            $params['status'] = trim((string) $filters['status']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT cd.*, con.name AS consultant_name, cd.review_status AS status
             {$baseSql}{$whereSql}
             ORDER BY cd.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $dataStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStatement->execute();

        return [
            'items' => $dataStatement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function updateDeliverableStatus(int $id, string $status): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE consultant_deliverables SET review_status = :status, reviewed_at = NOW() WHERE id = :id"
        );
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    public function paginateBills(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM consultant_bills cb
            LEFT JOIN consultant_assignments ca ON ca.id = cb.consultant_assignment_id
            LEFT JOIN consultants con ON con.id = ca.consultant_user_id
            WHERE 1=1";

        $whereSql = '';
        $params = [];

        if (trim((string) ($filters['status'] ?? '')) !== '') {
            $whereSql .= " AND cb.review_status = :status";
            $params['status'] = trim((string) $filters['status']);
        }

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}{$whereSql}");
        $countStatement->execute($params);
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT cb.*, con.name AS consultant_name, cb.review_status AS status
             {$baseSql}{$whereSql}
             ORDER BY cb.id DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $dataStatement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $dataStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStatement->execute();

        return [
            'items' => $dataStatement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function updateBillStatus(int $id, string $status): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE consultant_bills SET review_status = :status WHERE id = :id"
        );
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    public function paginatePayments(array $filters, int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $baseSql = "FROM consultant_payments cp
            LEFT JOIN consultant_bills cb ON cb.id = cp.consultant_bill_id
            LEFT JOIN consultant_assignments ca ON ca.id = cb.consultant_assignment_id
            LEFT JOIN consultants con ON con.id = ca.consultant_user_id
            WHERE 1=1";

        $countStatement = Database::connection()->prepare("SELECT COUNT(*) {$baseSql}");
        $countStatement->execute();
        $total = (int) $countStatement->fetchColumn();

        $dataStatement = Database::connection()->prepare(
            "SELECT cp.*, cb.bill_no, con.name AS consultant_name, cp.payment_mode AS mode
             {$baseSql}
             ORDER BY cp.id DESC
             LIMIT :limit OFFSET :offset"
        );

        $dataStatement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $dataStatement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $dataStatement->execute();

        return [
            'items' => $dataStatement->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function createPayment(array $data): int
    {
        $statement = Database::connection()->prepare(
            "INSERT INTO consultant_payments (consultant_bill_id, payment_date, amount, payment_mode, reference_no, remarks, paid_by)
             VALUES (:consultant_bill_id, :payment_date, :amount, :payment_mode, :reference_no, :remarks, :paid_by)"
        );
        $statement->execute([
            'consultant_bill_id' => $data['consultant_bill_id'],
            'payment_date' => $data['payment_date'] ?: date('Y-m-d'),
            'amount' => $data['amount'],
            'payment_mode' => $data['mode'] ?: null,
            'reference_no' => $data['reference_no'] ?: null,
            'remarks' => $data['remarks'] ?: null,
            'paid_by' => $data['created_by'],
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    private function scalar(string $sql, array $params = []): int
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }
}

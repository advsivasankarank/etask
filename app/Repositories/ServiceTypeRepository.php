<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ServiceTypeRepository
{
    public function allActive(): array
    {
        $statement = Database::connection()->query(
            "SELECT st.id,
                    st.code,
                    st.name,
                    st.service_group,
                    st.requires_payment_stage,
                    st.requires_e_verification,
                    st.default_sla_days,
                    c.id AS default_company_id,
                    c.code AS default_company_code,
                    c.display_name AS default_company_name
             FROM service_types st
             LEFT JOIN company_service_type_map cstm
                 ON cstm.service_type_id = st.id
                AND cstm.is_default_company = 1
             LEFT JOIN companies c
                 ON c.id = cstm.company_id
             WHERE st.is_active = 1
             ORDER BY st.name ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findWithDefaultCompany(int $serviceTypeId): ?array
    {
        $statement = Database::connection()->prepare(
            "SELECT st.*,
                    c.id AS default_company_id,
                    c.code AS default_company_code,
                    c.display_name AS default_company_name
             FROM service_types st
             LEFT JOIN company_service_type_map cstm
                 ON cstm.service_type_id = st.id
                AND cstm.is_default_company = 1
             LEFT JOIN companies c
                 ON c.id = cstm.company_id
             WHERE st.id = :id
               AND st.is_active = 1
             LIMIT 1"
        );
        $statement->execute(['id' => $serviceTypeId]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        return $record === false ? null : $record;
    }
}

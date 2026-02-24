<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Repositories;

use stdClass;

/**
 * Class AdminMailerRepository
 * @package Plugin\jtl_paypal_commerce\Repositories
 */
class AdminMailerRepository extends AbstractPluginRepository
{
    public function getTableName(): string
    {
        return 'tadminlogin';
    }

    /**
     * @return stdClass[]
     */
    public function getAdminList(): array
    {
        return $this->db->getObjects(
            'SELECT cMail, cName
                    FROM tadminlogin
                    WHERE tadminlogin.kAdminlogingruppe = 1
                        AND tadminlogin.bAktiv = 1
                    ORDER BY tadminlogin.kAdminlogin'
        );
    }
}

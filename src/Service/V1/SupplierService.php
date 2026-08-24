<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class SupplierService extends AbstractService
{
    /**
     * Warehouses available for supplies and their schedule.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SupplierAPI_SupplierAvailableWarehouses
     *
     * @return list<array{warehouse?: array, schedule?: array}>
     */
    public function availableWarehouses(): array
    {
        return $this->request('GET', '/v1/supplier/available_warehouses');
    }
}

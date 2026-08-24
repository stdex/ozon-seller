<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\SupplierService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\SupplierService
 */
final class SupplierServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return SupplierService::class;
    }

    /**
     * @covers ::availableWarehouses
     */
    public function testAvailableWarehouses(): void
    {
        $this->quickTest(
            'availableWarehouses',
            [],
            ['GET', '/v1/supplier/available_warehouses', null],
            '{"result":[]}'
        );
    }
}

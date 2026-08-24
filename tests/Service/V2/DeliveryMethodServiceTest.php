<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\DeliveryMethodService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\DeliveryMethodService
 */
final class DeliveryMethodServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return DeliveryMethodService::class;
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $this->quickTest(
            'list',
            [
                [
                    'filter' => [
                        'delivery_method_ids' => ['456'],
                        'provider_ids'        => ['1'],
                        'status'              => ['ACTIVE'],
                        'warehouse_ids'       => ['123'],
                        // must be filtered out
                        'foo'                 => 'bar',
                    ],
                    'cursor'   => 'prev-cursor',
                    'limit'    => '20',
                    'sort_dir' => 'DESC',
                ],
            ],
            [
                'POST',
                '/v2/delivery-method/list',
                '{"limit":20,"sort_dir":"DESC","filter":{"delivery_method_ids":[456],"provider_ids":[1],"status":["ACTIVE"],"warehouse_ids":[123]},"cursor":"prev-cursor"}',
            ],
            '{"delivery_methods":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['delivery_methods' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::list
     */
    public function testListDefaults(): void
    {
        $this->quickTest(
            'list',
            [],
            ['POST', '/v2/delivery-method/list', '{"limit":50,"sort_dir":"ASC"}'],
            '{"delivery_methods":[]}',
            static function (array $result): void {
                self::assertSame(['delivery_methods' => []], $result);
            }
        );
    }
}

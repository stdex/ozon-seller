<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\DraftService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\DraftService
 */
final class DraftServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return DraftService::class;
    }

    /**
     * @covers ::directCreate
     */
    public function testDirectCreate(): void
    {
        $this->quickTest(
            'directCreate',
            [
                [
                    'macrolocal_cluster_id' => '1',
                    'items'                 => [['sku' => 160249683, 'quantity' => 10]],
                    // must be filtered out
                    'foo'                   => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/draft/direct/create',
                '{"cluster_info":{"macrolocal_cluster_id":1,"items":[{"sku":160249683,"quantity":10}]},"deletion_sku_mode":"FULL"}',
            ],
            '{"draft_id":1}',
            static function (array $result): void {
                self::assertSame(['draft_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::crossdockCreate
     */
    public function testCrossdockCreate(): void
    {
        $this->quickTest(
            'crossdockCreate',
            [
                ['macrolocal_cluster_id' => 1, 'items' => []],
                [
                    'type'                => 'DROPOFF',
                    'seller_warehouse_id' => '123',
                    // must be filtered out
                    'foo'                 => 'bar',
                ],
                'PARTIAL',
            ],
            [
                'POST',
                '/v1/draft/crossdock/create',
                '{"cluster_info":{"macrolocal_cluster_id":1,"items":[]},"delivery_info":{"type":"DROPOFF","seller_warehouse_id":123},"deletion_sku_mode":"PARTIAL"}',
            ],
            '{"draft_id":1}',
            static function (array $result): void {
                self::assertSame(['draft_id' => 1], $result);
            }
        );
    }

    /**
     * @covers ::multiClusterCreate
     */
    public function testMultiClusterCreate(): void
    {
        $this->quickTest(
            'multiClusterCreate',
            [
                [['macrolocal_cluster_id' => 1, 'items' => []]],
                ['type' => 'PICKUP'],
            ],
            [
                'POST',
                '/v1/draft/multi-cluster/create',
                '{"clusters_info":[{"macrolocal_cluster_id":1,"items":[]}],"delivery_info":{"type":"PICKUP"},"deletion_sku_mode":"FULL"}',
            ],
            '{"draft_id":1}',
            static function (array $result): void {
                self::assertSame(['draft_id' => 1], $result);
            }
        );
    }
}

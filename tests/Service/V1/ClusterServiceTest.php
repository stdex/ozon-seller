<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\ClusterService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\ClusterService
 */
final class ClusterServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ClusterService::class;
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $this->quickTest(
            'list',
            [],
            ['POST', '/v1/cluster/list', '{"cluster_type":"CLUSTER_TYPE_OZON"}'],
            '{"clusters":[]}',
            static function (array $result): void {
                self::assertSame(['clusters' => []], $result);
            }
        );
    }

    /**
     * @covers ::list
     */
    public function testListFiltered(): void
    {
        $this->quickTest(
            'list',
            [[1, '2'], 'CLUSTER_TYPE_CIS'],
            [
                'POST',
                '/v1/cluster/list',
                '{"cluster_type":"CLUSTER_TYPE_CIS","cluster_ids":["1","2"]}',
            ],
            '{"clusters":[]}',
            static function (array $result): void {
                self::assertSame(['clusters' => []], $result);
            }
        );
    }
}

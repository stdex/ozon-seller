<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\ClusterService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\ClusterService
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
            ['POST', '/v2/cluster/list', '{}'],
            '{"result":[{"macrolocal_cluster_id":1}]}'
        );
    }
}

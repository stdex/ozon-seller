<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\SearchQueriesService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\SearchQueriesService
 */
final class SearchQueriesServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return SearchQueriesService::class;
    }

    /**
     * @covers ::text
     */
    public function testText(): void
    {
        $this->quickTest(
            'text',
            ['заратустра', 50, 10, 'ADD_TO_CART', 'ASC'],
            [
                'POST',
                '/v1/search-queries/text',
                '{"text":"заратустра","limit":"50","offset":"10","sort_by":"ADD_TO_CART","sort_dir":"ASC"}',
            ],
            '{"search_queries":[],"offset":"10","total":"0"}',
            static function (array $result): void {
                self::assertSame(['search_queries' => [], 'offset' => '10', 'total' => '0'], $result);
            }
        );
    }

    /**
     * @covers ::top
     */
    public function testTop(): void
    {
        $this->quickTest(
            'top',
            [],
            [
                'POST',
                '/v1/search-queries/top',
                '{"limit":"10","offset":"0"}',
            ],
            '{"search_queries":[],"offset":"0","total":"0"}',
            static function (array $result): void {
                self::assertSame(['search_queries' => [], 'offset' => '0', 'total' => '0'], $result);
            }
        );
    }
}

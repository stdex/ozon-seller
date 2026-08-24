<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Utils;

use Gam6itko\OzonSeller\Enum\PostingScheme;
use Gam6itko\OzonSeller\Utils\WithResolver;
use PHPUnit\Framework\TestCase;

class WithResolverTest extends TestCase
{
    /**
     * @dataProvider dataResolve
     */
    public function testResolve(array $options, array $expected, int $version = 2, string $postingScheme = PostingScheme::FBS): void
    {
        self::assertEquals($expected, WithResolver::resolve($options, $version, $postingScheme));
    }

    public function dataResolve(): iterable
    {

        // v3 fbo
        yield [
            ['with' => ['analytics_data' => true, 'barcodes' => true]],
            [
                'analytics_data' => true,
                'financial_data' => false,
                'legal_info'     => false,
            ],
            3,
            PostingScheme::FBO,
        ];
        yield [
            [
                'with' => ['foo' => 1, 'bar' => 3, 'analytics_data' => true],
            ],
            [
                'analytics_data' => true,
                'barcodes'       => false,
                'financial_data' => false,
            ],
        ];

        yield [
            [
                'foo'            => 1,
                'bar'            => 3,
                'analytics_data' => true,
            ],
            [
                'analytics_data' => true,
                'barcodes'       => false,
                'financial_data' => false,
            ],
        ];

        yield [
            [],
            [
                'analytics_data' => false,
                'barcodes'       => false,
                'financial_data' => false,
            ],
        ];

        yield [
            [
                'analytics_data' => true,
                'barcodes'       => true,
                'financial_data' => true,
            ],
            [
                'analytics_data' => true,
                'barcodes'       => true,
                'financial_data' => true,
            ],
        ];
    }
}

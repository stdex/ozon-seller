<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Enum\Visibility;
use Gam6itko\OzonSeller\Service\V2\ProductService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;
use Psr\Http\Client\ClientInterface;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\ProductService
 */
class ProductServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ProductService::class;
    }

    /**
     * @dataProvider dataImport
     */
    public function testImport(array $item): void
    {
        $this->quickTest(
            'import',
            [$item],
            [
                'POST',
                '/v2/product/import',
                '{"items":[{"barcode":"string","category_id":1,"depth":1,"dimension_unit":"cm","height":1,"image_group_id":"string","images":["string"],"images360":["string"],"name":"string","offer_id":"string","old_price":"string","pdf_list":[{"index":0,"name":"string","src_url":"string"}],"premium_price":"string","price":"string","vat":"string","weight":1,"weight_unit":"g","width":1,"attributes":[{"complex_id":0,"id":0,"values":[{"dictionary_value_id":0,"value":"string"}]}],"complex_attributes":[{"attributes":[{"complex_id":0,"id":0,"values":[{"dictionary_value_id":0,"value":"string"}]}]}]}]}',
            ]
        );
    }

    public function dataImport()
    {
        $item = [
            'description'        => 'text of description',
            'barcode'            => 'string',
            'category_id'        => 1,
            'depth'              => 1,
            'dimension_unit'     => 'cm',
            'height'             => 1,
            'image_group_id'     => 'string',
            'images'             => [
                'string',
            ],
            'images360'          => [
                'string',
            ],
            'name'               => 'string',
            'offer_id'           => 'string',
            'old_price'          => 'string',
            'pdf_list'           => [
                [
                    'index'   => 0,
                    'name'    => 'string',
                    'src_url' => 'string',
                ],
            ],
            'premium_price'      => 'string',
            'price'              => 'string',
            'vat'                => 'string',
            'weight'             => 1,
            'weight_unit'        => 'g',
            'width'              => 1,
            'attributes'         => [
                [
                    'complex_id' => 0,
                    'id'         => 0,
                    'values'     => [
                        [
                            'dictionary_value_id' => 0,
                            'value'               => 'string',
                        ],
                    ],
                ],
            ],
            'complex_attributes' => [
                [
                    'attributes' => [
                        [
                            'complex_id' => 0,
                            'id'         => 0,
                            'values'     => [
                                [
                                    'dictionary_value_id' => 0,
                                    'value'               => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        yield [$item];
        yield [[$item]];
        yield [['items' => [$item]]];
    }

    public function testList(): void
    {
        $this->quickTest(
            'list',
            [
                [
                    'filter'  => ['offer_id' => 'item_6060091', 'product_id' => 7154396, 'visibility' => Visibility::ALL],
                    'last_id' => '100',
                    'limit'   => 100,
                ],
            ],
            [
                'POST',
                '/v2/product/list',
                '{"filter":{"offer_id":["item_6060091"],"product_id":[7154396],"visibility":"ALL"},"last_id":"100","limit":100}',
            ]
        );
    }

    public function testInfo(): void
    {
        $this->quickTest(
            'info',
            [
                ['offer_id' => 'item_6060091', 'product_id' => 7154396, 'sku' => 150583609],
            ],
            [
                'POST',
                '/v2/product/info',
                '{"offer_id":"item_6060091","product_id":7154396,"sku":150583609}',
            ]
        );
    }

    /**
     * @dataProvider dataInfoList
     */
    public function testInfoList(array $query): void
    {
        $this->quickTest(
            'infoList',
            [$query],
            [
                'POST',
                '/v2/product/info/list',
                '{"offer_id":["item_6060091"],"product_id":[7154396],"sku":[150583609]}',
            ]
        );
    }

    public function dataInfoList(): iterable
    {
        yield [
            ['offer_id' => 'item_6060091', 'product_id' => 7154396, 'sku' => 150583609],
        ];

        yield [
            ['offer_id' => ['item_6060091'], 'product_id' => '7154396', 'sku' => '150583609'],
        ];

        yield [
            ['offer_id' => ['item_6060091'], 'product_id' => [7154396], 'sku' => [150583609]],
        ];

        yield [
            ['offer_id' => ['item_6060091'], 'product_id' => ['7154396'], 'sku' => ['150583609']],
        ];
    }

    public function testInfoAttributes(): void
    {
        $this->quickTest(
            'infoAttributes',
            [
                [
                    'offer_id'   => ['ABC-123'],
                    'product_id' => [2346321],
                ],
                0,
                10,
            ],
            [
                'POST',
                '/v2/products/info/attributes',
                '{"filter":{"offer_id":["ABC-123"],"product_id":[2346321]},"page":0,"page_size":10}',
            ]
        );
    }

    /**
     * @covers ::importStocks
     *
     * @dataProvider dataImportStocks
     */
    public function testImportStocks(array $stocks): void
    {
        $this->quickTest(
            'importStocks',
            [$stocks],
            [
                'POST',
                '/v2/products/stocks',
                '{"stocks":[{"product_id":120000,"offer_id":"PRD-1","stock":20,"warehouse_id":22043923995000}]}',
            ]
        );
    }

    public function dataImportStocks(): iterable
    {
        $stock = [
            'product_id'   => 120000,
            'offer_id'     => 'PRD-1',
            'stock'        => 20,
            'warehouse_id' => 22043923995000,
        ];

        yield [$stock];
        yield [[$stock]];
        yield [['stocks' => [$stock]]];
    }

    /**
     * @dataProvider dataFailImportStocks
     */
    public function testFailImportStocks(array $prices): void
    {
        self::expectException(\InvalidArgumentException::class);

        $config = [123, 'api-key'];
        $client = $this->createMock(ClientInterface::class);
        $svc = new \Gam6itko\OzonSeller\Service\V1\ProductService($config, $client, $this->createRequestFactory(), $this->createStreamFactory());
        $svc->importStocks($prices);
    }

    public function dataFailImportStocks(): iterable
    {
        $item = [
            'foo'     => 'far',
            'bad_key' => 'bad_value',
            2,
            3,
        ];

        yield [$item];
        yield [[]];
        yield [['some_key' => [$item]]];
    }

    public function testInfoStocks(): void
    {
        $this->quickTest(
            'infoStocks',
            [],
            [
                'POST',
                '/v2/product/info/stocks',
                '{"page":1,"page_size":100}',
            ]
        );
    }

    /**
     * @covers ::delete
     *
     * @dataProvider dataDelete
     */
    public function testDelete(array $products, string $expectedJsonString): void
    {
        $this->quickTest(
            'delete',
            [$products],
            [
                'POST',
                '/v2/products/delete',
                $expectedJsonString,
            ]
        );
    }

    public function dataDelete(): iterable
    {
        yield [
            [
                'products' => [
                    ['offer_id' => 'PRD-1'],
                    ['offer_id' => 2],
                ],
            ],

            '{"products":[{"offer_id":"PRD-1"}, {"offer_id":"2"}]}',
        ];

        yield [
            [
                ['offer_id' => 'PRD-1'],
                ['offer_id' => 2],
            ],
            '{"products":[{"offer_id":"PRD-1"}, {"offer_id":"2"}]}',
        ];

        yield [
            ['offer_id' => 'PRD-1'],
            '{"products":[{"offer_id":"PRD-1"}]}',
        ];
    }

    /**
     * @covers ::infoStocksByWarehouseFbs
     *
     * @dataProvider dataInfoStocksByWarehouseFbs
     */
    public function testInfoStocksByWarehouseFbs($query, $request): void
    {
        $this->quickTest(
            'infoStocksByWarehouseFbs',
            [
                $query,
            ],
            [
                'POST',
                '/v2/product/info/stocks-by-warehouse/fbs',
                $request,
            ]
        );
    }

    public function dataInfoStocksByWarehouseFbs(): iterable
    {
        yield [
            ['limit' => 10, 'cursor' => 'string', 'sku' => ['1234567', '9876543']],
            '{"cursor":"string","limit":10,"sku":["1234567","9876543"]}',
        ];
        yield [
            ['limit' => 15, 'offer_id' => ['sku-1', '09876sku']],
            '{"limit":15,"offer_id":["sku-1","09876sku"]}',
        ];
    }

    /**
     * @covers ::infoStocksByWarehouseFbs
     */
    public function testInfoStocksByWarehouseFbsEmptyList(): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Non-empty list of sku or offer_id is required');
        $config = [123, 'api-key'];
        $client = $this->createMock(ClientInterface::class);
        $svc = new ProductService($config, $client, $this->createRequestFactory(), $this->createStreamFactory());
        $svc->infoStocksByWarehouseFbs(['limit' => 10]);
    }

    /**
     * @covers ::certificateCreate
     */
    public function testCertificateCreate(): void
    {
        $this->quickTest(
            'certificateCreate',
            [
                [
                    'name'                => 'Сертификат соответствия',
                    'number'              => 'RU C-CN.AB99.B.00000/26',
                    'certificate_type'    => 'CERTIFICATE_OF_CONFORMITY',
                    'certificate_country' => 'RU',
                    'accordance_type'     => 'certificate_of_conformity',
                    'product_type'        => 'product',
                    'issue_date'          => '2026-08-01T00:00:00Z',
                    'expired_date'        => ['date' => ['day' => 1, 'month' => 8, 'year' => 2031]],
                    'link_to_registry'    => 'https://pub.fsa.gov.ru/rss/certificate',
                    'files'               => [['name' => 'cert.pdf', 'file_content' => 'JVBERi0=']],
                    'skus'                => [123456789],
                    // must be filtered out
                    'foo'                 => 'bar',
                ],
            ],
            [
                'POST',
                '/v2/product/certificate/create',
                '{"params":{"name":"\u0421\u0435\u0440\u0442\u0438\u0444\u0438\u043a\u0430\u0442 \u0441\u043e\u043e\u0442\u0432\u0435\u0442\u0441\u0442\u0432\u0438\u044f","number":"RU C-CN.AB99.B.00000\/26","certificate_type":"CERTIFICATE_OF_CONFORMITY","certificate_country":"RU","accordance_type":"certificate_of_conformity","product_type":"product","issue_date":"2026-08-01T00:00:00Z","expired_date":{"date":{"day":1,"month":8,"year":2031}},"link_to_registry":"https:\/\/pub.fsa.gov.ru\/rss\/certificate","files":[{"name":"cert.pdf","file_content":"JVBERi0="}],"skus":["123456789"]}}',
            ],
            '{"certificate_id":15074,"status":"COMPLETED"}',
            static function (array $result): void {
                self::assertSame(15074, $result['certificate_id']);
                self::assertSame('COMPLETED', $result['status']);
            }
        );
    }

    /**
     * @covers ::certificateCreate
     */
    public function testCertificateCreateInvalidFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $svc = $this->createSvc(
            $this->createMock(\Psr\Http\Client\ClientInterface::class),
            $this->createRequestFactory(),
            $this->createStreamFactory()
        );
        $svc->certificateCreate(['name' => 'cert', 'files' => [['name' => 'cert.pdf']]]);
    }

    /**
     * @covers ::certificationParams
     */
    public function testCertificationParams(): void
    {
        $this->quickTest(
            'certificationParams',
            [
                [
                    'certificate_type' => 'DECLARATION',
                    'product_type'     => 'product',
                    // must be filtered out
                    'bar'              => 'baz',
                ],
            ],
            [
                'POST',
                '/v2/product/certification/params',
                '{"params":{"certificate_type":"DECLARATION","product_type":"product"}}',
            ],
            '{"params":[{"name":"NAME","required":true},{"name":"LINK_TO_REGISTRY","required":false}]}',
            static function (array $result): void {
                self::assertCount(2, $result['params']);
                self::assertSame('NAME', $result['params'][0]['name']);
                self::assertTrue($result['params'][0]['required']);
            }
        );
    }

    /**
     * @covers ::certificationOptions
     */
    public function testCertificationOptions(): void
    {
        $this->quickTest(
            'certificationOptions',
            [],
            [
                'POST',
                '/v2/product/certification/options',
                null,
            ],
            '{"option":[{"name":"NAME","required":true},{"name":"FILES","required":true},{"name":"INFINITE","required":false}]}',
            static function (array $result): void {
                self::assertCount(3, $result['option']);
                self::assertSame('FILES', $result['option'][1]['name']);
            }
        );
    }
}

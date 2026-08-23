<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\ProductService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;
use Psr\Http\Client\ClientInterface;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\ProductService
 */
class ProductServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ProductService::class;
    }

    public function testClassify(): void
    {
        $input = [
            'offer_id'                => '147190464',
            'shop_category_full_path' => 'Электроника/Телефоны и аксессуары/Смартфоны',
            'shop_category'           => 'Смартфоны',
            'shop_category_id'        => 15502,
            'vendor'                  => 'Apple, Inc',
            'model'                   => 'iPhone XS 256GB Space Grey',
            'name'                    => 'Смартфон Apple iPhone XS 256GB Space Grey',
            'price'                   => '100990',
            'offer_url'               => 'https://www.ozon.ru/context/detail/id/147190464/',
            'img_url'                 => 'https://ozon-st.cdn.ngenix.net/multimedia/1024351473.jpg',
            'vendor_code'             => 'apple_inc',
            'barcode'                 => '190198794017',
            // bad options
            'foo'                     => 'bar',
            'you'                     => 'shall not pass',
        ];
        $this->quickTest(
            'classify',
            [$input],
            [
                'POST',
                '/v1/product/classify',
                '{"products":[{"offer_id":"147190464","shop_category_full_path":"\u042d\u043b\u0435\u043a\u0442\u0440\u043e\u043d\u0438\u043a\u0430\/\u0422\u0435\u043b\u0435\u0444\u043e\u043d\u044b \u0438 \u0430\u043a\u0441\u0435\u0441\u0441\u0443\u0430\u0440\u044b\/\u0421\u043c\u0430\u0440\u0442\u0444\u043e\u043d\u044b","shop_category":"\u0421\u043c\u0430\u0440\u0442\u0444\u043e\u043d\u044b","shop_category_id":15502,"vendor":"Apple, Inc","model":"iPhone XS 256GB Space Grey","name":"\u0421\u043c\u0430\u0440\u0442\u0444\u043e\u043d Apple iPhone XS 256GB Space Grey","price":"100990","offer_url":"https:\/\/www.ozon.ru\/context\/detail\/id\/147190464\/","img_url":"https:\/\/ozon-st.cdn.ngenix.net\/multimedia\/1024351473.jpg","vendor_code":"apple_inc","barcode":"190198794017"}]}',
            ]
        );
    }

    /**
     * @dataProvider dataImport
     */
    public function testImport(array $input): void
    {
        $this->quickTest(
            'import',
            [$input],
            [
                'POST',
                '/v1/product/import',
                '{"items":[{"barcode":"8801643566784","description":"Red Samsung Galaxy S9 with 512GB","category_id":17030819,"name":"Samsung Galaxy S9","offer_id":"REDSGS9-512","price":"79990","old_price":"89990","premium_price":"75555","vat":"0","vendor":"Samsung","vendor_code":"SM-G960UZPAXAA","height":77,"depth":11,"width":120,"dimension_unit":"mm","weight":120,"weight_unit":"g","images":[{"file_name":"https:\/\/ozon-st.cdn.ngenix.net\/multimedia\/c1200\/1022555115.jpg","default":true},{"file_name":"https:\/\/ozon-st.cdn.ngenix.net\/multimedia\/c1200\/1022555110.jpg","default":false}],"attributes":[{"id":8229,"value":"4747"},{"id":4413,"collection":["1","2","13"]}]}]}',
            ]
        );
    }

    public function dataImport(): iterable
    {
        $item = [
            'barcode'        => '8801643566784',
            'description'    => 'Red Samsung Galaxy S9 with 512GB',
            'category_id'    => 17030819,
            'name'           => 'Samsung Galaxy S9',
            'offer_id'       => 'REDSGS9-512',
            'price'          => '79990',
            'old_price'      => '89990',
            'premium_price'  => '75555',
            'vat'            => '0',
            'vendor'         => 'Samsung',
            'vendor_code'    => 'SM-G960UZPAXAA',
            'height'         => 77,
            'depth'          => 11,
            'width'          => 120,
            'dimension_unit' => 'mm',
            'weight'         => 120,
            'weight_unit'    => 'g',
            'images'         => [
                ['file_name' => 'https://ozon-st.cdn.ngenix.net/multimedia/c1200/1022555115.jpg', 'default' => true],
                ['file_name' => 'https://ozon-st.cdn.ngenix.net/multimedia/c1200/1022555110.jpg', 'default' => false],
            ],
            'attributes'     => [
                ['id' => 8229, 'value' => '4747'],
                ['id' => 4413, 'collection' => ['1', '2', '13']],
            ],
        ];

        yield [$item];
        yield [
            ['items' => [$item]],
        ];
    }

    /**
     * @dataProvider dataImportBySku
     */
    public function testImportBySku(array $input): void
    {
        $this->quickTest(
            'importBySku',
            [$input],
            [
                'POST',
                '/v1/product/import-by-sku',
                '{"items":[{"sku":1445625485,"name":"Nice boots 1","offer_id":"RED-SHOES-MODEL-1-38-39","price":"7999","old_price":"8999","premium_price":"7555","vat":"0"}]}',
            ]
        );
    }

    public function dataImportBySku(): iterable
    {
        $item = [
            'sku'           => 1445625485,
            'name'          => 'Nice boots 1',
            'offer_id'      => 'RED-SHOES-MODEL-1-38-39',
            'price'         => '7999',
            'old_price'     => '8999',
            'premium_price' => '7555',
            'vat'           => '0',
        ];

        yield [$item];
        yield [
            ['items' => [$item]],
        ];
    }

    public function testImportInfo(): void
    {
        $this->quickTest(
            'importInfo',
            [33919],
            [
                'POST',
                '/v1/product/import/info',
                '{"task_id":33919}',
            ]
        );
    }

    public function testInfo(): void
    {
        $this->quickTest(
            'info',
            [7154396],
            [
                'POST',
                '/v1/product/info',
                '{"product_id":7154396}',
            ]
        );
    }

    public function testInfoBy(): void
    {
        $query = [
            'product_id' => 7154396,
            'offer_id'   => 'item_6060091',
            'sku'        => 150583609,
        ];
        $this->quickTest(
            'infoBy',
            [$query],
            [
                'POST',
                '/v1/product/info',
                '{"product_id":7154396,"offer_id":"item_6060091","sku":150583609}',
            ]
        );
    }

    public function testInfoStocks(): void
    {
        $this->quickTest(
            'infoStocks',
            [],
            [
                'POST',
                '/v1/product/info/stocks',
                '{"page":1,"page_size":100}',
            ]
        );
    }

    public function testInfoPrices(): void
    {
        $this->quickTest(
            'infoPrices',
            [],
            [
                'POST',
                '/v1/product/info/prices',
                '{"page":1,"page_size":100}',
            ]
        );
    }

    /**
     * @covers ::list
     *
     * @dataProvider dataList
     */
    public function testList(array $filters, array $pagination, string $json): void
    {
        $responseJson = <<<JSON
{
  "result": {
    "items": [
      {
        "product_id": 124100,
        "offer_id": "REDSGS10-128"
      },
      {
        "product_id": 124201,
        "offer_id": "REDSGS10-512"
      }
    ],
    "total": 2
  }
}
JSON;

        $this->quickTest(
            'list',
            [$filters, $pagination],
            [
                'POST',
                '/v1/product/list',
                $json,
            ],
            $responseJson
        );
    }

    public function dataList(): iterable
    {
        yield [
            [
                'offer_id'   => ['1255959'],
                'product_id' => [552526],
                'visibility' => 'ALL',
            ],
            [],
            '{"page":1,"page_size":10,"filter":{"offer_id":["1255959"],"product_id":[552526],"visibility":"ALL"}}',
        ];

        yield [
            [
                'offer_id'   => '1255959',
                'product_id' => 552526,
                'visibility' => 'ALL',
            ],
            [
                'page'      => 10,
                'page_size' => 100,
            ],
            '{"page":10,"page_size":100,"filter":{"offer_id":["1255959"],"product_id":[552526],"visibility":"ALL"}}',
        ];

        yield [
            [
                'filter'    => [
                    'offer_id'   => ['1255959'],
                    'product_id' => [552526],
                    'visibility' => 'ALL',
                ],
                'page'      => 10,
                'page_size' => 100,
            ],
            [],
            '{"page":10,"page_size":100,"filter":{"offer_id":["1255959"],"product_id":[552526],"visibility":"ALL"}}',
        ];

        yield [
            [
                'page'      => 10,
                'page_size' => 100,
            ],
            [],
            '{"page":10,"page_size":100}',
        ];
    }

    /**
     * @covers ::list
     */
    public function testListOfferIdType(): void
    {
        $responseJson = <<<JSON
{
  "result": {
    "items": [
      {
        "product_id": 1,
        "offer_id": "1"
      },
      {
        "product_id": 2,
        "offer_id": "2"
      },
      {
        "product_id": 3,
        "offer_id": "3"
      }
    ],
    "total": 3
  }
}
JSON;

        $filters = [
            'offer_id' => [1, 2, 3],
        ];
        $this->quickTest(
            'list',
            [$filters],
            [
                'POST',
                '/v1/product/list',
                '{"page":1,"page_size":10,"filter":{"offer_id":["1","2","3"]}}',
            ],
            $responseJson
        );
    }

    /**
     * @covers ::importPrices
     *
     * @dataProvider dataImportPrices
     */
    public function testImportPrices(array $prices, string $expectedJson): void
    {
        $this->quickTest(
            'importPrices',
            [$prices],
            [
                'POST',
                '/v1/product/import/prices',
                $expectedJson,
            ]
        );
    }

    public function dataImportPrices(): iterable
    {
        $item = [
            'product_id'    => 120000,
            'offer_id'      => 'PRD-1',
            'price'         => '79990',
            'old_price'     => '89990',
            'premium_price' => '75555',
        ];
        $json = '{"prices":[{"product_id":120000,"offer_id":"PRD-1","price":"79990","old_price":"89990","premium_price":"75555"}]}';

        yield [$item, $json];
        yield [[$item], $json];
        yield [['prices' => [$item]], $json];

        yield 'no discount' => [
            [
                'offer_id'      => 'PRD-2',
                'price'         => '10000.10',
                'old_price'     => '5000.50',
            ],
            '{"prices":[{"offer_id":"PRD-2","price":"10000.10","old_price":"0"}]}',
        ];
    }

    /**
     * @dataProvider dataFailImportPrices
     */
    public function testFailImportPrices(array $prices): void
    {
        self::expectException(\InvalidArgumentException::class);

        $config = [123, 'api-key'];
        $client = $this->createMock(ClientInterface::class);
        $svc = new ProductService($config, $client, $this->createRequestFactory(), $this->createStreamFactory());
        $svc->importPrices($prices);
    }

    public function dataFailImportPrices(): iterable
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

    /**
     * @dataProvider dataImportStocks
     */
    public function testImportStocks(array $stocks): void
    {
        $this->quickTest(
            'importStocks',
            [$stocks],
            [
                'POST',
                '/v1/product/import/stocks',
                '{"stocks":[{"product_id":120000,"offer_id":"PRD-1","stock":20}]}',
            ]
        );
    }

    public function dataImportStocks(): iterable
    {
        $stock = [
            'product_id' => 120000,
            'offer_id'   => 'PRD-1',
            'stock'      => 20,
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
        $svc = new ProductService($config, $client, $this->createRequestFactory(), $this->createStreamFactory());
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

    public function testUpdate()
    {
        $item = [
            'product_id'     => 124100,
            'barcode'        => '8801643566784',
            'description'    => 'Red Samsung Galaxy S10 with 512GB',
            'name'           => 'Samsung Galaxy S10',
            'vendor'         => 'Samsung',
            'vendor_code'    => 'SM-G960UZPAXAA',
            'height'         => 77,
            'depth'          => 11,
            'width'          => 120,
            'dimension_unit' => 'mm',
            'weight'         => 120,
            'weight_unit'    => 'g',
            'images'         => [
                ['file_name' => 'http://pic.com/1.jpg', 'default' => true],
                ['file_name' => 'http://pic.com/2.jpg', 'default' => false],
            ],
            'attributes'     => [
                ['id' => 1, 'value' => 'Samsung Galaxy S10'],
                ['id' => 2, 'collection' => ['128GB', '512GB']],
            ],
        ];
        $this->quickTest(
            'update',
            [$item],
            [
                'POST',
                '/v1/product/update',
                '{"product_id":124100,"barcode":"8801643566784","description":"Red Samsung Galaxy S10 with 512GB","name":"Samsung Galaxy S10","vendor":"Samsung","vendor_code":"SM-G960UZPAXAA","height":77,"depth":11,"width":120,"dimension_unit":"mm","weight":120,"weight_unit":"g","images":[{"file_name":"http:\/\/pic.com\/1.jpg","default":true},{"file_name":"http:\/\/pic.com\/2.jpg","default":false}],"attributes":[{"id":1,"value":"Samsung Galaxy S10"},{"id":2,"collection":["128GB","512GB"]}]}',
            ]
        );
    }

    public function testSetPayment(): void
    {
        $data = [
            'is_prepayment' => true,
            'offers_ids'    => ['Offer_RbtbQseqtTeBlHB8AjF9t-23'],
            'products_ids'  => [5376526],
        ];
        $this->quickTest(
            'setPrepayment',
            [$data],
            [
                'POST',
                '/v1/product/prepayment/set',
                '{"is_prepayment":true,"offers_ids":["Offer_RbtbQseqtTeBlHB8AjF9t-23"],"products_ids":[5376526]}',
            ]
        );
    }

    public function testInfoStocksByWarehouseFbs(): void
    {
        $data = [
            'sku' => ['123456', '8365532'],
        ];
        $this->quickTest(
            'infoStocksByWarehouseFbs',
            [$data],
            [
                'POST',
                '/v1/product/info/stocks-by-warehouse/fbs',
                '{"sku":["123456","8365532"]}',
            ]
        );
    }

    /**
     * @covers ::updateDiscount
     *
     * @dataProvider dataUpdateDiscount
     */
    public function testUpdateDiscount(int $product_id, int $discount, string $request): void
    {
        $this->quickTest(
            'updateDiscount',
            [
                $product_id,
                $discount,
            ],
            [
                'POST',
                '/v1/product/update/discount',
                $request,
            ],
            '{"result": true}'
        );
    }

    public function dataUpdateDiscount(): iterable
    {
        yield [
            1234567,
            10,
            '{"discount":10,"product_id":1234567}',
        ];
        yield [
            9876754,
            66,
            '{"discount":66,"product_id":9876754}',
        ];
    }

    /**
     * @covers ::infoStocksByWarehouseFbs
     *
     * @dataProvider dataInvalidDiscount
     */
    public function testUpdateDiscountInvalidDiscount(int $discount): void
    {
        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Discount should be between 3 and 99 percent');
        $config = [123, 'api-key'];
        $client = $this->createMock(ClientInterface::class);
        $svc = new ProductService($config, $client, $this->createRequestFactory(), $this->createStreamFactory());
        $svc->updateDiscount(123998, $discount);
    }

    public function dataInvalidDiscount(): iterable
    {
        yield [-1];
        yield [1];
        yield [2];
        yield [100];
    }

    public function testPlacementZoneInfo(): void
    {
        $this->quickTest(
            'placementZoneInfo',
            [[160249683]],
            ['POST', '/v1/product/placement-zone/info', '{"skus":["160249683"]}'],
            '{"products_placement":[]}',
            static function (array $result): void {
                self::assertSame(['products_placement' => []], $result);
            }
        );
    }

    public function testAttributesUpdate(): void
    {
        $this->quickTest(
            'attributesUpdate',
            [
                [
                    [
                        'offer_id'   => '9789785079999',
                        'attributes' => [['id' => 85, 'values' => [['dictionary_value_id' => 1]]]],
                        // must be filtered out
                        'foo'        => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/product/attributes/update',
                '{"items":[{"offer_id":"9789785079999","attributes":[{"id":85,"values":[{"dictionary_value_id":1}]}]}]}',
            ],
            '{"task_id":1234}',
            static function (array $result): void {
                self::assertSame(['task_id' => 1234], $result);
            }
        );
    }

    public function testUpdateOfferId(): void
    {
        $this->quickTest(
            'updateOfferId',
            [
                [
                    [
                        'offer_id'     => 'old-1',
                        'new_offer_id' => 'new-1',
                        // must be filtered out
                        'foo'          => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/product/update/offer-id',
                '{"update_offer_id":[{"offer_id":"old-1","new_offer_id":"new-1"}]}',
            ],
            '{"errors":[]}',
            static function (array $result): void {
                self::assertSame(['errors' => []], $result);
            }
        );
    }

    public function testInfoSubscription(): void
    {
        $this->quickTest(
            'infoSubscription',
            [[160249683]],
            ['POST', '/v1/product/info/subscription', '{"skus":["160249683"]}'],
            '{"result":[{"sku":160249683,"count":5}]}'
        );
    }

    public function testRelatedSkuGet(): void
    {
        $this->quickTest(
            'relatedSkuGet',
            [[160249683]],
            ['POST', '/v1/product/related-sku/get', '{"sku":["160249683"]}'],
            '{"items":[],"errors":[]}',
            static function (array $result): void {
                self::assertSame(['items' => [], 'errors' => []], $result);
            }
        );
    }

    public function testInfoWrongVolume(): void
    {
        $this->quickTest(
            'infoWrongVolume',
            [50, 'prev-cursor'],
            [
                'POST',
                '/v1/product/info/wrong-volume',
                '{"limit":50,"cursor":"prev-cursor"}',
            ],
            '{"products":[],"cursor":""}',
            static function (array $result): void {
                self::assertSame(['products' => [], 'cursor' => ''], $result);
            }
        );
    }

    public function testInfoWarehouseStocks(): void
    {
        $this->quickTest(
            'infoWarehouseStocks',
            [123],
            [
                'POST',
                '/v1/product/info/warehouse/stocks',
                '{"warehouse_id":123,"limit":100}',
            ],
            '{"stocks":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['stocks' => [], 'has_next' => false], $result);
            }
        );
    }

    public function testActionTimerUpdate(): void
    {
        $this->quickTest(
            'actionTimerUpdate',
            [[123456]],
            ['POST', '/v1/product/action/timer/update', '{"product_ids":["123456"]}'],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    public function testActionTimerStatus(): void
    {
        $this->quickTest(
            'actionTimerStatus',
            [[123456]],
            ['POST', '/v1/product/action/timer/status', '{"product_ids":["123456"]}'],
            '{"statuses":[]}',
            static function (array $result): void {
                self::assertSame(['statuses' => []], $result);
            }
        );
    }

    public function testInfoDiscounted(): void
    {
        $this->quickTest(
            'infoDiscounted',
            [[160249683]],
            ['POST', '/v1/product/info/discounted', '{"discounted_skus":["160249683"]}'],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
            }
        );
    }

    public function testInfoStocksByWarehouseFbo(): void
    {
        $this->quickTest(
            'infoStocksByWarehouseFbo',
            [
                [
                    'skus'      => [160249683],
                    'offer_ids' => ['9789785079999'],
                    'limit'     => '50',
                    'cursor'    => 'prev-cursor',
                    // must be filtered out
                    'foo'       => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/product/info/stocks-by-warehouse/fbo',
                '{"limit":50,"skus":["160249683"],"offer_ids":["9789785079999"],"cursor":"prev-cursor"}',
            ],
            '{"products":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['products' => [], 'has_next' => false], $result);
            }
        );
    }

    public function testDigitalStocksImport(): void
    {
        $this->quickTest(
            'digitalStocksImport',
            [
                [
                    [
                        'offer_id' => '9789785079999',
                        'stock'    => '10',
                        // must be filtered out
                        'foo'      => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/product/digital/stocks/import',
                '{"stocks":[{"offer_id":"9789785079999","stock":10}]}',
            ],
            '{"status":[]}',
            static function (array $result): void {
                self::assertSame(['status' => []], $result);
            }
        );
    }

    public function testStairwayDiscountByQuantitySet(): void
    {
        $this->quickTest(
            'stairwayDiscountByQuantitySet',
            [
                [
                    [
                        'sku'      => '160249683',
                        'enabled'  => true,
                        'stairway' => ['steps' => []],
                        // must be filtered out
                        'foo'      => 'bar',
                    ],
                ],
                true,
            ],
            [
                'POST',
                '/v1/product/stairway-discount/by-quantity/set',
                '{"stairways":[{"sku":160249683,"enabled":true,"stairway":{"steps":[]}}],"suppress_warnings":true}',
            ],
            '{"accepted":true}',
            static function (array $result): void {
                self::assertSame(['accepted' => true], $result);
            }
        );
    }

    public function testStairwayDiscountByQuantityGet(): void
    {
        $this->quickTest(
            'stairwayDiscountByQuantityGet',
            [[160249683]],
            [
                'POST',
                '/v1/product/stairway-discount/by-quantity/get',
                '{"skus":["160249683"]}',
            ],
            '{"stairways":[]}',
            static function (array $result): void {
                self::assertSame(['stairways' => []], $result);
            }
        );
    }

    public function testVisibilitySet(): void
    {
        $this->quickTest(
            'visibilitySet',
            [
                [
                    [
                        'sku'       => '160249683',
                        'placement' => 'PLACEMENT_OZON',
                        // must be filtered out
                        'foo'       => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/product/visibility/set',
                '{"item_placement":[{"sku":160249683,"placement":"PLACEMENT_OZON"}]}',
            ],
            '{"items":[],"items_errors":[]}',
            static function (array $result): void {
                self::assertSame(['items' => [], 'items_errors' => []], $result);
            }
        );
    }

    public function testVisibilityInfo(): void
    {
        $this->quickTest(
            'visibilityInfo',
            [[160249683]],
            ['POST', '/v1/product/visibility/info', '{"skus":["160249683"]}'],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
            }
        );
    }

    public function testVisibilityInfoAll(): void
    {
        $this->quickTest(
            'visibilityInfo',
            [],
            ['POST', '/v1/product/visibility/info', '{}'],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
            }
        );
    }

    public function testQuantList(): void
    {
        $this->quickTest(
            'quantList',
            [50, 'prev-cursor', 'ALL'],
            [
                'POST',
                '/v1/product/quant/list',
                '{"limit":50,"cursor":"prev-cursor","visibility":"ALL"}',
            ],
            '{"products":[],"total_items":0}',
            static function (array $result): void {
                self::assertSame(['products' => [], 'total_items' => 0], $result);
            }
        );
    }

    public function testQuantInfo(): void
    {
        $this->quickTest(
            'quantInfo',
            [['quant-1']],
            ['POST', '/v1/product/quant/info', '{"quant_code":["quant-1"]}'],
            '{"items":[]}',
            static function (array $result): void {
                self::assertSame(['items' => []], $result);
            }
        );
    }

    public function testPricesDetails(): void
    {
        $this->quickTest(
            'pricesDetails',
            [[160249683]],
            ['POST', '/v1/product/prices/details', '{"skus":["160249683"]}'],
            '{"prices":[]}',
            static function (array $result): void {
                self::assertSame(['prices' => []], $result);
            }
        );
    }

    public function testCertificateList(): void
    {
        $this->quickTest(
            'certificateList',
            [
                [
                    'offer_id'  => '9789785079999',
                    'status'    => 'ACTIVE',
                    'type'      => 'certificate',
                    'page'      => '2',
                    'page_size' => '50',
                    // must be filtered out
                    'foo'       => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/product/certificate/list',
                '{"page":2,"page_size":50,"offer_id":"9789785079999","status":"ACTIVE","type":"certificate"}',
            ],
            '{"result":{"certificates":[],"page_count":1}}'
        );
    }

    public function testCertificateInfo(): void
    {
        $this->quickTest(
            'certificateInfo',
            ['certificate-1'],
            [
                'POST',
                '/v1/product/certificate/info',
                '{"certificate_number":"certificate-1"}',
            ],
            '{"result":{"certificate_id":1,"certificate_number":"certificate-1"}}'
        );
    }

    public function testCertificateDelete(): void
    {
        $this->quickTest(
            'certificateDelete',
            [1],
            ['POST', '/v1/product/certificate/delete', '{"certificate_id":1}'],
            '{"result":{"is_delete":true}}'
        );
    }

    public function testCertificateUnbind(): void
    {
        $this->quickTest(
            'certificateUnbind',
            [
                1,
                [
                    'product_id' => [123456],
                    'skus'       => [160249683],
                    // must be filtered out
                    'foo'        => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/product/certificate/unbind',
                '{"product_id":["123456"],"skus":["160249683"],"certificate_id":1}',
            ],
            '{"result":[]}'
        );
    }

    public function testCertificateProductsList(): void
    {
        $this->quickTest(
            'certificateProductsList',
            [1, ['limit' => '50', 'last_id' => '10']],
            [
                'POST',
                '/v1/product/certificate/products/list',
                '{"last_id":10,"limit":50,"certificate_id":1}',
            ],
            '{"result":{"items":[],"count":0}}'
        );
    }

    public function testCertificateProductStatusList(): void
    {
        $this->quickTest(
            'certificateProductStatusList',
            [],
            ['POST', '/v1/product/certificate/product_status/list', '{}'],
            '{"result":[{"code":"ACCEPTED","name":"Принят"}]}'
        );
    }

    public function testCertificateStatusList(): void
    {
        $this->quickTest(
            'certificateStatusList',
            [],
            ['POST', '/v1/product/certificate/status/list', '{}'],
            '{"result":[{"code":"ACTIVE","name":"Активный"}]}'
        );
    }

    public function testCertificateRejectionReasonsList(): void
    {
        $this->quickTest(
            'certificateRejectionReasonsList',
            [],
            ['POST', '/v1/product/certificate/rejection_reasons/list', '{}'],
            '{"result":[{"code":"WRONG_TYPE","name":"Неверный тип"}]}'
        );
    }

    public function testCertificationList(): void
    {
        $this->quickTest(
            'certificationList',
            [2, 50],
            [
                'POST',
                '/v1/product/certification/list',
                '{"page":2,"page_size":50}',
            ],
            '{"result":{"certification":[],"total":0}}'
        );
    }
}

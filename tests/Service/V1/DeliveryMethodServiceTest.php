<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\DeliveryMethodService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\DeliveryMethodService
 */
final class DeliveryMethodServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return DeliveryMethodService::class;
    }

    /**
     * The response has a top-level `result` key, so it is returned unwrapped.
     *
     * @covers ::list
     */
    public function testList(): void
    {
        $this->quickTest(
            'list',
            [
                [
                    'filter' => [
                        'provider_id'  => '1',
                        'status'       => 'ACTIVE',
                        'warehouse_id' => '123',
                        // must be filtered out
                        'foo'          => 'bar',
                    ],
                    'limit'  => '20',
                    'offset' => '10',
                ],
            ],
            [
                'POST',
                '/v1/delivery-method/list',
                '{"limit":20,"offset":10,"filter":{"provider_id":1,"status":"ACTIVE","warehouse_id":123}}',
            ],
            '{"result":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['result' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::returnSettingsGet
     */
    public function testReturnSettingsGet(): void
    {
        $this->quickTest(
            'returnSettingsGet',
            [456],
            [
                'POST',
                '/v1/delivery-method/return/settings/get',
                '{"delivery_method_id":456}',
            ],
            '{"settings":{"post_office_zipcode":"123456"}}',
            static function (array $result): void {
                self::assertSame(['settings' => ['post_office_zipcode' => '123456']], $result);
            }
        );
    }
}

<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\SellerService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\SellerService
 */
final class SellerServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return SellerService::class;
    }

    /**
     * @covers ::info
     */
    public function testInfo(): void
    {
        $json = '{"company":{"name":"ООО Ромашка","inn":"7700000000","tax_system":"OSNO"},"subscription":{"is_premium":true,"type":"PREMIUM_PLUS"}}';

        $this->quickTest(
            'info',
            [],
            ['POST', '/v1/seller/info', '{}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::ozonLogisticsInfo
     */
    public function testOzonLogisticsInfo(): void
    {
        $json = '{"ozon_logistics_enabled":true,"available_schemas":["FBO","FBS"]}';

        $this->quickTest(
            'ozonLogisticsInfo',
            [],
            ['POST', '/v1/seller/ozon-logistics/info', '{}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::roles
     */
    public function testRoles(): void
    {
        $json = '{"expires_at":"2026-12-31T00:00:00Z","roles":[{"name":"admin","methods":[]}]}';

        $this->quickTest(
            'roles',
            [],
            ['POST', '/v1/roles', '{}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }
}

<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\ReturnService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\ReturnService
 */
final class ReturnServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ReturnService::class;
    }

    /**
     * @covers ::giveoutIsEnabled
     */
    public function testGiveoutIsEnabled(): void
    {
        $this->quickTest(
            'giveoutIsEnabled',
            [],
            ['POST', '/v1/return/giveout/is-enabled', '{}'],
            '{"enabled":true}',
            static function (array $result): void {
                self::assertSame(['enabled' => true], $result);
            }
        );
    }

    /**
     * @covers ::giveoutList
     */
    public function testGiveoutList(): void
    {
        $this->quickTest(
            'giveoutList',
            [50, 10],
            ['POST', '/v1/return/giveout/list', '{"limit":50,"last_id":10}'],
            '{"giveouts":[]}',
            static function (array $result): void {
                self::assertSame(['giveouts' => []], $result);
            }
        );
    }

    /**
     * @covers ::giveoutInfo
     */
    public function testGiveoutInfo(): void
    {
        $this->quickTest(
            'giveoutInfo',
            [123],
            ['POST', '/v1/return/giveout/info', '{"giveout_id":123}'],
            '{"giveout_id":123,"giveout_status":"CREATED"}',
            static function (array $result): void {
                self::assertSame(['giveout_id' => 123, 'giveout_status' => 'CREATED'], $result);
            }
        );
    }

    /**
     * @covers ::giveoutBarcode
     */
    public function testGiveoutBarcode(): void
    {
        $this->quickTest(
            'giveoutBarcode',
            [],
            ['POST', '/v1/return/giveout/barcode', '{}'],
            '{"barcode":"1234567890"}',
            static function (array $result): void {
                self::assertSame(['barcode' => '1234567890'], $result);
            }
        );
    }

    /**
     * @covers ::giveoutBarcodeReset
     */
    public function testGiveoutBarcodeReset(): void
    {
        $this->quickTest(
            'giveoutBarcodeReset',
            [],
            ['POST', '/v1/return/giveout/barcode-reset', '{}'],
            '{"barcode":"0987654321"}',
            static function (array $result): void {
                self::assertSame(['barcode' => '0987654321'], $result);
            }
        );
    }

    /**
     * @covers ::giveoutGetPdf
     */
    public function testGiveoutGetPdf(): void
    {
        $this->quickTest(
            'giveoutGetPdf',
            [],
            ['POST', '/v1/return/giveout/get-pdf', '{}'],
            '{"file_name":"barcode.pdf","content_type":"application/pdf"}',
            static function (array $result): void {
                self::assertSame('barcode.pdf', $result['file_name']);
            }
        );
    }

    /**
     * @covers ::giveoutGetPng
     */
    public function testGiveoutGetPng(): void
    {
        $this->quickTest(
            'giveoutGetPng',
            [],
            ['POST', '/v1/return/giveout/get-png', '{}'],
            '{"file_name":"barcode.png","content_type":"image/png"}',
            static function (array $result): void {
                self::assertSame('barcode.png', $result['file_name']);
            }
        );
    }

    /**
     * @covers ::passCreate
     */
    public function testPassCreate(): void
    {
        $this->quickTest(
            'passCreate',
            [
                [
                    [
                        'warehouse_id'          => '123',
                        'dropoff_point_id'      => '456',
                        'arrival_time'          => '2026-08-25T10:00:00Z',
                        'driver_name'           => 'Иванов И.И.',
                        'driver_phone'          => '+79001234567',
                        'vehicle_license_plate' => 'А123БВ777',
                        'vehicle_model'         => 'ГАЗель',
                        // must be filtered out
                        'foo'                   => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/return/pass/create',
                '{"arrival_passes":[{"warehouse_id":123,"dropoff_point_id":456,"arrival_time":"2026-08-25T10:00:00Z","driver_name":"Иванов И.И.","driver_phone":"+79001234567","vehicle_license_plate":"А123БВ777","vehicle_model":"ГАЗель"}]}',
            ],
            '{"arrival_pass_ids":["10"]}',
            static function (array $result): void {
                self::assertSame(['arrival_pass_ids' => ['10']], $result);
            }
        );
    }

    /**
     * @covers ::passUpdate
     */
    public function testPassUpdate(): void
    {
        $this->quickTest(
            'passUpdate',
            [
                [
                    [
                        'arrival_pass_id'       => '10',
                        'arrival_time'          => '2026-08-26T10:00:00Z',
                        'driver_name'           => 'Петров П.П.',
                        'driver_phone'          => '+79001234567',
                        'vehicle_license_plate' => 'А123БВ777',
                        'vehicle_model'         => 'ГАЗель',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/return/pass/update',
                '{"arrival_passes":[{"arrival_pass_id":10,"arrival_time":"2026-08-26T10:00:00Z","driver_name":"Петров П.П.","driver_phone":"+79001234567","vehicle_license_plate":"А123БВ777","vehicle_model":"ГАЗель"}]}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::passDelete
     */
    public function testPassDelete(): void
    {
        $this->quickTest(
            'passDelete',
            [[10, '11']],
            ['POST', '/v1/return/pass/delete', '{"arrival_pass_ids":["10","11"]}'],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }
}

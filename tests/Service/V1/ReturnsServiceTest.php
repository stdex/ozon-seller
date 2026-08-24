<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\ReturnsService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\ReturnsService
 */
final class ReturnsServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ReturnsService::class;
    }

    /**
     * @covers ::rfbsActionSet
     */
    public function testRfbsActionSet(): void
    {
        $this->quickTest(
            'rfbsActionSet',
            [
                [
                    'return_id'           => '123',
                    'id'                  => '1',
                    'comment'             => 'ок',
                    'compensation_amount' => '100.5',
                    'rejection_reason_id' => '2',
                    'return_for_back_way' => '50',
                    // must be filtered out
                    'foo'                 => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/returns/rfbs/action/set',
                '{"return_id":123,"id":1,"comment":"ок","compensation_amount":100.5,"rejection_reason_id":2,"return_for_back_way":50}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::companyFbsInfo
     */
    public function testCompanyFbsInfo(): void
    {
        $this->quickTest(
            'companyFbsInfo',
            [50, 10, 456],
            [
                'POST',
                '/v1/returns/company/fbs/info',
                '{"pagination":{"limit":50,"last_id":10},"filter":{"place_id":456}}',
            ],
            '{"drop_off_points":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['drop_off_points' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::companyFbsInfo
     */
    public function testCompanyFbsInfoDefaults(): void
    {
        $this->quickTest(
            'companyFbsInfo',
            [],
            [
                'POST',
                '/v1/returns/company/fbs/info',
                '{"pagination":{"limit":100}}',
            ],
            '{"drop_off_points":[]}',
            static function (array $result): void {
                self::assertSame(['drop_off_points' => []], $result);
            }
        );
    }

    /**
     * @covers ::settingsUtilizationInfo
     */
    public function testSettingsUtilizationInfo(): void
    {
        $this->quickTest(
            'settingsUtilizationInfo',
            [],
            ['POST', '/v1/returns/settings/utilization/info', '{}'],
            '{"min_price":{"amount":"100","currency":"RUB"}}',
            static function (array $result): void {
                self::assertSame('100', $result['min_price']['amount']);
            }
        );
    }

    /**
     * @covers ::settingsUtilizationUpdate
     */
    public function testSettingsUtilizationUpdate(): void
    {
        $this->quickTest(
            'settingsUtilizationUpdate',
            [
                ['enabled' => true, 'value' => '500', 'foo' => 'bar'],
                ['enabled' => false],
            ],
            [
                'POST',
                '/v1/returns/settings/utilization/update',
                '{"utilization_price":{"enabled":true,"value":500},"utilization_price_defects":{"enabled":false}}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::settingsUtilizationHistory
     */
    public function testSettingsUtilizationHistory(): void
    {
        $this->quickTest(
            'settingsUtilizationHistory',
            [],
            ['POST', '/v1/returns/settings/utilization/history', '{}'],
            '{"history":[]}',
            static function (array $result): void {
                self::assertSame(['history' => []], $result);
            }
        );
    }
}

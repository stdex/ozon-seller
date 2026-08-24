<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1;

use Gam6itko\OzonSeller\Service\V1\NotificationService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\NotificationService
 */
final class NotificationServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return NotificationService::class;
    }

    /**
     * @covers ::set
     */
    public function testSet(): void
    {
        $this->quickTest(
            'set',
            ['https://example.com/ozon', ['TYPE_NEW_POSTING']],
            [
                'POST',
                '/v1/notification/set',
                '{"url":"https:\/\/example.com\/ozon","types":["TYPE_NEW_POSTING"]}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::update
     */
    public function testUpdate(): void
    {
        $this->quickTest(
            'update',
            [
                [
                    'id'    => '1',
                    'url'   => 'https://example.com/ozon',
                    'types' => ['TYPE_NEW_POSTING'],
                    // must be filtered out
                    'foo'   => 'bar',
                ],
            ],
            [
                'POST',
                '/v1/notification/update',
                '{"id":1,"types":["TYPE_NEW_POSTING"],"url":"https:\/\/example.com\/ozon"}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::delete
     */
    public function testDelete(): void
    {
        $this->quickTest(
            'delete',
            [1],
            ['POST', '/v1/notification/delete', '{"id":1}'],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::check
     */
    public function testCheck(): void
    {
        $this->quickTest(
            'check',
            ['https://example.com/ozon'],
            [
                'POST',
                '/v1/notification/check',
                '{"url":"https:\/\/example.com\/ozon"}',
            ],
            '{"is_active":true,"errors":[]}',
            static function (array $result): void {
                self::assertSame(['is_active' => true, 'errors' => []], $result);
            }
        );
    }

    /**
     * @covers ::enable
     */
    public function testEnable(): void
    {
        $this->quickTest(
            'enable',
            [1, false],
            ['POST', '/v1/notification/enable', '{"id":1,"enabled":false}'],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $json = '{"urls":[{"id":1,"url":"https://example.com/ozon","enable":true,"created_at":"2026-08-01T10:00:00Z","types":[]}]}';

        $this->quickTest(
            'list',
            [],
            ['POST', '/v1/notification/list', '{}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::pushTypeList
     */
    public function testPushTypeList(): void
    {
        $json = '{"types":[{"type":"TYPE_NEW_POSTING","description":"Новое отправление"}]}';

        $this->quickTest(
            'pushTypeList',
            [],
            ['POST', '/v1/notification/push-type/list', '{}'],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }
}

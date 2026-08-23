<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V3;

use Gam6itko\OzonSeller\Service\V3\ChatService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V3\ChatService
 */
final class ChatServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ChatService::class;
    }

    /**
     * @covers ::list
     */
    public function testList(): void
    {
        $json = <<<JSON
{
  "chats": [
    {
      "chat": {
        "chat_id": "5969c8ba-a8bd-4c1a-b1b1-1b1b1b1b1b1b",
        "chat_status": "Opened",
        "chat_type": "Buyer_Seller",
        "created_at": "2026-08-01T10:00:00Z"
      },
      "first_unread_message_id": 3000000000117918000,
      "last_message_id": 3000000000117918001,
      "unread_count": 1
    }
  ],
  "total_unread_count": 1,
  "cursor": "next-cursor",
  "has_next": false
}
JSON;

        $this->quickTest(
            'list',
            [
                [
                    'filter' => [
                        'chat_status' => 'OPENED',
                        'unread_only' => true,
                        // must be filtered out
                        'foo'         => 'bar',
                    ],
                    'limit'  => '50',
                    'cursor' => 'prev-cursor',
                ],
            ],
            [
                'POST',
                '/v3/chat/list',
                '{"limit":50,"filter":{"chat_status":"OPENED","unread_only":true},"cursor":"prev-cursor"}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::list
     */
    public function testListDefaults(): void
    {
        $this->quickTest(
            'list',
            [],
            ['POST', '/v3/chat/list', '{"limit":30}'],
            '{"chats":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['chats' => [], 'has_next' => false], $result);
            }
        );
    }

    /**
     * @covers ::history
     */
    public function testHistory(): void
    {
        $json = <<<JSON
{
  "messages": [
    {
      "message_id": 3000000000117918000,
      "created_at": "2026-08-01T10:00:00Z",
      "data": ["Здравствуйте!"],
      "is_image": false,
      "is_read": true,
      "context": {"order_number": "33920474-0032", "sku": "160249683"},
      "user": {"id": "115568", "type": "Customer"}
    }
  ],
  "has_next": false
}
JSON;

        $this->quickTest(
            'history',
            [
                '5969c8ba-a8bd-4c1a-b1b1-1b1b1b1b1b1b',
                [
                    'direction'       => 'Forward',
                    'from_message_id' => '3000000000117918000',
                    'limit'           => '100',
                    'filter'          => [
                        'message_ids' => [3000000000117918000],
                        // must be filtered out
                        'foo'         => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v3/chat/history',
                '{"direction":"Forward","filter":{"message_ids":["3000000000117918000"]},"from_message_id":3000000000117918000,"limit":100,"chat_id":"5969c8ba-a8bd-4c1a-b1b1-1b1b1b1b1b1b"}',
            ],
            $json,
            static function (array $result) use ($json): void {
                self::assertEquals(json_decode($json, true), $result);
            }
        );
    }

    /**
     * @covers ::history
     */
    public function testHistoryMinimal(): void
    {
        $this->quickTest(
            'history',
            ['5969c8ba-a8bd-4c1a-b1b1-1b1b1b1b1b1b'],
            [
                'POST',
                '/v3/chat/history',
                '{"chat_id":"5969c8ba-a8bd-4c1a-b1b1-1b1b1b1b1b1b"}',
            ],
            '{"messages":[],"has_next":false}',
            static function (array $result): void {
                self::assertSame(['messages' => [], 'has_next' => false], $result);
            }
        );
    }
}

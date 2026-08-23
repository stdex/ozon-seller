<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Service\V2\ChatService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\ChatService
 */
final class ChatServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ChatService::class;
    }

    /**
     * @covers ::read
     */
    public function testRead(): void
    {
        $this->quickTest(
            'read',
            ['5969c8ba-a8bd-4c1a-b1b1-1b1b1b1b1b1b'],
            [
                'POST',
                '/v2/chat/read',
                '{"chat_id":"5969c8ba-a8bd-4c1a-b1b1-1b1b1b1b1b1b"}',
            ],
            '{"unread_count":0}',
            static function (array $result): void {
                self::assertSame(['unread_count' => 0], $result);
            }
        );
    }

    /**
     * @covers ::read
     */
    public function testReadFromMessage(): void
    {
        $this->quickTest(
            'read',
            ['5969c8ba-a8bd-4c1a-b1b1-1b1b1b1b1b1b', 3000000000117918000],
            [
                'POST',
                '/v2/chat/read',
                '{"chat_id":"5969c8ba-a8bd-4c1a-b1b1-1b1b1b1b1b1b","from_message_id":3000000000117918000}',
            ],
            '{"unread_count":2}',
            static function (array $result): void {
                self::assertSame(['unread_count' => 2], $result);
            }
        );
    }
}

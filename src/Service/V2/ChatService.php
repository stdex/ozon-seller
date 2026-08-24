<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Chats with customers.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class ChatService extends AbstractService
{
    private $path = '/v2/chat';

    /**
     * Marks messages as read.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ChatAPI_ChatReadV2
     *
     * @param int $fromMessageId id of the message to start marking as read from
     *
     * @return array{unread_count?: int}
     */
    public function read(string $chatId, int $fromMessageId = 0): array
    {
        $requestData = ['chat_id' => $chatId];

        if ($fromMessageId > 0) {
            $requestData['from_message_id'] = $fromMessageId;
        }

        return $this->request('POST', "{$this->path}/read", $requestData);
    }
}

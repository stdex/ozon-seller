<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V3;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Чаты с покупателями.
 *
 * Типы описаны по var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TListFilter = array{
 *     chat_status?: 'ALL'|'OPENED'|'CLOSED',
 *     unread_only?: bool
 * }
 * @psalm-type TListRequest = array{
 *     filter?: TListFilter,
 *     limit?: int,
 *     cursor?: string
 * }
 * @psalm-type TChat = array{
 *     chat?: array{
 *         chat_id?: string,
 *         chat_status?: string,
 *         chat_type?: string,
 *         created_at?: string
 *     },
 *     first_unread_message_id?: int,
 *     last_message_id?: int,
 *     unread_count?: int
 * }
 * @psalm-type TListResponse = array{
 *     chats?: list<TChat>,
 *     total_unread_count?: int,
 *     cursor?: string,
 *     has_next?: bool
 * }
 * @psalm-type THistoryRequest = array{
 *     direction?: 'Forward'|'Backward',
 *     filter?: array{message_ids?: list<string>},
 *     from_message_id?: int,
 *     limit?: int
 * }
 * @psalm-type TMessage = array{
 *     message_id?: int,
 *     created_at?: string,
 *     data?: list<string>,
 *     is_image?: bool,
 *     is_read?: bool,
 *     moderate_image_status?: 'SUCCESS'|'MODERATION'|'FAILED',
 *     context?: array{order_number?: string, sku?: string},
 *     user?: array{id?: string, type?: string}
 * }
 * @psalm-type THistoryResponse = array{
 *     messages?: list<TMessage>,
 *     has_next?: bool
 * }
 */
class ChatService extends AbstractService
{
    private $path = '/v3/chat';

    /**
     * Список чатов.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ChatAPI_ChatListV3
     *
     * @param TListRequest $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 30],
            ArrayHelper::pick($requestData, ['filter', 'limit', 'cursor'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['chat_status', 'unread_only']),
                ['chat_status' => 'str', 'unread_only' => 'bool']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'limit'  => 'int',
            'cursor' => 'str',
        ]);

        return $this->request('POST', "{$this->path}/list", $requestData);
    }

    /**
     * История чата.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ChatAPI_ChatHistoryV3
     *
     * @param THistoryRequest $requestData
     *
     * @return THistoryResponse
     */
    public function history(string $chatId, array $requestData = []): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'direction',
            'filter',
            'from_message_id',
            'limit',
        ]);

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], ['message_ids']),
                ['message_ids' => 'arrOfStr']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'direction'       => 'str',
            'from_message_id' => 'int',
            'limit'           => 'int',
        ]);

        $requestData['chat_id'] = $chatId;

        return $this->request('POST', "{$this->path}/history", $requestData);
    }
}

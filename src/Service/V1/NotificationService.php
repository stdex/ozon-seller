<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Push notifications: configuring the addresses Ozon sends notifications to.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TUpdateRequest = array{
 *     id: int,
 *     types?: list<string>,
 *     url?: string
 * }
 * @psalm-type TCheckResponse = array{
 *     is_active: bool,
 *     errors?: list<array{type?: string, description?: string}>
 * }
 * @psalm-type TListResponse = array{
 *     urls: list<array{
 *         id: int,
 *         url: string,
 *         enable: bool,
 *         created_at: string,
 *         types: list<array>
 *     }>
 * }
 * @psalm-type TPushTypeListResponse = array{
 *     types: list<array{type: string, description: string, seller_endpoint?: array}>
 * }
 */
class NotificationService extends AbstractService
{
    private $path = '/v1/notification';

    /**
     * Adds an address for push notifications.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/NotificationAPI_SetNotification
     *
     * @param list<string> $types notification types from self::pushTypeList
     */
    public function set(string $url, array $types): array
    {
        return $this->request('POST', "{$this->path}/set", [
            'url'   => $url,
            'types' => array_map('strval', $types),
        ]);
    }

    /**
     * Changes the address or the list of notification types.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/NotificationAPI_UpdateNotification
     *
     * @param TUpdateRequest $requestData
     */
    public function update(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, ['id', 'types', 'url']),
            ['id' => 'int', 'types' => 'arrOfStr', 'url' => 'str']
        );

        return $this->request('POST', "{$this->path}/update", $requestData);
    }

    /**
     * Deletes an address for push notifications.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/NotificationAPI_DeleteNotification
     */
    public function delete(int $id): array
    {
        return $this->request('POST', "{$this->path}/delete", ['id' => $id]);
    }

    /**
     * Checks that the address is reachable.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/NotificationAPI_CheckNotification
     *
     * @return TCheckResponse
     */
    public function check(string $url): array
    {
        return $this->request('POST', "{$this->path}/check", ['url' => $url]);
    }

    /**
     * Enables or disables sending notifications to the address.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/NotificationAPI_EnableNotification
     */
    public function enable(int $id, bool $enabled): array
    {
        return $this->request('POST', "{$this->path}/enable", [
            'id'      => $id,
            'enabled' => $enabled,
        ]);
    }

    /**
     * Push notification addresses list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/NotificationAPI_ListNotification
     *
     * @return TListResponse
     */
    public function list(): array
    {
        return $this->request('POST', "{$this->path}/list", '{}');
    }

    /**
     * Push notification types dictionary.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/NotificationAPI_ListPushType
     *
     * @return TPushTypeListResponse
     */
    public function pushTypeList(): array
    {
        return $this->request('POST', "{$this->path}/push-type/list", '{}');
    }
}

<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Discount requests from customers and the auto-add promotion.
 *
 * These endpoints live on the default host, unlike V1\ActionsService which is
 * pinned to seller-api.ozon.ru, so they are kept in a separate class.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TDiscountTask = array{
 *     id: int,
 *     approved_price?: float,
 *     approved_quantity_min?: int,
 *     approved_quantity_max?: int,
 *     seller_comment?: string
 * }
 * @psalm-type TTaskMoveResult = array{
 *     success_count?: int,
 *     fail_count?: int,
 *     fail_details?: list<array>
 * }
 * @psalm-type TAutoAddProduct = array{
 *     product_id: int,
 *     action_price?: float,
 *     quantity?: int,
 *     currency?: string
 * }
 */
class DiscountsTaskService extends AbstractService
{
    /**
     * Discount requests list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/promos_task_list
     *
     * @return list<array>
     */
    public function list(string $status = 'NEW', int $page = 1, int $limit = 100): array
    {
        return $this->request('POST', '/v1/actions/discounts-task/list', [
            'status' => $status,
            'page'   => $page,
            'limit'  => $limit,
        ]);
    }

    /**
     * Approves discount requests.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/promos_task_approve
     *
     * @param list<TDiscountTask> $tasks
     *
     * @return TTaskMoveResult
     */
    public function approve(array $tasks): array
    {
        return $this->request('POST', '/v1/actions/discounts-task/approve', [
            'tasks' => array_map([$this, 'pickTask'], $tasks),
        ]);
    }

    /**
     * Declines discount requests.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/promos_task_decline
     *
     * @param list<TDiscountTask> $tasks
     *
     * @return TTaskMoveResult
     */
    public function decline(array $tasks): array
    {
        return $this->request('POST', '/v1/actions/discounts-task/decline', [
            'tasks' => array_map([$this, 'pickTask'], $tasks),
        ]);
    }

    /**
     * Products already added to the auto-add promotion of a date.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ActionsAPI_ActionsAutoAddProductsList
     *
     * @return array{products?: list<array>, total?: int}
     */
    public function autoAddProductsList(int $actionId, string $autoAddDate, int $limit = 100, int $offset = 0): array
    {
        return $this->request('POST', '/v1/actions/auto-add/products/list', [
            'action_id'     => $actionId,
            'auto_add_date' => $autoAddDate,
            'limit'         => $limit,
            'offset'        => $offset,
        ]);
    }

    /**
     * Products that can be added to the auto-add promotion of a date.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ActionsAPI_ActionsAutoAddProductsCandidates
     *
     * @return array{products?: list<array>, total?: int}
     */
    public function autoAddProductsCandidates(int $actionId, string $autoAddDate, int $limit = 100, int $offset = 0): array
    {
        return $this->request('POST', '/v1/actions/auto-add/products/candidates', [
            'action_id'     => $actionId,
            'auto_add_date' => $autoAddDate,
            'limit'         => $limit,
            'offset'        => $offset,
        ]);
    }

    /**
     * Removes products from the auto-add promotion of a date.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ActionsAPI_ActionsAutoAddProductsDelete
     *
     * @param list<int|string> $productIds
     *
     * @return array{product_ids?: list<string>}
     */
    public function autoAddProductsDelete(int $actionId, string $autoAddDate, array $productIds): array
    {
        return $this->request('POST', '/v1/actions/auto-add/products/delete', [
            'action_id'     => $actionId,
            'auto_add_date' => $autoAddDate,
            'product_ids'   => array_map('strval', $productIds),
        ]);
    }

    /**
     * Adds products to the auto-add promotion of a date or updates their prices.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ActionsAPI_ActionsAutoAddProductsUpdate
     *
     * @param list<TAutoAddProduct> $toUpdate
     *
     * @return array{
     *     updated_ids?: list<string>,
     *     rejected?: list<array>,
     *     failed_price?: list<array>,
     *     below_min_price?: list<array>,
     *     extremely_low_price?: list<array>
     * }
     */
    public function autoAddProductsUpdate(int $actionId, string $autoAddDate, array $toUpdate): array
    {
        $toUpdate = array_map(static function (array $item): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($item, ['product_id', 'action_price', 'quantity', 'currency']),
                [
                    'product_id'   => 'int',
                    'action_price' => 'float',
                    'quantity'     => 'int',
                    'currency'     => 'str',
                ]
            );
        }, $toUpdate);

        return $this->request('POST', '/v1/actions/auto-add/products/update', [
            'action_id'     => $actionId,
            'auto_add_date' => $autoAddDate,
            'to_update'     => $toUpdate,
        ]);
    }

    /**
     * @param array<array-key, mixed> $task
     *
     * @return array<string, mixed>
     */
    private function pickTask(array $task): array
    {
        return TypeCaster::castArr(
            ArrayHelper::pick($task, [
                'id',
                'approved_price',
                'approved_quantity_min',
                'approved_quantity_max',
                'seller_comment',
            ]),
            [
                'id'                    => 'int',
                'approved_price'        => 'float',
                'approved_quantity_min' => 'int',
                'approved_quantity_max' => 'int',
                'seller_comment'        => 'str',
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Seller promotions: discounts, installments, vouchers.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TActionType = 'DISCOUNT'|'VOUCHER_DISCOUNT'|'DISCOUNT_WITH_CONDITION'|'INSTALLMENT'|'INDIVIDUAL_DISCOUNT_BY_PRODUCTS'|'OZON_ACCOUNT_DISCOUNT'|'MULTI_LEVEL_DISCOUNT_ON_AMOUNT'
 * @psalm-type TActionStatus = 'ACTIVE'|'ENDED'|'PLANNED'|'PAUSED'
 * @psalm-type TListRequest = array{
 *     action_ids?: list<string>,
 *     action_type?: list<TActionType>,
 *     status?: list<TActionStatus>,
 *     search?: string,
 *     limit?: int,
 *     offset?: int
 * }
 * @psalm-type TListResponse = array{actions?: list<array>, total?: int}
 * @psalm-type TProductListResponse = array{products?: list<array>, cursor?: int, has_next?: bool}
 * @psalm-type TDiscountLevel = array{order_amount: float, discount_value: float}
 * @psalm-type TActionProduct = array{sku: int, discount_percent?: float, currency?: string}
 */
class SellerActionsService extends AbstractService
{
    private $path = '/v1/seller-actions';

    /**
     * Promotions list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsList
     *
     * @param TListRequest $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 100, 'offset' => 0],
            ArrayHelper::pick($requestData, ['action_ids', 'action_type', 'status', 'search', 'limit', 'offset'])
        );

        $requestData = TypeCaster::castArr($requestData, [
            'action_ids'  => 'arrOfStr',
            'action_type' => 'arrOfStr',
            'status'      => 'arrOfStr',
            'search'      => 'str',
            'limit'       => 'int',
            'offset'      => 'int',
        ]);

        return $this->request('POST', "{$this->path}/list", $requestData);
    }

    /**
     * Archives a promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsArchive
     */
    public function archive(int $actionId): array
    {
        return $this->request('POST', "{$this->path}/archive", ['action_id' => $actionId]);
    }

    /**
     * Pauses or resumes a promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsChangeActivity
     */
    public function changeActivity(int $actionId, bool $isTurnOn): array
    {
        return $this->request('POST', "{$this->path}/change-activity", [
            'action_id'  => $actionId,
            'is_turn_on' => $isTurnOn,
        ]);
    }

    /**
     * Voucher codes as a base64-encoded file.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsVoucherGet
     *
     * @return array{file?: string}
     */
    public function voucherGet(int $actionId): array
    {
        return $this->request('POST', "{$this->path}/voucher/get", ['action_id' => $actionId]);
    }

    /**
     * Products of a promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsProductsList
     *
     * @return TProductListResponse
     */
    public function productsList(int $actionId, int $limit = 100, ?int $cursor = null): array
    {
        return $this->request('POST', "{$this->path}/products/list", $this->pagination($actionId, $limit, $cursor));
    }

    /**
     * Products that can be added to a promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsProductsCandidates
     *
     * @return TProductListResponse
     */
    public function productsCandidates(int $actionId, int $limit = 100, ?int $cursor = null): array
    {
        return $this->request('POST', "{$this->path}/products/candidates", $this->pagination($actionId, $limit, $cursor));
    }

    /**
     * Adds products to a promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsProductsAdd
     *
     * @param list<TActionProduct> $products
     */
    public function productsAdd(int $actionId, array $products): array
    {
        $products = array_map(static function (array $product): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($product, ['sku', 'discount_percent', 'currency']),
                ['sku' => 'int', 'discount_percent' => 'float', 'currency' => 'str']
            );
        }, $products);

        return $this->request('POST', "{$this->path}/products/add", [
            'action_id' => $actionId,
            'products'  => $products,
        ]);
    }

    /**
     * Removes products from a promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsProductsDelete
     *
     * @param list<int|string> $skus
     */
    public function productsDelete(int $actionId, array $skus): array
    {
        return $this->request('POST', "{$this->path}/products/delete", [
            'action_id' => $actionId,
            'skus'      => array_map('strval', $skus),
        ]);
    }

    /**
     * Creates a discount promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsCreateDiscount
     *
     * @param array{date_start: string, date_end: string, min_action_percent: float, title?: string} $requestData
     *
     * @return array{action_id?: int}
     */
    public function createDiscount(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, ['date_start', 'date_end', 'min_action_percent', 'title']),
            [
                'date_start'         => 'str',
                'date_end'           => 'str',
                'min_action_percent' => 'float',
                'title'              => 'str',
            ]
        );

        return $this->request('POST', "{$this->path}/create/discount", $requestData);
    }

    /**
     * Creates a discount promotion with a minimum order amount.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsCreateDiscountWithCondition
     *
     * @param array{date_start: string, date_end: string, discount_type: 'PERCENT'|'CURRENCY', discount_value: float, min_order_amount: float, title?: string} $requestData
     *
     * @return array{action_id?: int}
     */
    public function createDiscountWithCondition(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'date_start',
                'date_end',
                'discount_type',
                'discount_value',
                'min_order_amount',
                'title',
            ]),
            [
                'date_start'       => 'str',
                'date_end'         => 'str',
                'discount_type'    => 'str',
                'discount_value'   => 'float',
                'min_order_amount' => 'float',
                'title'            => 'str',
            ]
        );

        return $this->request('POST', "{$this->path}/create/discount-with-condition", $requestData);
    }

    /**
     * Creates an installment promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsCreateInstallment
     *
     * @return array{action_id?: int}
     */
    public function createInstallment(string $dateStart, string $title): array
    {
        return $this->request('POST', "{$this->path}/create/installment", [
            'date_start' => $dateStart,
            'title'      => $title,
        ]);
    }

    /**
     * Creates a multi-level discount promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsCreateMultiLevelDiscount
     *
     * @param array{date_start: string, date_end: string, discount_type: 'PERCENT'|'CURRENCY', discount_levels: list<TDiscountLevel>, is_legal_entities_segment?: bool, title?: string} $requestData
     *
     * @return array{action_id?: int}
     */
    public function createMultiLevelDiscount(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'date_start',
            'date_end',
            'discount_type',
            'discount_levels',
            'is_legal_entities_segment',
            'title',
        ]);

        if (isset($requestData['discount_levels'])) {
            $requestData['discount_levels'] = $this->pickDiscountLevels($requestData['discount_levels']);
        }

        $requestData = TypeCaster::castArr($requestData, [
            'date_start'                => 'str',
            'date_end'                  => 'str',
            'discount_type'             => 'str',
            'is_legal_entities_segment' => 'bool',
            'title'                     => 'str',
        ]);

        return $this->request('POST', "{$this->path}/create/multi-level-discount", $requestData);
    }

    /**
     * Creates a voucher promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsCreateVoucher
     *
     * @param array{date_start: string, date_end: string, discount_type: 'PERCENT'|'CURRENCY', discount_value: float, budget: int, title: string, voucher_parameters: array{count_codes: int, is_private: bool, type: 'ONE'|'MULTIPLE'|'UNIQUE'}, user_ids?: list<string>} $requestData
     *
     * @return array{action_id?: int}
     */
    public function createVoucher(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'date_start',
            'date_end',
            'discount_type',
            'discount_value',
            'budget',
            'title',
            'voucher_parameters',
            'user_ids',
        ]);

        if (isset($requestData['voucher_parameters'])) {
            $requestData['voucher_parameters'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['voucher_parameters'], ['count_codes', 'is_private', 'type']),
                ['count_codes' => 'int', 'is_private' => 'bool', 'type' => 'str']
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'date_start'     => 'str',
            'date_end'       => 'str',
            'discount_type'  => 'str',
            'discount_value' => 'float',
            'budget'         => 'int',
            'title'          => 'str',
            'user_ids'       => 'arrOfStr',
        ]);

        return $this->request('POST', "{$this->path}/create/voucher", $requestData);
    }

    /**
     * Updates a discount promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsUpdateDiscount
     *
     * @param array{date_start: string, date_end: string, title: string} $actionParameters
     */
    public function updateDiscount(int $actionId, array $actionParameters): array
    {
        return $this->request('POST', "{$this->path}/update/discount", [
            'action_id'         => $actionId,
            'action_parameters' => TypeCaster::castArr(
                ArrayHelper::pick($actionParameters, ['date_start', 'date_end', 'title']),
                ['date_start' => 'str', 'date_end' => 'str', 'title' => 'str']
            ),
        ]);
    }

    /**
     * Updates a discount promotion with a minimum order amount.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsUpdateDiscountWithCondition
     *
     * @param array{date_start: string, date_end: string, discount_value: float, min_order_amount: float, title: string} $actionParameters
     */
    public function updateDiscountWithCondition(int $actionId, array $actionParameters): array
    {
        return $this->request('POST', "{$this->path}/update/discount-with-condition", [
            'action_id'         => $actionId,
            'action_parameters' => TypeCaster::castArr(
                ArrayHelper::pick($actionParameters, [
                    'date_start',
                    'date_end',
                    'discount_value',
                    'min_order_amount',
                    'title',
                ]),
                [
                    'date_start'       => 'str',
                    'date_end'         => 'str',
                    'discount_value'   => 'float',
                    'min_order_amount' => 'float',
                    'title'            => 'str',
                ]
            ),
        ]);
    }

    /**
     * Updates an installment promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsUpdateInstallment
     *
     * @param array{date_start: string, title: string} $actionParameters
     */
    public function updateInstallment(int $actionId, array $actionParameters): array
    {
        return $this->request('POST', "{$this->path}/update/installment", [
            'action_id'         => $actionId,
            'action_parameters' => TypeCaster::castArr(
                ArrayHelper::pick($actionParameters, ['date_start', 'title']),
                ['date_start' => 'str', 'title' => 'str']
            ),
        ]);
    }

    /**
     * Updates a multi-level discount promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsUpdateMultiLevelDiscount
     *
     * @param array{date_start: string, date_end: string, discount_levels: list<TDiscountLevel>, is_legal_entities_segment?: bool, title: string} $actionParameters
     */
    public function updateMultiLevelDiscount(int $actionId, array $actionParameters): array
    {
        $actionParameters = ArrayHelper::pick($actionParameters, [
            'date_start',
            'date_end',
            'discount_levels',
            'is_legal_entities_segment',
            'title',
        ]);

        if (isset($actionParameters['discount_levels'])) {
            $actionParameters['discount_levels'] = $this->pickDiscountLevels($actionParameters['discount_levels']);
        }

        return $this->request('POST', "{$this->path}/update/multi-level-discount", [
            'action_id'         => $actionId,
            'action_parameters' => TypeCaster::castArr($actionParameters, [
                'date_start'                => 'str',
                'date_end'                  => 'str',
                'is_legal_entities_segment' => 'bool',
                'title'                     => 'str',
            ]),
        ]);
    }

    /**
     * Updates a voucher promotion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerActionsAPI_SellerActionsUpdateVoucher
     *
     * @param array{date_start: string, date_end: string, discount_value: float, budget: int, title: string, user_ids?: list<string>} $actionParameters
     */
    public function updateVoucher(int $actionId, array $actionParameters): array
    {
        return $this->request('POST', "{$this->path}/update/voucher", [
            'action_id'         => $actionId,
            'action_parameters' => TypeCaster::castArr(
                ArrayHelper::pick($actionParameters, [
                    'date_start',
                    'date_end',
                    'discount_value',
                    'budget',
                    'title',
                    'user_ids',
                ]),
                [
                    'date_start'     => 'str',
                    'date_end'       => 'str',
                    'discount_value' => 'float',
                    'budget'         => 'int',
                    'title'          => 'str',
                    'user_ids'       => 'arrOfStr',
                ]
            ),
        ]);
    }

    /**
     * @return array{action_id: int, limit: int, cursor?: int}
     */
    private function pagination(int $actionId, int $limit, ?int $cursor): array
    {
        $requestData = [
            'action_id' => $actionId,
            'limit'     => $limit,
        ];

        if (null !== $cursor) {
            $requestData['cursor'] = $cursor;
        }

        return $requestData;
    }

    /**
     * @param array<array-key, mixed> $levels
     *
     * @return list<array<string, mixed>>
     */
    private function pickDiscountLevels(array $levels): array
    {
        return array_values(array_map(static function (array $level): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($level, ['order_amount', 'discount_value']),
                ['order_amount' => 'float', 'discount_value' => 'float']
            );
        }, $levels));
    }
}

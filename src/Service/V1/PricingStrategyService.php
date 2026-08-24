<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Pricing strategies: automatic prices based on competitors.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TCompetitor = array{competitor_id: int, coefficient: float}
 * @psalm-type TListResponse = array{strategies?: list<array>, total?: int}
 * @psalm-type TInfoResult = array{
 *     name?: string,
 *     type?: string,
 *     update_type?: string,
 *     enabled?: bool,
 *     competitors?: list<array>
 * }
 * @psalm-type TProductInfoResult = array{
 *     strategy_id?: string,
 *     is_enabled?: bool,
 *     strategy_product_price?: int,
 *     price_downloaded_at?: string,
 *     strategy_competitor_id?: int,
 *     strategy_competitor_product_url?: string
 * }
 */
class PricingStrategyService extends AbstractService
{
    private $path = '/v1/pricing-strategy';

    /**
     * Strategies list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyList
     *
     * @return TListResponse
     */
    public function list(int $page = 1, int $limit = 100): array
    {
        return $this->request('POST', "{$this->path}/list", [
            'page'  => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * Competitors list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyCompetitorsList
     *
     * @return array{competitor?: list<array>, total?: int}
     */
    public function competitorsList(int $page = 1, int $limit = 100): array
    {
        return $this->request('POST', "{$this->path}/competitors/list", [
            'page'  => $page,
            'limit' => $limit,
        ]);
    }

    /**
     * Creates a strategy.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyCreate
     *
     * @param list<TCompetitor> $competitors
     *
     * @return array{strategy_id?: string}
     */
    public function create(string $strategyName, array $competitors): array
    {
        return $this->request('POST', "{$this->path}/create", [
            'strategy_name' => $strategyName,
            'competitors'   => $this->pickCompetitors($competitors),
        ]);
    }

    /**
     * Strategy info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyInfo
     *
     * @return TInfoResult
     */
    public function info(string $strategyId): array
    {
        return $this->request('POST', "{$this->path}/info", ['strategy_id' => $strategyId]);
    }

    /**
     * Updates a strategy.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyUpdate
     *
     * @param list<TCompetitor> $competitors
     */
    public function update(string $strategyId, string $strategyName, array $competitors): array
    {
        return $this->request('POST', "{$this->path}/update", [
            'strategy_id'   => $strategyId,
            'strategy_name' => $strategyName,
            'competitors'   => $this->pickCompetitors($competitors),
        ]);
    }

    /**
     * Deletes a strategy.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyDelete
     */
    public function delete(string $strategyId): array
    {
        return $this->request('POST', "{$this->path}/delete", ['strategy_id' => $strategyId]);
    }

    /**
     * Enables or disables a strategy.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyStatus
     */
    public function status(string $strategyId, bool $enabled): array
    {
        return $this->request('POST', "{$this->path}/status", [
            'strategy_id' => $strategyId,
            'enabled'     => $enabled,
        ]);
    }

    /**
     * Adds products to a strategy.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyProductsAdd
     *
     * @param list<int|string> $productIds
     *
     * @return array{errors?: list<array>, failed_product_count?: int}
     */
    public function productsAdd(string $strategyId, array $productIds): array
    {
        return $this->request('POST', "{$this->path}/products/add", [
            'strategy_id' => $strategyId,
            'product_id'  => array_map('strval', $productIds),
        ]);
    }

    /**
     * Products of a strategy.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyProductsList
     *
     * @return array{product_id?: list<string>}
     */
    public function productsList(string $strategyId): array
    {
        return $this->request('POST', "{$this->path}/products/list", ['strategy_id' => $strategyId]);
    }

    /**
     * Removes products from their strategies.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyProductsDelete
     *
     * @param list<int|string> $productIds
     *
     * @return array{failed_product_count?: int}
     */
    public function productsDelete(array $productIds): array
    {
        return $this->request('POST', "{$this->path}/products/delete", [
            'product_id' => array_map('strval', $productIds),
        ]);
    }

    /**
     * Strategy info of a product.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyProductInfo
     *
     * @return TProductInfoResult
     */
    public function productInfo(int $productId): array
    {
        return $this->request('POST', "{$this->path}/product/info", ['product_id' => $productId]);
    }

    /**
     * Strategy ids of products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PricingStrategyAPI_PricingStrategyIDsByProductIDs
     *
     * @param list<int|string> $productIds
     *
     * @return array{products_info?: list<array>}
     */
    public function strategyIdsByProductIds(array $productIds): array
    {
        return $this->request('POST', "{$this->path}/strategy-ids-by-product-ids", [
            'product_id' => array_map('strval', $productIds),
        ]);
    }

    /**
     * @param array<array-key, mixed> $competitors
     *
     * @return list<array<string, mixed>>
     */
    private function pickCompetitors(array $competitors): array
    {
        return array_values(array_map(static function (array $competitor): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($competitor, ['competitor_id', 'coefficient']),
                ['competitor_id' => 'int', 'coefficient' => 'float']
            );
        }, $competitors));
    }
}

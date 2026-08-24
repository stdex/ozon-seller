<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * @see    https://cb-api.ozonru.me/apiref/en/#t-title_action
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TAction = array{
 *     id?: int,
 *     title?: string,
 *     action_type?: string,
 *     description?: string,
 *     date_start?: string,
 *     date_end?: string,
 *     auto_add_dates?: list<string>,
 *     freeze_date?: string,
 *     potential_products_count?: int,
 *     participating_products_count?: int,
 *     is_participating?: bool,
 *     is_voucher_action?: bool,
 *     banned_products_count?: int,
 *     with_targeting?: bool,
 *     order_amount?: float,
 *     discount_type?: string,
 *     discount_value?: float
 * }
 * @psalm-type TActionProduct = array{
 *     id?: int,
 *     price?: float,
 *     action_price?: float,
 *     alert_max_action_price_failed?: bool,
 *     alert_max_action_price?: float,
 *     max_action_price?: float,
 *     add_mode?: string,
 *     min_stock?: float,
 *     stock?: float,
 *     current_boost?: float,
 *     price_min_elastic?: float,
 *     price_max_elastic?: float,
 *     min_boost?: float,
 *     max_boost?: float
 * }
 * @psalm-type TActionProductList = array{
 *     products?: list<TActionProduct>,
 *     total?: int,
 *     last_id?: int
 * }
 * @psalm-type TActivateProduct = array{
 *     product_id: int,
 *     action_price: float,
 *     stock?: float
 * }
 * @psalm-type TActionProductsResult = array{
 *     product_ids?: list<int>,
 *     rejected?: list<array{product_id?: int, reason?: string}>
 * }
 */
class ActionsService extends AbstractService
{
    private $path = '/v1/actions';

    protected function getDefaultHost(): string
    {
        return 'https://seller-api.ozon.ru/';
    }

    /**
     * Promotional offers list.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_action_available
     *
     * @return list<TAction>
     */
    public function list(): array
    {
        return $this->request('GET', $this->path);
    }

    /**
     * List of products which can participate in the promotional offer.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_action_available_products
     *
     * @return TActionProductList
     */
    public function candidates(int $actionId, int $offset = 0, int $limit = 10): array
    {
        $body = [
            'action_id' => $actionId,
            'offset'    => $offset,
            'limit'     => $limit,
        ];

        return $this->request('POST', "{$this->path}/candidates", $body);
    }

    /**
     * List of products which participate in the promotional offer.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_action_products
     *
     * @return TActionProductList
     */
    public function products(int $actionId, int $offset = 0, int $limit = 10)
    {
        $body = [
            'action_id' => $actionId,
            'offset'    => $offset,
            'limit'     => $limit,
        ];

        return $this->request('POST', "{$this->path}/products", $body);
    }

    /**
     * Add product to the promotional offer.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_action_add_products
     *
     * @param TActivateProduct|list<TActivateProduct>|array<array-key, mixed> $products single product structure or list of them
     *
     * @return TActionProductsResult
     */
    public function productsActivate(int $actionId, array $products): array
    {
        $products = $this->ensureCollection($products);
        foreach ($products as &$p) {
            $p = ArrayHelper::pick($p, ['product_id', 'action_price', 'stock']);
        }
        unset($p);

        $body = [
            'action_id' => $actionId,
            'products'  => $products,
        ];

        return $this->request('POST', "{$this->path}/products/activate", $body);
    }

    /**
     * This method allows to delete products from the promotional offer.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_action_add_products
     *
     * @param list<int> $productIds
     *
     * @return TActionProductsResult
     */
    public function productsDeactivate(int $actionId, array $productIds): array
    {
        $body = [
            'action_id'   => $actionId,
            'product_ids' => $productIds,
        ];

        return $this->request('POST', "{$this->path}/products/deactivate", $body);
    }
}

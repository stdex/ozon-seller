<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\ProductValidator;
use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TInfoByQuery = array{
 *     product_id?: int,
 *     sku?: int,
 *     offer_id?: string
 * }
 * @psalm-type TPagination = array{page?: int, page_size?: int}
 */
class ProductService extends AbstractService
{
    /**
     * Automatically determines a product category for a product.
     *
     * @param array $income Single product structure or array of structures
     *
     * @return array
     *
     * @deprecated
     * @see http://cb-api.ozonru.me/apiref/en/#t-title_post_product_classifier
     */
    public function classify(array $income)
    {
        if (!array_key_exists('products', $income)) {
            $income = $this->ensureCollection($income);
            $income = ['products' => $income];
        }

        $income = ArrayHelper::pick($income, ['products']);
        foreach ($income['products'] as &$p) {
            $p = ArrayHelper::pick($p, [
                'offer_id',
                'shop_category_full_path',
                'shop_category',
                'shop_category_id',
                'vendor',
                'model',
                'name',
                'price',
                'offer_url',
                'img_url',
                'vendor_code',
                'barcode',
            ]);
        }

        return $this->request('POST', '/v1/product/classify', $income);
    }

    /**
     * Creates product page in our system.
     *
     * @see http://cb-api.ozonru.me/apiref/en/#t-title_post_products_create
     *
     * @param array $income Single item structure or array of items
     *
     * @return array
     */
    public function import(array $income, bool $validateBeforeSend = true)
    {
        if (!array_key_exists('items', $income)) {
            $income = $this->ensureCollection($income);
            $income = ['items' => $income];
        }

        $income = ArrayHelper::pick($income, ['items']);

        if ($validateBeforeSend) {
            $pv = new ProductValidator('create');
            foreach ($income['items'] as &$item) {
                $item = $pv->validateItem($item);
            }
        }

        // cast attributes types.
        foreach ($income['items'] as &$item) {
            if (isset($item['attributes']) && is_array($item['attributes']) && count($item['attributes']) > 0) {
                foreach ($item['attributes'] as &$attribute) {
                    $attribute = TypeCaster::castArr($attribute, ['value' => 'str']);
                    if (isset($item['collection']) && is_array($attribute['collection']) && count($attribute['collection']) > 0) {
                        foreach ($attribute['collection'] as &$collectionItem) {
                            $collectionItem = (string) $collectionItem;
                        }
                    }
                }
            }
        }

        return $this->request('POST', '/v1/product/import', $income);
    }

    /**
     * @param array $income Single item structure or array of item
     *
     * @return array|string
     */
    public function importBySku(array $income)
    {
        if (!array_key_exists('items', $income)) {
            $income = $this->ensureCollection($income);
            $income = ['items' => $income];
        }

        $income = ArrayHelper::pick($income, ['items']);
        foreach ($income['items'] as &$item) {
            $item = TypeCaster::castArr(
                // `premium_price` is gone from swagger.json, kept for backward compatibility
                ArrayHelper::pick($item, ['sku', 'name', 'offer_id', 'price', 'old_price', 'premium_price', 'vat', 'currency_code']),
                [
                    'offer_id'      => 'str',
                    'price'         => 'str',
                    'old_price'     => 'str',
                    'premium_price' => 'str',
                    'vat'           => 'str',
                    'currency_code' => 'str',
                ]
            );
        }

        return $this->request('POST', '/v1/product/import-by-sku', $income);
    }

    /**
     * Product creation status.
     *
     * @see http://cb-api.ozonru.me/apiref/en/#t-title_post_products_create_status
     *
     * @param int $taskId Product import task id
     *
     * @return array
     */
    public function importInfo(int $taskId)
    {
        $query = ['task_id' => $taskId];

        return $this->request('POST', '/v1/product/import/info', $query);
    }

    /**
     * @param int $productId Id of product in Ozon system
     *
     * @return array
     *
     * @deprecated use V2\ProductService::info
     *
     * Receive product info
     * @see        http://cb-api.ozonru.me/apiref/en/#t-title_get_products_info
     */
    public function info(int $productId)
    {
        $query = ['product_id' => $productId];
        $query = TypeCaster::castArr($query, ['product_id' => 'int']);

        return $this->request('POST', '/v1/product/info', $query);
    }

    /**
     * Receive product info.
     *
     * @see http://cb-api.ozonru.me/apiref/en/#t-title_get_products_info
     *
     * @param TInfoByQuery $query
     *
     * @return array
     */
    public function infoBy(array $query)
    {
        $query = ArrayHelper::pick($query, ['product_id', 'sku', 'offer_id']);
        $query = TypeCaster::castArr($query, ['product_id' => 'int', 'sku' => 'int', 'offer_id' => 'str']);

        return $this->request('POST', '/v1/product/info', $query);
    }

    /**
     * @param TPagination $pagination
     *
     * @return array
     *
     * @deprecated use V4\ProductService::infoPrices
     *
     * Receive products prices info
     * @see        https://cb-api.ozonru.me/apiref/en/#t-title_get_product_info_prices
     */
    public function infoPrices(array $pagination = [])
    {
        $pagination = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($pagination, ['page', 'page_size'])
        );

        return $this->request('POST', '/v1/product/info/prices', $pagination);
    }

    /**
     * @param TPagination $pagination
     *
     * @return array
     *
     * @deprecated use V3\ProductService::infoStocks
     *
     * Receive products stocks info
     * @deprecated
     * @see        https://cb-api.ozonru.me/apiref/en/#t-title_get_product_info_stocks
     */
    public function infoStocks(array $pagination = [])
    {
        $pagination = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($pagination, ['page', 'page_size'])
        );

        return $this->request('POST', '/v1/product/info/stocks', $pagination);
    }

    /**
     * @deprecated use V2\ProductService::list
     *
     * Receive the list of products.
     *
     * query['filter']
     *          [offer_id] string|int|array
     *          [product_id] string|int|array,
     *          [visibility] string
     *      [page] int
     * @see        http://cb-api.ozonru.me/apiref/en/#t-title_get_products_list
     */
    public function list(array $query = [], array $pagination = []): array
    {
        $filterKeys = ['offer_id', 'product_id', 'visibility'];

        if (!isset($query['filter']) && array_intersect($filterKeys, array_keys($query))) {
            $query = ['filter' => $query];
        }

        if (isset($query['filter'])) {
            $query['filter'] = ArrayHelper::pick($query['filter'], $filterKeys);
            // normalize offer_id data
            if (isset($query['filter']['offer_id'])) {
                $query['filter']['offer_id'] = array_map('strval', (array) $query['filter']['offer_id']);
            }
            // normalize product_id data
            if (isset($query['filter']['product_id'])) {
                $query['filter']['product_id'] = array_map('intval', (array) $query['filter']['product_id']);
            }
        }

        $query = array_merge($pagination, $query);
        $query = array_merge(['page' => 1, 'page_size' => 10], $query);

        return $this->request('POST', '/v1/product/list', array_filter($query));
    }

    /**
     * Update the price for one or multiple products.
     *
     * @see http://cb-api.ozonru.me/apiref/en/#t-title_post_products_prices
     *
     * @return array
     */
    public function importPrices(array $input)
    {
        if (empty($input)) {
            throw new \InvalidArgumentException('Empty prices data');
        }

        if ($this->isAssoc($input) && !isset($input['prices'])) {// if it one price
            $input = ['prices' => [$input]];
        } else {
            if (!$this->isAssoc($input)) {// if it plain array on prices
                $input = ['prices' => $input];
            }
        }

        if (!isset($input['prices'])) {
            throw new \InvalidArgumentException();
        }

        foreach ($input['prices'] as $i => &$p) {
            if (!$p = ArrayHelper::pick($p, [
                'product_id',
                'offer_id',
                'price',
                'old_price',
                // `premium_price` is gone from swagger.json, kept for backward compatibility
                'premium_price',
                'min_price',
                'net_price',
                'currency_code',
                'vat',
                'quant_size',
                'auto_action_enabled',
                'auto_add_to_ozon_actions_list_enabled',
                'min_price_for_auto_actions_enabled',
                'price_strategy_enabled',
                'manage_elastic_boosting_through_price',
            ])) {
                throw new \InvalidArgumentException('Invalid price data at index '.$i);
            }

            // old_price must be greater than price
            if (!empty($p['old_price']) && !empty($p['price']) && (float) $p['price'] > (float) $p['old_price']) {
                @trigger_error('`old_price` must be greater than `price`', E_USER_WARNING);
                $p['old_price'] = 0;
            }

            $p = TypeCaster::castArr(
                $p,
                [
                    'product_id'    => 'int',
                    'offer_id'      => 'str',
                    'price'         => 'str',
                    'old_price'     => 'str',
                    'premium_price' => 'str',
                    'min_price'     => 'str',
                ]
            );
        }

        return $this->request('POST', '/v1/product/import/prices', $input);
    }

    /**
     * @return array
     *
     * @deprecated use V2\ProductService::importStocks
     *
     * Update product stocks
     * @see        http://cb-api.ozonru.me/apiref/en/#t-title_post_products_stocks
     */
    public function importStocks(array $input)
    {
        if (empty($input)) {
            throw new \InvalidArgumentException('Empty stocks data');
        }

        if ($this->isAssoc($input) && !isset($input['stocks'])) {// if its one price
            $input = ['stocks' => [$input]];
        } else {
            if (!$this->isAssoc($input)) {// if it plain arrays on prices
                $input = ['stocks' => $input];
            }
        }

        if (!isset($input['stocks'])) {
            throw new \InvalidArgumentException();
        }

        foreach ($input['stocks'] as $i => &$s) {
            if (!$s = ArrayHelper::pick($s, ['product_id', 'offer_id', 'stock'])) {
                throw new \InvalidArgumentException('Invalid stock data at index '.$i);
            }

            $s = TypeCaster::castArr(
                $s,
                [
                    'product_id' => 'int',
                    'offer_id'   => 'str',
                    'stock'      => 'int',
                ]
            );
        }

        return $this->request('POST', '/v1/product/import/stocks', $input);
    }

    /**
     * Change the product info. Please note, that you cannot update price and stocks.
     *
     * @see  http://cb-api.ozonru.me/apiref/en/#t-title_post_products_prices
     *
     * @param array $product  Product structure
     * @param bool  $validate Perform validation before send
     *
     * @return array
     *
     * @todo return bool
     */
    public function update(array $product, bool $validate = true)
    {
        if ($validate) {
            $pv = new ProductValidator('update');
            $product = $pv->validateItem($product);
        }

        return $this->request('POST', '/v1/product/update', $product);
    }

    /**
     * Mark the product as in stock.
     *
     * @see        http://cb-api.ozonru.me/apiref/en/#t-title_post_products_activate
     *
     * @return bool success
     *
     * @deprecated
     */
    public function activate(int $productId): bool
    {
        $response = $this->request('POST', '/v1/product/activate', ['product_id' => $productId]);

        return 'success' === $response;
    }

    /**
     * Mark the product as not in stock.
     *
     * @see        http://cb-api.ozonru.me/apiref/en/#t-title_post_products_deactivate
     *
     * @param int $productId Ozon Product Id
     *
     * @return bool success
     *
     * @deprecated
     */
    public function deactivate(int $productId): bool
    {
        $response = $this->request('POST', '/v1/product/deactivate', ['product_id' => $productId]);

        return 'success' === $response;
    }

    /**
     * This method allows you to remove product in some cases: [product must not have active stocks, product should not have any sales].
     *
     * @return bool deleted
     *
     * @deprecated
     */
    public function delete(int $productId, ?string $offerId = null)
    {
        $query = array_filter([
            'product_id' => $productId,
            'offer_id'   => $offerId,
        ]);
        $response = $this->request('POST', '/v1/product/delete', $query);

        return 'deleted' === $response;
    }

    /**
     * @see  https://github.com/gam6itko/ozon-seller/issues/6
     *
     * @param array $filter     ["offer_id": [], "product_id": [], "visibility": "ALL"]
     * @param array $pagination [page, page_size]
     *
     * @todo filter not works
     *
     * @return array
     */
    public function price(array $filter = [], array $pagination = [])
    {
        $filter = ArrayHelper::pick($filter, ['offer_id', 'product_id', 'visibility']);
        $pagination = ArrayHelper::pick($pagination, ['page', 'page_size']);
        $body = array_merge($pagination, [
            'filter' => $filter,
        ]);

        return $this->request('POST', '/v1/product/list/price', $body);
    }

    /**
     * @see  https://cb-api.ozonru.me/apiref/en/#t-prepayment_set
     *
     * @param array $data ['is_prepayment', 'offers_ids', 'products_ids']
     *
     * @return array|string
     */
    public function setPrepayment(array $data)
    {
        $data = ArrayHelper::pick($data, ['is_prepayment', 'offers_ids', 'products_ids']);

        return $this->request('POST', '/v1/product/prepayment/set', $data);
    }

    /**
     * Place product to archive.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_DeleteProducts
     *
     * @param int|string|array $productId
     */
    public function archive($productId): bool
    {
        if (!is_array($productId)) {
            $productId = [$productId];
        }
        $query = ['product_id' => $productId];

        return $this->request('POST', '/v1/product/archive', $query);
    }

    /**
     * Returns product from archive to store.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductUnarchive
     *
     * @param int|string|array $productId
     */
    public function unarchive($productId): bool
    {
        if (!is_array($productId)) {
            $productId = [$productId];
        }
        $query = ['product_id' => $productId];

        return $this->request('POST', '/v1/product/unarchive', $query);
    }

    /**
     * @see https://docs.ozon.ru/api/seller#/certificate/accordance-types-get
     */
    public function certificateAccordanceTypes()
    {
        return $this->request('GET', '/v1/product/certificate/accordance-types', '{}');
    }

    /**
     * @see https://docs.ozon.ru/api/seller#/certificate/bind-post
     */
    public function certificateBind(int $certificateId, array $itemIds): bool
    {
        $body = [
            'certificate_id' => $certificateId,
            'item_id'        => $itemIds,
        ];

        return $this->request('POST', '/v1/product/certificate/accordance-types', $body);
    }

    /**
     * @deprecated will be removed 31.08.2026 - use /v2/product/certification/options, /v2/product/certification/params and /v2/product/certificate/create
     * @see https://docs.ozon.ru/api/seller#/certificate/create-post
     */
    public function certificateCreate(array $data): int
    {
        return $this->request('POST', '/v1/product/certificate/create', $data)['id'];
    }

    /**
     * @see https://docs.ozon.ru/api/seller#/certificate/types-get
     */
    public function certificateTypes(): array
    {
        return $this->request('GET', '/v1/product/certificate/types');
    }

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductImportPictures
     */
    public function picturesImport(array $query): array
    {
        // `images360` and `primary_image` are gone from swagger.json, kept for backward compatibility
        $query = ArrayHelper::pick($query, ['color_image', 'images', 'images360', 'primary_image', 'product_id']);
        $query = TypeCaster::castArr($query, [
            'color_image'   => 'str',
            'images'        => 'arrOfStr',
            'images360'     => 'arrOfStr',
            'primary_image' => 'str',
            'product_id'    => 'int',
        ]);

        return $this->request('POST', '/v1/product/pictures/import', $query);
    }

    /**
     * @param string[]|string $productId
     */
    public function picturesInfo($productId): array
    {
        return $this->request('POST', '/v1/product/pictures/info', [
            'product_id' => TypeCaster::cast($productId, 'arrOfStr'),
        ]);
    }

    /**
     * Receive product content rating by sku.
     *
     * @see https://seller-edu.ozon.ru/docs/work-with-goods/content-rating.html
     */
    public function ratingBySku(array $query): array
    {
        $query = ArrayHelper::pick($query, ['skus']);
        $query = TypeCaster::castArr($query, [
            'skus' => 'arrOfInt',
        ]);

        return $this->request('POST', '/v1/product/rating-by-sku', $query);
    }

    /**
     * Receive product description.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductInfoDescription
     *
     * @param array $query ['product_id', 'offer_id']
     */
    public function infoDescription(array $query): array
    {
        $query = ArrayHelper::pick($query, ['product_id', 'offer_id']);
        $query = TypeCaster::castArr($query, ['product_id' => 'int', 'offer_id' => 'str']);

        return $this->request('POST', '/v1/product/info/description', $query);
    }

    /**
     * Receive stocks in seller's warehouses (FBS and rFBS).
     * fbs-sku param is deprecated since August 15, 2023.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductStocksByWarehouseFbs
     *
     * @deprecated use V2\ProductService::infoStocksByWarehouseFbs
     *
     * @psalm-type TStocksQuery = array{
     *      sku?: int[],
     *      offer_id?: string[],
     *      fbs_sku?: int[],
     * }
     * @psalm-type TStocks = array{
     *      sku: int,
     *      fbs_sku?: int,
     *      present: int,
     *      product_id: int,
     *      reserved: int,
     *      warehouse_id: int,
     *      warehouse_name: string,
     * }
     *
     * @param TStocksQuery $query
     *
     * @return list<TStocks>
     */
    public function infoStocksByWarehouseFbs(array $query): array
    {
        // `fbs_sku` is gone from swagger.json, kept for backward compatibility
        $query = ArrayHelper::pick($query, ['sku', 'offer_id', 'fbs_sku']);
        $query = TypeCaster::castArr($query, ['sku' => 'arrayOfString', 'offer_id' => 'arrayOfString', 'fbs_sku' => 'arrayOfString']);

        return $this->request('POST', '/v1/product/info/stocks-by-warehouse/fbs', $query);
    }

    /**
     * Set a discount on a markdown product.
     *
     * @see https://docs.ozon.ru/api/seller/en/?__rr=1&abt_att=1#operation/ProductAPI_ProductUpdateDiscount
     *
     * @param int $product_id Product identifier in the Ozon system
     * @param int $discount   Discount amount: from 3 to 99 percents
     */
    public function updateDiscount(int $product_id, int $discount): bool
    {
        $query = [
            'discount'   => $discount,
            'product_id' => $product_id,
        ];
        if ($discount > 99 || $discount < 3) {
            throw new \InvalidArgumentException('Discount should be between 3 and 99 percent');
        }

        return $this->request('POST', '/v1/product/update/discount', $query);
    }

    /**
     * Placement zones of products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductPlacementZoneInfo
     *
     * @param list<int|string> $skus
     *
     * @return array{products_placement?: list<array>}
     */
    public function placementZoneInfo(array $skus): array
    {
        return $this->request('POST', '/v1/product/placement-zone/info', [
            'skus' => array_map('strval', $skus),
        ]);
    }

    /**
     * Updates product attributes.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductUpdateAttributes
     *
     * @param list<array{offer_id: string, attributes?: list<array>}> $items
     *
     * @return array{task_id?: int}
     */
    public function attributesUpdate(array $items): array
    {
        $items = array_map(static function (array $item): array {
            return ArrayHelper::pick($item, ['offer_id', 'attributes']);
        }, $items);

        return $this->request('POST', '/v1/product/attributes/update', ['items' => $items]);
    }

    /**
     * Changes product offer ids.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductUpdateOfferID
     *
     * @param list<array{offer_id: string, new_offer_id: string}> $updateOfferId
     *
     * @return array{errors?: list<array>}
     */
    public function updateOfferId(array $updateOfferId): array
    {
        $updateOfferId = array_map(static function (array $pair): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($pair, ['offer_id', 'new_offer_id']),
                ['offer_id' => 'str', 'new_offer_id' => 'str']
            );
        }, $updateOfferId);

        return $this->request('POST', '/v1/product/update/offer-id', ['update_offer_id' => $updateOfferId]);
    }

    /**
     * Number of customers who subscribed to a product.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductInfoSubscription
     *
     * @param list<int|string> $skus
     *
     * @return list<array{sku?: int, count?: int}>
     */
    public function infoSubscription(array $skus): array
    {
        return $this->request('POST', '/v1/product/info/subscription', [
            'skus' => array_map('strval', $skus),
        ]);
    }

    /**
     * Related SKUs of a product.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetRelatedSKU
     *
     * @param list<int|string> $skus
     *
     * @return array{items?: list<array>, errors?: list<array>}
     */
    public function relatedSkuGet(array $skus): array
    {
        return $this->request('POST', '/v1/product/related-sku/get', [
            'sku' => array_map('strval', $skus),
        ]);
    }

    /**
     * Products with wrong volume weight.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductInfoWrongVolume
     *
     * @return array{products?: list<array>, cursor?: string}
     */
    public function infoWrongVolume(int $limit = 100, string $cursor = ''): array
    {
        $query = ['limit' => $limit];

        if ('' !== $cursor) {
            $query['cursor'] = $cursor;
        }

        return $this->request('POST', '/v1/product/info/wrong-volume', $query);
    }

    /**
     * Stocks of a specific warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductInfoWarehouseStocks
     *
     * @return array{stocks?: list<array>, cursor?: string, has_next?: bool}
     */
    public function infoWarehouseStocks(int $warehouseId, int $limit = 100, string $cursor = ''): array
    {
        $query = [
            'warehouse_id' => $warehouseId,
            'limit'        => $limit,
        ];

        if ('' !== $cursor) {
            $query['cursor'] = $cursor;
        }

        return $this->request('POST', '/v1/product/info/warehouse/stocks', $query);
    }

    /**
     * Restarts the promotion timer of products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductActionTimerUpdate
     *
     * @param list<int|string> $productIds
     */
    public function actionTimerUpdate(array $productIds): array
    {
        return $this->request('POST', '/v1/product/action/timer/update', [
            'product_ids' => array_map('strval', $productIds),
        ]);
    }

    /**
     * Promotion timer status of products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductActionTimerStatus
     *
     * @param list<int|string> $productIds
     *
     * @return array{statuses?: list<array>}
     */
    public function actionTimerStatus(array $productIds): array
    {
        return $this->request('POST', '/v1/product/action/timer/status', [
            'product_ids' => array_map('strval', $productIds),
        ]);
    }

    /**
     * Info about markdown products and their main products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductInfoDiscounted
     *
     * @param list<int|string> $discountedSkus
     *
     * @return array{items?: list<array>}
     */
    public function infoDiscounted(array $discountedSkus): array
    {
        return $this->request('POST', '/v1/product/info/discounted', [
            'discounted_skus' => array_map('strval', $discountedSkus),
        ]);
    }

    /**
     * FBO stocks by warehouse.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductStocksByWarehouseFbo
     *
     * @param array{skus?: list<string>, offer_ids?: list<string>, limit?: int, cursor?: string} $query
     *
     * @return array{products?: list<array>, cursor?: string, has_next?: bool}
     */
    public function infoStocksByWarehouseFbo(array $query = []): array
    {
        $query = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($query, ['skus', 'offer_ids', 'limit', 'cursor'])
        );

        $query = TypeCaster::castArr($query, [
            'skus'      => 'arrOfStr',
            'offer_ids' => 'arrOfStr',
            'limit'     => 'int',
            'cursor'    => 'str',
        ]);

        return $this->request('POST', '/v1/product/info/stocks-by-warehouse/fbo', $query);
    }

    /**
     * Updates stocks of digital products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductsDigitalStocksImport
     *
     * @param list<array{offer_id: string, stock: int}> $stocks
     *
     * @return array{status?: list<array>}
     */
    public function digitalStocksImport(array $stocks): array
    {
        $stocks = array_map(static function (array $stock): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($stock, ['offer_id', 'stock']),
                ['offer_id' => 'str', 'stock' => 'int']
            );
        }, $stocks);

        return $this->request('POST', '/v1/product/digital/stocks/import', ['stocks' => $stocks]);
    }

    /**
     * Sets the quantity discount ladder.
     *
     * Nested `stairway` is passed as is.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_SetProductStairwayDiscountByQuantity
     *
     * @param list<array{sku: int, enabled: bool, stairway: array}> $stairways
     *
     * @return array{accepted?: bool, errors?: list<array>, warnings?: list<array>}
     */
    public function stairwayDiscountByQuantitySet(array $stairways, bool $suppressWarnings = false): array
    {
        $stairways = array_map(static function (array $stairway): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($stairway, ['sku', 'enabled', 'stairway']),
                ['sku' => 'int', 'enabled' => 'bool']
            );
        }, $stairways);

        return $this->request('POST', '/v1/product/stairway-discount/by-quantity/set', [
            'stairways'         => $stairways,
            'suppress_warnings' => $suppressWarnings,
        ]);
    }

    /**
     * Quantity discount ladder of products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductStairwayDiscountByQuantity
     *
     * @param list<int|string> $skus
     *
     * @return array{stairways?: list<array>}
     */
    public function stairwayDiscountByQuantityGet(array $skus): array
    {
        return $this->request('POST', '/v1/product/stairway-discount/by-quantity/get', [
            'skus' => array_map('strval', $skus),
        ]);
    }

    /**
     * Sets where products are placed.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductVisibilitySet
     *
     * @param list<array{sku: int, placement: string}> $itemPlacement
     *
     * @return array{items?: list<array>, items_errors?: list<array>}
     */
    public function visibilitySet(array $itemPlacement): array
    {
        $itemPlacement = array_map(static function (array $item): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($item, ['sku', 'placement']),
                ['sku' => 'int', 'placement' => 'str']
            );
        }, $itemPlacement);

        return $this->request('POST', '/v1/product/visibility/set', ['item_placement' => $itemPlacement]);
    }

    /**
     * Where products are placed.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductVisibilityInfo
     *
     * @param list<int|string> $skus
     *
     * @return array{items?: list<array>}
     */
    public function visibilityInfo(array $skus = []): array
    {
        $query = [];

        if ($skus) {
            $query['skus'] = array_map('strval', $skus);
        }

        return $this->request('POST', '/v1/product/visibility/info', $query ?: '{}');
    }

    /**
     * Economy products (quants) list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductQuantList
     *
     * @return array{products?: list<array>, cursor?: string, total_items?: int}
     */
    public function quantList(int $limit = 100, string $cursor = '', string $visibility = ''): array
    {
        $query = ['limit' => $limit];

        if ('' !== $cursor) {
            $query['cursor'] = $cursor;
        }

        if ('' !== $visibility) {
            $query['visibility'] = $visibility;
        }

        return $this->request('POST', '/v1/product/quant/list', $query);
    }

    /**
     * Economy products (quants) info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductQuantInfo
     *
     * @param list<string> $quantCodes
     *
     * @return array{items?: list<array>}
     */
    public function quantInfo(array $quantCodes): array
    {
        return $this->request('POST', '/v1/product/quant/info', [
            'quant_code' => array_map('strval', $quantCodes),
        ]);
    }

    /**
     * Price details of products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductPricesDetails
     *
     * @param list<int|string> $skus
     *
     * @return array{prices?: list<array>}
     */
    public function pricesDetails(array $skus): array
    {
        return $this->request('POST', '/v1/product/prices/details', [
            'skus' => array_map('strval', $skus),
        ]);
    }

    /**
     * Certificates list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificateList
     *
     * @param array{offer_id?: string, status?: string, type?: string, page?: int, page_size?: int} $query
     *
     * @return array{certificates?: list<array>, page_count?: int}
     */
    public function certificateList(array $query = []): array
    {
        $query = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($query, ['offer_id', 'status', 'type', 'page', 'page_size'])
        );

        $query = TypeCaster::castArr($query, [
            'offer_id'  => 'str',
            'status'    => 'str',
            'type'      => 'str',
            'page'      => 'int',
            'page_size' => 'int',
        ]);

        return $this->request('POST', '/v1/product/certificate/list', $query);
    }

    /**
     * Certificate info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificateInfo
     *
     * @return array<string, mixed>
     */
    public function certificateInfo(string $certificateNumber): array
    {
        return $this->request('POST', '/v1/product/certificate/info', [
            'certificate_number' => $certificateNumber,
        ]);
    }

    /**
     * Deletes a certificate.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificateDelete
     *
     * @return array{is_delete?: bool, error_message?: string}
     */
    public function certificateDelete(int $certificateId): array
    {
        return $this->request('POST', '/v1/product/certificate/delete', [
            'certificate_id' => $certificateId,
        ]);
    }

    /**
     * Unbinds products from a certificate.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificateUnbind
     *
     * @param array{product_id?: list<string>, skus?: list<string>} $query
     *
     * @return list<array{product_id?: int, updated?: bool, error?: string}>
     */
    public function certificateUnbind(int $certificateId, array $query = []): array
    {
        $query = TypeCaster::castArr(
            ArrayHelper::pick($query, ['product_id', 'skus']),
            ['product_id' => 'arrOfStr', 'skus' => 'arrOfStr']
        );

        $query['certificate_id'] = $certificateId;

        return $this->request('POST', '/v1/product/certificate/unbind', $query);
    }

    /**
     * Products bound to a certificate.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificateProductsList
     *
     * @param array{last_id?: int, limit?: int, product_status_code?: string, page?: int, page_size?: int} $query
     *
     * @return array{items?: list<array>, count?: int}
     */
    public function certificateProductsList(int $certificateId, array $query = []): array
    {
        $query = TypeCaster::castArr(
            ArrayHelper::pick($query, ['last_id', 'limit', 'product_status_code', 'page', 'page_size']),
            [
                'last_id'             => 'int',
                'limit'               => 'int',
                'product_status_code' => 'str',
                'page'                => 'int',
                'page_size'           => 'int',
            ]
        );

        $query['certificate_id'] = $certificateId;

        return $this->request('POST', '/v1/product/certificate/products/list', $query);
    }

    /**
     * Possible statuses of a product bound to a certificate.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificateProductStatusList
     *
     * @return list<array{code?: string, name?: string}>
     */
    public function certificateProductStatusList(): array
    {
        return $this->request('POST', '/v1/product/certificate/product_status/list', '{}');
    }

    /**
     * Possible certificate statuses.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificateStatusList
     *
     * @return list<array{code?: string, name?: string}>
     */
    public function certificateStatusList(): array
    {
        return $this->request('POST', '/v1/product/certificate/status/list', '{}');
    }

    /**
     * Possible certificate rejection reasons.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificateRejectionReasonsList
     *
     * @return list<array{code?: string, name?: string}>
     */
    public function certificateRejectionReasonsList(): array
    {
        return $this->request('POST', '/v1/product/certificate/rejection_reasons/list', '{}');
    }

    /**
     * Categories that require a certificate.
     *
     * @deprecated use \Gam6itko\OzonSeller\Service\V2\ProductService::certificationList
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificationList
     *
     * @return array{certification?: list<array>, total?: int}
     */
    public function certificationList(int $page = 1, int $pageSize = 100): array
    {
        return $this->request('POST', '/v1/product/certification/list', [
            'page'      => $page,
            'page_size' => $pageSize,
        ]);
    }
}

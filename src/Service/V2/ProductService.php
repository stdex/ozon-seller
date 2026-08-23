<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\ProductValidator;
use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TInfoQuery = array{
 *      product_id?: int,
 *      sku?: int,
 *      offer_id?: string
 * }
 * @psalm-type TCertificateType = 'UNKNOWN'|'CERTIFICATE_OF_CONFORMITY'|'DECLARATION'|'CERTIFICATE_OF_REGISTRATION'|'REGISTRATION_CERTIFICATE'|'REFUSED_LETTER'|'VETERINARY_COVER_DOCUMENT'|'SAFETY_DATA_SHEET'
 * @psalm-type TCertificateFile = array{
 *      name: string,
 *      file_content: string
 * }
 * @psalm-type TCertificateExpiredDate = array{
 *      date?: array{day?: int, month?: int, year?: int},
 *      infinite?: bool
 * }
 * @psalm-type TCertificateCreateParams = array{
 *      name?: string,
 *      number?: string,
 *      certificate_type?: TCertificateType,
 *      certificate_country?: string,
 *      accordance_type?: string,
 *      product_type?: string,
 *      issue_date?: string,
 *      expired_date?: TCertificateExpiredDate,
 *      link_to_registry?: string,
 *      files?: list<TCertificateFile>,
 *      skus?: list<string>
 * }
 * @psalm-type TCertificateOptionName = 'NAME'|'CERTIFICATE_TYPE'|'NUMBER'|'FILES'|'CERTIFICATE_COUNTRY'|'ACCORDANCE_TYPE'|'SKUS'|'ISSUE_DATE'|'EXPIRED_DATE'|'LINK_TO_REGISTRY'|'PRODUCT_TYPE'|'INFINITE'
 * @psalm-type TCertificationParamsResponse = array{
 *      params?: list<array{name?: TCertificateOptionName, required?: bool}>
 * }
 * @psalm-type TCertificationOptionsResponse = array{
 *      option?: list<array{name?: TCertificateOptionName, required?: bool}>
 * }
 * @psalm-type TCertificateCreateResponse = array{
 *      certificate_id?: int,
 *      status?: 'INCOMPLETE'|'COMPLETED',
 *      params?: list<array{name?: string, state?: 'VALID'|'INVALID'|'MISSING', error?: string}>
 * }
 */
class ProductService extends AbstractService
{
    private $path = '/v2/product';

    /**
     * @deprecated use V3\ProductService::import
     *
     * Creates product page in our system.
     *
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_product_import
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
            $pv = new ProductValidator('create', 2);
            foreach ($income['items'] as &$item) {
                $item = $pv->validateItem($item);
            }
        }

        return $this->request('POST', "{$this->path}/import", $income);
    }

    /**
     * @deprecated use V3\ProductService::list
     *
     * Receive the list of products.
     *
     * query['filter']
     *          [offer_id] string|int|array
     *          [product_id] string|int|array,
     *          [visibility] string
     *      [last_id] str
     *      [limit] int
     * @see http://cb-api.ozonru.me/apiref/en/#t-title_get_products_list
     */
    public function list(array $query)
    {
        $query = ArrayHelper::pick($query, ['filter', 'last_id', 'limit']);
        $query = TypeCaster::castArr($query, ['last_id' => 'str', 'limit' => 'int']);
        if (isset($query['filter'])) {
            $query['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($query['filter'], ['offer_id', 'product_id', 'visibility']),
                ['offer_id' => 'arrOfStr', 'product_id' => 'arrOfInt', 'visibility' => 'str']
            );
        }

        return $this->request('POST', "{$this->path}/list", $query);
    }

    /**
     * @deprecated use V3\ProductService::list
     *
     * Receive product info
     * @see https://cb-api.ozonru.me/apiref/en/#t-title_products_info
     *
     * @param array $query ['product_id', 'sku', 'offer_id']
     */
    public function info(array $query): array
    {
        $query = ArrayHelper::pick($query, ['product_id', 'sku', 'offer_id']);
        $query = TypeCaster::castArr($query, ['product_id' => 'int', 'sku' => 'int', 'offer_id' => 'str']);

        return $this->request('POST', "{$this->path}/info", $query);
    }

    /**
     * @deprecated use V3\ProductService::infoList
     */
    public function infoList(array $query): array
    {
        $query = ArrayHelper::pick($query, ['product_id', 'sku', 'offer_id']);
        $query = TypeCaster::castArr($query, [
            'product_id' => 'arrOfInt',
            'sku'        => 'arrOfInt',
            'offer_id'   => 'arrOfStr',
        ]);

        return $this->request('POST', "{$this->path}/info/list", $query);
    }

    /**
     * @deprecated use V3\ProductService::infoAttributes
     * @see        https://cb-api.ozonru.me/apiref/en/#t-title_products_info_attributes
     */
    public function infoAttributes(array $filter, int $page = 1, int $pageSize = 100): array
    {
        $keys = ['offer_id', 'product_id'];
        $filter = ArrayHelper::pick($filter, $keys);

        foreach ($keys as $k) {
            if (isset($filter[$k]) && !is_array($filter[$k])) {
                $filter[$k] = [$filter[$k]];
            }
        }

        if (isset($filter['offer_id'])) {
            $filter['offer_id'] = array_map('strval', $filter['offer_id']);
        }

        $query = [
            'filter'    => $filter,
            'page'      => $page,
            'page_size' => $pageSize,
        ];

        return $this->request('POST', "{$this->path}s/info/attributes", $query);
    }

    /**
     * @param array $pagination ['page', 'page_size']
     *
     * @return array {items: array, total: int}
     *
     * Receive products stocks info
     *
     * @see        https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductInfoPricesV2
     */
    public function infoStocks(array $pagination = []): array
    {
        $pagination = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($pagination, ['page', 'page_size'])
        );

        return $this->request('POST', "{$this->path}/info/stocks", $pagination);
    }

    /**
     * @param array $pagination [page, page_size]
     *
     * @return array
     *
     * @deprecated use V4\ProductService::infoPrices
     *
     * Receive products prices info
     * @see        https://docs.ozon.ru/api/seller/#operation/ProductAPI_GetProductInfoListV2
     */
    public function infoPrices(array $pagination = [])
    {
        $pagination = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($pagination, ['page', 'page_size'])
        );

        return $this->request('POST', "{$this->path}/info/prices", $pagination);
    }

    /**
     * Update product stocks.
     *
     * @return array
     *
     * @see        https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductsStocksV2
     */
    public function importStocks(array $input)
    {
        if (empty($input)) {
            throw new \InvalidArgumentException('Empty stocks data');
        }

        if ($this->isAssoc($input) && !isset($input['stocks'])) {// if it one price
            $input = ['stocks' => [$input]];
        } else {
            if (!$this->isAssoc($input)) {// if it plain array on prices
                $input = ['stocks' => $input];
            }
        }

        if (!isset($input['stocks'])) {
            throw new \InvalidArgumentException();
        }

        foreach ($input['stocks'] as $i => &$s) {
            if (!$s = ArrayHelper::pick($s, ['product_id', 'offer_id', 'stock', 'warehouse_id'])) {
                throw new \InvalidArgumentException('Invalid stock data at index '.$i);
            }

            $s = TypeCaster::castArr(
                $s,
                [
                    'product_id'   => 'int',
                    'offer_id'     => 'str',
                    'stock'        => 'int',
                    'warehouse_id' => 'int',
                ]
            );
        }

        return $this->request('POST', "{$this->path}s/stocks", $input);
    }

    /**
     * @param array $input one of: <br>
     *                     {products:[{offer_id: "str"}, ...]}<br>
     *                     [{offer_id: "str"}, ...]<br>
     *                     {offer_id: "str"}<br>
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_DeleteProducts
     */
    public function delete(array $input)
    {
        if ($this->isAssoc($input) && !isset($input['products'])) {// if it one price
            $input = ['products' => [$input]];
        } else {
            if (!$this->isAssoc($input)) {// if it plain array on prices
                $input = ['products' => $input];
            }
        }

        if (!isset($input['products'])) {
            throw new \InvalidArgumentException();
        }

        foreach ($input['products'] as $i => &$s) {
            if (!$s = ArrayHelper::pick($s, ['offer_id'])) {
                throw new \InvalidArgumentException('Invalid stock data at index '.$i);
            }

            $s = TypeCaster::castArr(
                $s,
                [
                    'offer_id' => 'str',
                ]
            );
        }

        return $this->request('POST', "{$this->path}s/delete", $input);
    }

    /**
     * Receive stocks in seller's warehouses (FBS and rFBS).
     *
     * @see https://docs.ozon.ru/api/seller/?__rr=1&abt_att=1#operation/ProductAPI_GetProductInfoStocksByWarehouseFbsV2
     *
     * @psalm-type TStocksQuery = array{
     *      cursor?: string,
     *      limitL: int,
     *      sku?: string[],
     *      fbs_sku?: string[],
     * }
     * @psalm-type TStocks = array{
     *      free_stock: int,
     *      offer_id: string,
     *      present: int,
     *      product_id: int,
     *      reserved: int,
     *      warehouse_id: int,
     *      warehouse_name: string,
     * }
     * @psalm-type TStocksResponse = array{
     *      cursor: string,
     *      has_next: bool,
     *      products: TStocks[]
     * }
     *
     * @param TStocksQuery $query
     *
     * @return TStocksResponse
     */
    public function infoStocksByWarehouseFbs(array $query): array
    {
        $query = ArrayHelper::pick($query, ['cursor', 'limit', 'offer_id', 'sku']);
        $query = TypeCaster::castArr($query, [
            'cursor'   => 'string',
            'limit'    => 'int',
            'offer_id' => 'arrayOfString',
            'sku'      => 'arrayOfString',
        ]
        );
        if (empty($query['offer_id']) && empty($query['sku'])) {
            throw new \InvalidArgumentException('Non-empty list of sku or offer_id is required');
        }

        return $this->request('POST', "{$this->path}/info/stocks-by-warehouse/fbs", $query);
    }

    /**
     * Creates a quality certificate. Replaces V1\ProductService::certificateCreate,
     * which Ozon shuts down on 31.08.2026.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductCertificateCreate
     *
     * @param TCertificateCreateParams|array<array-key, mixed> $params
     *
     * @return TCertificateCreateResponse
     */
    public function certificateCreate(array $params): array
    {
        $params = ArrayHelper::pick($params, [
            'accordance_type',
            'certificate_country',
            'certificate_type',
            'expired_date',
            'files',
            'issue_date',
            'link_to_registry',
            'name',
            'number',
            'product_type',
            'skus',
        ]);

        $params = TypeCaster::castArr($params, [
            'accordance_type'     => 'str',
            'certificate_country' => 'str',
            'certificate_type'    => 'str',
            'issue_date'          => 'str',
            'link_to_registry'    => 'str',
            'name'                => 'str',
            'number'              => 'str',
            'product_type'        => 'str',
            'skus'                => 'arrOfStr',
        ]);

        foreach ($params['files'] ?? [] as $file) {
            if (empty($file['name']) || empty($file['file_content'])) {
                throw new \InvalidArgumentException('Each file requires `name` and base64 encoded `file_content`');
            }
        }

        return $this->request('POST', "{$this->path}/certificate/create", ['params' => $params]);
    }

    /**
     * Required parameters for creating a quality certificate: which fields
     * have to be passed to self::certificateCreate for a particular certificate.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductCertificateParams
     *
     * @param TCertificateCreateParams|array<array-key, mixed> $params
     *
     * @return TCertificationParamsResponse
     */
    public function certificationParams(array $params = []): array
    {
        $params = ArrayHelper::pick($params, [
            'accordance_type',
            'certificate_country',
            'certificate_type',
            'expired_date',
            'files',
            'issue_date',
            'link_to_registry',
            'name',
            'number',
            'product_type',
            'skus',
        ]);

        $params = TypeCaster::castArr($params, [
            'accordance_type'     => 'str',
            'certificate_country' => 'str',
            'certificate_type'    => 'str',
            'issue_date'          => 'str',
            'link_to_registry'    => 'str',
            'name'                => 'str',
            'number'              => 'str',
            'product_type'        => 'str',
            'skus'                => 'arrOfStr',
        ]);

        return $this->request('POST', "{$this->path}/certification/params", ['params' => $params]);
    }

    /**
     * The full list of parameters a quality certificate is built from,
     * with a required flag.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductCertificateOptions
     *
     * @return TCertificationOptionsResponse
     */
    public function certificationOptions(): array
    {
        return $this->request('POST', "{$this->path}/certification/options");
    }

    /**
     * Product pictures info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductInfoPicturesV2
     *
     * @param list<int|string> $productIds
     *
     * @return array{items?: list<array>}
     */
    public function picturesInfo(array $productIds): array
    {
        return $this->request('POST', "{$this->path}/pictures/info", [
            'product_id' => array_map('strval', $productIds),
        ]);
    }

    /**
     * Categories that require a certificate.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificationListV2
     *
     * @return array{certification?: list<array>, total?: int}
     */
    public function certificationList(int $page = 1, int $pageSize = 100): array
    {
        return $this->request('POST', "{$this->path}/certification/list", [
            'page'      => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * Accordance types of certificates.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ProductAPI_ProductCertificateAccordanceTypesV2
     *
     * @return array{base?: list<array>, hazard?: list<array>}
     */
    public function certificateAccordanceTypesList(): array
    {
        return $this->request('POST', "{$this->path}/certificate/accordance-types/list", '{}');
    }
}

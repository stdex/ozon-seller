<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V4\Posting;

use Gam6itko\OzonSeller\Enum\PostingScheme;
use Gam6itko\OzonSeller\Enum\SortDirection;
use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\Utils\ArrayHelper;
use Gam6itko\OzonSeller\Utils\WithResolver;

/**
 * @psalm-type TShipResponseProduct = array{
 *     mandatory_mark: list<string>,
 *     name: string,
 *     offer_id: string,
 *     price: string,
 *     quantity: integer,
 *     sku: integer,
 *     currency_code: string
 * }
 * @psalm-type TShipResponseAdditionalData = array{
 *      posting_number: string,
 *      products: list<TShipResponseProduct>
 * }
 * @psalm-type TShipResponse = array{
 *      additional_data: TShipResponseAdditionalData,
 *      result: list<string>
 * }
 * @psalm-type THasProducts = array{
 *     products: list<TShipProduct>
 * }
 * @psalm-type TShipProduct = array{
 *     product_id: int,
 *     quantity: int
 * }
 * @psalm-type TShipPackageProduct = array{
 *      exemplarsIds: int,
 *      product_id: int
 *      quantity: int
 *  }
 * @psalm-type TShipWith = array{additional_data: bool}
 * @psalm-type TListStatusDate = array{
 *     from: string,
 *     to: string
 *  }
 * @psalm-type TListFilter = array{
 *     delivery_method_ids?: int[],
 *     integration_type_flow?: string,
 *     is_blr_traceable?: bool,
 *     last_changed_status_date?: TListStatusDate,
 *     order_id?: int,
 *     order_numbers?: string[],
 *     provider_ids?: int[],
 *     since?: string,
 *     statuses?: string[],
 *     to?: string,
 *     warehouse_ids?: int[]
 *  }
 * @psalm-type TListWith = array{
 *     analytics_data?: bool,
 *     barcodes?: bool,
 *     financial_data?: bool,
 *     legal_info?: bool
 *  }
 * @psalm-type TListRequest = array{
 *     cursor?: string,
 *     filter?: TListFilter,
 *     limit?: int,
 *     sort_dir?: string,
 *     translit?: bool,
 *     with?: TListWith,
 *  }
 * @psalm-type TUnfulfilledListFilter = array{
 *     cutoff_from?: string,
 *     cutoff_to?: string,
 *     delivering_date_from?: string,
 *     delivering_date_to?: string,
 *     delivery_method_ids?: int[],
 *     last_changed_status_date?: TListStatusDate,
 *     provider_ids?: int[],
 *     statuses?: string[],
 *     warehouse_ids?: int[]
 *  }
 * @psalm-type TUnfulfilledListRequest = array{
 *     cursor?: string,
 *     filter?: TUnfulfilledListFilter,
 *     limit?: int,
 *     sort_dir?: string,
 *     translit?: bool,
 *     with?: TListWith,
 *  }
 */
class FbsService extends AbstractService
{
    private $path = '/v4/posting/fbs';

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_ShipFbsPostingV4
     *
     * @param $packages list<THasProducts>
     * @param $with     TShipWith
     *
     * @return TShipResponse
     */
    public function ship(array $packages, string $postingNumber, array $with = []): array
    {
        \assert([] !== $packages);
        \assert(!$this->isAssoc($packages));
        foreach ($packages as &$package) {
            \assert(\array_key_exists('products', $package));
            $package = ArrayHelper::pick($package, ['products']);
            \assert(!$this->isAssoc($package['products']));
            \assert(\count($package['products']) > 0);
        }

        $body = [
            'packages'       => $packages,
            'posting_number' => $postingNumber,
            'with'           => WithResolver::resolve($with, 4, PostingScheme::FBS, __FUNCTION__),
        ];

        return $this->request('POST', "$this->path/ship", $body);
    }

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_ShipFbsPostingPackage
     *
     * @return string PostingNumber
     */
    public function shipPackage(string $postingNumber, array $products): string
    {
        $body = [
            'posting_number' => $postingNumber,
            'products'       => $products,
        ];

        return $this->request('POST', "$this->path/ship/package", $body);
    }

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingFbsList
     *
     * @param TListRequest $requestData
     */
    public function list(array $requestData = []): array
    {
        $default = [
            'with'     => WithResolver::getDefaults(4, PostingScheme::FBS, 'list'),
            'filter'   => [],
            'sort_dir' => SortDirection::ASC,
            'cursor'   => '',
            'translit' => true,
            'limit'    => 10,
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        $requestData['filter'] = ArrayHelper::pick($requestData['filter'], [
            'delivery_method_ids',
            'integration_type_flow',
            'is_blr_traceable',
            'last_changed_status_date',
            'order_id',
            'order_numbers',
            'provider_ids',
            'statuses',
            'since',
            'to',
            'warehouse_ids',
        ]);

        // default filter parameters
        $requestData['filter'] = array_merge(
            [
                'since' => (new \DateTime('now - 7 days'))->format(DATE_W3C),
                'to'    => (new \DateTime('now'))->format(DATE_W3C),
            ],
            $requestData['filter']
        );

        return $this->request('POST', "{$this->path}/list", $requestData);
    }

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingFbsUnfulfilledList
     *
     * @param TUnfulfilledListRequest $requestData
     */
    public function unfulfilledList(array $requestData = []): array
    {
        $default = [
            'with'        => WithResolver::getDefaults(4, PostingScheme::FBS, 'list'),
            'filter'      => [],
            'sort_dir'    => SortDirection::ASC,
            'cursor'      => '',
            'translit'    => true,
            'limit'       => 10,
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        $requestData['filter'] = ArrayHelper::pick($requestData['filter'], [
            'cutoff_from',
            'cutoff_to',
            'delivering_date_from',
            'delivering_date_to',
            'delivery_method_ids',
            'last_changed_status_date',
            'provider_ids',
            'statuses',
            'warehouse_ids',
        ]);

        if (
            (empty($requestData['filter']['cutoff_from']) || empty($requestData['filter']['cutoff_to']))
            && (empty($requestData['filter']['delivering_date_from']) || empty($requestData['filter']['delivering_date_to']))
        ) {
            throw new \LogicException('Not defined mandatory filter date ranges `cutoff` or `delivering_date`');
        }

        return $this->request('POST', "{$this->path}/unfulfilled/list", $requestData);
    }
}

<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V3\Posting;

use Gam6itko\OzonSeller\Enum\PostingScheme;
use Gam6itko\OzonSeller\Enum\SortDirection;
use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\Service\GetOrderInterface;
use Gam6itko\OzonSeller\Service\HasOrdersInterface;
use Gam6itko\OzonSeller\Service\HasUnfulfilledOrdersInterface;
use Gam6itko\OzonSeller\Utils\ArrayHelper;
use Gam6itko\OzonSeller\Utils\WithResolver;

/**
 * Types are derived from var/swagger.json. For nested posting structures only the
 * top-level keys are listed, deeper levels are left as bare `array`.
 *
 * @psalm-type TWith = array{
 *     analytics_data?: bool,
 *     barcodes?: bool,
 *     financial_data?: bool
 * }
 * @psalm-type TStatusDate = array{
 *     from?: string,
 *     to?: string
 * }
 * @psalm-type TListFilter = array{
 *     delivery_method_id?: list<int>,
 *     integration_type_flow?: string,
 *     is_blr_traceable?: bool,
 *     is_quantum?: bool,
 *     last_changed_status_date?: TStatusDate,
 *     order_id?: int,
 *     provider_id?: list<int>,
 *     status?: string,
 *     since?: string,
 *     to?: string,
 *     warehouse_id?: list<string>
 * }
 * @psalm-type TListRequest = array{
 *     filter?: TListFilter,
 *     dir?: string,
 *     offset?: int,
 *     limit?: int,
 *     with?: TWith
 * }
 * @psalm-type TUnfulfilledListFilter = array{
 *     cutoff_from?: string,
 *     cutoff_to?: string,
 *     delivering_date_from?: string,
 *     delivering_date_to?: string,
 *     delivery_method_id?: list<int>,
 *     is_quantum?: bool,
 *     last_changed_status_date?: TStatusDate,
 *     provider_id?: list<int>,
 *     status?: string,
 *     warehouse_id?: list<int>
 * }
 * @psalm-type TUnfulfilledListRequest = array{
 *     filter?: TUnfulfilledListFilter,
 *     dir?: string,
 *     offset?: int,
 *     limit?: int,
 *     with?: TWith
 * }
 * @psalm-type TPostingProduct = array{
 *     sku?: int,
 *     name?: string,
 *     offer_id?: string,
 *     price?: string,
 *     currency_code?: string,
 *     quantity?: int,
 *     mandatory_mark?: list<array>,
 *     dimensions?: array
 * }
 * @psalm-type TPosting = array{
 *     posting_number?: string,
 *     order_id?: int,
 *     order_number?: string,
 *     parent_posting_number?: string,
 *     status?: string,
 *     substatus?: string,
 *     previous_substatus?: string,
 *     provider_status?: string,
 *     tpl_integration_type?: string,
 *     integration_type_flow?: string,
 *     tracking_number?: string,
 *     in_process_at?: string,
 *     shipment_date?: string,
 *     shipment_date_without_delay?: string,
 *     delivering_date?: string,
 *     fact_delivery_date?: string,
 *     pickup_code_verified_at?: string,
 *     delivery_price?: string,
 *     multi_box_qty?: int,
 *     is_express?: bool,
 *     is_multibox?: bool,
 *     require_blr_traceable_attrs?: bool,
 *     container_sort_type?: string,
 *     products?: list<TPostingProduct>,
 *     addressee?: array,
 *     customer?: array,
 *     courier?: array,
 *     container?: array,
 *     analytics_data?: array,
 *     financial_data?: array,
 *     barcodes?: array,
 *     cancellation?: array,
 *     delivery_method?: array,
 *     legal_info?: array,
 *     external_order?: array,
 *     product_exemplars?: array,
 *     related_postings?: array,
 *     related_weight_postings?: list<array>,
 *     requirements?: array,
 *     sorting_center?: array,
 *     tariffication?: array,
 *     tariffication_steps?: list<array>,
 *     prr_option?: array,
 *     optional?: array,
 *     additional_data?: list<array{key?: string, value?: string}>,
 *     available_actions?: mixed
 * }
 * @psalm-type TListResponse = array{
 *     has_next?: bool,
 *     postings?: list<TPosting>
 * }
 * @psalm-type TUnfulfilledListResponse = array{
 *     count?: int,
 *     postings?: list<TPosting>
 * }
 */
class FbsService extends AbstractService implements HasOrdersInterface, HasUnfulfilledOrdersInterface, GetOrderInterface
{
    private $path = '/v3/posting/fbs';

    /**
     * @param TListRequest|array<array-key, mixed> $requestData
     *
     * @return TListResponse
     *@deprecated will be removed 01.06.2026 - use V4\Posting\FbsService::list
     * @see       https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetFbsPostingList
     *
     */
    public function list(array $requestData = []): array
    {
        $default = [
            'with'   => WithResolver::getDefaults(3, PostingScheme::FBS),
            'filter' => [],
            'dir'    => SortDirection::ASC,
            'offset' => 0,
            'limit'  => 10,
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        $requestData['filter'] = ArrayHelper::pick($requestData['filter'], [
            'delivery_method_id',
            'integration_type_flow',
            'is_blr_traceable',
            'is_quantum',
            'last_changed_status_date',
            'order_id',
            'provider_id',
            'status',
            'since',
            'to',
            'warehouse_id',
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
     * @param TUnfulfilledListRequest|array<array-key, mixed> $requestData
     *
     * @return TUnfulfilledListResponse
     * @deprecated will be removed 01.06.2026 - use V4\Posting\FbsService::unfulfilledList
     *
     */
    public function unfulfilledList(array $requestData = []): array
    {
        $default = [
            'with'   => WithResolver::getDefaults(3, PostingScheme::FBS),
            'filter' => [],
            'dir'    => SortDirection::ASC,
            'offset' => 0,
            'limit'  => 10,
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
            'delivery_method_id',
            'is_quantum',
            'last_changed_status_date',
            'provider_id',
            'status',
            'warehouse_id',
        ]);

        // https://github.com/gam6itko/ozon-seller/issues/48
        if (
            (empty($requestData['filter']['cutoff_from']) && empty($requestData['filter']['cutoff_to']))
            && (empty($requestData['filter']['delivering_date_from']) && empty($requestData['filter']['delivering_date_to']))
        ) {
            throw new \LogicException('Not defined mandatory filter date ranges `cutoff` or `delivering_date`');
        }

        return $this->request('POST', "{$this->path}/unfulfilled/list", $requestData);
    }

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetFbsPosting
     *
     * @param TWith|array{with?: TWith}|array<array-key, mixed> $options
     *
     * @return TPosting
     */
    public function get(string $postingNumber, array $options = []): array
    {
        return $this->request('POST', "{$this->path}/get", [
            'posting_number' => $postingNumber,
            'with'           => WithResolver::resolve($options, 3, PostingScheme::FBS),
        ]);
    }

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_ShipFbsPostingV3
     *
     * @return array list of postings IDs
     */
    public function ship(array $packages, string $postingNumber, array $options = []): array
    {
        foreach ($packages as &$package) {
            $package = ArrayHelper::pick($package, ['products']);
        }

        $body = [
            'packages'       => $packages,
            'posting_number' => $postingNumber,
            'with'           => WithResolver::resolve($options, 3, PostingScheme::FBS, __FUNCTION__),
        ];

        return $this->request('POST', "$this->path/ship", $body);
    }

    /**
     * Sets the number of boxes a multi-box posting is split into.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_SetMultiBoxQtyV3
     *
     * @return array{result?: bool}
     */
    public function multiBoxQtySet(string $postingNumber, int $multiBoxQty): array
    {
        return $this->request('POST', '/v3/posting/multiboxqty/set', [
            'posting_number' => $postingNumber,
            'multi_box_qty'  => $multiBoxQty,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V3\Posting;

use Gam6itko\OzonSeller\Enum\PostingScheme;
use Gam6itko\OzonSeller\Enum\SortDirection;
use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\Service\HasOrdersInterface;
use Gam6itko\OzonSeller\Utils\ArrayHelper;
use Gam6itko\OzonSeller\Utils\WithResolver;

/**
 * Типы описаны по var/swagger.json.
 *
 * @psalm-type TListFilter = array{
 *     order_numbers?: list<string>,
 *     posting_numbers?: list<string>,
 *     since?: string|\DateTimeInterface,
 *     to?: string|\DateTimeInterface,
 *     statuses?: list<string>
 * }
 * @psalm-type TListWith = array{
 *     analytics_data?: bool,
 *     financial_data?: bool,
 *     legal_info?: bool
 * }
 * @psalm-type TListRequest = array{
 *     filter?: TListFilter,
 *     cursor?: string,
 *     limit?: int,
 *     sort_dir?: string,
 *     translit?: bool,
 *     with?: TListWith
 * }
 * @psalm-type TPostingProduct = array{
 *     sku?: int,
 *     name?: string,
 *     offer_id?: string,
 *     price?: string,
 *     quantity?: int,
 *     digital_codes?: list<string>,
 *     is_marketplace_buyout?: bool
 * }
 * @psalm-type TPosting = array{
 *     posting_number?: string,
 *     order_id?: int,
 *     order_number?: string,
 *     status?: string,
 *     substatus?: string,
 *     created_at?: string,
 *     in_process_at?: string,
 *     cancel_reason_id?: int,
 *     products?: list<TPostingProduct>,
 *     analytics_data?: array{
 *         city?: string,
 *         client_delivery_date_begin?: string,
 *         client_delivery_date_end?: string,
 *         delivery_type?: string,
 *         is_legal?: bool,
 *         is_premium?: bool,
 *         payment_type_group_name?: string,
 *         warehouse_id?: int,
 *         warehouse_name?: string
 *     },
 *     cancellation?: array{
 *         cancel_reason?: string,
 *         cancellation_initiator?: string,
 *         cancellation_type?: string
 *     },
 *     financial_data?: array{cluster_from?: string, cluster_to?: string, products?: list<array>},
 *     legal_info?: array{company_name?: string, inn?: string, kpp?: string},
 *     external_order?: array{is_external?: bool, platform_name?: string},
 *     additional_data?: list<array{key?: string, value?: string}>
 * }
 * @psalm-type TListResponse = array{
 *     cursor?: string,
 *     has_next?: bool,
 *     postings?: list<TPosting>
 * }
 */
class FboService extends AbstractService implements HasOrdersInterface
{
    private $path = '/v3/posting/fbo';

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_GetFboPostingList
     *
     * @param TListRequest|array<array-key, mixed> $requestData
     *
     * @return TListResponse
     */
    public function list(array $requestData = []): array
    {
        $default = [
            'filter'   => [],
            'cursor'   => '',
            'limit'    => 10,
            'sort_dir' => SortDirection::ASC,
            'translit' => true,
            'with'     => [],
        ];

        $requestData = array_merge(
            $default,
            ArrayHelper::pick($requestData, array_keys($default))
        );

        $requestData['with'] = WithResolver::resolve($requestData['with'], 3, PostingScheme::FBO);

        $filter = ArrayHelper::pick($requestData['filter'], [
            'order_numbers',
            'posting_numbers',
            'since',
            'to',
            'statuses',
        ]);

        foreach (['since', 'to'] as $key) {
            if (isset($filter[$key]) && $filter[$key] instanceof \DateTimeInterface) {
                $filter[$key] = $filter[$key]->format(DATE_RFC3339);
            }
        }

        // default filter parameters
        $requestData['filter'] = array_merge(
            [
                'since' => (new \DateTime('now - 7 days'))->format(DATE_RFC3339),
                'to'    => (new \DateTime('now'))->format(DATE_RFC3339),
            ],
            $filter
        );

        return $this->request('POST', "{$this->path}/list", $requestData);
    }
}

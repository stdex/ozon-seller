<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Assembly lists of FBS postings.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TFbsFilter = array{
 *     cutoff_from: string,
 *     cutoff_to: string,
 *     delivery_method_id?: int
 * }
 * @psalm-type TCarriageFilter = array{
 *     carriage_id: int,
 *     cutoff_from?: string,
 *     cutoff_to?: string,
 *     delivery_method_id?: int
 * }
 */
class AssemblyService extends AbstractService
{
    private $path = '/v1/assembly';

    /**
     * Postings to assemble.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/AssemblyAPI_AssemblyFbsPostingList
     *
     * @param array{filter: TFbsFilter, limit?: int, cursor?: string, sort_dir?: 'ASC'|'DESC'} $requestData
     *
     * @return array{postings?: list<array>, cursor?: string, cutoff?: string}
     */
    public function fbsPostingList(array $requestData): array
    {
        $requestData = array_merge(
            ['limit' => 100, 'sort_dir' => 'ASC'],
            ArrayHelper::pick($requestData, ['filter', 'limit', 'cursor', 'sort_dir'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = $this->pickFbsFilter($requestData['filter']);
        }

        $requestData = TypeCaster::castArr($requestData, [
            'limit'    => 'int',
            'cursor'   => 'str',
            'sort_dir' => 'str',
        ]);

        return $this->request('POST', "{$this->path}/fbs/posting/list", $requestData);
    }

    /**
     * Products to assemble.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/AssemblyAPI_AssemblyFbsProductList
     *
     * @param array{filter: TFbsFilter, limit?: int, offset?: int, sort_dir?: 'ASC'|'DESC'} $requestData
     *
     * @return array{products?: list<array>, products_count?: int, has_next?: bool}
     */
    public function fbsProductList(array $requestData): array
    {
        $requestData = array_merge(
            ['limit' => 100, 'offset' => 0, 'sort_dir' => 'ASC'],
            ArrayHelper::pick($requestData, ['filter', 'limit', 'offset', 'sort_dir'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = $this->pickFbsFilter($requestData['filter']);
        }

        $requestData = TypeCaster::castArr($requestData, [
            'limit'    => 'int',
            'offset'   => 'int',
            'sort_dir' => 'str',
        ]);

        return $this->request('POST', "{$this->path}/fbs/product/list", $requestData);
    }

    /**
     * Postings of a carriage to assemble.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/AssemblyAPI_AssemblyCarriagePostingList
     *
     * @param array{filter: TCarriageFilter, limit?: int, cursor?: string} $requestData
     *
     * @return array{postings?: list<array>, cursor?: string, can_print_mass_label?: bool}
     */
    public function carriagePostingList(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/carriage/posting/list",
            $this->pickCarriageRequest($requestData)
        );
    }

    /**
     * Products of a carriage to assemble.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/AssemblyAPI_AssemblyCarriageProductList
     *
     * @param array{filter: TCarriageFilter, limit?: int, cursor?: string} $requestData
     *
     * @return array{products?: list<array>, cursor?: string}
     */
    public function carriageProductList(array $requestData): array
    {
        return $this->request(
            'POST',
            "{$this->path}/carriage/product/list",
            $this->pickCarriageRequest($requestData)
        );
    }

    /**
     * @param array<array-key, mixed> $filter
     *
     * @return array<string, mixed>
     */
    private function pickFbsFilter(array $filter): array
    {
        return TypeCaster::castArr(
            ArrayHelper::pick($filter, ['cutoff_from', 'cutoff_to', 'delivery_method_id']),
            ['cutoff_from' => 'str', 'cutoff_to' => 'str', 'delivery_method_id' => 'int']
        );
    }

    /**
     * @param array<array-key, mixed> $requestData
     *
     * @return array<string, mixed>
     */
    private function pickCarriageRequest(array $requestData): array
    {
        $requestData = array_merge(
            ['limit' => 100],
            ArrayHelper::pick($requestData, ['filter', 'limit', 'cursor'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], [
                    'carriage_id',
                    'cutoff_from',
                    'cutoff_to',
                    'delivery_method_id',
                ]),
                [
                    'carriage_id'        => 'int',
                    'cutoff_from'        => 'str',
                    'cutoff_to'          => 'str',
                    'delivery_method_id' => 'int',
                ]
            );
        }

        return TypeCaster::castArr($requestData, ['limit' => 'int', 'cursor' => 'str']);
    }
}

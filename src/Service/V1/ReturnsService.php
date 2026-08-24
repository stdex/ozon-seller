<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Returns: rFBS actions, drop-off points and utilization settings.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TUtilizationPrice = array{enabled: bool, value?: int}
 * @psalm-type TActionSetRequest = array{
 *     return_id: int,
 *     id?: int,
 *     comment?: string,
 *     compensation_amount?: float,
 *     rejection_reason_id?: int,
 *     return_for_back_way?: float
 * }
 */
class ReturnsService extends AbstractService
{
    private $path = '/v1/returns';

    /**
     * Applies an action to an rFBS return.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ReturnsAPI_ReturnsRfbsActionSet
     *
     * @param TActionSetRequest $requestData
     */
    public function rfbsActionSet(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'return_id',
                'id',
                'comment',
                'compensation_amount',
                'rejection_reason_id',
                'return_for_back_way',
            ]),
            [
                'return_id'           => 'int',
                'id'                  => 'int',
                'comment'             => 'str',
                'compensation_amount' => 'float',
                'rejection_reason_id' => 'int',
                'return_for_back_way' => 'float',
            ]
        );

        return $this->request('POST', "{$this->path}/rfbs/action/set", $requestData);
    }

    /**
     * Drop-off points that accept FBS returns.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ReturnsAPI_ReturnsCompanyFbsInfo
     *
     * @return array{drop_off_points?: list<array>, has_next?: bool}
     */
    public function companyFbsInfo(int $limit = 100, ?int $lastId = null, ?int $placeId = null): array
    {
        $pagination = ['limit' => $limit];

        if (null !== $lastId) {
            $pagination['last_id'] = $lastId;
        }

        $requestData = ['pagination' => $pagination];

        if (null !== $placeId) {
            $requestData['filter'] = ['place_id' => $placeId];
        }

        return $this->request('POST', "{$this->path}/company/fbs/info", $requestData);
    }

    /**
     * Utilization settings.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ReturnsAPI_ReturnsSettingsUtilizationInfo
     *
     * @return array{min_price?: array, utilization_settings?: array}
     */
    public function settingsUtilizationInfo(): array
    {
        return $this->request('POST', "{$this->path}/settings/utilization/info", '{}');
    }

    /**
     * Changes the utilization settings.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ReturnsAPI_ReturnsSettingsUtilizationUpdate
     *
     * @param TUtilizationPrice $utilizationPrice
     * @param TUtilizationPrice $utilizationPriceDefects
     */
    public function settingsUtilizationUpdate(array $utilizationPrice, array $utilizationPriceDefects): array
    {
        return $this->request('POST', "{$this->path}/settings/utilization/update", [
            'utilization_price'         => $this->pickUtilizationPrice($utilizationPrice),
            'utilization_price_defects' => $this->pickUtilizationPrice($utilizationPriceDefects),
        ]);
    }

    /**
     * History of the utilization settings changes.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ReturnsAPI_ReturnsSettingsUtilizationHistory
     *
     * @return array{history?: list<array>}
     */
    public function settingsUtilizationHistory(): array
    {
        return $this->request('POST', "{$this->path}/settings/utilization/history", '{}');
    }

    /**
     * @param array<array-key, mixed> $price
     *
     * @return array<string, mixed>
     */
    private function pickUtilizationPrice(array $price): array
    {
        return TypeCaster::castArr(
            ArrayHelper::pick($price, ['enabled', 'value']),
            ['enabled' => 'bool', 'value' => 'int']
        );
    }
}

<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Delivery of orders created by the seller.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class DeliveryService extends AbstractService
{
    private $path = '/v1/delivery';

    /**
     * Whether an order can be created for the customer.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DeliveryAPI_DeliveryCheck
     *
     * @return array{is_possible?: bool}
     */
    public function check(string $clientPhone): array
    {
        return $this->request('POST', "{$this->path}/check", ['client_phone' => $clientPhone]);
    }

    /**
     * Pick-up point clusters of a map viewport.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DeliveryAPI_DeliveryMap
     *
     * @param array{viewport?: array{left_bottom?: array, right_top?: array}, zoom?: int} $requestData
     *
     * @return array{clusters?: list<array>}
     */
    public function map(array $requestData = []): array
    {
        $requestData = ArrayHelper::pick($requestData, ['viewport', 'zoom']);

        if (isset($requestData['viewport'])) {
            $requestData['viewport'] = ArrayHelper::pick($requestData['viewport'], ['left_bottom', 'right_top']);
        }

        return $this->request('POST', "{$this->path}/map", $requestData ?: '{}');
    }

    /**
     * Pick-up points info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DeliveryAPI_DeliveryPointInfo
     *
     * @param list<int|string> $mapPointIds
     *
     * @return array{points?: list<array>}
     */
    public function pointInfo(array $mapPointIds): array
    {
        return $this->request('POST', "{$this->path}/point/info", [
            'map_point_ids' => array_map('strval', $mapPointIds),
        ]);
    }

    /**
     * Pick-up points list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DeliveryAPI_DeliveryPointList
     *
     * @return array{points?: list<array>}
     */
    public function pointList(): array
    {
        return $this->request('POST', "{$this->path}/point/list", '{}');
    }
}

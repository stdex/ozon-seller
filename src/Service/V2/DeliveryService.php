<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Delivery of orders created by the seller.
 *
 * Types are derived from var/swagger.json. Nested `delivery_type` and `items`
 * are passed as is.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class DeliveryService extends AbstractService
{
    /**
     * Calculates the delivery options of an order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/DeliveryAPI_DeliveryCheckoutV2
     *
     * @param array{buyer_phone?: string, delivery_schema?: 'MIX'|'FBO'|'FBS', delivery_type?: array, items?: list<array>} $requestData
     *
     * @return array{splits?: list<array>}
     */
    public function checkout(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, ['buyer_phone', 'delivery_schema', 'delivery_type', 'items']),
            ['buyer_phone' => 'str', 'delivery_schema' => 'str']
        );

        return $this->request('POST', '/v2/delivery/checkout', $requestData);
    }
}

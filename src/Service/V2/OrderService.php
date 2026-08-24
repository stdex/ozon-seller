<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Orders created by the seller.
 *
 * Types are derived from var/swagger.json. Nested `delivery` and `splits` are
 * passed as is.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TBuyer = array{first_name: string, last_name: string, phone: string, middle_name?: string}
 * @psalm-type TRecipient = array{
 *     recipient_first_name: string,
 *     recipient_last_name: string,
 *     recipient_phone: string,
 *     recipient_middle_name?: string
 * }
 */
class OrderService extends AbstractService
{
    /**
     * Creates an order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/OrderAPI_OrderCreateV2
     *
     * @param array{buyer: TBuyer, recipient: TRecipient, delivery: array, delivery_schema: 'MIX'|'FBO'|'FBS', splits: list<array>} $requestData
     *
     * @return array{order_number?: string, postings?: list<string>}
     */
    public function create(array $requestData): array
    {
        $requestData = ArrayHelper::pick($requestData, [
            'buyer',
            'recipient',
            'delivery',
            'delivery_schema',
            'splits',
        ]);

        if (isset($requestData['buyer'])) {
            $requestData['buyer'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['buyer'], ['first_name', 'last_name', 'middle_name', 'phone']),
                ['first_name' => 'str', 'last_name' => 'str', 'middle_name' => 'str', 'phone' => 'str']
            );
        }

        if (isset($requestData['recipient'])) {
            $requestData['recipient'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['recipient'], [
                    'recipient_first_name',
                    'recipient_last_name',
                    'recipient_middle_name',
                    'recipient_phone',
                ]),
                [
                    'recipient_first_name'  => 'str',
                    'recipient_last_name'   => 'str',
                    'recipient_middle_name' => 'str',
                    'recipient_phone'       => 'str',
                ]
            );
        }

        return $this->request('POST', '/v2/order/create', TypeCaster::castArr($requestData, [
            'delivery_schema' => 'str',
        ]));
    }
}

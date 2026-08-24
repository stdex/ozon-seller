<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Orders created by the seller.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class OrderService extends AbstractService
{
    private $path = '/v1/order';

    /**
     * Cancels an order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/OrderAPI_OrderCancel
     *
     * @return array{message?: string}
     */
    public function cancel(string $orderNumber, int $reasonId, string $reasonMessage = ''): array
    {
        $requestData = [
            'order_number' => $orderNumber,
            'reason_id'    => $reasonId,
        ];

        if ('' !== $reasonMessage) {
            $requestData['reason_message'] = $reasonMessage;
        }

        return $this->request('POST', "{$this->path}/cancel", $requestData);
    }

    /**
     * Whether an order can be cancelled.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/OrderAPI_OrderCancelCheck
     *
     * @return array{cancellable?: bool, order_number?: string, postings?: list<array>, posting_groups?: list<array>}
     */
    public function cancelCheck(string $orderNumber): array
    {
        return $this->request('POST', "{$this->path}/cancel/check", ['order_number' => $orderNumber]);
    }

    /**
     * Status of an order cancellation.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/OrderAPI_OrderCancelStatus
     *
     * @return array{state?: string, order_number?: string, posting_number?: list<string>}
     */
    public function cancelStatus(string $orderNumber): array
    {
        return $this->request('POST', "{$this->path}/cancel/status", ['order_number' => $orderNumber]);
    }
}

<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Cancellation reasons.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class CancelReasonService extends AbstractService
{
    private $path = '/v1/cancel-reason';

    /**
     * All cancellation reasons.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CancelReasonAPI_CancelReasonList
     *
     * @return array{reasons?: list<array>}
     */
    public function list(): array
    {
        return $this->request('POST', "{$this->path}/list", '{}');
    }

    /**
     * Cancellation reasons available for an order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CancelReasonAPI_CancelReasonListByOrder
     *
     * @return array{reasons?: list<array>}
     */
    public function listByOrder(string $orderNumber): array
    {
        return $this->request('POST', "{$this->path}/list-by-order", ['order_number' => $orderNumber]);
    }

    /**
     * Cancellation reasons available for a posting.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CancelReasonAPI_CancelReasonListByPosting
     *
     * @return array{reasons?: list<array>}
     */
    public function listByPosting(string $postingNumber): array
    {
        return $this->request('POST', "{$this->path}/list-by-posting", ['posting_number' => $postingNumber]);
    }
}

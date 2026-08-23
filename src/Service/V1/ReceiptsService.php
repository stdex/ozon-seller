<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Чеки.
 *
 * Типы описаны по var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TSellerListRequest = array{
 *     page?: int,
 *     page_size?: int,
 *     posting_numbers?: list<string>
 * }
 * @psalm-type TReceipt = array{
 *     receipt_id?: string,
 *     receipt_number?: string,
 *     parent_receipt_id?: string,
 *     order_id?: int,
 *     posting_numbers?: list<array>,
 *     type?: 'UNSPECIFIED'|'INCOMING'|'REFUND',
 *     operation_type?: 'UNSPECIFIED'|'COMMODITY',
 *     created_at?: string,
 *     updated_at?: string
 * }
 * @psalm-type TSellerListResponse = array{
 *     receipts?: list<TReceipt>,
 *     has_next?: bool
 * }
 *
 * Метод /v1/receipts/upload не реализован: он принимает multipart/form-data,
 * а транспорт библиотеки отправляет только JSON.
 */
class ReceiptsService extends AbstractService
{
    /**
     * Список чеков продавца.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/ReceiptAPI_SellerList
     *
     * @param TSellerListRequest $requestData
     *
     * @return TSellerListResponse
     */
    public function sellerList(array $requestData = []): array
    {
        $requestData = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($requestData, ['page', 'page_size', 'posting_numbers'])
        );

        $requestData = TypeCaster::castArr($requestData, [
            'page'            => 'int',
            'page_size'       => 'int',
            'posting_numbers' => 'arrOfStr',
        ]);

        return $this->request('POST', '/v1/receipts/seller/list', $requestData);
    }

    /**
     * Получить чек в формате PDF, закодированный в base64.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/GetReceipt
     *
     * @return array{content?: string}
     */
    public function get(string $receiptId): array
    {
        return $this->request('POST', '/v1/receipts/get', ['receipt_id' => $receiptId]);
    }
}

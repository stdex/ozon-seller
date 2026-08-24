<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * @psalm-type TTimeRange = array{time_from: string, time_to: string}
 *
 * @psalm-type TReturnListRequestFilter = array{
 *      logistic_return_date?: TTimeRange,
 *      storage_tariffication_start_date?: TTimeRange,
 *      visual_status_change_moment?: TTimeRange,
 *      order_id?: int,
 *      posting_numbers?: list<string>,
 *      product_name?: string,
 *      offer_id?: string,
 *      visual_status_name?: string,
 *      warehouse_id?: int,
 *      barcode?: string,
 *      return_schema?: string
 * }
 *
 * @psalm-type TReturnListRequestLimit = int
 *
 * @psalm-type TReturnListRequestLastId = int
 *
 * @psalm-type TReturnListRequestResponse = array{
 *       returns: array<array>,
 *       has_next: bool
 * }
 */
class ReturnService extends AbstractService
{
    private $path = '/v1/returns';

    /**
     * @see https://docs.ozon.ru/api/seller/#operation/returnsList
     *
     * @param TReturnListRequestFilter $filter
     * @param TReturnListRequestLastId $lastId
     * @param TReturnListRequestLimit $limit
     *
     * @return TReturnListRequestResponse
     */
    public function list(array $filter, int $lastId = 0, int $limit = 100): array
    {
        assert($limit > 0 && $limit <= 500);

        $body = [
            'filter' => ArrayHelper::pick($filter, [
                'logistic_return_date', 'storage_tariffication_start_date', 'visual_status_change_moment',
                'order_id', 'posting_numbers', 'product_name', 'offer_id', 'visual_status_name', 'warehouse_id',
                'barcode', 'return_schema', 'compensation_status_id',
            ]),
            'last_id' => $lastId,
            'limit' => $limit,
        ];

        return $this->request('POST', "{$this->path}/list", $body);
    }

    /**
     * Whether the return giveout is available to the seller.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/GiveoutAPI_GiveoutIsEnabled
     *
     * @return array{enabled?: bool}
     */
    public function giveoutIsEnabled(): array
    {
        return $this->request('POST', '/v1/return/giveout/is-enabled', '{}');
    }

    /**
     * Return giveouts list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/GiveoutAPI_GiveoutList
     *
     * @return array{giveouts?: list<array>}
     */
    public function giveoutList(int $limit = 100, ?int $lastId = null): array
    {
        $requestData = ['limit' => $limit];

        if (null !== $lastId) {
            $requestData['last_id'] = $lastId;
        }

        return $this->request('POST', '/v1/return/giveout/list', $requestData);
    }

    /**
     * Return giveout info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/GiveoutAPI_GiveoutInfo
     *
     * @return array{
     *     giveout_id?: int,
     *     giveout_status?: string,
     *     warehouse_name?: string,
     *     warehouse_address?: string,
     *     articles?: list<array>
     * }
     */
    public function giveoutInfo(int $giveoutId): array
    {
        return $this->request('POST', '/v1/return/giveout/info', ['giveout_id' => $giveoutId]);
    }

    /**
     * Barcode value of the return giveout.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/GiveoutAPI_GiveoutBarcode
     *
     * @return array{barcode?: string}
     */
    public function giveoutBarcode(): array
    {
        return $this->request('POST', '/v1/return/giveout/barcode', '{}');
    }

    /**
     * Generates a new barcode of the return giveout.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/GiveoutAPI_GiveoutBarcodeReset
     *
     * @return array{barcode?: string}
     */
    public function giveoutBarcodeReset(): array
    {
        return $this->request('POST', '/v1/return/giveout/barcode-reset', '{}');
    }

    /**
     * Return giveout barcode as a PDF.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/GiveoutAPI_GiveoutGetPDF
     *
     * @return array{file_name?: string, file_content?: string, content_type?: string}
     */
    public function giveoutGetPdf(): array
    {
        return $this->request('POST', '/v1/return/giveout/get-pdf', '{}');
    }

    /**
     * Return giveout barcode as a PNG.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/GiveoutAPI_GiveoutGetPNG
     *
     * @return array{file_name?: string, file_content?: string, content_type?: string}
     */
    public function giveoutGetPng(): array
    {
        return $this->request('POST', '/v1/return/giveout/get-png', '{}');
    }

    /**
     * Creates arrival passes for a return giveout.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PassAPI_ReturnPassCreate
     *
     * @param list<array{
     *     warehouse_id: int,
     *     dropoff_point_id: int,
     *     arrival_time: string,
     *     driver_name: string,
     *     driver_phone: string,
     *     vehicle_license_plate: string,
     *     vehicle_model: string
     * }> $arrivalPasses
     *
     * @return array{arrival_pass_ids?: list<string>}
     */
    public function passCreate(array $arrivalPasses): array
    {
        $arrivalPasses = array_map(static function (array $pass): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($pass, [
                    'warehouse_id',
                    'dropoff_point_id',
                    'arrival_time',
                    'driver_name',
                    'driver_phone',
                    'vehicle_license_plate',
                    'vehicle_model',
                ]),
                [
                    'warehouse_id'          => 'int',
                    'dropoff_point_id'      => 'int',
                    'arrival_time'          => 'str',
                    'driver_name'           => 'str',
                    'driver_phone'          => 'str',
                    'vehicle_license_plate' => 'str',
                    'vehicle_model'         => 'str',
                ]
            );
        }, $arrivalPasses);

        return $this->request('POST', '/v1/return/pass/create', ['arrival_passes' => $arrivalPasses]);
    }

    /**
     * Updates arrival passes of a return giveout.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PassAPI_ReturnPassUpdate
     *
     * @param list<array{
     *     arrival_pass_id: int,
     *     arrival_time: string,
     *     driver_name: string,
     *     driver_phone: string,
     *     vehicle_license_plate: string,
     *     vehicle_model: string
     * }> $arrivalPasses
     */
    public function passUpdate(array $arrivalPasses): array
    {
        $arrivalPasses = array_map(static function (array $pass): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($pass, [
                    'arrival_pass_id',
                    'arrival_time',
                    'driver_name',
                    'driver_phone',
                    'vehicle_license_plate',
                    'vehicle_model',
                ]),
                [
                    'arrival_pass_id'       => 'int',
                    'arrival_time'          => 'str',
                    'driver_name'           => 'str',
                    'driver_phone'          => 'str',
                    'vehicle_license_plate' => 'str',
                    'vehicle_model'         => 'str',
                ]
            );
        }, $arrivalPasses);

        return $this->request('POST', '/v1/return/pass/update', ['arrival_passes' => $arrivalPasses]);
    }

    /**
     * Deletes arrival passes of a return giveout.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PassAPI_ReturnPassDelete
     *
     * @param list<int|string> $arrivalPassIds
     */
    public function passDelete(array $arrivalPassIds): array
    {
        return $this->request('POST', '/v1/return/pass/delete', [
            'arrival_pass_ids' => array_map('strval', $arrivalPassIds),
        ]);
    }
}

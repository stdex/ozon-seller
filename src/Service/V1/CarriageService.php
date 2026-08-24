<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * FBS carriages (shipments to the drop-off point) and their containers.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TCarriage = array{
 *     carriage_id?: int,
 *     company_id?: int,
 *     status?: string,
 *     act_type?: string,
 *     first_mile_type?: string,
 *     integration_type?: string,
 *     delivery_method_id?: int,
 *     warehouse_id?: int,
 *     tpl_provider_id?: int,
 *     departure_date?: string,
 *     containers_count?: int,
 *     partial_num?: int,
 *     retry_count?: int,
 *     is_partial?: bool,
 *     is_econom?: bool,
 *     is_waybill_enabled?: bool,
 *     is_container_label_printed?: bool,
 *     has_postings_for_next_carriage?: bool,
 *     all_blr_traceable?: bool,
 *     arrival_pass_ids?: list<string>,
 *     available_actions?: list<string>,
 *     cancel_availability?: array{is_cancel_available?: bool, reason?: string},
 *     created_at?: string,
 *     updated_at?: string
 * }
 * @psalm-type TArrivalPass = array{
 *     driver_name: string,
 *     driver_phone: string,
 *     vehicle_license_plate: string,
 *     vehicle_model: string,
 *     with_returns?: bool
 * }
 * @psalm-type TArrivalPassUpdate = array{
 *     id: int,
 *     driver_name: string,
 *     driver_phone: string,
 *     vehicle_license_plate: string,
 *     vehicle_model: string,
 *     with_returns?: bool
 * }
 * @psalm-type TTaskResult = array{task_id?: int, error_containers?: list<array>, error_postings?: list<array>}
 * @psalm-type TContainerListFilter = array{
 *     created_from: string,
 *     created_to: string,
 *     sort_type: string,
 *     cargo_type?: string,
 *     statuses?: list<string>,
 *     warehouse_id?: int
 * }
 * @psalm-type TContainerListRequest = array{
 *     filter?: TContainerListFilter,
 *     cursor?: string,
 *     limit?: int,
 *     sort_dir?: 'ASC'|'DESC'
 * }
 * @psalm-type TContainer = array{
 *     container_id?: int,
 *     container_number?: int,
 *     parent_container_id?: int,
 *     related_container_ids?: list<string>,
 *     status?: string,
 *     cargo_type?: string,
 *     sort_type?: string,
 *     count_of_postings?: int,
 *     postings?: list<array>,
 *     weight?: float,
 *     warehouse_id?: int,
 *     warehouse_name?: string,
 *     warehouse_date?: string,
 *     available_actions?: list<string>,
 *     created_at?: string
 * }
 * @psalm-type TFile = array{file_name?: string, file_content?: string, content_type?: string}
 */
class CarriageService extends AbstractService
{
    private $path = '/v1/carriage';

    /**
     * Creates a carriage.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriageCreate
     *
     * @param array{delivery_method_id?: int, departure_date?: string, all_blr_traceable?: bool} $requestData
     *
     * @return array{carriage_id?: int}
     */
    public function create(array $requestData = []): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, ['delivery_method_id', 'departure_date', 'all_blr_traceable']),
            [
                'delivery_method_id' => 'int',
                'departure_date'     => 'str',
                'all_blr_traceable'  => 'bool',
            ]
        );

        return $this->request('POST', "{$this->path}/create", $requestData ?: '{}');
    }

    /**
     * Confirms a carriage and closes the acceptance act.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriageApprove
     */
    public function approve(int $carriageId, ?int $containersCount = null): array
    {
        $requestData = ['carriage_id' => $carriageId];

        if (null !== $containersCount) {
            $requestData['containers_count'] = $containersCount;
        }

        return $this->request('POST', "{$this->path}/approve", $requestData);
    }

    /**
     * Replaces the postings of a carriage.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_SetPostings
     *
     * @param list<string> $postingNumbers
     */
    public function setPostings(int $carriageId, array $postingNumbers): array
    {
        return $this->request('POST', "{$this->path}/set-postings", [
            'carriage_id'     => $carriageId,
            'posting_numbers' => array_map('strval', $postingNumbers),
        ]);
    }

    /**
     * Cancels a carriage.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriageCancel
     *
     * @return array{carriage_status?: string, error?: string}
     */
    public function cancel(int $carriageId): array
    {
        return $this->request('POST', "{$this->path}/cancel", ['carriage_id' => $carriageId]);
    }

    /**
     * Carriage info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriageGet
     *
     * @return TCarriage
     */
    public function get(int $carriageId): array
    {
        return $this->request('POST', "{$this->path}/get", ['carriage_id' => $carriageId]);
    }

    /**
     * Delivery methods with their carriages.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriageDeliveryList
     *
     * @return list<array>
     */
    public function deliveryList(?int $deliveryMethodId = null, string $departureDate = ''): array
    {
        $requestData = [];

        if (null !== $deliveryMethodId) {
            $requestData['delivery_method_id'] = $deliveryMethodId;
        }

        if ('' !== $departureDate) {
            $requestData['departure_date'] = $departureDate;
        }

        return $this->request('POST', "{$this->path}/delivery/list", $requestData ?: '{}');
    }

    /**
     * Postings available for a carriage.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_CarriageAvailableList
     *
     * @return list<array>
     */
    public function availableList(int $deliveryMethodId, string $departureDate = ''): array
    {
        $requestData = ['delivery_method_id' => $deliveryMethodId];

        if ('' !== $departureDate) {
            $requestData['departure_date'] = $departureDate;
        }

        return $this->request('POST', '/v1/posting/carriage-available/list', $requestData);
    }

    /**
     * Sets the courier contact of a carriage.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriageCourierContactSet
     *
     * @param array{carriage_id: int, phone: string, wechat_nickname?: string, comment?: string} $requestData
     */
    public function courierContactSet(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, ['carriage_id', 'phone', 'wechat_nickname', 'comment']),
            [
                'carriage_id'      => 'int',
                'phone'            => 'str',
                'wechat_nickname'  => 'str',
                'comment'          => 'str',
            ]
        );

        return $this->request('POST', "{$this->path}/courier-contact/set", $requestData);
    }

    /**
     * Courier contact of a carriage.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriageCourierContactGet
     *
     * @return array{contact?: list<array>}
     */
    public function courierContactGet(int $carriageId): array
    {
        return $this->request('POST', "{$this->path}/courier-contact/get", ['carriage_id' => $carriageId]);
    }

    /**
     * Creates arrival passes for a carriage.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriagePassCreate
     *
     * @param list<TArrivalPass> $arrivalPasses
     *
     * @return array{arrival_pass_ids?: list<string>}
     */
    public function passCreate(int $carriageId, array $arrivalPasses): array
    {
        return $this->request('POST', "{$this->path}/pass/create", [
            'carriage_id'    => $carriageId,
            'arrival_passes' => array_map([$this, 'pickArrivalPass'], $arrivalPasses),
        ]);
    }

    /**
     * Updates arrival passes of a carriage.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriagePassUpdate
     *
     * @param list<TArrivalPassUpdate> $arrivalPasses
     */
    public function passUpdate(int $carriageId, array $arrivalPasses): array
    {
        return $this->request('POST', "{$this->path}/pass/update", [
            'carriage_id'    => $carriageId,
            'arrival_passes' => array_map([$this, 'pickArrivalPass'], $arrivalPasses),
        ]);
    }

    /**
     * Deletes arrival passes of a carriage.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriagePassDelete
     *
     * @param list<int|string> $arrivalPassIds
     */
    public function passDelete(int $carriageId, array $arrivalPassIds): array
    {
        return $this->request('POST', "{$this->path}/pass/delete", [
            'carriage_id'      => $carriageId,
            'arrival_pass_ids' => array_map('strval', $arrivalPassIds),
        ]);
    }

    /**
     * Discrepancy act as a base64-encoded PDF.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriageActDiscrepancyPdf
     *
     * @return array{name?: string, type?: string, content?: string}
     */
    public function actDiscrepancyPdf(int $carriageId): array
    {
        return $this->request('POST', "{$this->path}/act-discrepancy/pdf", ['carriage_id' => $carriageId]);
    }

    /**
     * e-TTN upload status of a carriage.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_CarriageEttnStatus
     *
     * @return array{status?: 'NOT_UPLOADED'|'PROCESSING'|'SUCCESS'|'FAILED', errors?: list<string>}
     */
    public function ettnStatus(int $carriageId): array
    {
        return $this->request('POST', "{$this->path}/ettn/status", ['carriage_id' => $carriageId]);
    }

    /**
     * Creates containers.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerCreate
     *
     * @param array{warehouse_id: int, containers_count: int, cargo_type: string, sort_type: string} $requestData
     *
     * @return array{container_ids?: list<string>}
     */
    public function containerCreate(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, ['warehouse_id', 'containers_count', 'cargo_type', 'sort_type']),
            [
                'warehouse_id'     => 'int',
                'containers_count' => 'int',
                'cargo_type'       => 'str',
                'sort_type'        => 'str',
            ]
        );

        return $this->request('POST', "{$this->path}/container/create", $requestData);
    }

    /**
     * Puts postings into a container.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerFill
     *
     * @param list<string> $postingNumbers
     *
     * @return TTaskResult
     */
    public function containerFill(int $containerId, array $postingNumbers): array
    {
        return $this->request('POST', "{$this->path}/container/fill", [
            'container_id'    => $containerId,
            'posting_numbers' => array_map('strval', $postingNumbers),
        ]);
    }

    /**
     * Confirms containers.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerApprove
     *
     * @param list<int|string> $containerIds
     *
     * @return TTaskResult
     */
    public function containerApprove(array $containerIds): array
    {
        return $this->request('POST', "{$this->path}/container/approve", [
            'container_ids' => array_map('strval', $containerIds),
        ]);
    }

    /**
     * Puts containers into a parent container.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerPlaceInto
     *
     * @param list<int|string> $childContainerIds
     *
     * @return TTaskResult
     */
    public function containerPlaceInto(int $parentContainerId, array $childContainerIds): array
    {
        return $this->request('POST', "{$this->path}/container/place-into", [
            'parent_container_id' => $parentContainerId,
            'child_container_ids' => array_map('strval', $childContainerIds),
        ]);
    }

    /**
     * Removes postings from a container.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerRemovePostings
     *
     * @param list<string> $postingNumbers
     *
     * @return TTaskResult
     */
    public function containerRemovePostings(int $containerId, array $postingNumbers): array
    {
        return $this->request('POST', "{$this->path}/container/remove-postings", [
            'container_id'    => $containerId,
            'posting_numbers' => array_map('strval', $postingNumbers),
        ]);
    }

    /**
     * Removes containers from a parent container.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerRemoveFrom
     *
     * @param list<int|string> $childContainerIds
     *
     * @return TTaskResult
     */
    public function containerRemoveFrom(int $parentContainerId, array $childContainerIds): array
    {
        return $this->request('POST', "{$this->path}/container/remove-from", [
            'parent_container_id' => $parentContainerId,
            'child_container_ids' => array_map('strval', $childContainerIds),
        ]);
    }

    /**
     * Cancels containers.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerCancel
     *
     * @param list<int|string> $containerIds
     *
     * @return TTaskResult
     */
    public function containerCancel(array $containerIds): array
    {
        return $this->request('POST', "{$this->path}/container/cancel", [
            'container_ids' => array_map('strval', $containerIds),
        ]);
    }

    /**
     * Containers list.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerList
     *
     * @param TContainerListRequest $requestData
     *
     * @return array{containers?: list<TContainer>, cursor?: string}
     */
    public function containerList(array $requestData = []): array
    {
        $requestData = array_merge(
            ['limit' => 100, 'sort_dir' => 'ASC'],
            ArrayHelper::pick($requestData, ['filter', 'cursor', 'limit', 'sort_dir'])
        );

        if (isset($requestData['filter'])) {
            $requestData['filter'] = TypeCaster::castArr(
                ArrayHelper::pick($requestData['filter'], [
                    'created_from',
                    'created_to',
                    'sort_type',
                    'cargo_type',
                    'statuses',
                    'warehouse_id',
                ]),
                [
                    'created_from' => 'str',
                    'created_to'   => 'str',
                    'sort_type'    => 'str',
                    'cargo_type'   => 'str',
                    'statuses'     => 'arrOfStr',
                    'warehouse_id' => 'int',
                ]
            );
        }

        $requestData = TypeCaster::castArr($requestData, [
            'cursor'   => 'str',
            'limit'    => 'int',
            'sort_dir' => 'str',
        ]);

        return $this->request('POST', "{$this->path}/container/list", $requestData);
    }

    /**
     * Container info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerGet
     *
     * @return TContainer
     */
    public function containerGet(int $containerId): array
    {
        return $this->request('POST', "{$this->path}/container/get", ['container_id' => $containerId]);
    }

    /**
     * Statuses of containers.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerStatusGet
     *
     * @param list<int|string> $containerIds
     *
     * @return array{containers?: list<array>}
     */
    public function containerStatusGet(array $containerIds): array
    {
        return $this->request('POST', "{$this->path}/container/status/get", [
            'container_ids' => array_map('strval', $containerIds),
        ]);
    }

    /**
     * Status of an asynchronous container task.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerTaskInfo
     *
     * @return array{status?: string, error_message?: string}
     */
    public function containerTaskInfo(int $taskId): array
    {
        return $this->request('POST', "{$this->path}/container/task/info", ['task_id' => $taskId]);
    }

    /**
     * Container documents as a base64-encoded file.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerDocumentGet
     *
     * @param list<int|string> $containerIds
     *
     * @return TFile
     */
    public function containerDocumentGet(array $containerIds): array
    {
        return $this->request('POST', "{$this->path}/container/document/get", [
            'container_ids' => array_map('strval', $containerIds),
        ]);
    }

    /**
     * Container labels as a base64-encoded file.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CarriageAPI_ContainerLabelGet
     *
     * @param list<int|string> $containerIds
     *
     * @return array{content?: TFile, error_containers?: list<array>}
     */
    public function containerLabelGet(array $containerIds): array
    {
        return $this->request('POST', "{$this->path}/container/label/get", [
            'container_ids' => array_map('strval', $containerIds),
        ]);
    }

    /**
     * @param array<array-key, mixed> $pass
     *
     * @return array<string, mixed>
     */
    private function pickArrivalPass(array $pass): array
    {
        return TypeCaster::castArr(
            ArrayHelper::pick($pass, [
                'id',
                'driver_name',
                'driver_phone',
                'vehicle_license_plate',
                'vehicle_model',
                'with_returns',
            ]),
            [
                'id'                    => 'int',
                'driver_name'           => 'str',
                'driver_phone'          => 'str',
                'vehicle_license_plate' => 'str',
                'vehicle_model'         => 'str',
                'with_returns'          => 'bool',
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Cargoes (boxes and pallets) of FBO supplies.
 *
 * Most methods are asynchronous: they return an `operation_id` whose result is
 * fetched by the matching status method. Status responses carry a top-level
 * `result` key next to `status`, so they are returned unwrapped.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TCargo = array{key: string, value: array}
 * @psalm-type TOperationResult = array{operation_id?: string, errors?: array}
 * @psalm-type TTransportCargo = array{type: string, count: int}
 * @psalm-type TTransportCargoBind = array{transport_cargo_id: int, cargo_ids: list<string>}
 */
class CargoesService extends AbstractService
{
    private $path = '/v1/cargoes';

    /**
     * Creates cargoes of a supply.
     *
     * Nested `value` of every cargo is passed as is.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesCreate
     *
     * @param list<TCargo> $cargoes
     *
     * @return TOperationResult
     */
    public function create(int $supplyId, array $cargoes, bool $deleteCurrentVersion = false): array
    {
        $cargoes = array_map(static function (array $cargo): array {
            return ArrayHelper::pick($cargo, ['key', 'value']);
        }, $cargoes);

        return $this->request('POST', "{$this->path}/create", [
            'supply_id'              => $supplyId,
            'cargoes'                => $cargoes,
            'delete_current_version' => $deleteCurrentVersion,
        ]);
    }

    /**
     * Cargoes of supplies.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesGet
     *
     * @param list<int|string> $supplyIds
     *
     * @return array{supply?: list<array>}
     */
    public function get(array $supplyIds): array
    {
        return $this->request('POST', "{$this->path}/get", [
            'supply_ids' => array_map('strval', $supplyIds),
        ]);
    }

    /**
     * Deletes cargoes of a supply.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesDelete
     *
     * @param list<int|string> $cargoIds
     *
     * @return TOperationResult
     */
    public function delete(int $supplyId, array $cargoIds): array
    {
        return $this->request('POST', "{$this->path}/delete", [
            'supply_id' => $supplyId,
            'cargo_ids' => array_map('strval', $cargoIds),
        ]);
    }

    /**
     * Status of a cargoes deletion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesDeleteStatus
     *
     * @return array{status?: 'SUCCESS'|'IN_PROGRESS'|'ERROR', errors?: array}
     */
    public function deleteStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/delete/status", ['operation_id' => $operationId]);
    }

    /**
     * Packing rules of supplies.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesRulesGet
     *
     * @param list<int|string> $supplyIds
     *
     * @return array{supply_check_lists?: list<array>}
     */
    public function rulesGet(array $supplyIds): array
    {
        return $this->request('POST', "{$this->path}/rules/get", [
            'supply_ids' => array_map('strval', $supplyIds),
        ]);
    }

    /**
     * Cargoes of supplies grouped by supply.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesSuppliesGet
     *
     * @param list<int|string> $supplyIds
     *
     * @return array{supplies_cargoes?: list<array>, not_found_supply_ids?: list<string>}
     */
    public function suppliesGet(array $supplyIds): array
    {
        return $this->request('POST', "{$this->path}/supplies/get", [
            'supply_ids' => array_map('strval', $supplyIds),
        ]);
    }

    /**
     * Creates transport cargoes of a supply.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesTransportCreate
     *
     * @param list<TTransportCargo> $transportCargoes
     *
     * @return array{operation_id?: string, error_reasons?: list<string>}
     */
    public function transportCreate(int $supplyId, array $transportCargoes): array
    {
        $transportCargoes = array_map(static function (array $cargo): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($cargo, ['type', 'count']),
                ['type' => 'str', 'count' => 'int']
            );
        }, $transportCargoes);

        return $this->request('POST', "{$this->path}/transport/create", [
            'supply_id'         => $supplyId,
            'transport_cargoes' => $transportCargoes,
        ]);
    }

    /**
     * Status of a transport cargoes creation.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesTransportCreateStatus
     *
     * @return array{status?: 'SUCCESS'|'IN_PROGRESS'|'FAILED', result?: array{transport_cargoes?: list<array>}, error_reasons?: list<string>}
     */
    public function transportCreateStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/transport/create/status", ['operation_id' => $operationId], true, false);
    }

    /**
     * Enables or disables transport cargoes for a supply.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesTransportActivate
     *
     * @return array{operation_id?: string}
     */
    public function transportActivate(int $supplyId, bool $isTransport): array
    {
        return $this->request('POST', "{$this->path}/transport/activate", [
            'supply_id'    => $supplyId,
            'is_transport' => $isTransport,
        ]);
    }

    /**
     * Status of a transport cargoes activation.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesTransportActivateStatus
     *
     * @return array{status?: 'SUCCESS'|'IN_PROGRESS'|'FAILED', error_reasons?: list<string>}
     */
    public function transportActivateStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/transport/activate/status", ['operation_id' => $operationId]);
    }

    /**
     * Binds cargoes to transport cargoes and unbinds them.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesTransportBind
     *
     * @param list<TTransportCargoBind> $transportCargoBind
     * @param list<int|string>          $cargoesUnbindTransportCargoes
     *
     * @return array{operation_id?: string, error_reasons?: list<string>}
     */
    public function transportBind(int $supplyId, array $transportCargoBind = [], array $cargoesUnbindTransportCargoes = []): array
    {
        $requestData = ['supply_id' => $supplyId];

        if ($transportCargoBind) {
            $requestData['transport_cargo_bind'] = array_map(static function (array $bind): array {
                return TypeCaster::castArr(
                    ArrayHelper::pick($bind, ['transport_cargo_id', 'cargo_ids']),
                    ['transport_cargo_id' => 'int', 'cargo_ids' => 'arrOfStr']
                );
            }, $transportCargoBind);
        }

        if ($cargoesUnbindTransportCargoes) {
            $requestData['cargoes_unbind_transport_cargoes'] = array_map('strval', $cargoesUnbindTransportCargoes);
        }

        return $this->request('POST', "{$this->path}/transport/bind", $requestData);
    }

    /**
     * Status of a transport cargoes binding.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesTransportBindStatus
     *
     * @return array{status?: 'SUCCESS'|'IN_PROGRESS'|'FAILED', error_reasons?: list<string>}
     */
    public function transportBindStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/transport/bind/status", ['operation_id' => $operationId]);
    }

    /**
     * Requests labels of transport cargoes.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesLabelTransportCreate
     *
     * @param list<int|string> $transportCargoIds
     *
     * @return array{operation_id?: string, error_reasons?: list<string>}
     */
    public function labelTransportCreate(int $supplyId, array $transportCargoIds = []): array
    {
        $requestData = ['supply_id' => $supplyId];

        if ($transportCargoIds) {
            $requestData['transport_cargo_ids'] = array_map('strval', $transportCargoIds);
        }

        return $this->request('POST', "{$this->path}/label/transport/create", $requestData);
    }

    /**
     * Status of a transport cargoes labels generation.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesLabelTransportStatus
     *
     * @return array{status?: 'SUCCESS'|'IN_PROGRESS'|'FAILED', result?: array{file_url?: string}, error_reasons?: list<string>}
     */
    public function labelTransportStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/label/transport/status", ['operation_id' => $operationId], true, false);
    }

    /**
     * Requests labels of all transport cargoes of a supply order.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesLabelTransportByOrderCreate
     *
     * @return array{operation_id?: string, error_reasons?: list<string>}
     */
    public function labelTransportByOrderCreate(int $orderId): array
    {
        return $this->request('POST', "{$this->path}/label/transport-by-order/create", ['order_id' => $orderId]);
    }

    /**
     * Status of a supply order transport cargoes labels generation.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesLabelTransportByOrderStatus
     *
     * @return array{status?: 'SUCCESS'|'IN_PROGRESS'|'FAILED', result?: array, error_reasons?: list<string>}
     */
    public function labelTransportByOrderStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/label/transport-by-order/status", ['operation_id' => $operationId], true, false);
    }

    /**
     * Requests labels of cargoes.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesLabelCreate
     *
     * @param list<array{cargo_id: int}> $cargoes
     *
     * @return TOperationResult
     */
    public function labelCreate(int $supplyId, array $cargoes = []): array
    {
        $requestData = ['supply_id' => $supplyId];

        if ($cargoes) {
            $requestData['cargoes'] = array_map(static function (array $cargo): array {
                return TypeCaster::castArr(ArrayHelper::pick($cargo, ['cargo_id']), ['cargo_id' => 'int']);
            }, $cargoes);
        }

        return $this->request('POST', '/v1/cargoes-label/create', $requestData);
    }

    /**
     * Status of a cargoes labels generation.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesLabelGet
     *
     * @return array{status?: 'SUCCESS'|'IN_PROGRESS'|'FAILED', result?: array{file_guid?: string, file_url?: string}, errors?: array}
     */
    public function labelGet(string $operationId): array
    {
        return $this->request('POST', '/v1/cargoes-label/get', ['operation_id' => $operationId], true, false);
    }

    /**
     * Cargoes labels PDF.
     *
     * @deprecated will be removed 10.04.2026 - use self::labelGet
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesLabelFile
     *
     * @return string PDF contents
     */
    public function labelFile(string $fileGuid): string
    {
        return $this->request('GET', "/v1/cargoes-label/file/{$fileGuid}", null, false);
    }
}

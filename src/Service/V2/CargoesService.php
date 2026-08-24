<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V2;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Cargoes (boxes and pallets) of FBO supplies.
 *
 * Types are derived from var/swagger.json. Only the top-level keys of nested
 * structures are listed, deeper levels are left as bare `array`.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TSupplyCargoes = array{supply_id: int, cargo_ids: list<string>}
 */
class CargoesService extends AbstractService
{
    private $path = '/v2/cargoes';

    /**
     * Result of a cargoes creation started by V1\CargoesService::create.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesCreateInfoV2
     *
     * @return array{status?: 'STATUS_UNSPECIFIED'|'SUCCESS'|'IN_PROGRESS'|'FAILED', result?: array{cargoes?: list<array>}, errors?: array}
     */
    public function createInfo(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/create/info", ['operation_id' => $operationId], true, false);
    }

    /**
     * Cargoes of supplies.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesGetV2
     *
     * @param list<TSupplyCargoes> $supplies
     *
     * @return array{supplies?: list<array>}
     */
    public function get(array $supplies): array
    {
        $supplies = array_map(static function (array $supply): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($supply, ['supply_id', 'cargo_ids']),
                ['supply_id' => 'int', 'cargo_ids' => 'arrOfStr']
            );
        }, $supplies);

        return $this->request('POST', "{$this->path}/get", ['supplies' => $supplies]);
    }

    /**
     * Deletes cargoes and transport cargoes of a supply.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesDeleteV2
     *
     * @param array{supply_id: int, transport_cargo_deletion_type: 'UNBIND_CONTAINED_CARGOES'|'DELETE_CONTAINED_CARGOES', cargo_ids?: list<string>, transport_cargo_ids?: list<string>} $requestData
     *
     * @return array{operation_id?: string, errors?: array}
     */
    public function delete(array $requestData): array
    {
        $requestData = TypeCaster::castArr(
            ArrayHelper::pick($requestData, [
                'supply_id',
                'transport_cargo_deletion_type',
                'cargo_ids',
                'transport_cargo_ids',
            ]),
            [
                'supply_id'                     => 'int',
                'transport_cargo_deletion_type' => 'str',
                'cargo_ids'                     => 'arrOfStr',
                'transport_cargo_ids'           => 'arrOfStr',
            ]
        );

        return $this->request('POST', "{$this->path}/delete", $requestData);
    }

    /**
     * Status of a cargoes deletion.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/CargoesAPI_CargoesDeleteStatusV2
     *
     * @return array{status?: 'UNSPECIFIED'|'SUCCESS'|'IN_PROGRESS'|'FAILED', errors?: array}
     */
    public function deleteStatus(string $operationId): array
    {
        return $this->request('POST', "{$this->path}/delete/status", ['operation_id' => $operationId]);
    }
}

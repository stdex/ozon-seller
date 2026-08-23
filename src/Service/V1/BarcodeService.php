<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\TypeCaster;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * Product barcodes.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TBarcode = array{barcode: string, sku: int}
 * @psalm-type TAddResponse = array{
 *     errors?: list<array{code?: string, error?: string, barcode?: string, sku?: int}>
 * }
 * @psalm-type TGenerateResponse = array{
 *     errors?: list<array{code?: string, error?: string, barcode?: string, product_id?: int}>
 * }
 */
class BarcodeService extends AbstractService
{
    private $path = '/v1/barcode';

    /**
     * Binds barcodes to products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/BarcodeAPI_BarcodeAdd
     *
     * @param list<TBarcode> $barcodes
     *
     * @return TAddResponse
     */
    public function add(array $barcodes): array
    {
        $barcodes = array_map(static function (array $barcode): array {
            return TypeCaster::castArr(
                ArrayHelper::pick($barcode, ['barcode', 'sku']),
                ['barcode' => 'str', 'sku' => 'int']
            );
        }, $barcodes);

        return $this->request('POST', "{$this->path}/add", ['barcodes' => $barcodes]);
    }

    /**
     * Generates barcodes for products.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/BarcodeAPI_BarcodeGenerate
     *
     * @param list<int|string> $productIds
     *
     * @return TGenerateResponse
     */
    public function generate(array $productIds): array
    {
        return $this->request('POST', "{$this->path}/generate", [
            'product_ids' => array_map('strval', $productIds),
        ]);
    }
}

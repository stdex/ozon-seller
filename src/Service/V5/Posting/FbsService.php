<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V5\Posting;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\Utils\ArrayHelper;


class FbsService extends AbstractService
{
    private $path = '/v5/fbs/posting';

    /**
     * @deprecated use V6\Posting\FbsService::productExemplarCreateOrGet
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_FbsPostingProductExemplarCreateOrGet
     */
    public function productExemplarCreateOrGet(string $postingNumber): array
    {
        $body = [
            'posting_number' => $postingNumber,
        ];
        return $this->request('POST', "{$this->path}/product/exemplar/create-or-get", $body);
    }
    
    /**
     * @deprecated use V6\Posting\FbsService::productExemplarSet
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_FbsPostingProductExemplarSet
     */
    public function productExemplarSet(int $multiBoxQty, string $postingNumber, array $products): bool
    {
        $body = [
            'multi_box_qty' => $multiBoxQty,
            'posting_number' => $postingNumber,
            'products' => $products,
        ];
        return $this->request('POST', "{$this->path}/product/exemplar/set", $body);
    }

    /**
     * Status of the exemplars check.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_FbsPostingProductExemplarStatusV5
     *
     * @return array{posting_number?: string, status?: string, products?: list<array>}
     */
    public function productExemplarStatus(string $postingNumber): array
    {
        return $this->request('POST', '/v5/fbs/posting/product/exemplar/status', [
            'posting_number' => $postingNumber,
        ]);
    }

    /**
     * Validates the marking codes of the posting exemplars.
     *
     * Nested `exemplars` of every product is passed as is.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_FbsPostingProductExemplarValidateV5
     *
     * @param list<array{product_id: int, exemplars: list<array>}> $products
     *
     * @return array{products?: list<array>}
     */
    public function productExemplarValidate(string $postingNumber, array $products): array
    {
        $products = array_map(static function (array $product): array {
            return ArrayHelper::pick($product, ['product_id', 'exemplars']);
        }, $products);

        return $this->request('POST', '/v5/fbs/posting/product/exemplar/validate', [
            'posting_number' => $postingNumber,
            'products'       => $products,
        ]);
    }
}

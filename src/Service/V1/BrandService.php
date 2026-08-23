<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;
use Gam6itko\OzonSeller\Utils\ArrayHelper;

/**
 * @psalm-type TCompanyCertificationList = array{
 *     brand_certification?: list<array{brand_name?: string, has_certificate?: bool}>,
 *     total?: int
 * }
 */
class BrandService extends AbstractService
{
    private $path = '/v1/brand';

    /**
     * Get a list of brands that require a certificate. The response contains a list of brands whose products are added in your seller profile.
     * The list of brands may change if Ozon receives a requirement from the brand to provide a certificate.
     *
     * @see https://docs.ozon.ru/api/seller/en/#operation/BrandAPI_BrandCompanyCertificationList
     *
     * @param array{page?: int, page_size?: int} $query
     *
     * @return TCompanyCertificationList
     */
    public function companyCertificationList(array $query = []): array
    {
        $pagination = array_merge(
            ['page' => 1, 'page_size' => 100],
            ArrayHelper::pick($query, ['page', 'page_size'])
        );

        return $this->request('POST', "{$this->path}/company-certification/list", $pagination);
    }
}

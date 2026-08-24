<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Seller info.
 *
 * Types are derived from var/swagger.json.
 *
 * @author Alexander Strizhak <gam6itko@gmail.com>
 *
 * @psalm-type TInfoResponse = array{
 *     company?: array{
 *         name?: string,
 *         legal_name?: string,
 *         country?: string,
 *         currency?: string,
 *         inn?: string,
 *         ogrn?: string,
 *         ownership_form?: string,
 *         tax_system?: 'UNKNOWN'|'UNSPECIFIED'|'OSNO'|'USN'|'NPD'|'AUSN'|'PSN'
 *     },
 *     ratings?: list<array{
 *         name?: string,
 *         rating?: string,
 *         current_value?: array,
 *         past_value?: array,
 *         status?: 'UNKNOWN'|'OK'|'WARNING'|'CRITICAL',
 *         value_type?: 'UNKNOWN'|'INDEX'|'PERCENT'|'TIME'|'RATIO'|'REVIEW_SCORE'|'COUNT'
 *     }>,
 *     subscription?: array{
 *         is_premium?: bool,
 *         type?: 'UNKNOWN'|'UNSPECIFIED'|'PREMIUM'|'PREMIUM_LITE'|'PREMIUM_PLUS'|'PREMIUM_PRO'
 *     }
 * }
 * @psalm-type TOzonLogisticsInfoResponse = array{
 *     ozon_logistics_enabled?: bool,
 *     available_schemas?: list<'UNKNOWN'|'FBO'|'FBS'>
 * }
 * @psalm-type TRolesResponse = array{
 *     expires_at?: string,
 *     roles?: list<array{name?: string, methods?: list<array>}>
 * }
 */
class SellerService extends AbstractService
{
    /**
     * Seller info: company, ratings, subscription.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerAPI_SellerInfo
     *
     * @return TInfoResponse
     */
    public function info(): array
    {
        return $this->request('POST', '/v1/seller/info', '{}');
    }

    /**
     * Ozon logistics connection info.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerAPI_OzonLogisticsInfo
     *
     * @return TOzonLogisticsInfoResponse
     */
    public function ozonLogisticsInfo(): array
    {
        return $this->request('POST', '/v1/seller/ozon-logistics/info', '{}');
    }

    /**
     * Roles and methods available for the API key.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/SellerAPI_Roles
     *
     * @return TRolesResponse
     */
    public function roles(): array
    {
        return $this->request('POST', '/v1/roles', '{}');
    }
}

<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * Информация о продавце.
 *
 * Типы описаны по var/swagger.json.
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
     * Информация о продавце: компания, рейтинги, подписка.
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
     * Информация о подключении к логистике Ozon.
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
     * Список доступных для API-ключа ролей и методов.
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

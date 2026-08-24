<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Service\V1\Posting;

use Gam6itko\OzonSeller\Service\AbstractService;

/**
 * @author Alexander Strizhak <gam6itko@gmail.com>
 */
class FboService extends AbstractService
{
    /**
     * Cancellation reasons available for FBO postings.
     *
     * @see https://docs.ozon.ru/api/seller/#operation/PostingAPI_PostingFboCancelReasonList
     *
     * @return array{reasons?: list<array>}
     */
    public function cancelReasonList(): array
    {
        return $this->request('POST', '/v1/posting/fbo/cancel-reason/list', '{}');
    }
}

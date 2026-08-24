<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V1\Posting;

use Gam6itko\OzonSeller\Service\V1\Posting\FbsService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;
use Psr\Http\Client\ClientInterface;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V1\Posting\FbsService
 */
class FbsServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return FbsService::class;
    }

    /**
     * @covers ::cancelReason
     */
    public function testCancelReason(): void
    {
        $this->quickTest(
            'cancelReason',
            [
                [
                    '12345678-0001-12',
                    '12345619-98741-12',
                ],
            ],
            [
                'POST',
                '/v1/posting/fbs/cancel-reason',
                '{"related_posting_numbers":["12345678-0001-12","12345619-98741-12"]}',
            ]
        );
    }

    /**
     * @covers ::cancelReason
     */
    public function testCancelReasonEmptyRequestException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $config = [123, 'api-key'];
        $client = $this->createMock(ClientInterface::class);
        $svc = new FbsService($config, $client, $this->createRequestFactory(), $this->createStreamFactory());
        $svc->cancelReason([]);
    }

    /**
     * @covers ::split
     */
    public function testSplit(): void
    {
        $this->quickTest(
            'split',
            [
                '33920474-0032-1',
                [
                    [
                        'products' => [['sku' => 160249683, 'quantity' => 1]],
                        // must be filtered out
                        'foo'      => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/posting/fbs/split',
                '{"posting_number":"33920474-0032-1","postings":[{"products":[{"sku":160249683,"quantity":1}]}]}',
            ],
            '{"postings":[]}',
            static function (array $result): void {
                self::assertSame(['postings' => []], $result);
            }
        );
    }

    /**
     * @covers ::traceableSplit
     */
    public function testTraceableSplit(): void
    {
        $this->quickTest(
            'traceableSplit',
            ['33920474-0032-1'],
            [
                'POST',
                '/v1/posting/fbs/traceable/split',
                '{"posting_number":"33920474-0032-1"}',
            ],
            '{"postings":[]}',
            static function (array $result): void {
                self::assertSame(['postings' => []], $result);
            }
        );
    }

    /**
     * @covers ::productTraceableAttribute
     */
    public function testProductTraceableAttribute(): void
    {
        $this->quickTest(
            'productTraceableAttribute',
            ['33920474-0032-1'],
            [
                'POST',
                '/v1/posting/fbs/product/traceable/attribute',
                '{"posting_number":"33920474-0032-1"}',
            ],
            '{"products":[]}',
            static function (array $result): void {
                self::assertSame(['products' => []], $result);
            }
        );
    }

    /**
     * @covers ::timeslotChangeRestrictions
     */
    public function testTimeslotChangeRestrictions(): void
    {
        $this->quickTest(
            'timeslotChangeRestrictions',
            ['33920474-0032-1'],
            [
                'POST',
                '/v1/posting/fbs/timeslot/change-restrictions',
                '{"posting_number":"33920474-0032-1"}',
            ],
            '{"remaining_changes_count":2}',
            static function (array $result): void {
                self::assertSame(['remaining_changes_count' => 2], $result);
            }
        );
    }

    /**
     * @covers ::timeslotSet
     */
    public function testTimeslotSet(): void
    {
        $this->quickTest(
            'timeslotSet',
            ['33920474-0032-1', '2026-08-25T10:00:00Z', '2026-08-25T12:00:00Z'],
            [
                'POST',
                '/v1/posting/fbs/timeslot/set',
                '{"posting_number":"33920474-0032-1","new_timeslot":{"from":"2026-08-25T10:00:00Z","to":"2026-08-25T12:00:00Z"}}',
            ],
            '{"result":true}',
            static function ($result): void {
                self::assertTrue($result);
            }
        );
    }

    /**
     * @covers ::restrictions
     */
    public function testRestrictions(): void
    {
        $this->quickTest(
            'restrictions',
            ['33920474-0032-1'],
            [
                'POST',
                '/v1/posting/fbs/restrictions',
                '{"posting_number":"33920474-0032-1"}',
            ],
            '{"result":{"posting_number":"33920474-0032-1","max_posting_weight":25000}}'
        );
    }

    /**
     * @covers ::packageLabelCreate
     */
    public function testPackageLabelCreate(): void
    {
        $this->quickTest(
            'packageLabelCreate',
            [['33920474-0032-1']],
            [
                'POST',
                '/v1/posting/fbs/package-label/create',
                '{"posting_number":["33920474-0032-1"]}',
            ],
            '{"result":{"task_id":1234}}'
        );
    }

    /**
     * @covers ::pickUpCodeVerify
     */
    public function testPickUpCodeVerify(): void
    {
        $this->quickTest(
            'pickUpCodeVerify',
            ['33920474-0032-1', '1234'],
            [
                'POST',
                '/v1/posting/fbs/pick-up-code/verify',
                '{"posting_number":"33920474-0032-1","pickup_code":"1234"}',
            ],
            '{"valid":true}',
            static function (array $result): void {
                self::assertSame(['valid' => true], $result);
            }
        );
    }

    /**
     * @covers ::cancel
     */
    public function testCancel(): void
    {
        $this->quickTest(
            'cancel',
            ['33920474-0032-1', 352, 'нет товара'],
            [
                'POST',
                '/v1/posting/cancel',
                '{"posting_number":"33920474-0032-1","reason_id":352,"reason_message":"нет товара"}',
            ],
            '{"message":"ok"}',
            static function (array $result): void {
                self::assertSame(['message' => 'ok'], $result);
            }
        );
    }

    /**
     * @covers ::cancelStatus
     */
    public function testCancelStatus(): void
    {
        $this->quickTest(
            'cancelStatus',
            ['33920474-0032-1'],
            ['POST', '/v1/posting/cancel/status', '{"posting_number":"33920474-0032-1"}'],
            '{"state":"SUCCESS","order_number":"33920474-0032"}',
            static function (array $result): void {
                self::assertSame('SUCCESS', $result['state']);
            }
        );
    }

    /**
     * @covers ::marks
     */
    public function testMarks(): void
    {
        $this->quickTest(
            'marks',
            [['33920474-0032-1']],
            ['POST', '/v1/posting/marks', '{"posting_numbers":["33920474-0032-1"]}'],
            '{"issued_exemplars":[],"invalid_postings":[]}',
            static function (array $result): void {
                self::assertSame(['issued_exemplars' => [], 'invalid_postings' => []], $result);
            }
        );
    }

    /**
     * @covers ::cutoffSet
     */
    public function testCutoffSet(): void
    {
        $this->quickTest(
            'cutoffSet',
            ['33920474-0032-1', '2026-08-26T00:00:00Z'],
            [
                'POST',
                '/v1/posting/cutoff/set',
                '{"posting_number":"33920474-0032-1","new_cutoff_date":"2026-08-26T00:00:00Z"}',
            ],
            '{"result":true}',
            static function ($result): void {
                self::assertTrue($result);
            }
        );
    }

    /**
     * @covers ::unpaidLegalProductList
     */
    public function testUnpaidLegalProductList(): void
    {
        $this->quickTest(
            'unpaidLegalProductList',
            [50, 'prev-cursor'],
            [
                'POST',
                '/v1/posting/unpaid-legal/product/list',
                '{"limit":50,"cursor":"prev-cursor"}',
            ],
            '{"products":[],"cursor":""}',
            static function (array $result): void {
                self::assertSame(['products' => [], 'cursor' => ''], $result);
            }
        );
    }

    /**
     * @covers ::productExemplarUpdate
     */
    public function testProductExemplarUpdate(): void
    {
        $this->quickTest(
            'productExemplarUpdate',
            ['33920474-0032-1'],
            [
                'POST',
                '/v1/fbs/posting/product/exemplar/update',
                '{"posting_number":"33920474-0032-1"}',
            ],
            '{}',
            static function (array $result): void {
                self::assertSame([], $result);
            }
        );
    }

    /**
     * @covers ::digitalCodesUpload
     */
    public function testDigitalCodesUpload(): void
    {
        $this->quickTest(
            'digitalCodesUpload',
            [
                '33920474-0032-1',
                [
                    [
                        'sku'                        => '160249683',
                        'exemplar_qty'               => '1',
                        'not_available_exemplar_qty' => '0',
                        'exemplar_keys'              => ['KEY-1'],
                        // must be filtered out
                        'foo'                        => 'bar',
                    ],
                ],
            ],
            [
                'POST',
                '/v1/posting/digital/codes/upload',
                '{"posting_number":"33920474-0032-1","exemplars_by_sku":[{"sku":160249683,"exemplar_qty":1,"not_available_exemplar_qty":0,"exemplar_keys":["KEY-1"]}]}',
            ],
            '{"exemplars_by_sku":[]}',
            static function (array $result): void {
                self::assertSame(['exemplars_by_sku' => []], $result);
            }
        );
    }

    /**
     * @covers ::globalEtgb
     */
    public function testGlobalEtgb(): void
    {
        $this->quickTest(
            'globalEtgb',
            ['2026-08-01T00:00:00Z', '2026-08-08T00:00:00Z'],
            [
                'POST',
                '/v1/posting/global/etgb',
                '{"date":{"from":"2026-08-01T00:00:00Z","to":"2026-08-08T00:00:00Z"}}',
            ],
            '{"result":[]}'
        );
    }
}

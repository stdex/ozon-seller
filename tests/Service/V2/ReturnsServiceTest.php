<?php

declare(strict_types=1);

namespace Gam6itko\OzonSeller\Tests\Service\V2;

use Gam6itko\OzonSeller\Enum\PostingScheme;
use Gam6itko\OzonSeller\Service\V2\ReturnsService;
use Gam6itko\OzonSeller\Tests\Service\AbstractTestCase;
use Psr\Http\Client\ClientInterface;

/**
 * @coversDefaultClass \Gam6itko\OzonSeller\Service\V2\ReturnsService
 */
class ReturnsServiceTest extends AbstractTestCase
{
    protected function getClass(): string
    {
        return ReturnsService::class;
    }

    /**
     * @covers ::company
     */
    public function testCompany(): void
    {
        $this->quickTest(
            'company',
            [
                PostingScheme::FBO,
                ['filter' => ['posting_number' => '00000000-0000-0']],
            ],
            [
                'POST',
                '/v2/returns/company/fbo',
                '{"filter":{"posting_number":"00000000-0000-0"},"offset":0,"limit":10}',
            ]
        );
    }

    /**
     * @covers ::company
     *
     * @dataProvider dataCompanyWrongScheme
     */
    public function testCompanyWrongScheme(string $postingScheme): void
    {
        self::expectExceptionMessage("Unsupported posting scheme: $postingScheme");

        $client = $this->createMock(ClientInterface::class);
        $svc = new ReturnsService(['clientId' => 1, 'apiKey' => '123'], $client, $this->createRequestFactory(), $this->createStreamFactory());
        $svc->company($postingScheme, []);
    }

    public function dataCompanyWrongScheme(): iterable
    {
        yield [PostingScheme::CROSSBORDER];

        yield ['boom'];
    }

    /**
     * @covers ::rfbsList
     */
    public function testRfbsList(): void
    {
        $this->quickTest(
            'rfbsList',
            [
                [
                    'filter' => [
                        'offer_id'       => '9789785079999',
                        'posting_number' => '33920474-0032-1',
                        'group_state'    => ['Approved'],
                        'created_at'     => [
                            'from' => '2026-08-01T00:00:00Z',
                            'to'   => '2026-08-08T00:00:00Z',
                            // must be filtered out
                            'foo'  => 'bar',
                        ],
                        // must be filtered out
                        'baz' => 'qux',
                    ],
                    'limit'   => '50',
                    'last_id' => '10',
                ],
            ],
            [
                'POST',
                '/v2/returns/rfbs/list',
                '{"limit":50,"filter":{"offer_id":"9789785079999","posting_number":"33920474-0032-1","group_state":["Approved"],"created_at":{"from":"2026-08-01T00:00:00Z","to":"2026-08-08T00:00:00Z"}},"last_id":10}',
            ],
            '{"returns":{}}',
            static function (array $result): void {
                self::assertSame(['returns' => []], $result);
            }
        );
    }

    /**
     * @covers ::rfbsGet
     */
    public function testRfbsGet(): void
    {
        $this->quickTest(
            'rfbsGet',
            [123],
            ['POST', '/v2/returns/rfbs/get', '{"return_id":123}'],
            '{"returns":{"return_number":"1234"}}',
            static function (array $result): void {
                self::assertSame(['returns' => ['return_number' => '1234']], $result);
            }
        );
    }
}

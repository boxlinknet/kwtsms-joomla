<?php

namespace KwtSMS\Tests\Unit;

use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use PHPUnit\Framework\TestCase;

final class VerifyTest extends TestCase
{
    private KwtSmsApiClient $client;

    protected function setUp(): void
    {
        $this->client = new KwtSmsApiClient('', '');
    }

    /**
     * @test
     * @dataProvider coverageProvider
     */
    public function verifyReturnsExpected(string $phone, array $coverage, bool $expected): void
    {
        $this->assertSame($expected, $this->client->verify($phone, $coverage));
    }

    public function coverageProvider(): array
    {
        return [
            'Kuwait number in coverage'       => ['96598765432',  ['965', '971'], true],
            'US number not in coverage'        => ['12025550100',  ['965', '971'], false],
            'empty coverage blocks all'        => ['96598765432',  [],             false],
            'UAE number in coverage'           => ['971501234567', ['965', '971'], true],
        ];
    }
}

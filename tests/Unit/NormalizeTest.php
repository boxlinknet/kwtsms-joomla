<?php

namespace KwtSMS\Tests\Unit;

use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use PHPUnit\Framework\TestCase;

final class NormalizeTest extends TestCase
{
    private KwtSmsApiClient $client;

    protected function setUp(): void
    {
        $this->client = new KwtSmsApiClient('', '');
    }

    /**
     * @test
     * @dataProvider phoneProvider
     */
    public function normalizeReturnsExpected(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->client->normalize($input));
    }

    public function phoneProvider(): array
    {
        return [
            'strips plus'           => ['+96598765432',      '96598765432'],
            'strips 00 prefix'      => ['0096598765432',     '96598765432'],
            'strips spaces'         => ['965 9876 5432',     '96598765432'],
            'strips dashes'         => ['965-9876-5432',     '96598765432'],
            'Arabic-Indic digits'   => ["\u{0669}\u{0666}\u{0665}\u{0669}\u{0668}\u{0667}\u{0666}\u{0665}\u{0664}\u{0663}\u{0662}", '96598765432'],
            'Ext Arabic-Indic'      => ["\u{06F9}\u{06F6}\u{06F5}\u{06F9}\u{06F8}\u{06F7}\u{06F6}\u{06F5}\u{0664}\u{06F3}\u{06F2}", '96598765432'],
            'empty string'          => ['',                  ''],
        ];
    }
}

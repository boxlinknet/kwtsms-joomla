<?php

namespace KwtSMS\Tests\Unit;

use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use PHPUnit\Framework\TestCase;

final class CleanMessageTest extends TestCase
{
    private KwtSmsApiClient $client;

    protected function setUp(): void
    {
        $this->client = new KwtSmsApiClient('', '');
    }

    /**
     * @test
     * @dataProvider messageProvider
     */
    public function cleanMessageReturnsExpected(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->client->cleanMessage($input));
    }

    public function messageProvider(): array
    {
        return [
            'strips emoji'          => ["Hello World \u{1F600}",    'Hello World'],
            'strips HTML tags'      => ['<b>Hello</b> World',        'Hello World'],
            'strips zero-width sp'  => ["Hello\u{200B}World",        'HelloWorld'],
            'strips BOM'            => ["\u{FEFF}Hello",             'Hello'],
            'converts Arabic nums'  => ["OTP: \u{0661}\u{0662}\u{0663}\u{0664}\u{0665}", 'OTP: 12345'],
            'preserves Arabic text' => ['مرحبا بك',                  'مرحبا بك'],
            'preserves newlines'    => ["Line1\nLine2",              "Line1\nLine2"],
        ];
    }
}

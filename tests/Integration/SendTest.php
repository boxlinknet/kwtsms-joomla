<?php

namespace KwtSMS\Tests\Integration;

use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use PHPUnit\Framework\TestCase;

final class SendTest extends TestCase
{
    private KwtSmsApiClient $client;
    private string $sender;

    protected function setUp(): void
    {
        $username = $_ENV['KWTSMS_USERNAME'] ?? '';
        $password = $_ENV['KWTSMS_PASSWORD'] ?? '';

        if ($username === '' || $password === '') {
            $this->markTestSkipped('No credentials configured');
        }

        $this->client = new KwtSmsApiClient($username, $password);
        $this->sender = $_ENV['KWTSMS_SENDER'] ?? 'KWT-SMS';
    }

    /** @test */
    public function sendSingleWithTestModeReturnsOk(): void
    {
        $response = $this->client->send(
            ['96598765432'],
            'Test from kwtSMS Joomla plugin',
            $this->sender,
            true,   // testMode
            null    // no SettingsService
        );

        $this->assertSame('OK', $response['result'], 'Expected result=OK, got: ' . json_encode($response));
        $this->assertNotEmpty($response['msg-id'], 'msg-id must be non-empty');
        $this->assertIsNumeric($response['balance-after'], 'balance-after must be numeric');
    }
}

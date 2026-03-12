<?php

namespace KwtSMS\Tests\Integration;

use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use PHPUnit\Framework\TestCase;

final class SenderidTest extends TestCase
{
    private KwtSmsApiClient $client;

    protected function setUp(): void
    {
        $username = $_ENV['KWTSMS_USERNAME'] ?? '';
        $password = $_ENV['KWTSMS_PASSWORD'] ?? '';

        if ($username === '' || $password === '') {
            $this->markTestSkipped('No credentials configured');
        }

        $this->client = new KwtSmsApiClient($username, $password);
    }

    /** @test */
    public function senderidReturnsOkWithSenderArray(): void
    {
        $response = $this->client->senderid();
        $this->assertSame('OK', $response['result']);
        $this->assertIsArray($response['senderid']);
    }
}

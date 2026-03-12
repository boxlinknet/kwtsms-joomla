<?php

namespace KwtSMS\Tests\Integration;

use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use PHPUnit\Framework\TestCase;

final class ValidateTest extends TestCase
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
    public function validateReturnsOkWithPhoneInOkArray(): void
    {
        $response = $this->client->validate('96598765432');
        $this->assertSame('OK', $response['result']);
        $this->assertIsArray($response['mobile']['OK']);
        $this->assertContains('96598765432', $response['mobile']['OK']);
    }
}

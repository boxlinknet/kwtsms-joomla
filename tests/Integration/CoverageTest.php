<?php

namespace KwtSMS\Tests\Integration;

use KwtSMS\Component\Kwtsms\Administrator\Service\KwtSmsApiClient;
use PHPUnit\Framework\TestCase;

final class CoverageTest extends TestCase
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
    public function coverageReturnsOkWithPrefixesContaining965(): void
    {
        $response = $this->client->coverage();
        $this->assertSame('OK', $response['result']);
        $this->assertIsArray($response['prefixes']);
        $this->assertContains('965', $response['prefixes']);
    }
}

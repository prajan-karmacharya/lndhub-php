<?php

use LNDHub\Client;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    private function client(): Client
    {
        $url = '';
        $login = '';
        $password = '';
        return new Client($url, $login, $password);
    }
    public function testCanBeInitialized(): void
    {
        $this->assertInstanceOf(
            Client::class,
            $this->client(),
        );
    }

    public function testCanAuthorize(): void
    {
        $client = $this->client();
        $this->assertFalse($client->isAuthenticated());
        $client->init();
        $this->assertTrue($client->isAuthenticated());
    }

    public function testCanGetBalance(): void
    {
        $client = $this->client();
        $client->init();
        $this->assertIsNumeric($client->getBalance()['data']['balance']);
    }

    public function testCanGetInfo(): void
    {
        $client = $this->client();
        $client->init();
        $this->assertIsString($client->getInfo()['data']['alias']);
    }
}

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
        $this->assertFalse($client->isConnectionValid());
        $client->init();
        $this->assertTrue($client->isConnectionValid());
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

    public function testCanAddInvoice(): void
    {
        $client = $this->client();
        $client->init();
        $response = $client->addInvoice([
            'value'=> 23,
            'memo'=> 'test invoice'
        ]);
        $this->assertIsString($response['data']['paymentRequest']);
        $this->assertIsString($response['data']['rHash']);
    }

    public function testCanGetInvoice(): void
    {
        $client = $this->client();
        $client->init();
        $response = $client->addInvoice([
            'value'=> 23,
            'memo'=> 'test invoice'
        ]);
        $invoice = $client->getInvoice($response['data']['rHash']);

        $this->assertArrayHasKey('settled', $invoice);
    }
}

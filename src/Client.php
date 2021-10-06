<?php

namespace LNDHub;

use GuzzleHttp;
use LNDHub\Contracts\LNDHubClient;

class Client implements LNDHubClient
{

  private $client;
  private $access_token;
  private $refresh_token;
  private $url;
  private $login;
  private $password;

  public function __construct($url, $login, $password)
  {
    $this->url = $url;
    $this->login = $login;
    $this->password = $password;
  }

  public function init()
  {
    return $this->authorize();
  }

  private function authorize()
  {
    $headers = [
      'Accept' => 'application/json',
      'Content-Type' => 'application/json',
      'Access-Control-Allow-Origin' => '*'
    ];
    $body = ["login" => $this->login, "password" => $this->password];
    $request = new GuzzleHttp\Psr7\Request('POST', '/auth?type=auth', $headers, json_encode($body));
    $response = $this->client()->send($request);
    if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
      $responseBody = $response->getBody()->getContents();
      $data = json_decode($responseBody, true);
      $this->access_token = $data['access_token'];
      $this->refresh_token = $data['refresh_token'];
      return $data;
    } else {
      // raise exception
    }
  }

  private function request($method, $path, $body = null)
  {
    $headers = [
      'Accept' => 'application/json',
      'Content-Type' => 'application/json',
      'Access-Control-Allow-Origin' => '*',
      'Authorization' => "Bearer {$this->access_token}"
    ];

    $requestBody = $body ? json_encode($body) : null;
    $request = new GuzzleHttp\Psr7\Request($method, $path, $headers, $requestBody);
    $response = $this->client()->send($request);
    if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
      $responseBody = $response->getBody()->getContents();
      return json_decode($responseBody, true);
    } else {
      // raise exception
    }
  }

  public function getInfo(): array
  {
    $data = $this->request("GET", "/getinfo");
    return [
      "data" => [
        "alias" => $data['alias']
      ]
    ];
  }

  public function getBalance()
  {
    $data = $this->request("GET", "/balance");
    return [
      "data" => [
        "balance" => $data['BTC']['AvailableBalance']
      ]
    ];
  }

  private function client()
  {
    if ($this->client) {
      return $this->client;
    }
    $options = ['base_uri' => $this->url];
    $this->client = new GuzzleHttp\Client($options);
    return $this->client;
  }

  public function isConnectionValid(): bool
  {
    return !empty($this->access_token);
  }

  public function addInvoice($invoice): array
  {
    $data = $this->request("POST", "/addinvoice", [
      'amt' => $invoice['value'],
      'memo' => $invoice['memo']
    ]);
    if (is_array($data) && $data['r_hash']['type'] === "Buffer") {
      $data['r_hash'] = bin2hex(join(array_map("chr", $data["r_hash"]["data"])));
    }

    return [
      'data' => [
        'paymentRequest' => $data['payment_request'],
        'rHash' => $data['r_hash']
      ]
    ];
  }

  public function getInvoice($checkingId): array
  {
    $invoice = $this->request("GET", "/checkpayment/{$checkingId}");

    $invoice['settled'] = $invoice['paid']; //kinda mimic lnd
    return $invoice;
  }

  public function isInvoicePaid($checkingId): bool
  {
    $invoice = $this->getInvoice($checkingId);
    return $invoice['settled'];
  }
}

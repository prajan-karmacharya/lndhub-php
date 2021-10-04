<?php

namespace LNDHub;

use GuzzleHttp;

class Client
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

  private function request($method, $path, $body = null)
  {
    $headers = [
      'Accept' => 'application/json',
      'Content-Type' => 'application/json',
      'Access-Control-Allow-Origin' => '*',
      'Authorization' => "Bearer {$this->access_token}"
    ];

    $request = new GuzzleHttp\Psr7\Request($method, $path, $headers, $body);
    $response = $this->client()->send($request);
    if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
      $responseBody = $response->getBody()->getContents();
      return json_decode($responseBody, true);
    } else {
      // raise exception
    }
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
      $this->access_token = $data->access_token;
      $this->refresh_token = $data->refresh_token;
      return $data;
    } else {
      // raise exception
    }
  }

  private function getInfo()
  {
    $data = $this->request("GET", "/getinfo", []);
    return [
      "data" => [
        "alias" => $data->alias
      ]
    ];
  }

  private function getBalance()
  {
    $data = $this->request("GET", "/balance", []);
    return [
      "data" => [
        "balance" => $data->BTC->AvailableBalance
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
}

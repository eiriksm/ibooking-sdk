<?php

namespace Nymediaas\IbookingSdk;

use GuzzleHttp\Client;

class IbookingClient {
  private $apiKey;
  private $urlBase = 'https://ibooking.sit.no/webapp/api/';
  public function __construct($api_key) {
    $this->apiKey = $api_key;
    $this->httpClient = new Client();
  }

  public function getAccessToken($phone, $ttl = 3600) {
    $path = 'User/login';
    $data = array(
      'username' => $phone,
      'ttl' => $ttl,
      'accessToken' => $this->apiKey,
      'spc' => 1,
    );
    $response = $this->apiCall($path, 'POST', $data);
    // See if we have it.
    $body = $this->getJson($response);
    if (empty($body->authToken)) {
      throw new \Exception('No auth token found in response');
    }
    return $body->authToken;
  }

  public function getBookings($token, $limit = 60) {
    $path = 'User/getBookings';
    $response = $this->apiCall($path, 'GET', array(
      'token' => $token,
      'limit' => $limit,
    ));
    $body = $this->getJson($response);
    return $body;
  }

  protected function getJson($response) {
    return @json_decode((string) $response->getBody());
  }

  public function apiCall($path, $method = 'GET', $data = NULL) {
    $url = sprintf('%s%s', $this->urlBase, $path);
    $opts = array(
      'form_params' => $data,
      'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
    );
    if ($method == 'GET') {
      unset($opts['form_params']);
      $opts['query'] = $data;
    }
    $response = $this->httpClient->request($method, $url, $opts);
    return $response;
  }
}

<?php

namespace Ifpa;

/**
 * Thrown if the IFPA API reports an error.
 */
class IfpaApiException extends Exception {}

/**
 * Wrapper class for accessing the IFPA API.
 *
 * This class was created to assist PHP developers in accessing the IFPA's
 * API for ranking, player, and event information.
 *
 * API Documentation: http://www.ifpapinball.com/api/documentation/
 * Github: https://github.com/heyrocker/IfpaApi
 *
 * @todo Caching (maybe? better to leave it to the source app?)
 */
class IfpaApi {

  /**
   * Base IFPA API endpoint.
   */
  const BASE_URL = "https://api.ifpapinball.com/v1/";

  /**
   * The api_key supplied by IFPA.
   */
  protected $api_key;

  /**
   * Constructs an IfpaApi object.
   */
  public function __construct($api_key) {
    $this->api_key = $api_key;
  }

  /**
   * Retrieve information for a specified player.
   *
   * @param $player_id
   *   The player's unique ID.
   *
   * @return stdClass
   *   An object with the player's information.
   */
  public function getPlayerInformation($player_id) {
    $url = IfpaApi::BASE_URL . "player/" . $player_id . "?api_key=" . $this->api_key;
    return $this->makeRequest($url);
  }

  /**
   * Retrieve history for a specified player.
   *
   * @param $player_id
   *   The player's unique ID.
   *
   * @return stdClass
   *   An object with the player's history.
   */
  public function getPlayerHistory($player_id) {
    $url = IfpaApi::BASE_URL . "player/" . $player_id . "/history?api_key=" . $this->api_key;
    return $this->makeRequest($url);
  }

  /**
   * Make a request to the IFPA API.
   *
   * @param $url
   *   The $url to the resource being requested.
   *
   * @return object
   *   Output of json_decode() on the results of the request, or FALSE.
   *
   * @todo Use cURL when the https stuff is sorted.
   * @todo verify json
   */
  protected function makeRequest($url) {
    $output = @file_get_contents($url);

    if ($output === FALSE) {
      error_log($this->getIfpaError($http_response_header));
      return FALSE;
    }

    return json_decode($output);
  }

  /**
   * Format an error message based on the HTTP return code.
   *
   * @param $http_response_header
   *   Array created by PHP with the HTTP response headers.
   *
   * @return string
   */
  private function getIfpaError($http_response_header) {
    $messages = array(
      '400'	=> 'A parameter is missing or is invalid',
      '401'	=> 'Authentication failed',
      '404'	=> 'Resource cannot be found',
      '405'	=> 'HTTP method not allowed',
      '429'	=> 'Rate limit exceeded',
      '500'	=> 'Server error',
    );

    $error_code_parts = explode(' ', $http_response_header[0]);
    $error_code = $error_code_parts[1];
    return $messages[$error_code];
  }
}

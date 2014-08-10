<?php

// Fix this
namespace Ifpa\WpprBundle\Helpers;

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
 * @todo Make proper PSR vendor library for easier distribution
 * @todo Composer blah blah blah
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
   * @todo Use cURL when the https stuff is sorted.
   * @todo verify json
   */
  protected function makeRequest($url) {
    $result = json_decode(file_get_contents($url));
    return $result;
  }

}

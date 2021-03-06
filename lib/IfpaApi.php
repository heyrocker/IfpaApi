<?php

namespace Ifpa;

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
   * Retrieve a list of calendar events.
   *
   * @param $country string
   *   The country whose events you wish to display. Defaults to 'United
   *   States'.
   * @param $state string
   *   The state whose events you wish to display. If specified, only this
   *   state's results will be returned. Note that the state field for
   *   countries outside of the US and Canada doesn't really work very well.
   * @param $past bool
   *   If set to TRUE, return past events rather than current events. Defaults
   *   to FALSE. NOTE: Historical result sets can be very large.
   *
   * @return stdClass
   *   An object with the events.
   */
  public function getCalendarEvents($country = 'United States', $state = NULL, $past = FALSE) {
    // Set whether we are retrieving current or past events so we can hit
    // the correct endpoint.
    $type = 'active';
    if ($past) {
      $type = 'history';
    }

    $url = IfpaApi::BASE_URL . "calendar/" . $type . "?api_key=" . $this->api_key . '&country=' . urlencode($country);
    $results = $this->makeRequest($url);

    // IFPA returns the results in an indexed array on the object's 'calendar'
    // property. I feel its a little more useful to get an associative array
    // with the event's id as the key, so that's what I do here. It doesn't
    // make anything harder and it makes some things easier. We also do the
    // state filtering at the same time.
    if ($results->total_entries) {
      foreach ($results->calendar as $result) {
        if (!$state || ($state && $result->state == $state)) {
          $munged_results[$result->calendar_id] = $result;
        }
      }
      $results->calendar = $munged_results;
      $results->total_entries = count($munged_results);
    }

    // Since we have modified the results so heavily, re-encode them into the
    // json property.
    unset($results->json);
    $json = json_encode($results);
    $results->json = $json;

    return $results;
  }

  /**
   * Retrieve player vs player information.
   *
   * @param $player_id
   *   The player's unique ID.
   *
   * @return stdClass
   *   An object with the player vs player information
   */
  public function getPlayerVsPlayer($player_id) {
    $url = IfpaApi::BASE_URL . "player/" . $player_id . "/pvp?api_key=" . $this->api_key;
    return $this->makeRequest($url);
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
   * Retrieve list of country directors.
   *
   * @return stdClass
   *   An object with all the country directors.
   */
  public function getCountryDirectors() {
    $url = IfpaApi::BASE_URL . "player/country_directors?api_key=" . $this->api_key;
    return $this->makeRequest($url);
  }

  /**
   * Search IFPA players by name.
   *
   * @param $name
   *   The player's name to search. This will match any part of a player's
   *   first or last names.
   *
   * @return stdClass
   *   An object with all matching players, or FALSE if none found.
   */
  public function searchPlayersByName($name) {
    return $this->searchPlayers($name, 'name');
  }

  /**
   * Search IFPA players by email.
   *
   * @param $email
   *   The player's email to search.
   *
   * @return stdClass
   *   An object with all matching players, or FALSE if none found.
   */
  public function searchPlayersByEmail($email) {
    return $this->searchPlayers($email, 'email');
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
    // PHP uses the "allow_url_fopen" setting to control whether or not a
    // file stream can open a remote URL. Many hosting providers force this to
    // FALSE, which causes us problems. This line forces this setting to TRUE.
    // This will be removed when the SSL cert at the IFPA site is sorted out
    // and we can use cURL.
    ini_set("allow_url_fopen", TRUE);
    $output = @file_get_contents($url);

    if ($output === FALSE) {
      error_log($this->getIfpaError($http_response_header));
      return FALSE;
    }
    $results = json_decode($output);

    // Save the original json into the results object so that the callers can
    // have access to it for caching or other purposes.
    $results->json = $output;
    return $results;
  }

  /**
   * Format an error message based on the HTTP return code.
   *
   * @param $http_response_header
   *   Array created by PHP with the HTTP response headers.
   *
   * @return string
   */
  protected function getIfpaError($http_response_header) {
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

  /**
   * Search IFPA players.
   *
   * @param $param
   *   The name or email to search
   * @param $type
   *   Whether this is a name search or an email search
   *
   * @return stdClass
   *   An object with all matching players, or FALSE if none found.
   */
  protected function searchPlayers($param, $type) {
    // The player search function only works with email OR name searches, not
    // both.
    $fragment = '&q=';
    if ($type == 'email') {
      $fragment = '&email=';
    }

    $url = IfpaApi::BASE_URL . "player/search?api_key=" . $this->api_key . $fragment . $param;
    $results = $this->makeRequest($url);

    // When search results are found, the API returns an array of players in the
    // 'search' property. When no results are found, it returns the string
    // 'No players found'.
    if (!is_array($results->search)) {
      return FALSE;
    }
    return $results;
  }
}

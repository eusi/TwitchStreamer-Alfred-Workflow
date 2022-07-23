<?php
  //Twitch API now requires a specific client id per application/developer
  $clientId = "fzilmsdg3isch2r0zeu1lss52md0xr";
  $clientSecret = "a9r8vcy9bqp0gxeko6y84vrywuccp5";
  $twitchAPI = "https://api.twitch.tv/helix";
  $twitchURL = "https://www.twitch.tv/";

  $sessionFile = __DIR__ . '/auth.json';

  /**
   * Initialize TwitchAPI
   * 
   */
  function init() {
    
    getAccessToken();
  }

  /**
   * Get streams from Twitch.tv sorted by number of viewers descending.
   * This is used for e.g. twtop
   * 
   * @param int $limit - maximum number of results
   */
  function getTwitchStreams( $limit ) {

    global $twitchURL;

    $ch = getDataByUrl( "/streams?first=" . $limit );
    $data = curl_exec( $ch );
    $info = curl_getinfo( $ch );
    curl_close( $ch );

    if ($info['http_code'] == 200) {
      $data = json_decode($data);
      $games = array();

      if($data != null) {
        $i = 0;
        foreach ($data->data as $stream) {
          $games[$i]["title"] = $stream->title;
          $games[$i]["streamer"] = $stream->user_name;
          $games[$i]["viewers"] = $stream->viewer_count;
          $games[$i]["game"] = $stream->game_name;
          $games[$i]["url"] = $twitchURL . $stream->user_login;
          $i++;
        }
      }
      return $games;
    }

    echo 'Failed with ' . $info['http_code'];
    return null;
  }

  /**
   * Get top games.
   * This is used for e.g. twgames
   * 
   * @param int $limit - maximum number of results
   */
  function getTopGames( $limit ) {

    $ch = getDataByUrl( "/games/top?first=" . $limit );
    $data = curl_exec( $ch );
    $info = curl_getinfo( $ch );
    curl_close( $ch );

    if ($info['http_code'] == 200) {
      $data = json_decode($data);
      $games = array();

      if($data != null) {
        $i = 0;
        foreach ($data->data as $stream) {
          $games[$i]["game"] = $stream->name;
          $games[$i]["top"] = "#".$i;
          $i++;
        }
      }
      return $games;
    }

    echo 'Failed with ' . $info['http_code'];
    return null;
  }

  /**
   * Search for games and channels.
   * This is used for e.g. tw *
   * 
   * @param String $q - stream or game name or at least a part of it
   * @param int $limit - maximum number of results
   */
  function searchStream( $q, $limit ) {

    global $twitchURL;

    $ch = getDataByUrl( "/search/channels?query=" . $q . "&first=" . $limit);
    $data = curl_exec( $ch );
    $info = curl_getinfo( $ch );
    curl_close( $ch );

    if ($info['http_code'] == 200) {
      $data = json_decode($data);
      $games = array();

      if($data != null) {
        $i = 0;
        foreach ($data->data as $stream) {
          $games[$i]["title"] = $stream->title;
          $games[$i]["streamer"] = $stream->display_name;
          $games[$i]["viewers"] = "";//$stream->viewer_count;
          $games[$i]["game"] = $stream->game_name;
          $games[$i]["url"] = $twitchURL . $stream->broadcaster_login;
          $i++;
        }
      }
      return $games;
    }

    echo 'Failed with ' . $info['http_code'];
    return null;
  }

  /**
   * Get a list of games sorted by number of viewers descending.
   * This is used for e.g. twcover
   * 
   * @param int $limit - maximum number of results
   */
  function getTwitchGames( $limit ) {

    $ch = getDataByUrl( "/games/top?first=" . $limit );
    $data = curl_exec( $ch );
    $info = curl_getinfo( $ch );
    curl_close( $ch );

    if ($info['http_code'] == 200) {
      $data = json_decode($data);
      $games = array();

      if($data != null) {
        $i = 0;
        foreach ($data->data as $stream) {
          $games[$i]["name"] = $stream->name;
          $i++;
        }
      }
      return $games;
    }

    echo 'Failed with ' . $info['http_code'];
    return null;
  }

  /**
   * Get's the access token from Twitch.tv OAUTH API.
   * 
   */
  function getAccessToken() {

    global $clientId;
    global $clientSecret;
    global $twitchAPI;
    global $sessionFile;

    session_start();

    $json = file_get_contents( $sessionFile );
    $jsonDecoded = json_decode($json, true);
    $_SESSION['token'] = $jsonDecoded['access_token'];
    $_SESSION['expire_time'] = $jsonDecoded['expire_time'];

    // check if token needs to be created
    if( !isset($_SESSION['token']) || $_SESSION['expire_time'] <= time() ) {

      $_SESSION['token'] = 0;

      $ch = curl_init('https://id.twitch.tv/oauth2/token');
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_POSTFIELDS, array(
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'grant_type' => 'client_credentials'
      ));

      // fetch the data
      $r = curl_exec($ch);
      $i = curl_getinfo($ch);
      // close the request
      curl_close($ch);

      if ($i['http_code'] == 200) {
        $token = json_decode($r);
        $token->expire_time = $token->expires_in + time();

        if (json_last_error() == JSON_ERROR_NONE) {
          $_SESSION['token'] = $token->access_token;
          
          file_put_contents( $sessionFile, json_encode($token), JSON_PRETTY_PRINT );
          exit;
        } else {
          echo 'Failed to parse the JSON at code for token exchange';
        }
      } else {
        echo 'Error for token exchange: ' . print_r($r, true);
      }
    }
  }

  /**
   * Get stream data by url with available token.
   * 
   * @param String $url - url of Twitch API, e.g. "/streams/"
   */
  function getDataByUrl( $url ) {

    global $clientId;
    global $twitchAPI;
    global $sessionFile;

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $json = file_get_contents( $sessionFile );
    $jsonDecoded = json_decode($json, true);
    $_SESSION['token'] = $jsonDecoded['access_token'];
    $_SESSION['expire_time'] = $jsonDecoded['expire_time'];

    $ch = curl_init();
    $UURL = $twitchAPI . $url;
    curl_setopt($ch, CURLOPT_URL, $UURL);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Authorization: Bearer '.$_SESSION['token'],
      'Client-Id: '.$clientId
    ));

    return $ch;
  }

  /**
   * Functions checks if cover/icon already exists.
   * 
   * @param String $game - game name
   */
  function checkCover( $game ) {

    $game = rawurlencode( $game );
    $file = 'images/'.$game.'.jpg';

    if( file_exists( $file )) {
      return $file;
    }
    elseif ( downloadCover ( $game ) ) {  
      return $file;
    }
    else {
      return 'icon.png';
    }
  }

  /**
   * Functions checks if cover already exists (twcover only).
   * 
   * @param String $game - game name
   */
  function checkingCovers( $game ) {

    $game = rawurlencode( $game );
    $file = 'images/'.$game.'.jpg';

    if( !file_exists( $file) ) {
      return downloadCover ( $game );
    }
  }

  /**
   * Functions downloads cover from Twitch.tv.
   * 
   * @param String $game - game name
   */
  function downloadCover( $game ) {

    $ok = false;
    $url = "https://static-cdn.jtvnw.net/ttv-boxart/" . $game . "-92x128.jpg";
    
    try {
      if( urlExists( $url ) ) {
        $temp = "images/". $game .".jpg";

        //using curl instead now
        $ch = curl_init($url);
        $fp = fopen($temp, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $ok = $game;
      }
    } 
    catch (Exception $e) {
      echo "Error: " . $e->getMessage();
    }
    
    return $ok;
  }

  /**
   * Functions checks if url exists.
   * 
   * @param String $game - game name
   */
  function urlExists($file) {

    $file_headers = @get_headers($file);
    if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
      return false;
    }
    else {
      return true;
    }
  }

  /**
   * Functions replaces file_get_contents (get content of url).
   * 
   * @param String $url
   */
  function url_get_contents($url) {

    if (function_exists('curl_exec')) { 
      $conn = curl_init($url);
      curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
      curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
      curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
      $url_get_contents_data = (curl_exec($conn));
      curl_close($conn);
    }
    elseif(function_exists('file_get_contents')) {
      $url_get_contents_data = file_get_contents($url);
    }
    elseif(function_exists('fopen') && function_exists('stream_get_contents')) {
      $handle = fopen ($url, "r");
      $url_get_contents_data = stream_get_contents($handle);
    }
    else {
      $url_get_contents_data = false;
    }
  
    return $url_get_contents_data;
  } 

?>
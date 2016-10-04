<?php
	//Twitch API now requires a specific client id per application/developer
	$clientId = "&client_id=pq1763hfd37807lw0z8pqum3isqr4hd";

    /**
     * Add all stream data from the Twitch.tv JSON API.
     * 
     * @param int $limit - maximum number of streams sorted by number of viewers descending
     */
	function getTwitchStreams( $limit ) {

		global $clientId;
		//https://github.com/justintv/Twitch-API/blob/master/v3_resources/streams.md
		$url = 'https://api.twitch.tv/kraken/streams?limit=' . $limit . $clientId; 
		$json = url_get_contents($url);

		if (!$json) {
			return false;
		}

		$data = json_decode($json, false)->streams;
		$streams = array();
		
		if($data != null) {
			foreach ($data as $key => $stream) {
				$streams[$key]["title"] = $stream->channel->status;
				$streams[$key]["streamer"] = $stream->channel->display_name;
				$streams[$key]["viewers"] = $stream->viewers;
				$streams[$key]["game"] = $stream->game;
				$streams[$key]["url"] = $stream->channel->url;
			}
		}

		return $streams;
	}

    /**
     * Add all top games data from the Twitch.tv JSON API.
     * 
     */
	function getTopGames( $limit ) {

		global $clientId;
		$url = 'https://api.twitch.tv/kraken/games/top?limit=' . $limit . $clientId; 
		$json = url_get_contents($url);

		if (!$json) {
			return false;
		}

		$data = json_decode($json, false)->top;
		$games = array();
		
		if($data != null) {
			foreach ($data as $key => $game) {
				$games[$key]["viewers"] = $game->viewers;
				$games[$key]["game"] = $game->game->name;
				$games[$key]["name"] = $game->game->name;
			}
		}
		return $games;
	}

    /**
     * Search streams and add channel stream data from the Twitch.tv JSON API.
     * 
     * @param String $q - stream or game name or a part of it
     * @param int $limit
     */
	function searchStream( $q, $limit ) {

		global $clientId;
		//https://github.com/justintv/Twitch-API/blob/master/v3_resources/search.md
		$url = 'https://api.twitch.tv/kraken/search/streams?q=' . $q . '&limit=' . $limit . $clientId; 
		$json = url_get_contents($url);

		if (!$json) {
			return false;
		}

		$data = json_decode($json, false)->streams;
		$streams = array();
		
		if($data != null) {
			foreach ($data as $key => $stream) {
				$streams[$key]["title"] = $stream->channel->status;
				$streams[$key]["streamer"] = $stream->channel->display_name;
				$streams[$key]["viewers"] = $stream->viewers;
				$streams[$key]["game"] = $stream->game;
				$streams[$key]["url"] = $stream->channel->url;
			}
		}
		return $streams;
	}

    /**
     * Search games and add games data from the Twitch.tv JSON API.
     * 
     * @param String $q - game name or a part of it
     * @param int $limit
     */
	function searchGame( $q, $limit ) {

		global $clientId;
		//https://github.com/justintv/Twitch-API/blob/master/v3_resources/search.md
		$url = 'https://api.twitch.tv/kraken/streams?game=' . $q . '&limit=' . $limit . '&live=true' . $clientId; 
		$json = url_get_contents($url);

		if (!$json) {
			return false;
		}

		$data = json_decode($json, false)->streams;
		$streams = array();
		
		if($data != null) {
			foreach ($data as $key => $stream) {
				$streams[$key]["title"] = $stream->channel->status;
				$streams[$key]["streamer"] = $stream->channel->display_name;
				$streams[$key]["viewers"] = $stream->viewers;
				$streams[$key]["game"] = $stream->game;
				$streams[$key]["url"] = $stream->channel->url;
			}
		}
		return $streams;
	}

    /**
     * Search games and return game list.
     * 
     */
	function getTwitchGames() {

		global $clientId;
		//https://github.com/justintv/Twitch-API/blob/master/v3_resources/games.md
		$url = 'https://api.twitch.tv/kraken/games/top?limit=100' . $clientId; 
		$json = url_get_contents($url);

		if (!$json) {
			return false;
		}

		$data = json_decode($json, false)->top;
		$games = array();
		
		if($data != null) {
			foreach ($data as $key => $game) {
				$games[$key]["name"] = $game->game->name;
			}
		}
		return $games;
	}
	
    /**
     * Functions checks if cover already exists.
     * 
     * @param String $game - game name
     */
	function checkCover ( $game ) {

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
	function checkingCovers ( $game ) {

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
	function downloadCover ( $game ) {

		$ok = false;
		$url = "http://static-cdn.jtvnw.net/ttv-boxart/" . $game . "-92x128.jpg";
		
		try 
		{
			if( urlExists( $url ) ) {

				/*$arrContextOptions=array(
				    "ssl"=>array(
				        "cafile" => "/bundle/cacert.pem", //https://curl.haxx.se/docs/caextract.html
				        "verify_peer"=> true,
				        "verify_peer_name"=> true,
				    ),
				);*/

				$temp = "images/". $game .".jpg";
				//file_put_contents( $temp, fopen( $url , 'r'));

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
		catch (Exception $e) 
		{	
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
	function url_get_contents ($url) {

	    if (function_exists('curl_exec'))
	    { 
	        $conn = curl_init($url);
            //curl_setopt($ch, CURLOPT_HEADER, false);
	        //curl_setopt($conn, CURLOPT_SSLVERSION,3); 
	        //curl_setopt($conn, CURLOPT_SSL_VERIFYHOST, true);
	        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
	        curl_setopt($conn, CURLOPT_FRESH_CONNECT,  true);
	        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
	        $url_get_contents_data = (curl_exec($conn));
	        curl_close($conn);
	    }
	    elseif(function_exists('file_get_contents'))
	    {
	        $url_get_contents_data = file_get_contents($url);
	    }
	    elseif(function_exists('fopen') && function_exists('stream_get_contents'))
	    {
	        $handle = fopen ($url, "r");
	        $url_get_contents_data = stream_get_contents($handle);
	    }
	    else
	    {
	        $url_get_contents_data = false;
	    }
		
		return $url_get_contents_data;
	} 

?>
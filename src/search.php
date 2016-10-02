<?php
	//twitch api now requires a specific client id per application/developer
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

		$data = json_decode($json)->top;
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

		$data = json_decode($json)->streams;
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

		$data = json_decode($json)->streams;
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

		$data = json_decode($json)->top;
		$games = array();
		
		if($data != null) {
			foreach ($data as $key => $game) {
				$games[$key]["name"] = $game->game->name;
			}
		}
		return $games;
	}
	
    /**
     * functions checks if cover already exists
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
     * functions checks if cover already exists (twcover only)
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
     * functions downloads cover from twitch.tv
     * 
     * @param String $game - game name
     */
	function downloadCover ( $game ) {

		$ok = false;
		$url = "http://static-cdn.jtvnw.net/ttv-boxart/" . $game . "-92x128.jpg";
		
		if( urlExists( $url ) ) {
			$temp = "images/". $game .".jpg";
			file_put_contents( $temp, fopen( $url , 'r'));
			$ok = $game;
		}

	 	return $ok;
	}

    /**
     * functions checks if url exists
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
     * functions replaces file_get_contents (get content of url)
     * 
     * @param String $url
     */
	function url_get_contents ($Url) {
	    if (!function_exists('curl_init')){ 
	        die('CURL is not installed!');
	    }

	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, $Url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $output = curl_exec($ch);
	    
	    curl_close($ch);

    	return $output;
    }

?>
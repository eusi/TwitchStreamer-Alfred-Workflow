<?php

    /**
     * Add all stream data from the Twitch.tv JSON API.
     * 
     * @param int $limit 
	 * (maximum number of streams sorted by number of viewers descending)
     */
	function getTwitchStreams( $limit ) {
		//https://github.com/justintv/Twitch-API/blob/master/v3_resources/streams.md
		$url = 'https://api.twitch.tv/kraken/streams?limit=' . $limit; 
		$json = file_get_contents($url);

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

	/*
	// Alternative
	function getTwitchStreams() {
		$url = 'http://api.justin.tv/api/stream/list.json?category=gaming';
		
		$json = file_get_contents($url);

		if (!$json) {
			return false;
		}

		$data = json_decode($json);
		$streams = array();
		
		if($data != null) {
			foreach ($data as $key => $stream) {
				$streams[$key]["title"] = $stream->title;
				$streams[$key]["streamer"] = strtoupper( $stream->channel->title );
				$streams[$key]["viewers"] = $stream->channel_count;
				$streams[$key]["game"] = $stream->channel->meta_game;
				$streams[$key]["url"] = $stream->channel->channel_url;
			}
		}
		return $streams;
	}*/


    /**
     * Search streams and add channel stream data from the Twitch.tv JSON API.
     * 
     * @param String $q
     * @param int $limit
     */
	function searchStream( $q, $limit ) {
		//https://github.com/justintv/Twitch-API/blob/master/v3_resources/search.md
		$url = 'https://api.twitch.tv/kraken/search/streams?q=' . $q . '&limit=' . $limit; 
		$json = file_get_contents($url);

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
     * @param String $q
     * @param int $limit
     */
	function searchGame( $q, $limit ) {
		//https://github.com/justintv/Twitch-API/blob/master/v3_resources/search.md
		$url = 'https://api.twitch.tv/kraken/search/games?q=' . $q . '&type=suggest&live=true&limit=' . $limit; 
		$json = file_get_contents($url);

		if (!$json) {
			return false;
		}

		$data = json_decode($json)->games;
		$games = array();
		
		if($data != null) {
			foreach ($data as $key => $game) {
				$games[$key]["name"] = $game->name;
				$games[$key]["popularity"] = $game->popularity;
			}
		}
		return $games;
	}

?>
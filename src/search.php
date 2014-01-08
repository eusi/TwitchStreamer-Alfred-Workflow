<?php

/**
 * Parses and stores information about a Twitch.tv stream.
 *
 * @version    1.0
 * @author     Christoph Friegel <eusi.cf@gmail.com>
 */

    /**
     * stream class
     */
	require_once('stream.php');

    /**
     * full streamer list (category: gaming!)
     */
	$url = 'http://api.justin.tv/api/stream/list.json?featured=true&category=gaming';


    /**
     * method for sorting a multid. array
     */
	function aasort (&$array, $key) {
	    $sorter=array();
	    $ret=array();
	    reset($array);
	    foreach ($array as $ii => $va) {
	        $sorter[$ii]=$va[$key];
	    }
	    arsort($sorter); //asort
	    foreach ($sorter as $ii => $va) {
	        $ret[$ii]=$array[$ii];
	    }
	    $array=$ret;
	}

    /**
     * method to get all streamer who are online at the moment
     */
	function getAllLOLStreamer() {
		global $url;
		$json = file_get_contents($url);

		if (!$json) {
			return false;
		}

		$obj = json_decode($json);
		$newObj = array();
		
		foreach ($obj as $ele) {
			array_push( $newObj, $ele->{'channel'}->{'channel_url'} );
		}
		return $newObj;
	}

    /**
     * method to create a correct 'stream'-obj á streamer
     */
	function createStreamObjects($allStreamer) {
		$mystreams = array();

		for ($i = 0; $i < count($allStreamer); $i++) {

			$mystreams[] = new Stream(array(
				'url' => "http://www.twitch.tv/" . substr($allStreamer[$i], 21), // parses URLs just fine...
				'myname' => substr($allStreamer[$i], 21)
			));
		}

		return $mystreams;
	}

    /**
     * method to create a streamer-list (normalized and sorted)
     */
	function createStreamerList($mystreams) {
		$thisArrayIsGood = array();
		$tempRay = array();

		foreach ($mystreams as $stream) {
		    if ($stream->isLive()) {
				$tempRay['url'] = $stream->url;
				$tempRay['title'] = $stream->stream_title;
				$tempRay['viewers'] = $stream->stream_viewers;
				$tempRay['channel'] = strtoupper($stream->channel);
				//$tempRay['avatar'] = $stream->stream_avatar_tiny;
				$tempRay['game'] = $stream->stream_game;
				array_push($thisArrayIsGood, $tempRay);
		    }
		}
		//aasort should not be necessary because of featured=true and new twitch api which focus viewer count
		//aasort($thisArrayIsGood,"viewers");

		return $thisArrayIsGood;
	}

    /**
     * start method
     */
	function main() {
		$streamer = array();
		$streamer = getAllLOLStreamer();

		$streams = array();
		$streams = createStreamObjects( $streamer );

		$list = array();
		$list = createStreamerList( $streams );

		return $list;
	}

?>
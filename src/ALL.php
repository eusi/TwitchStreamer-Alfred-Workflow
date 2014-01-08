<?php
require_once('Stream.php');

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

function getAllLOLStreamer() {
	$url = 'http://api.justin.tv/api/stream/list.json?games=League%20of%20Legends';
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

$allStreamer = getAllLOLStreamer();

$mystreams = array();
$thisArrayIsGood = array();
$tempRay = array();

for ($i = 0; $i < count($allStreamer); $i++) {

	$mystreams[] = new Stream(array(
		'url' => "http://www.twitch.tv/" . substr($allStreamer[$i], 21),
		'myname' => substr($allStreamer[$i], 21)
	));
}

foreach ($mystreams as $stream) {
    if ($stream->isLive()) {
		$tempRay['url'] = $stream->url;
		$tempRay['title'] = $stream->stream_title;
		$tempRay['viewers'] = $stream->stream_viewers;
		$tempRay['avatar'] = $stream->stream_avatar_tiny;
		$tempRay['game'] = $stream->stream_game;
		array_push($thisArrayIsGood, $tempRay);
    }
}

aasort($thisArrayIsGood,"viewers");

echo "<table width=100% border=1>";
echo "<tr><td><b>Game</b></td><td><b>Viewer</b></td><td><b>Im‰‰‰ge</b></td><td><b>Title</b></td><td><b>Stream</b></td></tr>";
foreach ($thisArrayIsGood as $streamer) {
	if(isset($_GET['game']) && $_GET['game'] != "") {
			if(strpos( strtolower($streamer['game']), strtolower($_GET['game'])) === false)
				continue;
	}


		echo "<tr>";
		echo "<td class='game'>";
		echo $streamer['game'];
		echo "</td>";echo "<td class='viewers'>";
		echo $streamer['viewers'];
		echo "</td>";echo "<td class='avatar'>";
		echo "<img src='" . $streamer['avatar'] . "' class='avatarimg' />";
		echo "</td>";echo "<td class='title'>";
		echo $streamer['title'];
		echo "</td>";echo "<td class='url'>";
		echo "<a href='" .$streamer['url']. "' target='_blank' class='streamurl'>" .$streamer['url']. "</a>";
		echo "</td>";
		echo "</tr>";
    }
echo "</table>";
?>
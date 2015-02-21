TwitchStreamer Workflow for [Alfred 2](http://www.alfredapp.com)
==============================

## In Short
* Search Twitch.TV streams and watch in [VLC](http://www.videolan.org/vlc/index.html) ([Livestreamer](https://github.com/chrippa/livestreamer) required)

* keywords: `tw`, `twtop`, `twgame`, `twcover`

* `twtop`: shows top live streams on twitch.tv with some informations

* `tw`: search specific live stream on twitch.tv with some informations (argument: game or streamer)

* `twgame`: search games, result leads to `tw ` (optional)

* `twcover`: download and convert game covers/posters (optional, you only have to use it once you want to download many new game covers)

* example: `tw star` finds streamer like "starman" and "starcraft" streams


## Details

Check who is streaming on Twitch.Tv (category gaming) and watch your favorite stream via [Livestreamer](https://github.com/chrippa/livestreamer) on [VLC](http://www.videolan.org/vlc/index.html) (no lags anymore, thanks to buffering).

The main keyword is `tw` and the second word is the game or stream you want to watch (examples: `tw voyboy` or `tw league of legends` or also simply `tw league`). Alternative: Use keyword `twtop` to see the current TOP streams. Limit of streams is changable, 50 by default.

With `enter` you can open the stream via Terminal ([Livestreamer](https://github.com/chrippa/livestreamer)) on [VLC](http://www.videolan.org/vlc/index.html). The Terminal has to be open (in background) while you are watching the stream. If you close the Terminal the stream will be shutting down.

The streamer list is sorted by number of viewers descending. The quality of the stream is "high". If you want to change it to best (e.g.), feel free to open the existing "Terminal Command" (alfred, workflow-window) and modify the Livestreamer line.

Optional: If you have no idea which game you want to watch or you want to search a game, use `twgame`. For example: `twgame world` it lists something like World of Warcraft. If you click on one of them like "World War", it leads you to `tw World War` which shows you live streams in this category.

Optional 2: If you deleted your game covers/posters folder you should use `twcover` to download all the top game covers. Otherwise `tw ` or `twtop` will do it, but it takes more time and downloads less covers. 


## Screenshot 1: Searching TOP STREAMS
![Workflow Screenshot](https://raw2.github.com/eusi/alfred2-twitch-streamer/master/screenshots/workflow1.jpg)

## Screenshot 2: Searching GAME
![Workflow Screenshot](https://raw2.github.com/eusi/alfred2-twitch-streamer/master/screenshots/workflow2.jpg)

## Screenshot 3: Searching STREAM
![Workflow Screenshot](https://raw2.github.com/eusi/alfred2-twitch-streamer/master/screenshots/workflow3.jpg)

## Screenshot 4: Browsing GAMES
![Workflow Screenshot](https://raw2.github.com/eusi/alfred2-twitch-streamer/master/screenshots/workflow4.jpg)

(enter "Warframe" leads to screenshot 2: `tw Warframe`)

## Screenshot 5: Downloading GAME COVERS/POSTERS
![Workflow Screenshot](https://raw2.github.com/eusi/alfred2-twitch-streamer/master/screenshots/workflow5.png)

## Screenshot 6: Watching STREAM
![Workflow Screenshot](https://raw2.github.com/eusi/alfred2-twitch-streamer/master/screenshots/workflow6.jpg)


## Download

Required: [Livestreamer](https://github.com/chrippa/livestreamer) and [VLC](http://www.videolan.org/vlc/index.html)

Made in/with OSX 10.9.1, livestreamer 1.11.1, VLC 2.1.2, PHP 5.3, AppleScript 2.3, Twitch-API/v3

Supports AlleyOop/[Monkey Patch](http://www.alfredforum.com/topic/2218-monkey-patch-update-alfred-workflows-via-alleyoop/) (workflow updater).

**[DOWNLOAD HERE](https://raw2.github.com/eusi/alfred2-twitch-streamer/master/workflow/TwitchStreamer.alfredworkflow)**


## Issues

* it takes a lil time between entries because of the twitch.tv-api


## Contacts

If you have found bugs/issues or if you just want to say "hello" so send me an email: eusi.cf@gmail.com

<a href="https://github.com/eusi"><img src="https://2.gravatar.com/avatar/d954b2ec10b10436505ae62fe972df97?d=https%3A%2F%2Fidenticons.github.com%2Fe098fc2b57681a6f25ba17badf99aa6f.png&r=x&s=440" alt="eusi" title="eusi" width="100" height="100"></a>


## License

GNU General Public License version 3



#Changelog

## 1.6.0

* switched cover-format from png to jpg (due to issue#1)
* cleaned up code

## 1.5.0 - 1.5.2

* Big Update
* Added `twgame` 
* Added `twcover` (downloads and converts game covers)
* fixed sort order bug, thanks to tyler and andrew
* Already added some covers

## 1.3.0

* Big Update (reworked the whole code)
* Added `twtop`
* `tw` searches more efficient and games as well as streams


## 1.1.0

* First real release without webserver.
* Added AlleyOop/[Monkey Patch](http://www.alfredforum.com/topic/2218-monkey-patch-update-alfred-workflows-via-alleyoop/) support (workflow updater).
* Downgraded Workflows from 0.32 to 0.3 due to a sort-bug


## 0.5.0

* First release with all functions. Based on stream-parsing on webserver.

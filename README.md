TwitchStreamer Workflow for [Alfred 2](http://www.alfredapp.com)
==============================

## In Short
* keywords: `tw`, `twtop`

* `twtop: shows top (online) streamer on twitch.tv with some informations

* `tw: shows specific (online) streamer on twitch.tv with some informations

* opens the selected stream in VLC (Livestreamer required)

* example: `tw star` finds streamer like "starman" and "starcraft" streams


## Description

Check who is streaming on Twitch.Tv (category gaming) and watch your favorite stream via [Livestreamer](https://github.com/chrippa/livestreamer) on [VLC](http://www.videolan.org/vlc/index.html) (no lags anymore, thanks to buffering).

The keyword is `tw` and the second word is the game you want to watch (example: `tw league of legends` or simply `tw league`).

With `enter` you can open the stream via Terminal (Livestreamer) on VLC. The Terminal has to be open (in background) while you watch the stream. If you close the Terminal the stream will be shutting down.

The streamer list is sorted by featured streamer (first) and number of viewers descending. The quality of the stream is "high". If you want to change it to best (e.g.), feel free to open the existing "Terminal Command" (alfred, workflow-window) and modify the Livestreamer line.


## Screenshot 1: Searching
![Workflow Screenshot](https://github.com/eusi/alfred2-twitch-streamer/blob/master/screenshots/workflow1.jpg?raw=true)

## Screenshot 2: Watching
![Workflow Screenshot](https://github.com/eusi/alfred2-twitch-streamer/blob/master/screenshots/workflow2.jpg?raw=true)


## Download
**[DOWNLOAD](https://github.com/eusi/alfred2-twitch-streamer/blob/master/workflow/TwitchStreamer.alfredworkflow?raw=true)**


## Issues

* it takes a bit time between entries because of the twitch.tv-api

* it would be great to have an icon á game, but without web images its a lil overkill I think (twitch.tv streams more than 250 different games per day = 250 icons + future support for new games), maybe we could create game icons for the most famous games

* sorting of `twtop` seems to be a bit broken


## Contacts

If you have found bugs/issues or if you just want to say "hello" so send me an email: eusi.cf@gmail.com

<a href="https://github.com/eusi"><img src="https://2.gravatar.com/avatar/d954b2ec10b10436505ae62fe972df97?d=https%3A%2F%2Fidenticons.github.com%2Fe098fc2b57681a6f25ba17badf99aa6f.png&r=x&s=440" alt="eusi" title="eusi" width="100" height="100"></a>


## License

GNU General Public License version 3



#Changelog

## 1.3.0

* Big Update (reworked the whole code)
* Added `twtop`
* `tw` searches more efficient and games as well as streams


## 1.1.0

* First real release without webserver.
* Added Alleyoop support.
* Downgraded Workflows from 0.32 to 0.3 due to a sort-bug


## 0.5.0

* First release with all functions. Based on stream-parsing on webserver.

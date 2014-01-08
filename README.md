TwitchStreamer Workflow for [Alfred 2](http://www.alfredapp.com)
==============================

Check who is streaming on Twitch.Tv (category gaming) and watch your favorite stream via [Livestreamer](https://github.com/chrippa/livestreamer) on [VLC](http://www.videolan.org/vlc/index.html).

The keyword is `tw` and the second word is the game you want to watch (example: `tw league of legends` or simply `tw league`).

With `enter` you can open the stream via Terminal (Livestreamer) on VLC. The Terminal has to be open (in background) while you watch the stream. If you close the Terminal the stream will be shutting down.

The streamer list is sorted by featured streamer (first) and number of viewers descending. The quality of the stream is "high". If you want to change it to best (e.g.), feel free to open the existing "Terminal Command" (alfred, workflow-window) and modify the Livestreamer line.


## Screenshot 1: Searching
![Workflow Screenshot](https://github.com/eusi/alfred2-twitch-streamer/blob/master/workflow/workflow1.jpg?raw=true)

## Screenshot 2: Watching
![Workflow Screenshot](https://github.com/eusi/alfred2-twitch-streamer/blob/master/workflow/workflow2.jpg?raw=true)


## Download
**[DOWNLOAD](https://github.com/eusi/alfred2-twitch-streamer/blob/master/workflow/TwitchStreamer%20v1.0.alfredworkflow?raw=true)**


## Issues

* it takes some time between entries (delay 5~10 Sec.) because of the twitch.tv-api

* it would be great to have an icon á game, but without web images its a lil overkill I think (twitch.tv streams more than 250 different games per day = 250 icons + future support for new games), maybe we could create game icons for the most famous games


## Contact

If you have found bugs/issues or if you just want to say "hello" so send me an email: eusi.cf@gmail.com

<a href="https://github.com/ruedap"><img src="https://dl.dropboxusercontent.com/u/281168/images/github-ruedap-avatar-1500x1500.png" alt="ruedap" title="ruedap" width="100" height="100"></a>


## License

GNU General Public License version 3



#Changelog

## 1.1.0

* First real release without webserver.
* Added Alleyoop support.
* Downgraded Workflows from 3.2 to 3.0 due to a sort-bug


## 0.5.0

* First release with all functions. Based on stream-parsing on webserver.
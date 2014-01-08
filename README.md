TwitchStreamer Workflow for [Alfred 2](http://www.alfredapp.com)
==============================

Check who is streaming on Twitch.Tv (category gaming) and watch your favorite stream via [Livestreamer](https://github.com/chrippa/livestreamer) on [VLC](http://www.videolan.org/vlc/index.html).

The keyword is "tw" and the second word is the game you want to watch (example: `tw league of legends` or simply `tw league`).

With `enter` you can open the stream via Terminal (Livestreamer) on VLC. The Terminal has to be open (in background) while you watch the stream. If you close the Terminal the stream will be shutting down.

The streamer list is sorted by featured streamer and number of viewers descending. The quality of the stream is "high". If you want to change it to best (e.g.), feel free to open the existing "Terminal Command" (alfred, workflow-window) and modify the Livestreamer line.


### Screenshot 1
![Workflow Screenshot](https://github.com/eusi/alfred2-twitch-streamer/blob/master/workflow/workflow1.jpg?raw=true)

### Screenshot 2

![Workflow Screenshot](https://github.com/eusi/alfred2-twitch-streamer/blob/master/workflow/workflow2.jpg?raw=true)


### Download
**[DOWNLOAD](https://github.com/eusi/alfred2-twitch-streamer/blob/master/workflow/TwitchStreamer%20v1.0.alfredworkflow)**


### Known Bugs

* sorted list is a bit broken sometimes (it puts last one before first)

* it takes some time between entries (delay ~10 Sec.) because of twitch.tv-api


### Contact

If you have found more bugs or if you just want to say "hello" so send me an email: eusi.cf@gmail.com

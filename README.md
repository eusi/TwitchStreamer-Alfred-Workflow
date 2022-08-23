[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=GPC2XKCNED664)


TwitchStreamer Workflow for [Alfred](http://www.alfredapp.com)
==============================

## Summary
* Search Twitch.tv streams and watch via [VLC](http://www.videolan.org/vlc/index.html) or [mpv](https://mpv.io/) or [IINA](https://iina.io) ([Streamlink (former Livestreamer)](https://streamlink.github.io/index.html) required)

* keywords: `tw`, `twtop`, `twgames`, optional: twcover`

* `twtop`: show top live streams (sorted by number of current viewers)

* `twgames`: show top live games streamed (sorted by number of current viewers)

* `tw`: search specific live channel/game

* `twcover`: download/preload game icons for better user experience

* browse philosophy: `twtop` or `twgames` for getting the most wanted streams on twitch.tv

* search philosophy: `tw star` finds streamer like "starman" and "starcraft"-games streams


## Details

Check who is streaming on Twitch.tv (category gaming) and watch your favorite stream via [Streamlink](https://streamlink.github.io/index.html) on [VLC](http://www.videolan.org/vlc/index.html) or [mpv](https://mpv.io/) or [IINA](https://iina.io) (no lags anymore, thanks to buffering).

The main keyword is `tw` and the second word is the game or stream you want to watch (examples: `tw voyboy` or `tw league of legends` or also simply `tw league`). Alternative: Use keyword `twtop` to see the current TOP streams or `twgames` for the current TOP streamed games. Limit of streams is changable via specific workflow ($limit-variable).

With `enter` you can open the stream via [Streamlink](https://streamlink.github.io/index.html) on [VLC](http://www.videolan.org/vlc/index.html) or [mpv](https://mpv.io/) or [IINA](https://iina.io). 

The streamer list is sorted by number of viewers descending. The quality of the stream is "high". If you want to change it to best (e.g.), feel free to open the existing "Terminal Command" (alfred, workflow-window) and modify the streamlink line.

Optional: If you deleted your game icons/covers folder (because many of them are outdated) you should use `twcover` to download all the top game covers. Otherwise `tw ` or `twtop` will do it, but it takes more time and downloads less covers at the same time. 

## Screenshot: Entry point
![Workflow Screenshot](https://raw.githubusercontent.com/eusi/TwitchStreamer-Alfred-Workflow/master/screenshots/workflow0.jpg)

## Screenshot: Browsing TOP STREAMS
![Workflow Screenshot](https://raw.githubusercontent.com/eusi/TwitchStreamer-Alfred-Workflow/master/screenshots/workflow1.jpg)

## Screenshot: Browsing TOP GAMES
![Workflow Screenshot](https://raw.githubusercontent.com/eusi/TwitchStreamer-Alfred-Workflow/master/screenshots/workflow2.jpg)

## Screenshot: Searching STREAM (Channel or Game)
![Workflow Screenshot](https://raw.githubusercontent.com/eusi/TwitchStreamer-Alfred-Workflow/master/screenshots/workflow4.jpg)

## Screenshot: Downloading GAME COVERS/POSTERS
![Workflow Screenshot](https://raw.githubusercontent.com/eusi/TwitchStreamer-Alfred-Workflow/master/screenshots/workflow5.jpg)
![Workflow Screenshot](https://raw.githubusercontent.com/eusi/TwitchStreamer-Alfred-Workflow/master/screenshots/workflow5-.jpg)

## Screenshot: Watching STREAM
![Workflow Screenshot](https://raw.githubusercontent.com/eusi/TwitchStreamer-Alfred-Workflow/master/screenshots/workflow6.jpg)


## Download

Required: [Alfred](https://www.alfredapp.com/), [Streamlink](https://streamlink.github.io/index.html) and [VLC](http://www.videolan.org/vlc/index.html) or [mpv](https://mpv.io/) or [IINA](https://iina.io). 

Made/Tested in/with OSX 10.15.7 and macOS 12.4, Streamlink 2.4.0, VLC 3.0.16, PHP 7.3.11, AppleScript >2.5, Twitch API (Oct. 2021)

Supports [`OneUpdater`](https://github.com/vitorgalvao/alfred-workflows/tree/master/OneUpdater)/AlleyOop/[Monkey Patch](http://www.alfredforum.com/topic/2218-monkey-patch-update-alfred-workflows-via-alleyoop/) (workflow updater).

**[DOWNLOAD HERE](https://raw.githubusercontent.com/eusi/TwitchStreamer-Alfred-Workflow/master/workflow/TwitchStreamer.alfredworkflow)**

## Issues

* it takes a lil time between entries because of the Twitch API


## Next

* Adding more information for "twgames": number of cannels and viewer (via get streams and game_-_id/user_id)

* Adding more information for "tw *": viewers (via get streams and broadcaster-id)


## Contacts

If you have found bugs/issues use the [issues tracker](https://github.com/eusi/TwitchStreamer-Alfred-Workflow/issues) or if you just want to say "hello" in addition so send me an email: eusi.cf@gmail.com

<a href="https://github.com/eusi"><img src="https://2.gravatar.com/avatar/d954b2ec10b10436505ae62fe972df97?d=https%3A%2F%2Fidenticons.github.com%2Fe098fc2b57681a6f25ba17badf99aa6f.png&r=x&s=440" alt="eusi" title="eusi" width="100" height="100"></a>


## License

GNU General Public License version 3



## Changelog

### 2.02

* Small update to make it work under Alfred 5

### 2.01

* Small update to make it work under Alfred 4

### 2.00

* Updated entire Twitch API to latest (incl. new oauth).
* Changed livestreamer to streamlink.
* Added VLC, MVP and IINA player support.
* Updated game covers.

### 1.82

* Changed streaming quality to Best (from High), because many streams will not work with High as default quality.
* Added new game covers.

### 1.81

* Updated jdfwarriors workflows.php for Alfred 3 support (fixed warnings), updated github project link to a generic one.

### 1.8

* Updated OneUpdater (thx@ vitorgalvao), replaced NSAppleScript by a normal RunScript(JS) that handles the latest installed Alfred (no matter if we switch to Alfred 4..5..6 in future), fixed connection issues by changing file_put_contents to curl and extending url_get_contents().

### 1.75

* Added [`OneUpdater`](https://github.com/vitorgalvao/alfred-workflows/tree/master/OneUpdater) ([forum post](http://www.alfredforum.com/topic/9224-oneupdater-—-update-workflows-with-a-single-node/)) that allows you to update the workflow out of the box (it checks for updates automatically every 15 days and downloads/opens new versions). Changed the usage of Terminal to Run Script (no opened terminal any longer during watching stream). Thx@ vitorgalvao.

### 1.74

* Replaced file_get_contents() by an own created url_get_contents() that is using curl. It seems file_get_contents(): https:// wrapper is disabled in the server configuration by allow_url_fopen=0 on the new macOS Sierra.

### 1.73

* Livestreamer has not been updated so far regarding Twitch API changes. => Added a workaround to the workflow without requiring a livestreamer update to make it work.

### 1.72

* Regarding twitch API changes, added client id to every request.
* Regarding twitch API changes, changed string encoding of images.
* Hence all game covers are renamed or replaced.

### 1.71

* Minor update.
* Added support for mpv playback (thx@ Jonathan Dahan)

### 1.7

* Changed vjpg-branch to master.
* Modified terminal command: Quits Terminal automatically.
* Deleted `twgame`.
* Added `twgames` (top streamed games by viewers).
* Added `twbygame`, similar to the old `twgame`.

### 1.6.1

* Modified terminal command: Terminal must no longer stay open (thx@ mclowe-directnic).
* Added some covers.
* Fixed updater (json).

### 1.6.0

* Switched cover-format from png to jpg (due to issue#1).
* Cleaned up code.

### 1.5.0 - 1.5.2

* Big Update.
* Added `twgame`.
* Added `twcover` (downloads and converts game covers).
* Fixed sort order bug, thanks to tyler and andrew.
* Added some covers.

### 1.3.0

* Big Update (reworked the whole code).
* Added `twtop`.
* `tw` searches more efficient and games as well as streams.

### 1.1.0

* First real release without webserver.
* Added AlleyOop/[Monkey Patch](http://www.alfredforum.com/topic/2218-monkey-patch-update-alfred-workflows-via-alleyoop/) support (workflow updater).
* Downgraded Workflows from 0.32 to 0.3 due to a sort-bug.

### 0.5.0

* First release with all functions. Based on stream-parsing on webserver.

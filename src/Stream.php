<?php //namespace anlutro\TwitchTv;

/**
 * Parses and stores information about a Twitch.tv stream.
 *
 * @version    2.0
 * @author     Andreas Lutro <anlutro@gmail.com>
 */
class Stream
{
    /**
     * Stores whether or not the stream has been checked for live status, and if
     * it has, whether or not it's live.
     *
     * @var null|boolean
     */
    protected $live = null;

    /**
     * We keep our own URL variable separate from the original data.
     *
     * @var string
     */
    protected $url;

    /**
     * The name of the twitch.tv channel.
     *
     * @var string
     */
    protected $channel;

    /**
     * The original data passed to the object.
     *
     * @var StdClass
     */
    protected $data;

    /**
     * Create a new Twitch.tv stream. Provide a data array or object with a url
     * field and we'll do the rest. The data will be stored for later should you
     * need it.
     * 
     * @param object|array $data (Optional) object or array with stream info
     * that you want to preserve - for example name, featured etc.
     */
    public function __construct($data = null)
    {
        $this->data = new \StdClass;

        // check if passed data is an array or an object
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->data->$key = $value;
            }
        } elseif (is_object($data)) {
            $this->data = $data;
        }

        if (isset($this->data->channel)) {
            $this->setChannel($this->data->channel);
        } elseif (isset($this->data->url)) {
            $this->setURL($this->data->url);
        }

        self::$all_streams[] = $this;

        if (self::$checked_live == true) {
            self::$checked_live = false;
        }
    }

    /**
     * Magic method for getting information from the object's data set.
     */
    public function __get($key)
    {
        if ($key == 'url') {
            return $this->getURL();
        } elseif ($key == 'channel') {
            return $this->getChannel();
        } elseif (isset($this->data->$key)) {
            return $this->data->$key;
        } else {
            return null;
        }
    }

    /**
     * Magic method for adding information to the object's data set.
     */
    public function __set($key, $val)
    {
        if ($key == 'url') {
            return $this->setURL($val);
        } elseif ($key == 'channel') {
            return $this->setChannel($val);
        } else {
            return $this->data->$key = $val;
        }
    }

    /**
     * Get the twitch.tv channel.
     * 
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set the twitch.tv channel, doing no parsing. Automatically updates the
     * URL.
     * 
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = (string) $channel;
        $this->url = 'http://www.twitch.tv/' . $this->channel;
    }

    /**
     * Get the twitch.tv URL.
     * 
     * @return string
     */
    public function getURL()
    {
        return $this->url;
    }

    /**
     * Set the URL of the stream. Will parse the URL to find the channel name
     * and regenerate the URL.
     * 
     * @param string $url
     */
    public function setURL($url)
    {
        $url = (string) $url;

        if (strpos($url, 'twitch.tv') === false) {
			throw new \InvalidArgumentException('Only twitch.tv URLs are permitted.');
        }

        // split the url into bits separated by the /'s
        $url_array = explode('/', $url);
        $at_channel_name = false;

        // iterate through the parts until we get to the twitch.tv part
        foreach ($url_array as $part) {

            if ($at_channel_name) {
                // this will be triggered when we're at the channel name
                $channel = strtolower($part);
                break;
            } else {
                if (strpos($part, 'twitch.tv') !== false) {
                    // we know that the next bit will be the channel name
                    $at_channel_name = true;
                }
            }
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $channel)) {
            throw new \InvalidArgumentException('Invalid channel name.');
        }
        
        $this->channel = $channel;

        // recreate the url to strip language etc. from it
        $this->url = 'http://www.twitch.tv/' . $this->channel;
    }

    /**
     * Check if a stream is live or not.
     * @return boolean
     */
    public function isLive()
    {
        if (!isset(self::$live_data) || self::$checked_live == false) {
            // get new information on live streams from twitch.tv
            self::getLiveStatus();
        } elseif (isset($this->live) && $this->live !== null) {
            // stream has been checked before
            return $this->live;
        }

        // iterate through twitch.tv's returned data to check if stream is live
        foreach (self::$live_data as $stream) {
            if ($stream->channel->login == $this->getChannel()) {
                // add live stream data and return true
                $this->addStreamData($stream);
                return $this->live;
            }
        }

        // if stream wasn't found in list, return false
        $this->live = false;
        return $this->live;
    }

    /**
     * Add stream data from the Twitch.tv JSON API.
     * 
     * @param array $data Decoded JSON from a Twitch.tv API call
     */
    public function addStreamData($data)
    {
        if ($data->channel->login != $this->channel) {
            // this stream's channel and the data's channel does not match
            throw new \UnexpectedValueException('Channel mismatch!');
        }

        // we can assume the stream is live at this point
        $this->live = true;

        // append the live twitch.tv API data to the existing data
        $this->data->stream_title = $data->title;
        $this->data->stream_viewers = $data->channel_count;
        $this->data->stream_res_height = $data->video_height;
        $this->data->stream_res_width = $data->video_width;
        $this->data->stream_bitrate = $data->video_bitrate;
        $this->data->stream_game = $data->channel->meta_game;
        $this->data->stream_thumb_huge = $data->channel->screen_cap_url_huge;
        $this->data->stream_thumb_large = $data->channel->screen_cap_url_large;
        $this->data->stream_thumb_medium = $data->channel->screen_cap_url_medium;
        $this->data->stream_thumb_small = $data->channel->screen_cap_url_small;
        $this->data->stream_avatar_huge = $data->channel->image_url_huge;
        $this->data->stream_avatar_large = $data->channel->image_url_large;
        $this->data->stream_avatar_medium = $data->channel->image_url_medium;
        $this->data->stream_avatar_small = $data->channel->image_url_small;
        $this->data->stream_avatar_tiny = $data->channel->image_url_tiny;

        return true;
    }

    /**
     * Array with references to all Stream objects.
     *
     * @var array
     */
    protected static $all_streams;

    /**
     * Live stream data from the Twitch.tv API.
     *
     * @var string
     */
    protected static $live_data;

    /**
     * Whether or not we've retrieved live stream data during this request.
     *
     * @var boolean
     */
    protected static $checked_live = false;

    /**
     * Get live status if we haven't checked already.
     *
     * @return void
     */
    protected static function getLiveStatus()
    {
        if (!isset(self::$live_data)) {
            // get a comma-separated list of stream channels for our request
            $list_array = array();
            foreach (self::$all_streams as $stream) {
                $list_array[] = $stream->getChannel();
            }
            $list = implode(',', $list_array);

            // retrieve the JSON data from the justin.tv API
            $url = 'http://api.justin.tv/api/stream/list.json?channel=' . $list;

            // @todo implement a safer way to fetch the remote file
            $json = file_get_contents($url);

            if (!$json) {
                return false;
            }

            self::$live_data = json_decode($json);

            self::$checked_live = true;
        }
    }
}

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Newblock block caps.
 *
 * @package    block_twitter
 * @copyright  Liam Mann <liam@liammann.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_twitter extends block_base {
    private $twitter;

    function init() {
        $this->title = get_string('pluginname', 'block_twitter');
    }

    function get_content() {
        global $CFG, $OUTPUT;

        $settings = get_config('twitter');

        $screen_name = $this->config->username;

        if (!empty($this->config->count)){
            $count = $this->config->count;
        } else {
            $count = $settings->count;
        }
 
        if (isset($this->config->type)){
            $type = $this->config->type;
        }

        if($type  === '1'){
            $twitter_url = 'statuses/user_timeline.json';
            $twitter_url .= '?screen_name=' . $screen_name;
            $twitter_url .= '&count=' . $count;
        } elseif ($type  === '2') {
            $list_name = $this->config->list;
            $twitter_url = 'lists/statuses.json';
            $twitter_url .= '?owner_screen_name=' . $screen_name;
            $twitter_url .= '&slug=' . $list_name;
            $twitter_url .= '&count=' . $count;
        }


        // Create a Twitter  object from our twitter_proxy.php class
        $this->twitter = new Twitter(
            $settings->oauth_access_token,            // 'Access token' on https://apps.twitter.com
            $settings->oauth_access_token_secret,     // 'Access token secret' on https://apps.twitter.com
            $settings->consumer_key,                  // 'API key' on https://apps.twitter.com
            $settings->consumer_secret               // 'API secret' on https://apps.twitter.com
        );

        $this->content = new stdClass();

        foreach (json_decode($this->twitter->get($twitter_url)) as $key => $value) {
            $text .= '<div class="tweets" >';
            $text .= "<div class='img' ><img src='" . $value->user->profile_image_url . "'></div><div class='tweets-content'><div class='tweets-header'><p><a href='http://twitter.com/" . $value->user->screen_name . "'>" . $value->user->screen_name . "</a></p> ";
            $date = new DateTime($value->created_at);
            $text .= '<a href="https://twitter.com/'. $screen_name.'/status/'.$value->id.'">'.$date->format('M j').'</a></div>';
            $text .= '<p>'.$this->twitter->linkify_twitter_status($value->text).'</p>';
            $text .= '</div></div>';
        }

        $this->content->text .=  $text;
 
        return $this->content;
    }

    function has_config() {return true;}

}
class Twitter {

    private $config = [
         'base_url' => 'https://api.twitter.com/1.1/'
    ];
    
    /**
     *  @param  string  $oauth_access_token         OAuth Access Token          
     *  @param  string  $oauth_access_token_secret  OAuth Access Token Secret
     *  @param  string  $consumer_key               Consumer key                
     *  @param  string  $consumer_secret            Consumer secret             
      */
    public function __construct($oauth_access_token, $oauth_access_token_secret, $consumer_key, $consumer_secret, $screen_name) {

        $this->config = array_merge($this->config, compact('oauth_access_token', 'oauth_access_token_secret', 'consumer_key', 'consumer_secret'));

     }

    private function buildBaseString($baseURI, $method, $params) {
        $r = [];
        ksort($params);
        foreach($params as $key=>$value){
            $r[] = "$key=" . rawurlencode($value);
        }

        return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
    }

    private function buildAuthorizationHeader($oauth) {
        $r = 'Authorization: OAuth ';
        $values = [];
        foreach($oauth as $key => $value) {
            $values[] = "$key=\"" . rawurlencode($value) . "\"";
        }
        $r .= implode(', ', $values);

        return $r;
    }

    public function get($url) {
        if (! isset($url)){
            return('No URL set');
        }
         
        // Figure out the URL parameters
        $url_parts = parse_url($url);
        parse_str($url_parts['query'], $url_arguments);
         
        $full_url = $this->config['base_url'] . $url; // URL with the query on it
        $base_url = $this->config['base_url'] . $url_parts['path']; // URL without the query
         
        // Set up the OAuth Authorization array
        $oauth = [
            'oauth_consumer_key' => $this->config['consumer_key'],
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token' => $this->config['oauth_access_token'],
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        ];

        $base_info = $this->buildBaseString($base_url, 'GET', array_merge($oauth, $url_arguments));
        
        $composite_key = rawurlencode($this->config['consumer_secret']) . '&' . rawurlencode($this->config['oauth_access_token_secret']);

        $oauth['oauth_signature'] = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));

        // Make Requests
        $header = [
            $this->buildAuthorizationHeader($oauth), 
            'Expect:'
        ];
        $options = [
            CURLOPT_HTTPHEADER => $header,
            //CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HEADER => false,
            CURLOPT_URL => $full_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ];

        $feed = curl_init();
        curl_setopt_array($feed, $options);
        $result = curl_exec($feed);
        $info = curl_getinfo($feed);
        curl_close($feed);
        
        return $result;
    }
    
    public function linkify_twitter_status($status_text){
      $status_text = preg_replace(
        '/(https?:\/\/\S+)/',
        '<a href="\1">\1</a>',
        $status_text
      );
      $status_text = preg_replace(
        '/(^|\s)@(\w+)/',
        '\1<a href="http://twitter.com/\2">@\2</a>',
        $status_text
      );
      $status_text = preg_replace(
        '/(^|\s)#(\w+)/',
        '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>',
        $status_text
      );

      return $status_text;
    }
}
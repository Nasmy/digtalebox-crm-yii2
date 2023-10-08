<?php
namespace app\components;
require_once('twitter/OAuth.php');
use yii\base\Component;
use Yii;

class TwitterApi extends Component
{
    const TWITTER = 1;
    public $sha1_method = '';
    public $consumer = '';
    public $token = '';
    /**
     * Twitter API methods
     */
    const VERIFY_CREDENTIALS = 'account/verify_credentials';
    const FOLLOWERS_IDS = 'followers/ids';
    const USERS_SHOW = 'users/show';
    const USERS_LOOKUP = 'users/lookup';
    const SEARCH_TWEETS = 'search/tweets';
    const DIRECT_MESSAGES_NEW = 'direct_messages/events/new';
    const USERS_SEARCH ='users/search';
    const FOLLOWERS_LIST = 'followers/list';
    const STATUS_UPDATE = 'statuses/update';
    const IMAGE_UPLOAD='https://upload.twitter.com/1.1/media/upload.json';
    const FAVORITES_CREATE = 'favorites/create';
    const STATUSES_RETWEET = 'statuses/retweet';
    const STATUSES_UPDATE_WITH_MEDIA = 'statuses/update_with_media';
    const FRIENDSHIPS_CREATE = 'friendships/create';
    const FRIENDSHIPS_SHOW = 'friendships/show';
    const FRIENDSHIPS_LOOKUP = 'friendships/lookup';
    const FRIENDS_IDS = 'friends/ids';
    const FRIENDSHIPS_DESTROY = 'friendships/destroy';

    const OK = 0;
    const RATELIMIT_EXCEEDED = 1;
    const API_ERROR = 2;

    public $apiStatusCode;

    /* Contains the last HTTP status code returned. */
    public $http_code;
    /* Contains the last API call. */
    public $url;
    /* Set up the API root URL. */
    public $host = "https://api.twitter.com/1.1/";
    /* Set timeout default. */
    public $timeout = 30;
    /* Set connect timeout. */
    public $connecttimeout = 30;
    /* Verify SSL Cert. */
    public $ssl_verifypeer = FALSE;
    /* Respons format. */
    public $format = 'json';
    /* Decode returned json data. */
    public $decode_json = TRUE;
    /* Contains the last HTTP headers returned. */
    public $http_info;
    /* Set the useragnet. */
    public $useragent = 'TwitterOAuth v0.2.0-beta2';
    /* Immediately retry the API call if the response was not successful. */
    //public $retry = TRUE;

    public $http_header = array();
    /**
     * Set API URLS
     */
    function accessTokenURL()  { return 'https://api.twitter.com/oauth/access_token'; }
    function authenticateURL() { return 'https://api.twitter.com/oauth/authenticate'; }
    function authorizeURL()    { return 'https://api.twitter.com/oauth/authorize'; }
    function requestTokenURL() { return 'https://api.twitter.com/oauth/request_token'; }

    /**
     * Debug helpers
     */
    function lastStatusCode() { return $this->http_status; }
    function lastAPICall() { return $this->last_api_call; }

    public function init() {
        $consumer_key = Yii::$app->params['twitter']['consumerKey'];
        $consumer_secret = Yii::$app->params['twitter']['consumerSecret'];
        $this->sha1_method = new \OAuthSignatureMethod_HMAC_SHA1();
        $this->consumer = new \OAuthConsumer($consumer_key, $consumer_secret);
    }
    /**
     * Get a request_token from Twitter
     *
     * @returns a key/value array containing oauth_token and oauth_token_secret
     */
    public function getRequestToken($oauth_callback) {
        $parameters = array();
        $parameters['oauth_callback'] = $oauth_callback;
        $request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
        $token = \OAuthUtil::parse_parameters($request);
        $this->token = new \OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }
    
    /**
     * Exchange request token and secret for an access token and
     * secret, to sign API calls.
     *
     * @returns array("oauth_token" => "the-access-token",
     *                "oauth_token_secret" => "the-access-secret",
     *                "user_id" => "9436992",
     *                "screen_name" => "abraham")
     */
    public function getAccessToken($oauth_verifier) {
        $parameters = array();
        $parameters['oauth_verifier'] = $oauth_verifier;
        $parameters['oauth_token'] = Yii::$app->session->get('oauth_token');
        $request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
        $token = \OAuthUtil::parse_parameters($request);
        $this->token = new \OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    /**
     * Format and sign an OAuth / API request
     */
    public function oAuthRequest($url, $method, $parameters, $multipart = false) {
        if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
        }
        $signature_parameters = array();
        // When making a multipart request, use only oauth_* -keys for signature
        foreach ($parameters AS $key => $value) {
            if ($multipart && strpos($key, 'oauth_') !== 0) {
                continue;
            }
            $signature_parameters[$key] = $value;
        }

        $request = \OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $signature_parameters);
        $request->sign_request($this->sha1_method, $this->consumer, $this->token);

        $request->sign_request($this->sha1_method, $this->consumer, $this->token);
        return $this->http($request->to_url(), 'GET',null,null,false);
    }


    /**
     * Format and sign an OAuth / API request
     */
    function oAuthRequestPost($url, $method, $parameters) {
        if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
            $url = "{$this->host}{$url}.{$this->format}";
        }
        $request = \OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
        $request->sign_request($this->sha1_method, $this->consumer, $this->token);
        switch ($method) {
            case 'GET':
                return $this->http($request->to_url(), 'GET');
            default:
                return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
        }
    }   


    /**
     * Make an HTTP request
     *
     * @return API results
     */
    public function http($url, $method, $postfields = NULL, OAuthRequest $request = NULL, $multipart = false) {
        $this->http_info = array();
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        $headers = array('Expect:');
        if ($multipart) {
            $headers[] = $request->to_header();
        }
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, FALSE);

        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields)) {
                    $url = "{$url}?{$postfields}";
                }
        }

        curl_setopt($ci, CURLOPT_URL, $url);
        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;
        curl_close ($ci);
        return $response;
    }

    /**
     * Get the header info to store.
     */
    public function getHeader($ch, $header) {
        $i = strpos($header, ':');
        if (!empty($i)) {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }
    public function sha1_method() {
        return new \OAuthSignatureMethod_HMAC_SHA1();
    }

    public function consumer(){
        // new \OAuthConsumer(, $consumer_secret);
    }

    /**
     * Get the authorize URL
     *
     * @returns a string
     */
    public function getAuthorizeURL($token, $sign_in_with_twitter = TRUE) {
        if (is_array($token)) {
            $token = $token['oauth_token'];
        }
        if (empty($sign_in_with_twitter)) {
            return $this->authorizeURL() . "?oauth_token={$token}";
        } else {
            return $this->authenticateURL() . "?oauth_token={$token}";
        }
    }

    /**
     * GET wrapper for oAuthRequest.
     * @param $url
     * @param array $parameters
     * @return API|mixed
     */
    public function get($url, $parameters = array()) {
        $parameters['oauth_token'] = Yii::$app->session->get('oauth_token');
        $oauth_token = Yii::$app->session->get('oauth_token');
        $oauth_token_secret = Yii::$app->session->get('oauth_token_secret');
        $this->token = new \OAuthConsumer($oauth_token, $oauth_token_secret);
        $response = $this->oAuthRequest($url, 'GET', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }


    /**
     * POST wrapper for oAuthRequest.
     */
    function post($url, $parameters = array(),$oauth_token,$oauth_token_secret) {
        $this->token = new \OAuthConsumer($oauth_token, $oauth_token_secret);
        $response = $this->oAuthRequestPost($url, 'POST', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }

    /**
     * UPLOAD wrapper for oAuthRequest.
     */
    function upload($url, $parameters = array(),$oauth_token,$oauth_token_secret) {
        $this->token = new \OAuthConsumer($oauth_token, $oauth_token_secret);
        $response = $this->oAuthRequestPost($url, 'POST', $parameters);
        if ($this->format === 'json' && $this->decode_json) {
            return json_decode($response);
        }
        return $response;
    }

}
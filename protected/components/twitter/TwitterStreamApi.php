<?php
class TwitterStreamApi
{
    private $oauthConsumerKey;
    private $oauthConsumerSecret;
    private $oauthToken;
    private $oauthTokenSecret;

    private $oauthNonce;
    private $oauthSignature;
    private $oauthSignatureMethod = 'HMAC-SHA1';
    private $oauthTimestamp;
    private $oauthVersion = '1.0';
	
	private $connection;
	private $host = "stream.twitter.com";
	private $port = 443;
	private $streamApiUrl = 'https://stream.twitter.com/1.1/';
	
	public function __construct($consumerKey, $consumerSecret, $token, $tokenSecret)
    {
		$this->oauthConsumerKey = $consumerKey;
		$this->oauthConsumerSecret = $consumerSecret;
        $this->oauthToken = $token;
        $this->oauthTokenSecret = $tokenSecret;
        $this->oauthNonce = md5(mt_rand());
		
        set_time_limit(0);
    }
	
	public function connect()
	{
		$this->connection = fsockopen("ssl://{$this->host}", 443, $errno, $errstr, 30);
		
		return $this->connection;
	}
	
	public function getRequest($method, $data, $requestType = 'POST')
	{
		$this->oauthTimestamp = time();
		$base_string = $this->getBaseString($method, $data, $requestType);
		$secret = rawurlencode($this->oauthConsumerSecret) . '&' . rawurlencode($this->oauthTokenSecret);
		$raw_hash = hash_hmac('sha1', $base_string, $secret, true);
		$this->oauthSignature = rawurlencode(base64_encode($raw_hash));
		
		$oauth = 'OAuth oauth_consumer_key="' . $this->oauthConsumerKey . '", ' .
				'oauth_nonce="' . $this->oauthNonce . '", ' .
				'oauth_signature="' . $this->oauthSignature . '", ' .
				'oauth_signature_method="' . $this->oauthSignatureMethod . '", ' .
				'oauth_timestamp="' . $this->oauthTimestamp . '", ' .
				'oauth_token="' . $this->oauthToken . '", ' .
				'oauth_version="' . $this->oauthVersion . '"';

		$request  = "POST /1.1/{$method}.json HTTP/1.1\r\n";
		$request .= "Host: {$this->host}\r\n";
		$request .= "Authorization: " . $oauth . "\r\n";
		$request .= "Content-Length: " . strlen($data) . "\r\n";
		$request .= "Content-Type: application/x-www-form-urlencoded\r\n\r\n";
		$request .= $data;

		return $request;
	}
	
	// method statuses/filter
	// $data = 'track=' . rawurlencode(implode($_keywords, ','));
	private function getBaseString($method, $data, $requestType)
	{		
		$base_string = "{$requestType}&" . 
			rawurlencode("{$this->streamApiUrl}{$method}.json") . '&' .
			rawurlencode('oauth_consumer_key=' . $this->oauthConsumerKey . '&' .
				'oauth_nonce=' . $this->oauthNonce . '&' .
				'oauth_signature_method=' . $this->oauthSignatureMethod . '&' . 
				'oauth_timestamp=' . $this->oauthTimestamp . '&' .
				'oauth_token=' . $this->oauthToken . '&' .
				'oauth_version=' . $this->oauthVersion . '&' .
				$data);
				
		return $base_string;
	}
}
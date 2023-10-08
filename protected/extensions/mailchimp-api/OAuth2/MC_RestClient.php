<?php

namespace MailchimpOAuth;

use Mailchimp\MC_OAuth2Client;
use yii\base\Component;
use yii\web\HttpException;

class MC_RestClient extends Component{

    private $cn = null;
    private $session = null;

    public function __construct($clientId = null, $clientSecret = null, $redirectUri = null,$session=null) {
        $this->cn = new MC_OAuth2Client($clientId = null, $clientSecret = null, $redirectUri = null,$session);
        $this->cn->setSession($session,false);
    }
    
    private function baseUri($url=null){
        return 'https://login.mailchimp.com/';
    }

    public function getMeta($return = null){
         if (!is_null($return)) {
        $return = json_decode(json_encode($return));

        }

        if (is_null($return)) {
            return 'MailChimp did not return Metadata';
        }

        if (!$return->access_token) {
            return 'MailChimp did not return an access token.';
        }

        // Metadata
        $headers = array('Authorization: OAuth ' . $return->access_token);
        $ch = curl_init("https://login.mailchimp.com/oauth2/metadata/");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $meta = curl_exec($ch);
        if (!is_null(json_decode($meta))) {
            $meta = json_decode($meta);
        }
        curl_close($ch);
        if (!$meta->dc) {
            throw new HttpException(
                'Unable to retrieve account meta-data',
                $meta
            );
        }
        $api_key =    $this->apiKey($return->access_token,$meta->dc);

        $data = array(
            'apikey'        => $api_key,
        );

        $mch_api = curl_init(); // initialize cURL connection

        curl_setopt($mch_api, CURLOPT_URL, 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/');
        curl_setopt($mch_api, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic '.base64_encode( 'user:'.$api_key )));
        curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
        curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true); // return the API response
        curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'GET'); // method GET
        curl_setopt($mch_api, CURLOPT_TIMEOUT, 10);
        curl_setopt($mch_api, CURLOPT_POST, true);
        curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($mch_api, CURLOPT_POSTFIELDS, json_encode($data) ); // send data in json
        $account = curl_exec($mch_api);
        $meta = (array) $meta; //Converting Object to array
        $result = array_merge(json_decode($account,true),$meta);

        return $result;
     }

     public function apiKey($accesstoken = null,$dc = null){
        if (!is_null($accesstoken) && !is_null($dc)){
            return $accesstoken . "-" . $dc;
        }else{
            return 'API Key is not generated';
        }
     }

       public function test($api_key= null){

        $data = array(
           'apikey'        => $api_key,
        );

       $mch_api = curl_init(); // initialize cURL connection

       curl_setopt($mch_api, CURLOPT_URL, 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/');
       curl_setopt($mch_api, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic '.base64_encode( 'user:'.$api_key )));
       curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
       curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true); // return the API response
       curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'GET'); // method GET
       curl_setopt($mch_api, CURLOPT_TIMEOUT, 10);
       curl_setopt($mch_api, CURLOPT_POST, true);
       curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
       curl_setopt($mch_api, CURLOPT_POSTFIELDS, json_encode($data) ); // send data in json

        $result = curl_exec($mch_api);

        return $result;

     }

    public function getMetadata(){
        return $this->cn->api('metadata', 'GET');
    }    


}

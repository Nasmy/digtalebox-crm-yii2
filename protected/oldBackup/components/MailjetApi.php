<?php


namespace app\components;


use yii\base\Component;

class MailjetApi extends Component
{
    // Mailjet Api key and secret
    private $apiKey = 'f6398804690ba0ca90e7d1bf7876227b';
    private $secretKey = '448554abf63c75dd7d6cd212f0881d94';

    // Mailjet API end point
    private $apiUrl = 'https://api.mailjet.com/v3/';

    public $response = '';
    public $errorCode;

    /**
     * Constructor.
     *
     * The default implementation does two things:
     *
     * - Initializes the object with the given configuration `$config`.
     * - Call [[init()]].
     *
     * If this method is overridden in a child class, it is recommended that
     *
     * - the last parameter of the constructor is a configuration array, like `$config` here.
     * - call the parent implementation at the end of the constructor.
     *
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($apiKey=null, $secretKey=null)
    {
        if (null != $apiKey) {
            $this->apiKey = $apiKey;
        }

        if (null != $secretKey) {
            $this->secretKey = $secretKey;
        }
    }

    private function httpRequest($url, $curlOptions=array(), $timeout=60)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->apiKey}:{$this->secretKey}");

        foreach ($curlOptions as $key => $value) {
            curl_setopt($ch, $key, $value);
        }

        $this->response = curl_exec($ch);
        $this->errorCode = curl_errno($ch);
        curl_close($ch);
    }

    public function sendRequest($endPoint, $params, $method='GET')
    {
        $curlOptions = array();
        switch ($method) {
            case 'POST':
                $queryString = $this->buildQueryString($params);
                $pramCount = count(explode('&',$queryString));
                $curlOptions[CURLOPT_POST] = $pramCount;
                $curlOptions[CURLOPT_POSTFIELDS] = $queryString;
                $requestUri = $this->apiUrl . $endPoint;
                break;

            case 'GET':
                $queryString = $this->buildQueryString($params);
                $requestUri = $this->apiUrl . $endPoint . '?' . $queryString;
                break;

            case 'POST_JSON':
                $jsonParams = json_encode($params);
                $curlOptions[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonParams)
                );
                $curlOptions[CURLOPT_POSTFIELDS] = $jsonParams;
                $requestUri = $this->apiUrl . $endPoint;
                break;

            case 'DELETE':
                $curlOptions[CURLOPT_CUSTOMREQUEST] = "DELETE";
                $requestUri = $this->apiUrl . $endPoint;
                break;
        }

        return $this->httpRequest($requestUri, $curlOptions);
    }

    private function buildQueryString($params)
    {
        $queryString = '';
        foreach ($params as $key => $value) {
            if (is_array($value) && 'multiple' == $value[0]) {
                $queryString .= "{$value[1]}&";
            } else {
                $queryString .= "{$key}={$value}&";
            }
        }

        return rtrim($queryString, '&');
    }

    public function setCallbackUrl($params)
    {
        $this->sendRequest('REST/eventcallbackurl/', $params, 'POST_JSON');
        return $this->response;
    }

    public function removeCallbackUrlByEvent($event)
    {
        $this->sendRequest("REST/eventcallbackurl/{$event}", null, 'DELETE');
        return $this->response;
    }

    public function getApiKeyId()
    {
        $this->sendRequest('REST/apikey/', array(), 'GET');
        return $this->response;
    }

    /**
     * get stat counters in mailjet
     * @param $params
     * @return string
     */
    public function getStatcounters($params)
    {
        $this->sendRequest('REST/statcounters/', $params, 'GET');
        return $this->response;
    }

    /**
     * get all message in mailjet
     * @param $params
     * @return string
     */
    public function getAllMessages($params)
    {
        $this->sendRequest('REST/message/', $params, 'GET');
        return $this->response;
    }

    /**
     * get all message in mailjet
     * @param $params
     * @return string
     */
    public function getClickStatistics($params)
    {
        $this->sendRequest('REST/clickstatistics/', $params, 'GET');
        return $this->response;
    }

}

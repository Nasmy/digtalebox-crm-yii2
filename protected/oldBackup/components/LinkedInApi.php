<?php


namespace app\components;

use GuzzleHttp\Client;
use http\Exception\RuntimeException;
use Yii;
use yii\base\Component;

class LinkedInApi extends Component
{
    const LINKEDIN = 3;
    private $_config = array();
    private $_state = null;
    private $_access_token = null;
    private $_access_token_expires = null;
    private $_debug_info = null;
    private $_curl_handle = null;
    private $ssl;

    const API_BASE = 'https://api.linkedin.com/v2';
    const OAUTH_BASE = 'https://www.linkedin.com/oauth/v2';

    const SCOPE_BASIC_PROFILE = 'r_basicprofile'; // Name, photo, headline, and current positions
    const SCOPE_LITE_PROFILE = 'r_liteprofile'; // ID, first name, last name and profile picture
    const SCOPE_FULL_PROFILE = 'r_fullprofile'; // Full profile including experience, education, skills, and recommendations
    const SCOPE_EMAIL_ADDRESS = 'r_emailaddress'; // The primary email address you use for your LinkedIn account
    const SCOPE_NETWORK = 'r_network'; // Your 1st and 2nd degree connections
    const SCOPE_CONTACT_INFO = 'r_contactinfo'; // Address, phone number, and bound accounts
    const SCOPE_READ_WRTIE_UPDATES = 'rw_nus'; // Retrieve and post updates to LinkedIn as you
    const SCOPE_READ_WRITE_GROUPS = 'rw_groups'; // Retrieve and post group discussions as you
    const SCOPE_WRITE_MESSAGES = 'w_messages'; // Send messages and invitations to connect as you
    const SCOPE_READ_WRITE_COMPANY_ADMIN = 'rw_organization_admin'; // Read write company page
    const SCOPE_READ_WRITE_COMPANY = 'w_organization_social'; // Read write company page
    const SCOPE_SHARE_CONTENTS = 'w_share'; // Grant permission to share content on LinkedIn
    const SCOPE_POST_COMMENTS_LIKES = 'w_member_social'; // Post, comment and like posts on behalf of an authenticated member
    const SCOPE_READ_COMPANY_POST = 'r_organization_social'; //Retrieve organizations' posts, comments, and likes

    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function init()
    {
        $this->_config = [
            'api_key' => Yii::$app->params['linkedIn']['apiKey'],
            'api_secret' => Yii::$app->params['linkedIn']['apiSecret'],
            'callback_url' => Yii::$app->params['linkedIn']['callbackUrl']
        ];
        parent::init(); // TODO: Change the autogenerated stub
    }

    /**
     * Get the login url, pass scope to request specific permissions
     *
     * @param array $scope - an array of requested permissions (can use scope constants defined in this class)
     * @param string $state - a unique identifier for this user, if none is passed, one is generated via uniqid
     * @return string $url
     */
    public function getLoginUrl(array $scope = array(), $state = null)
    {
        if (!empty($scope)) {
            $scope = implode('%20', $scope);
        }

        if (empty($state)) {
            $state = uniqid('', true);
        }
        $this->setState($state);

        $url = self::OAUTH_BASE . "/authorization?response_type=code&client_id={$this->_config['api_key']}&scope={$scope}&state={$state}&redirect_uri=" . urlencode($this->_config['callback_url']);
        return $url;
    }

    /**
     * Set the state manually. State is a unique identifier for the user
     *
     * @param string $state
     * @return \LinkedIn\LinkedIn
     * @throws \InvalidArgumentException
     */
    public function setState($state)
    {
        $state = trim($state);
        if (empty($state)) {
            throw new \InvalidArgumentException('Invalid state. State should be a unique identifier for this user');
        }

        $this->_state = $state;

        return $this->_state;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * POST to an authenciated API endpoint w/ payload
     *
     * @param string $endpoint
     * @param array $payload
     * @return array
     */
    public function post($endpoint, array $payload = array())
    {
        return $this->fetch($endpoint, $payload, self::HTTP_METHOD_POST);
    }

    /**
     * GET an authenticated API endpoind w/ payload
     *
     * @param string $endpoint
     * @param array $payload
     * @return array
     */
    public function get($endpoint, array $payload = array())
    {
        return $this->fetch($endpoint, $payload);
    }

    /**
     * PUT to an authenciated API endpoint w/ payload
     *
     * @param string $endpoint
     * @param array $payload
     * @return array
     */
    public function put($endpoint, array $payload = array())
    {
        return $this->fetch($endpoint, $payload, self::HTTP_METHOD_PUT);
    }

    /**
     * Make an authenticated API request to the specified endpoint
     * Headers are for additional headers to be sent along with the request.
     * Curl options are additional curl options that may need to be set
     *
     * @param string $endpoint
     * @param array $payload
     * @param string $method
     * @param array $headers
     * @param array $curl_options
     * @return array
     */
    public function fetch($endpoint, array $payload = array(), $method = 'GET', array $headers = array(), array $curl_options = array())
    {
        $endpoint = self::API_BASE . '/' . trim($endpoint, '/\\') . '&oauth2_access_token=' . $this->_access_token;
        $headers[] = 'Content-Type:application/json';
        return $this->_makeRequest($endpoint, $payload, $method, $headers, $curl_options);
    }

    /**
     * Get debug info from the CURL request
     *
     * @return array
     */
    public function getDebugInfo()
    {
        return $this->_debug_info;
    }



    /**
     * Make a CURL request
     *
     * @param string $url
     * @param array $payload
     * @param string $method
     * @param array $headers
     * @param array $curl_options
     * @return array
     * @throws \RuntimeException
     **/


    /**
     * Make a CURL request
     *
     * @param string $url
     * @param array $payload
     * @param string $method
     * @param array $headers
     * @param array $curl_options
     * @return array
     * @throws \RuntimeException
     */
    protected function _makeRequest($url, array $payload, $method = 'GET', array $headers = array(), array $curl_options = array())
    {

        $curl = curl_init();
        if (is_null($headers)) {
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }

        $payloads = http_build_query($payload);

        if (!empty($headers)) {

            if (in_array("Content-Type:application/json", $headers)) {
                $payloads = json_encode($payload);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        if ($method == "POST") {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payloads);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $response = json_decode($result, true);
        return $response;
        //todo: have to modify method as future request 22-01-2021
    }

    protected function _getCurlHandle()
    {
        if (!$this->_curl_handle) {
            $this->_curl_handle = curl_init();
        }

        return $this->_curl_handle;
    }

    /**
     * Retrieve profile information
     * @param string $id Id of the user. Keep null if you retrive profile details of authenticated user
     * @return array User profile details
     */
    public function getProfileInfo($id = null)
    {
        if ($id == null) {
            //set parameters for available permissions
            if (Yii::$app->session->get('linked_in_status')) {
                $query = "/me?projection=(id,firstName,localizedFirstName,localizedLastName,lastName,headline,profilePicture(displayImage~:playableStreams))";
            } else {
                $query = "/me?projection=(id,firstName,localizedFirstName,localizedLastName,lastName,profilePicture(displayImage~:playableStreams))";
            }

        } else {
            $query = "/people/id={$id}:(id,first-name,last-name,headline,picture-url,site-standard-profile-request)";
        }

        $lnProfInfo = $this->get($query);
        return $lnProfInfo;
    }

    /**
     * Retrieve primary email information
     * @param string $id Id of the user. Keep null if you retrive profile details of authenticated user
     * @return array User email details
     */
    public function getEmailAddress($id = null)
    {
        if ($id == null) {
            $query = "/emailAddress?q=members&projection=(elements*(handle~))";
        } else {
            $query = "/people/id={$id}:(id,first-name,last-name,headline,picture-url,site-standard-profile-request)";
        }

        $lnEmailInfo = $this->get($query);
        return $lnEmailInfo;
    }

    /**
     * Retrieve profile connections
     * @param integer $start Start index
     * @param integer $count Batch size
     * @param string $conFetchTime Last time that connections retrieved. Ex:YYYY-MM-DD HH:MM:SS
     * @return array Connections
     */
    public function getConnections($start, $count, $conFetchTime = null, $id = null)
    {
        if ($id == null) {
            $query = "/people/~/connections";
        } else {
            $query = "/people/id={$id}/connections";
        }

        $params = array(
            'start' => $start,
            'count' => $count
        );

        if (null != $conFetchTime) {
            $params['modified'] = 'new';
            $params['modified-since'] = strtotime($conFetchTime) * 1000;
        }

        $connections = array();

        try {
            $connections = $this->get($query, $params);
        } catch (Exception $e) {
        }

        return $connections;
    }

    /**
     * Send message to LinkedIn inbox
     * @param string $subject Message subject
     * @param string $body Message body
     * @param string $lnId Recipient`s LinkedIn profile id
     * @return boolean true if success otherwise false
     */
    public function sendMessage($subject, $body, $lnId)
    {
        $status = true;
        $messageInfo = array(
            'recipients' => array(
                'values' => array(
                    array(
                        'person' => array(
                            '_path' => "/people/{$lnId}"
                        )
                    )
                )
            ),
            'subject' => $subject,
            'body' => $body
        );

        try {
            $res = $this->post("/people/~/mailbox", $messageInfo);
        } catch (Exception $e) {
            Yii::$app->appLog->writeLog("LinkedIn message send failed. Error:{$e->getMessage()}");
            $status = false;
        }

        return $status;
    }

    public function linkedInPost($person_id, $message, $link_title, $image_path, $visibility = "PUBLIC")
    {
        $media = [];
        $shareMediaCategory = "NONE";
        if (!is_null($image_path)) {

            $prepareRequest = [
                "registerUploadRequest" => [
                    "recipes" => [
                        "urn:li:digitalmediaRecipe:feedshare-image"
                    ],
                    "owner" => "urn:li:person:" . $person_id,
                    "serviceRelationships" => [
                        [
                            "relationshipType" => "OWNER",
                            "identifier" => "urn:li:userGeneratedContent"
                        ],
                    ],
                ],
            ];

            $prepareUrl = self::API_BASE . "/assets?action=registerUpload&oauth2_access_token=" . $this->_access_token;
            $headers1 = ['Content-Type:application/json'];
            $prepareResponse = $this->_makeRequest($prepareUrl, $prepareRequest, self::HTTP_METHOD_POST, $headers1);
            $uploadURL = $prepareResponse['value']['uploadMechanism']["com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest"]["uploadUrl"];
            $asset_id = $prepareResponse['value']['asset'];


            $client = new Client();
            $response = $client->request('PUT', $uploadURL, [
                'headers' => ['Authorization' => 'Bearer ' . $this->_access_token],
                'body' => fopen($image_path, 'r'),
                'verify' => true
            ]);
            $shareMediaCategory = "IMAGE";
            $media = [[
                "status" => "READY",
                "description" => [
                    "text" => substr($message, 0, 200),
                ],
                "media" => $asset_id,

                "title" => [
                    "text" => $message,
                ],
            ]];

        }

        $request = [
            "author" => "urn:li:person:" . $person_id,
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary" => [
                        "text" => $message
                    ],
                    "shareMediaCategory" => $shareMediaCategory,
                    "media" => $media,
                ],

            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC",
            ]
        ];

        $post_url = self::API_BASE . "/ugcPosts?oauth2_access_token=" . $this->_access_token;
        $headers = ['X-Restli-Protocol-Version:2.0.0', 'Content-Type:application/json'];
        return $this->_makeRequest($post_url, $request, self::HTTP_METHOD_POST, $headers);

    }

    public function linkedInPagePost($person_id, $message, $link_title, $image_path, $visibility = "PUBLIC")
    {
        $media = [];
        $shareMediaCategory = "NONE";
        if (!is_null($image_path)) {
            $prepareRequest = [
                "registerUploadRequest" => [
                    "recipes" => [
                        "urn:li:digitalmediaRecipe:feedshare-image"
                    ],
                    "owner" => "urn:li:organization:" . $person_id,
                    "serviceRelationships" => [
                        [
                            "relationshipType" => "OWNER",
                            "identifier" => "urn:li:userGeneratedContent"
                        ],
                    ],
                ],
            ];

            $prepareUrl = self::API_BASE . "/assets?action=registerUpload&oauth2_access_token=" . $this->_access_token;
            $headers1 = ['Content-Type:application/json'];
            $prepareResponse = $this->_makeRequest($prepareUrl, $prepareRequest, self::HTTP_METHOD_POST, $headers1);
            $uploadURL = $prepareResponse['value']['uploadMechanism']["com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest"]["uploadUrl"];
            $asset_id = $prepareResponse['value']['asset'];


            $client = new Client();
            $response = $client->request('PUT', $uploadURL, [
                'headers' => ['Authorization' => 'Bearer ' . $this->_access_token],
                'body' => fopen($image_path, 'r'),
                'verify' => true
            ]);
            $shareMediaCategory = "IMAGE";
            $media = [[
                "status" => "READY",
                "description" => [
                    "text" => substr($message, 0, 200),
                ],
                "media" => $asset_id,

                "title" => [
                    "text" => $message,
                ],
            ]];
        }


        $request = [
            "author" => "urn:li:organization:{$person_id}",
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary" => [
                        "text" => $message
                    ],
                    "shareMediaCategory" => $shareMediaCategory,
                    "media" => $media,
                ],

            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC",
            ]
        ];

        $post_url = self::API_BASE . "/ugcPosts?oauth2_access_token=" . $this->_access_token;
        $headers = ['X-Restli-Protocol-Version:2.0.0', 'Content-Type:application/json'];
        $res = $this->_makeRequest($post_url, $request, self::HTTP_METHOD_POST, $headers);
        return $res;

    }


    /**
     * Share LinkedIn post
     * @param string $comment Post description
     * @param string $title Post title
     * @param string $imageUri Image URI
     * @return boolean true if success otherwise false
     */
    public function shares($comment, $title, $imageUri = null)
    {
        $status = true;

        if (null != $imageUri) {
            $shareInfo = array(
                'comment' => $comment,
                'content' => array(
                    'title' => $title,
                    'submitted-url' => $imageUri,
                    'submitted-image-url' => $imageUri
                ),
                'visibility' => array('code' => 'anyone')
            );
        } else {
            $shareInfo = array(
                'comment' => $comment,
                'visibility' => array('code' => 'anyone')
            );
        }

        try {
            $res = $this->post("/people/~/shares", $shareInfo);
        } catch (Exception $e) {
            Yii::$app->appLog->writeLog("LinkedIn share failed. Error:{$e->getMessage()}");
            $status = false;
        }

        return $status;
    }

    /**
     * Retrieve pages that authenticated user is admin
     * @return mixed $pages List of pages
     */
    public function getPages()
    {

        $request = [
            'q' => 'roleAssignee',
        ];

        $headers = ['X-Restli-Protocol-Version:2.0.0'];
        $company_pages = self::API_BASE . "/organizationAcls?q=roleAssignee&oauth2_access_token=" . $this->_access_token;
        $prepareResponse = $this->_makeRequest($company_pages, $request, self::HTTP_METHOD_GET, $headers);

        $companies = [];
        foreach ($prepareResponse['elements'] as $ids) {
            $organizations = self::API_BASE . "/organizations/" . filter_var($ids['organization'], FILTER_SANITIZE_NUMBER_INT) . "/?oauth2_access_token=" . $this->_access_token;
            $organizationsResponse = $this->_makeRequest($organizations, $request, self::HTTP_METHOD_GET, $headers);
            array_push($companies, $organizationsResponse);
        }

        $request2 = [
            'ids' => 3835879,
            'oauth2_access_token' => $this->_access_token,
        ];


        return $companies;

    }

    /**
     * Share post on LinkedIn page
     * @param string $pageId LinkedIn page id
     * @param string $comment Post description
     * @param string $title Post title
     * @param string $imageUri Image URI
     * @return boolean true if success otherwise false
     */
    public function companyShares($pageId, $comment, $title, $imageUri = null)
    {
        $status = true;
        /*
                if (null != $imageUri) {
                    $shareInfo = array(
                        'comment' => $comment,
                        'content' => array(
                            'title' => $title,
                            'submitted-url' => $imageUri,
                            'submitted-image-url' => $imageUri
                        ),
                        'visibility' => array('code' => 'anyone')
                    );
                } else {
                    $shareInfo = array(
                        'comment' => $comment,
                        'visibility' => array('code' => 'anyone')
                    );
                }

                try {
                    $res = $this->post("/companies/{$pageId}/shares", $shareInfo);
                } catch (Exception $e) {
                    Yii::$app->appLog->writeLog("LinkedIn page share failed. Error:{$e->getMessage()}");
                    $status = false;
                }*/

        return $status;
    }

    /**
     * Retrieve company status updates
     * @param string $pageId LinkedIn page id
     * @return array Status updates return from LinkedIn API
     */
    public function getCompanyUpdates($pageId)
    {
        $query = "/companies/{$pageId}/updates";

        $params = array();
        $updates = array();

        try {
            $updates = $this->get($query, $params);
        } catch (Exception $e) {
        }

        return $updates;
    }

    /**
     * Retrieve company page comments
     * @param string $pageId LinkedIn page id
     * @param string $updateKey Update key that comes with page post
     * @return array $comments Comments of a post
     */
    public function getComments($pageId, $updateKey)
    {
        $query = "/companies/{$pageId}/updates/key={$updateKey}/update-comments";

        $params = array();
        $comments = array();

        try {
            $comments = $this->get($query, $params);
        } catch (Exception $e) {
        }

        return $comments;
    }

    /**
     * Retrieve company page likes
     * @param string $pageId LinkedIn page id
     * @param string $updateKey Update key that comes with page post
     * @return array $comments Likes of a post
     */
    public function getLikes($pageId, $updateKey)
    {
        $query = "/companies/{$pageId}/updates/key={$updateKey}/likes";

        $params = array();
        $likes = array();

        try {
            $likes = $this->get($query, $params);
        } catch (Exception $e) {
        }

        return $likes;
    }

    public function __destruct()
    {
        if ($this->_curl_handle) {
            curl_close($this->_curl_handle);
        }
    }

    /**
     * Exchange the authorization code for an access token
     *
     * @param string $authorization_code
     * @return string $access_token
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getAccessToken($authorization_code = null)
    {


        if (empty($authorization_code)) {
            throw new \InvalidArgumentException('Invalid authorization code. Pass in the "code" parameter from your callback url');
        }

        $url = Yii::$app->params['linkedIn']['callbackUrl'];
        $isStaging = Yii::$app->toolKit->isStaging();

        if (!$isStaging) {
            $url = str_replace('http://', 'https://', $url);
        }

        $params = [
            'grant_type' => 'authorization_code',
            'code' => $authorization_code,
            'client_id' => $this->_config['api_key'],
            'client_secret' => $this->_config['api_secret'],
            'redirect_uri' => $url,
            'state' => Yii::$app->request->csrfToken,

        ];

        $data = $this->_makeRequest(self::OAUTH_BASE . '/accessToken', $params, self::HTTP_METHOD_POST);
        if (isset($data['error'])) {
            // regenerating token
            $data = $this->_makeRequest(self::OAUTH_BASE . '/accessToken', $params, self::HTTP_METHOD_POST, array('x-li-format: json'));

        }

        if (!empty($this->_access_token)) {
            return $this->_access_token;
        }

        if (isset($data['error']) && !empty($data['error'])) {
            // throw new \RuntimeException('Access Token Request Error: ' . $data['error'] . ' -- ' . $data['error_description']);
        }

        $this->_access_token = $data['access_token'];
        $this->_access_token_expires = $data['expires_in'];

        return $this->_access_token;
    }

    /**
     * Set the access token manually
     *
     * @param string $token
     * @return \LinkedIn\LinkedIn
     * @throws \InvalidArgumentException
     */
    public function setAccessToken($token)
    {
        $token = trim($token);
        if (empty($token)) {
            throw new \InvalidArgumentException('Invalid access token');
        }

        $this->_access_token = $token;

        return $this;
    }
}
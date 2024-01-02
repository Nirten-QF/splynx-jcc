<?php


/**
 * Splynx API v. 2.0
 * REST API Class
 * Author: Ruslan Malymon (Splynx s.r.o.)
 * https://splynx.com/wiki/index.php/API - documentation
 *
 * API v2 usage example
 *
 * ```php
 * $api = new SplynxApi('http://my_domain.com');
 * $api->setVersion(SplynxApi::API_VERSION_2);
 *
 * // Login as admin
 * $api->login([
 *      'auth_type' => SplynxApi::AUTH_TYPE_ADMIN,
 *      'login' => 'alen',
 *      'password' => '12345',
 *      // 'code' => '23422', // Uncomment it if two factor authorization is enabled
 * ]);
 *
 * echo "\nUser permissions: " . var_export($api->getPermissions(), 1);
 *
 * $api->api_call_get('admin/administration/locations'); // Get all locations
 * echo "\nLocations: " . var_export($api->response, 1);
 * $api->logoff();
 * ```
 *
 * Login with using API key
 *
 * ```php
 * $api->login([
 *     'auth_type' => SplynxApi::AUTH_TYPE_API_KEY,
 *     'key' => '6871925f25d7e3341255d35ef2c40feb',
 *     'secret' => '23012ba6d5698179b5d793074f0cfb2e',
 * ]);
 * ```
 *
 * Save auth data to external storage
 *
 * ```php
 * $_SESSION['auth_data'] = $api->getAuthData();
 *
 * // And then use it for authenticate instead of login
 * $api->setAuthData($_SESSION['auth_data']);
 * ```
 */
class SplynxApi
{
    /** @var int Current admin ID. Worked only if sash is passed. */
    public $administrator_id;

    /** @var int Current admin role. Worked only if sash is passed. */
    public $administrator_role;

    /** @var int Current admin's partner id. Worked only if sash is passed. */
    public $administrator_partner;

    /** @var bool Debug mode flag */
    public $debug = false;

    /** @var bool Result of last request */
    public $result;

    /** @var array|null Response of last request */
    public $response;

    /** @var string Status code of last request */
    public $response_code;

    /** @var array Response headers */
    public $response_headers;

    /** @var string Hash of admin session id. Will be send in $_GET['sash'] in add-ons requests */
    private $_sash;

    /** @var null|string Api key used for making requests. Only for API v1 */
    private $_api_key;

    /** @var null|string Api secret used for making requests. Only for API v1 */
    private $_api_secret;

    /** @var int Nonce integer */
    private $_nonce_v;

    /** @var string Base API url */
    private $_url;

    /** @var string Current used API version */
    private $_version = self::API_VERSION_1;

    /** @var string Access token for API v2 authorization */
    private $_access_token;

    /** @var int Access token expiration time */
    private $_access_token_expiration;

    /** @var string Refresh token for API v2. Used for renew access token */
    private $_refresh_token;

    /** @var int Refresh token expiration time */
    private $_refresh_token_expiration;

    /** @var array|null Current API v2 user permissions */
    private $_permissions;

    const API_VERSION_1 = '1.0';
    const API_VERSION_2 = '2.0';

    /** Url for working with auth tokens */
    const TOKEN_URL = 'admin/auth/tokens';

    const AUTH_TYPE_ADMIN = 'admin';
    const AUTH_TYPE_CUSTOMER = 'customer';
    const AUTH_TYPE_API_KEY = 'api_key';
    const AUTH_TYPE_SESSION = 'session';

    /** Name of header which contains amount of records */
    const HEADER_X_TOTAL_COUNT = 'X-total-count';

    /**
     * Create Splynx API object
     *
     * @param $url
     * @param string|null $api_key Required only for API v1
     * @param string|null $api_secret Required only for API v1
     */
    public function __construct($url, $api_key = null, $api_secret = null)
    {
        $this->_url = $url . 'api/';
        $this->_api_key = $api_key;
        $this->_api_secret = $api_secret;
        $this->nonce();
    }

    /**
     * Send curl request to Splynx API
     * @param string $method Method: GET, POST, PUT, DELETE, OPTIONS
     * @param string $url
     * @param array $param
     * @param string $contentType
     * @return array|bool
     */
    private function curlProcess($method, $url, $param = [], $contentType = 'application/json')
    {
        $ch = curl_init();

        if ($this->debug == true) {
            print $method . ' to ' . $url . "\n";
            print_r($param);
        }

        $headers = [];
        $headers[] = 'Content-type: ' . $contentType;
        $auth_str = $this->makeAuth();
        $headers[] = 'Authorization: Splynx-EA (' . $auth_str . ')';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        if ($method == 'OPTIONS') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
        }

        if ($method == 'HEAD') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }

        if ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Splynx PHP API ' . $this->_version);

        if ($this->debug == true) {
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
        }

        $out = curl_exec($ch);

        if (curl_errno($ch)) {
            trigger_error('cURL failed. Error #' . curl_errno($ch) . ': ' . curl_error($ch), E_USER_ERROR);
        }

        // Parse headers and body
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($out, 0, $header_size);
        $out = substr($out, $header_size);

        // Parse headers
        $this->parseResponseHeaders($header);

        $this->response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($this->debug == true) {
            var_dump($out);
        }

        $this->result = false;

        switch ($method) {
            case 'POST':
                if ($this->response_code == 201) {
                    $this->result = true;
                }
                break;

            case 'PUT':
                if ($this->response_code == 202) {
                    $this->result = true;
                }
                break;

            case 'HEAD':
            case 'DELETE':
                if ($this->response_code == 204) {
                    $this->result = true;
                }
                break;

            case 'OPTIONS':
            default:
                if ($this->response_code == 200) {
                    $this->result = true;
                }
                break;
        }
        $this->response = json_decode($out, true);
        if ($this->response === false) {
            $this->response = $out;
        }

        return $this->result;
    }

    /**
     * Send curl request to Splynx API. Also check if access token expired and renew that if need.
     * @param string $method
     * @param string $url
     * @param array $param
     * @param string $contentType
     * @return array|bool
     */
    private function request($method, $url, $param = [], $contentType = 'application/json')
    {
        if ($this->_version === self::API_VERSION_2) {
            if (time() + 5 < $this->_refresh_token_expiration) {
                if (time() + 5 > $this->_access_token_expiration) {
                    $this->renewToken();
                }
            }
        }

        return $this->curlProcess($method, $url, $param, $contentType);
    }

    /**
     * Make Splynx Extended Authorization string
     * @return string of Splynx EA
     */
    private function makeAuth()
    {
        if ($this->_version === self::API_VERSION_2) {
            $auth = [
                'access_token' => $this->_access_token,
            ];
        } else {
            $auth = [
                'key' => $this->_api_key,
                'signature' => $this->signature(),
                'nonce' => $this->_nonce_v++
            ];

            // Add $sash is needed
            if ($this->_sash !== null) {
                $auth['sash'] = $this->_sash;
            }
        }

        return http_build_query($auth);
    }

    /**
     * Create API url by path and id
     * @param string $path API endpoint
     * @param null|int $id
     * @return string
     */
    private function getUrl($path, $id = null)
    {
        $url = $this->_url . $this->_version . '/' . $path;
        if (!empty($id)) {
            $url .= '/' . $id;
        }
        return $url;
    }

    /**
     * Grab info from response headers
     * @param string $header_text
     */
    private function parseResponseHeaders($header_text)
    {
        $this->response_headers = [];

        foreach (explode("\r\n", $header_text) as $i => $line) {
            if ($i !== 0 && !empty($line)) {
                list ($key, $value) = array_pad(explode(': ', $line, 2), 2, null);
                $this->response_headers[$key] = $value;

                switch ($key) {
                    case 'SpL-Administrator-Id':
                        $this->administrator_id = $value;
                        break;
                    case 'SpL-Administrator-Role':
                        $this->administrator_role = $value;

                        break;
                    case 'SpL-Administrator-Partner':
                        $this->administrator_partner = $value;
                        break;
                }
            }
        }
    }

    /**
     * Validate API v2 auth data
     * @param array $data
     * @throws Exception
     */
    private function validateAuthData($data)
    {
        $required = [];
        switch ($data['auth_type']) {
            case self::AUTH_TYPE_API_KEY:
                $required[] = 'key';
                $required[] = 'secret';
                break;
            case self::AUTH_TYPE_ADMIN:
            case self::AUTH_TYPE_CUSTOMER:
                $required[] = 'login';
                $required[] = 'password';
                break;
            case self::AUTH_TYPE_SESSION:
                $required[] = 'session_id';
                break;
            default:
                throw new Exception('Auth type is invalid!');
        }
        foreach ($required as $property) {
            if (empty($data[$property])) {
                throw new Exception($property . ' is missing!');
            }
        }
    }

    /**
     * Create signature for API call validation
     * @param null|string $secret API secret
     * @return string hash
     */
    private function signature($secret = null)
    {
        // Create string
        $string = $this->_nonce_v . $this->_api_key;

        $secret = empty($secret) ? $this->_api_secret : $secret;

        // Create hash
        $hash = hash_hmac('sha256', $string, $secret);
        $hash = strtoupper($hash);

        return $hash;
    }

    /**
     * Set nonce as timestamp
     */
    private function nonce()
    {
        $this->_nonce_v = round(microtime(true) * 100);
        return $this->_nonce_v;
    }

    /**
     * Get $sash
     * @return string
     */
    public function getSash()
    {
        return $this->_sash;
    }

    /**
     * Set $sash
     * @param string $_sash
     */
    public function setSash($_sash)
    {
        $this->_sash = $_sash;
    }

    /**
     * Set API version
     * @param string $v
     */
    public function setVersion($v)
    {
        $this->_version = $v;
    }

    /**
     * Get current API version
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Get current user permissions
     * @return array|null
     */
    public function getPermissions()
    {
        return $this->_permissions;
    }

    /**
     * Set auth data (Only for API v2)
     *
     * You can use it instead of login when you store auth data in external storage like as session.
     * @param array $data
     */
    public function setAuthData($data)
    {
        $this->_access_token = isset($data['access_token']) ? $data['access_token'] : null;
        $this->_access_token_expiration = isset($data['access_token_expiration']) ? $data['access_token_expiration']: null;
        $this->_refresh_token = isset($data['refresh_token']) ? $data['refresh_token'] : null;
        $this->_refresh_token_expiration = isset($data['refresh_token_expiration']) ? $data['refresh_token_expiration'] : null;

        if (isset($data['permissions'])) {
            $this->_permissions = $data['permissions'];
        }
    }

    /**
     * Get auth data (Only for API v2)
     * @return array
     */
    public function getAuthData()
    {
        return [
            'access_token' => $this->_access_token,
            'access_token_expiration' => $this->_access_token_expiration,
            'refresh_token' => $this->_refresh_token,
            'refresh_token_expiration' => $this->_refresh_token_expiration,
            'permissions' => $this->_permissions,
        ];
    }

    /**
     * Make login. Generate JWT tokens and getting user permissions. (Only for API v2)
     * @param array $data
     * @return bool
     */
    public function login($data)
    {
        $this->validateAuthData($data);

        if ($data['auth_type'] === self::AUTH_TYPE_API_KEY) {
            $this->_api_key = $data['key'];
            $data['nonce'] = $this->nonce();
            // Calculate signature from secret
            $data['signature'] = $this->signature($data['secret']);
            unset($data['secret']);
        }

        $r = $this->curlProcess('POST', $this->getUrl(self::TOKEN_URL), json_encode($data), 'application/json');
        if (!$r) {
            return false;
        }
        $this->setAuthData($this->response);

        return true;
    }

    /**
     * Logout. (Only for API v2)
     * @return array|bool
     */
    public function logout()
    {
        $r = $this->request('DELETE', $this->getUrl(self::TOKEN_URL, $this->_refresh_token), [], 'application/json');
        $this->_access_token = null;
        $this->_access_token_expiration = null;
        $this->_refresh_token = null;
        $this->_refresh_token_expiration = null;
        $this->_permissions = null;

        return $r;
    }

    /**
     * Regenerate access token by refresh token.
     * @return bool
     */
    public function renewToken()
    {
        $url = $this->getUrl(self::TOKEN_URL, $this->_refresh_token);
        $r = $this->curlProcess('GET', $url, [], 'application/json');
        if (!$r) {
            return false;
        }
        $this->setAuthData($this->response);

        return true;
    }

    /**
     * Send API call GET to Splynx API
     * @param string $path API endpoint
     * @param string|null $id Record id
     * @return array
     */
    public function api_call_get($path, $id = null)
    {
        return $this->request('GET', $this->getUrl($path, $id), [], 'application/json');
    }

    /**
     * Send API call DELETE to Splynx API
     * @param string $path API endpoint
     * @param integer $id Record id
     * @return array JSON results
     */
    public function api_call_delete($path, $id)
    {
        return $this->request('DELETE', $this->getUrl($path, $id), [], 'application/json');
    }

    /**
     * Send API call POST (add) to Splynx API
     * @param string $path API endpoint
     * @param array $params Payload
     * @param bool $encode Encode payload?
     * @param string $contentType
     * @return array
     */
    public function api_call_post($path, $params, $encode = true, $contentType = 'application/json')
    {
        if ($encode) {
            $params = json_encode($params);
        }
        return $this->request('POST', $this->getUrl($path), $params, $contentType);
    }

    /**
     * Upload file to Splynx
     * @param string $path API endpoint
     * @param array $params Payload
     * @return array
     */
    public function api_call_post_file($path, $params)
    {
        return $this->api_call_post($path, $params, false, 'multipart/form-data');
    }

    /**
     * Send API call PUT (update) to Splynx API
     * @param string $path API endpoint
     * @param int $id Record id
     * @param array $params Payload
     * @param bool $encode
     * @param string $contentType
     * @return array
     */
    public function api_call_put($path, $id, $params, $encode = true, $contentType = 'application/json')
    {
        if ($encode) {
            $params = json_encode($params);
        }
        return $this->request('PUT', $this->getUrl($path, $id), $params, $contentType);
    }

    /**
     * Send API call OPTIONS to Splynx API
     * @param string $path API endpoint
     * @param int $id
     * @return array
     */
    public function api_call_options($path, $id = null)
    {
        return $this->request('OPTIONS', $this->getUrl($path, $id), [], 'application/json');
    }

    /**
     * Send API call HEAD to Splynx API
     * @param string $path API endpoint
     * @return array
     */
    public function api_call_head($path)
    {
        return $this->request('HEAD', $this->getUrl($path), [], 'application/json');
    }
}

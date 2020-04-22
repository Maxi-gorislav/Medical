<?php
/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 */

class CartsGuruRAPI implements Iterator, ArrayAccess
{
    private $site_id;

    private $auth_key;

    public $options;

    public $handle; // cURL resource handle.

    public $isOldCurl; // Test result for curl version

    // Populated after execution:
    public $response; // Response body.
    public $headers; // Parsed reponse header object.
    public $info; // Response info object.
    public $error; // Response error string.

    // Populated as-needed.
    public $decoded_response; // Decoded response body.
    private $iterator_positon;

    const API_PATH_ACCOUNTS = '/accounts';

    const API_PATH_ORDERS = '/orders';

    const API_PATH_CARTS = '/carts';

    const API_PATH_SUBSCRIBE = '/customers';

    const API_PATH_PROGRESS = '/prestashop/setup-progress';

    const API_PATH_REGISTER = '/sites/:siteId/register-plugin';

    const API_PATH_STATISTICS = '/sites/:siteId/stats/summary';

    const API_PATH_IMPORT_ORDERS = '/import/orders';

    const API_PATH_IMPORT_CARTS = '/import/carts';

    public function __construct($site_id = null, $auth_key = '', $options = array())
    {
        $this->site_id = $site_id;
        $this->auth_key = $auth_key;
        $default_options = array(
            'headers' => array(
                'x-auth-key' => $auth_key,
                'x-plugin-version' => _CARTSGURU_VERSION_,
                'Content-Type' => 'application/json'
            ),
            'parameters' => array(),
            'curl_options' => array(),
            'user_agent' => "PHP RestClient/0.1.4",
            'base_url' => null,
            'format' => null,
            'format_regex' => "/(\w+)\/(\w+)(;[.+])?/",
            'decoders' => array(
                'json' => 'json_decode',
                'php' => 'unserialize'
            ),
            'username' => null,
            'password' => null
        );
        $this->options = array_merge($default_options, $options);
        if (array_key_exists('decoders', $options)) {
            $this->options['decoders'] = array_merge($default_options['decoders'], $options['decoders']);
        }

        $info = curl_version();
        $this->isOldCurl = version_compare($info["version"], '7.35.0', '<');
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function registerDecoder($format, $method)
    {
        // Decoder callbacks must adhere to the following pattern:
        // array my_decoder(string $data)
        $this->options['decoders'][$format] = $method;
    }

    // Iterable methods:
    public function rewind()
    {
        $this->decodeResponse();
        return reset($this->decoded_response);
    }

    public function current()
    {
        return current($this->decoded_response);
    }

    public function key()
    {
        return key($this->decoded_response);
    }

    public function next()
    {
        return next($this->decoded_response);
    }

    public function valid()
    {
        return is_array($this->decoded_response) && (key($this->decoded_response) !== null);
    }

    // ArrayAccess methods:
    public function offsetExists($key)
    {
        $this->decodeResponse();
        return is_array($this->decoded_response) ?
        isset($this->decoded_response[$key]) :
        isset($this->decoded_response->{$key});
    }

    public function offsetGet($key)
    {
        $this->decodeResponse();
        if (! $this->offsetExists($key)) {
            return null;
        }

        return is_array($this->decoded_response) ? $this->decoded_response[$key] : $this->decoded_response->{$key};
    }

    public function offsetSet($key, $value)
    {
        throw new CartsGuruRAPIException("Decoded response data is immutable.");
    }

    public function offsetUnset($key)
    {
        throw new CartsGuruRAPIException("Decoded response data is immutable.");
    }

    // Request methods:
    public function get($path, $parameters = array(), $sync = true, $headers = array())
    {
        return $this->execute($path, 'GET', $parameters, $headers, $sync);
    }

    public function post($path, $parameters = array(), $sync = true, $headers = array())
    {
        return $this->execute($path, 'POST', $parameters, $headers, $sync);
    }

    public function put($path, $parameters = array(), $sync = true, $headers = array())
    {
        return $this->execute($path, 'PUT', $parameters, $headers, $sync);
    }

    public function delete($path, $parameters = array(), $sync = true, $headers = array())
    {
        return $this->execute($path, 'DELETE', $parameters, $headers, $sync);
    }
    /**
     * Call Carts Guru api
     * @param string $path eg /orders,/carts
     * @param string $method eg POST,PUT,DELETE
     * @param array $parameters override parameter
     * @param array $headers
     * @param string $sync
     * @return CartsGuruRAPI with initialization (result)
     */
    public function execute($path, $method = 'GET', $parameters = array(), $headers = array(), $sync = true)
    {
        $path = str_replace(':siteId', $this->site_id, $path);
        $client = clone $this;
        $client->url = _CARTSGURU_API_URL_ . $path;
        $client->handle = curl_init();
        $curlopt = array(
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => $client->options['user_agent'],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => $client->isOldCurl ? 0 : 2
        );
        if (! $sync) {
            /*
                $curlopt[CURLOPT_NOSIGNAL] = 1;
                $curlopt[CURLOPT_TIMEOUT_MS] = 50;
            */
            /* BIS SOLUTION */
            $curlopt[CURLOPT_FRESH_CONNECT] = true;
            $curlopt[CURLOPT_TIMEOUT] = 1;
        }
        if ($client->options['username'] && $client->options['password']) {
            $curlopt[CURLOPT_USERPWD] = sprintf("%s:%s", $client->options['username'], $client->options['password']);
        }

        if (is_array($parameters)) {
            $parameters = array_merge($client->options['parameters'], $parameters);
            $parameters_string = Tools::jsonEncode($parameters);
            $headers['Content-Length'] = Tools::strlen($parameters_string);
        } else {
            $parameters_string = (string) $parameters;
        }

        if (count($client->options['headers']) || count($headers)) {
            $curlopt[CURLOPT_HTTPHEADER] = array();
            $headers = array_merge($client->options['headers'], $headers);
            foreach ($headers as $key => $value) {
                $curlopt[CURLOPT_HTTPHEADER][] = sprintf("%s:%s", $key, $value);
            }
        }

        if ($client->options['format']) {
            $client->url .= '.' . $client->options['format'];
        }

        // Allow passing parameters as a pre-encoded string (or something that
        // allows casting to a string). Parameters passed as strings will not be
        // merged with parameters specified in the default options.

        $methode_upper = Tools::strtoupper($method);
        if ($methode_upper == 'POST') {
            $curlopt[CURLOPT_POST] = true;
            $curlopt[CURLOPT_POSTFIELDS] = $parameters_string;
        } elseif ($methode_upper != 'GET') {
            $curlopt[CURLOPT_CUSTOMREQUEST] = $methode_upper;
            $curlopt[CURLOPT_POSTFIELDS] = $parameters_string;
        } elseif ($parameters_string) {
            $client->url .= strpos($client->url, '?') ? '&' : '?';
            $client->url .= $parameters_string;
        }

        if ($client->options['base_url']) {
            $option_base_url_sub = Tools::substr($client->options['base_url'], - 1);
            if ($client->url[0] != '/' && $option_base_url_sub != '/') {
                $client->url = '/' . $client->url;
            }
            $client->url = $client->options['base_url'] . $client->url;
        }
        $curlopt[CURLOPT_URL] = $client->url;

        if ($client->options['curl_options']) {
            // array_merge would reset our numeric keys.
            foreach ($client->options['curl_options'] as $key => $value) {
                $curlopt[$key] = $value;
            }
        }
        curl_setopt_array($client->handle, $curlopt);
        $client->parseResponse(curl_exec($client->handle));
        $client->info = (object) curl_getinfo($client->handle);
        $client->error = curl_error($client->handle);

        curl_close($client->handle);
        return $client;
    }

    public function formatQuery($parameters, $primary = '=', $secondary = '&')
    {
        $query = "";
        foreach ($parameters as $key => $value) {
            $pair = array(
                urlencode($key),
                urlencode($value)
            );
            $query .= implode($primary, $pair) . $secondary;
        }
        return rtrim($query, $secondary);
    }

    public function parseResponse($response)
    {
        $headers = array();
        $http_ver = strtok($response, "\n");

        while ($line = strtok("\n")) {
            if (Tools::strlen(trim($line)) == 0) {
                break;
            }

            list($key, $value) = explode(':', $line, 2);
            $key = trim(Tools::strtolower(str_replace('-', '_', $key)));
            $value = trim($value);
            if (empty($headers[$key])) {
                $headers[$key] = $value;
            } elseif (is_array($headers[$key])) {
                $headers[$key][] = $value;
            } else {
                $headers[$key] = array(
                    $headers[$key],
                    $value
                );
            }
        }

        $this->headers = (object) $headers;
        $this->response = strtok("");
    }

    public function getResponseFormat()
    {
        if (! $this->response) {
            throw new CartsGuruRAPIException("A response must exist before it can be decoded.");
        }

        // User-defined format.
        if (! empty($this->options['format'])) {
            return $this->options['format'];
        }

        // Extract format from response content-type header.
        if (! empty($this->headers->content_type)) {
            if (preg_match($this->options['format_regex'], $this->headers->content_type, $matches)) {
                return $matches[2];
            }
        }

        throw new CartsGuruRAPIException("Response format could not be determined.");
    }

    public function decodeResponse()
    {
        if (empty($this->decoded_response)) {
            $format = $this->getResponseFormat();
            if (! array_key_exists($format, $this->options['decoders'])) {
                throw new CartsGuruRAPIException($format.' is not a supported ' . 'format');
            }

            $this->decoded_response = call_user_func($this->options['decoders'][$format], $this->response);
        }

        return $this->decoded_response;
    }

    /**
     * This method return true if connect to server is ok
     *
     * @return bool
     */
    public function checkAccess($adminUrl)
    {
        $fields = array(
            'plugin' => 'prestashop',
            'pluginVersion' => _CARTSGURU_VERSION_,
            'storeVersion' => _PS_VERSION_,
            'adminUrl' => $adminUrl
        );
        $result = $this->post(self::API_PATH_REGISTER, $fields);
        return $result;
    }

    public function getDashboardStatistics($fields)
    {
        return $this->post(self::API_PATH_STATISTICS, $fields);
    }

    public function subscribe($data)
    {
        $fields = array(
            'country' => CountryCore::getIsoById($data['country']),
            //'state' => $data['state'],
            'title' => $data['title'],
            'website' => $data['website'],
            'phoneNumber' => $data['phoneNumber'],

            //user creation
            'email'  => $data['email'],
            'lastname' => $data['lastname'],
            'firstname' => $data['firstname'],
            'password' => $data['password'],

            'plugin' => 'prestashop',
            'pluginVersion' => _CARTSGURU_VERSION_,
            'storeVersion' => _PS_VERSION_,
            'adminUrl' => $data['adminUrl']
        );

        $result = $this->post(self::API_PATH_SUBSCRIBE, $fields);
        return $result;
    }
}

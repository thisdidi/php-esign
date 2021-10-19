<?php

namespace Conle\ESign\Core;

use Conle\ESign\Exceptions\HttpException;
use Conle\ESign\Support\Collection;
use Conle\ESign\Support\Log;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAPI
{
    /**
     * Http instance.
     *
     * @var Http
     */
    protected $http;

    /**
     * The request token.
     *
     * @var AccessToken
     */
    protected $accessToken;

    const GET = 'get';
    const POST = 'post';
    const JSON = 'json';
    const PUT = 'put';
    const DELETE = 'delete';

    /**
     * @var int
     */
    protected static $maxRetries = 2;

    /**
     * Constructor.
     *
     * @param AccessToken $accessToken
     */
    public function __construct(AccessToken $accessToken)
    {
        $this->setAccessToken($accessToken);
    }

    /**
     * Return the http instance.
     *
     * @return Http
     */
    public function getHttp($method, array $args = [])
    {
        if (is_null($this->http)) {
            $this->http = new Http();
        }

        if (0 === count($this->http->getMiddlewares())) {
            $this->registerHttpMiddlewares($method, $args);
        }

        return $this->http;
    }

    /**
     * Set the http instance.
     *
     * @param Http $http
     *
     * @return $this
     */
    public function setHttp(Http $http)
    {
        $this->http = $http;

        return $this;
    }

    /**
     * Return the current accessToken.
     *
     * @return AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set the request token.
     *
     * @param AccessToken $accessToken
     *
     * @return $this
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @param int $retries
     */
    public static function maxRetries($retries)
    {
        self::$maxRetries = abs($retries);
    }

    /**
     * Parse JSON from response and check error.
     *
     * @param $method
     * @param array $args
     * @return Collection|null
     * @throws HttpException
     */
    public function parseJSON($method, array $args)
    {
        $http = $this->getHttp($method, $args);

        $contents = $http->parseJSON(call_user_func_array([$http, $method], $args));

        if (empty($contents)) {
            return null;
        }

        $this->checkAndThrow($contents);

        return (new Collection($contents))->get('data');
    }

    /**
     * Register Guzzle middlewares.
     */
    protected function registerHttpMiddlewares($method, array $args = [])
    {
        // log
        $this->http->addMiddleware($this->logMiddleware());
        // retry
        $this->http->addMiddleware($this->retryMiddleware());
        // access token
        $this->http->addMiddleware($this->accessTokenMiddleware($method, $args));
    }

    /**
     * Attache access token to request query.
     *
     * @return \Closure
     */
    protected function accessTokenMiddleware($method, array $args = [])
    {
        /*return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                if (!$this->accessToken) {
                    return $handler($request, $options);
                }

                $request = $request->withHeader('X-Tsign-Open-App-Id', $this->accessToken->getAppId());
                $request = $request->withHeader('X-Tsign-Open-Token', $this->accessToken->getToken());
                $request = $request->withHeader('Content-Type', 'application/json');

                return $handler($request, $options);
            };
        };*/

        return function (callable $handler) use ($method, $args) {
            return function (RequestInterface $request, array $options) use ($handler, $method, $args) {

                $method = strtoupper($method);
                $contentMD5 = "{}";
                switch ($method) {
                    case 'GET':
                        break;
                    default:
                        if (!empty($args[1])) {
                            $contentMD5 = $this->doContentMd5(json_encode((object)$args[1], JSON_FORCE_OBJECT));
                        }
                        break;
                }

                $appId = $this->accessToken->getAppId();
                $accept = "application/json";
                $contentType = "application/json; charset=UTF-8";
                $url = $args[0];
                $date = "";
                $headers = "";

                $plaintext = $method . "\n" . $accept . "\n" . $contentMD5 . "\n" . $contentType . "\n" . $date . "\n" . $headers;
                $plaintext = $headers == "" ? $plaintext . $url : $plaintext . "\n" . $url;


                $request = $request->withHeader('X-Tsign-Open-App-Id', $appId);
                $request = $request->withHeader("X-Tsign-Open-Auth-Mode", "Signature");
                list($msec, $sec) = explode(' ', microtime());
                $msectime = (string)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
                $request = $request->withHeader("X-Tsign-Open-Ca-Timestamp", $msectime);
                $request = $request->withHeader("Accept", $accept);
                $request = $request->withHeader("Content-Type", $contentType);
                $reqSignature = $this->doSignatureBase64($plaintext, $this->accessToken->getSecret());
                $request = $request->withHeader("X-Tsign-Open-Ca-Signature", $reqSignature);
                $request = $request->withHeader("Content-MD5", $contentMD5);
                //   header.put("X-Tsign-Open-Ca-Timestamp", String.valueOf(timeStamp));
                //      header.put();
                //      header.put();
                //      header.put("X-Tsign-Open-Ca-Signature", reqSignature);
                //      header.put("Content-MD5", contentMD5);

                return $handler($request, $options);
            };
        };
    }

    protected function doSignatureBase64(string $plaintext, string $secret)
    {
        $sign = hash_hmac('sha256', $plaintext, $secret, true);
        return base64_encode($sign);
    }

    protected function doContentMd5(string $content)
    {
        return base64_encode(md5($content));
    }

    /**
     * Log the request.
     *
     * @return \Closure
     */
    protected function logMiddleware()
    {
        return Middleware::tap(function (RequestInterface $request, $options) {
            Log::debug("Request: {$request->getMethod()} {$request->getUri()} " . json_encode($options));
            Log::debug('Request headers:' . json_encode($request->getHeaders()));
        });
    }

    /**
     * Return retry middleware.
     *
     * @return \Closure
     */
    protected function retryMiddleware()
    {
        return Middleware::retry(function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null
        ) {
            // Limit the number of retries to 2
            if ($retries <= self::$maxRetries && $response && $body = $response->getBody()) {
                // Retry on server errors
                if (false !== stripos($body, 'code') && (false !== stripos($body, '40001') || false !== stripos($body, '42001'))) {
                    $token = $this->accessToken->getToken(true);

                    $request = $request->withHeader('X-Tsign-Open-App-Id', $this->accessToken->getAppId());
                    $request = $request->withHeader('X-Tsign-Open-Token', $token);
                    $request = $request->withHeader('Content-Type', 'application/json');

                    Log::debug("Retry with Request Token: {$token}");

                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Check the array data errors, and Throw exception when the contents contains error.
     *
     * @param array $contents
     * @throws HttpException
     */
    protected function checkAndThrow(array $contents)
    {
        if (isset($contents['code']) && 0 !== $contents['code']) {
            if (empty($contents['message'])) {
                $contents['message'] = 'Unknown';
            }

            throw new HttpException($contents['message'], $contents['code']);
        }
    }
}
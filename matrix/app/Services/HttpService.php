<?php

namespace Matrix\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\TransferException;

use GuzzleHttp\Exception\TooManyRedirectsException;

use GuzzleHttp\Exception\RequestException;

use GuzzleHttp\Exception\ConnectException;

use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Log;

class HttpService extends BaseService
{
    protected $client;

    public function __construct(string $baseUri, float $timeout = 2.0)
    {
        $clientConfig = [
            'base_uri' => $baseUri,
            'timeout' => $timeout,
        ];
        $this->client = new Client($clientConfig);
    }

    public function send(string $method, string $uri, array $options)
    {
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (ClientException $e) {
            Log::error('Connect Exception', [$e]);
            return NULL;
        } catch (ServerException $e) {
            Log::error('Connect Exception', [$e]);
            return NULL;
        } catch (BadResponseException $e) {
            Log::error('Connect Exception', [$e]);
            return NULL;
        } catch (ConnectException $e) {
            Log::error('Connect Exception', [$e]);
            return NULL;
        } catch (TooManyRedirectsException $e) {
            Log::error('Connect Exception', [$e]);
            return NULL;
        } catch (RequestException $e) {
            Log::error('Connect Exception', [$e]);
            return NULL;
        } catch (TransferException $e) {
            Log::error('Connect Exception', [$e]);
            return NULL;
        }

        return $response;
    }

    public function get(string $uri, array $data = [], array $headers = [], array $cookies = [])
    {
        if (!empty($data)) {
            $options = [
                'query' => $data,
            ];
        }

        $headers['Accept'] = 'application/json';
        $options['headers'] = $headers;

        if (!empty($cookies)) {
            $sessionDomain = config('session.domain');
            $options['cookies'] = CookieJar::fromArray($cookies, $sessionDomain);
            $options['connect_timeout'] = 1000;
            $options['timeout'] = 1000;
        }

        $response = $this->send('GET', $uri, $options);
        //Log::info('response:'.print_r($response, true));
        if (empty($response)) {
            return [];
        }

        $httpStatusCode = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $body = $response->getBody();
        $content = $body->getContents();

        return [
            'code' => SYS_STATUS_OK,
            'data' => $content,
        ];
    }

    public function delete(string $uri, array $data = [], array $headers = [], array $cookies = [])
    {
        if (!empty($data)) {
            $options = [
                'query' => $data,
            ];
        }

        $headers['Accept'] = 'application/json';
        $options['headers'] = $headers;

        if (!empty($cookies)) {
            $sessionDomain = config('session.domain');
            $options['cookies'] = CookieJar::fromArray($cookies, $sessionDomain);
            $options['connect_timeout'] = 1000;
            $options['timeout'] = 1000;
        }

        $response = $this->send('DELETE', $uri, $options);
        //Log::info('response:'.print_r($response, true));
        if (empty($response)) {
            return [];
        }

        $httpStatusCode = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $body = $response->getBody();
        $content = $body->getContents();

        return [
            'code' => SYS_STATUS_OK,
            'data' => $content,
        ];
    }

    public function deleteJson(string $uri, array $data = [], array $headers = [], array $cookies = [])
    {
        $headers['Accept'] = 'application/json';
        $options['headers'] = $headers;

        $resp = $this->delete($uri, $data, $headers, $cookies);
        if (empty($resp) || SYS_STATUS_OK !== $resp['code']) {
            Log::error('Http error: ', [$resp]);
            return [];
        }

        $ret = @json_decode($resp['data'], true);

        return $ret ?? [];
    }



    public function getJson(string $uri, array $data = [], array $headers = [], array $cookies = [])
    {
        $headers['Accept'] = 'application/json';
        $options['headers'] = $headers;
        $resp = $this->get($uri, $data, $headers, $cookies);
        if (empty($resp) || SYS_STATUS_OK !== $resp['code']) {
            Log::error('Http error: ', [$resp]);
            return [];
        }

        $ret = @json_decode($resp['data'], true);

        return $ret ?? [];
    }

    public function postJson(string $uri, array $data = [], array $headers = [], array $cookies = [])
    {
        $headers['Accept'] = 'application/json';
        $options['headers'] = $headers;
        $resp = $this->post($uri, $data, $headers, $cookies);
        if (empty($resp) || SYS_STATUS_OK !== $resp['code']) {
            return [];
        }

        $ret = @json_decode($resp['data'], true);

        return $ret ?? [];
    }

    public function post(string $uri, array $data = [], array $headers = [], array $cookies = [])
    {
        $options = [
            'form_params' => $data,
            'timeout' => 1000,//设置为0代表永久等待,以s为单位,此处设置1000s,参考链接http://docs.guzzlephp.org/en/latest/request-options.html#read-timeout
        ];

        $headers['Accept'] = 'application/json';
        $options['headers'] = $headers;

        if (!empty($cookies)) {
            $sessionDomain = config('session.domain');
            $options['cookies'] = CookieJar::fromArray($cookies, $sessionDomain);
        }

        $response = $this->send('POST', $uri, $options);
        if (empty($response)) {
            return [];
        }

        $httpStatusCode = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $body = $response->getBody();
        $content = $body->getContents();

        return [
            'code' => SYS_STATUS_OK,
            'data' => $content,
        ];
    }

    public function postUc(string $uri, array $data = [], array $headers = [], array $cookies = [])
    {
        $options = [
            'json' => $data,
        ];

        if (!empty(array_get($data, 'nonce'))) {
            $options['query'] = ['nonce' => array_get($data, 'nonce')];
        }


        $headers['Accept'] = 'application/json';
        $options['headers'] = $headers;

        if (!empty($cookies)) {
            $sessionDomain = config('session.domain');
            $options['cookies'] = CookieJar::fromArray($cookies, $sessionDomain);
        }

        $response = $this->send('POST', $uri, $options);
        if (empty($response)) {
            return [];
        }

        $httpStatusCode = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $body = $response->getBody();
        $content = $body->getContents();

        return [
            'code' => SYS_STATUS_OK,
            'data' => $content,
        ];
    }

    public function deleteUc(string $uri, array $data = [], array $headers = [], array $cookies = [])
    {
        $options = [
            'json' => $data,
        ];

        if (!empty(array_get($data, 'nonce'))) {
            $options['query'] = ['nonce' => array_get($data, 'nonce')];
        }

        $headers['Accept'] = 'application/json';
        $options['headers'] = $headers;

        if (!empty($cookies)) {
            $sessionDomain = config('session.domain');
            $options['cookies'] = CookieJar::fromArray($cookies, $sessionDomain);
            $options['connect_timeout'] = 1000;
            $options['timeout'] = 1000;
        }

        $response = $this->send('DELETE', $uri, $options);
        //Log::info('response:'.print_r($response, true));
        if (empty($response)) {
            return [];
        }

        $httpStatusCode = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $body = $response->getBody();
        $content = $body->getContents();

        return [
            'code' => SYS_STATUS_OK,
            'data' => $content,
        ];
    }

}

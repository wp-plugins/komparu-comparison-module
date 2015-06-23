<?php

namespace Komparu\PhpClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use Komparu\PhpClient\Exceptions\UnauthorizedException;
use Komparu\PhpClient\Exceptions\NotFoundException;
use Komparu\PhpClient\Exceptions\UnknownException;
use Komparu\PhpClient\Exceptions\ValidationException;
use Komparu\PhpClient\Exceptions\RequestTimeoutException;

class Client {

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $url = 'http://api.komparu.com/v1';

    /**
     * @var string
     */
    protected $resource = '';

    /**
     * @var string
     */
    protected $defaultResource = '';

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client) {
        $this->client = $client;
        $this->resource = $this->defaultResource;

        // First add the server domain. This can be overridden using
        // the domain() method.
        $this->client->getEmitter()->on('before', function(BeforeEvent $event) {
            $event->getRequest()->setHeader('X-Auth-Domain', $_SERVER['SERVER_NAME']);
        });

        // Reset the params and resource after each request
        $this->client->getEmitter()->on('complete', function(CompleteEvent $event) {
            $this->params = [];
            $this->resource = $this->defaultResource;
        });
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken($token) {
        $this->client->getEmitter()->on('before', function(BeforeEvent $event) use ($token) {
            $event->getRequest()->setHeader('X-Auth-Token', $token);
        });

        return $this;
    }

    /**
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain) {
        $this->client->getEmitter()->on('before', function(BeforeEvent $event) use ($domain) {
            $event->getRequest()->setHeader('X-Auth-Domain', $domain);
        });

        return $this;
    }

    /**
     * @param string $lang
     * @return $this
     */
    public function setLanguage($lang) {
        $this->client->getEmitter()->on('before', function(BeforeEvent $event) use ($lang) {
            $event->getRequest()->setHeader('Accept-Language', $lang);
        });

        return $this;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string $resource
     * @return $this
     */
    public function resource($resource) {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @throws \Exception
     * @return string
     */
    protected function getResource() {
        if (!$this->resource) {
            throw new \Exception('Must provide a resource');
        }

        return $this->resource;
    }

    /**
     * @param string $username
     * @param string $password
     * @return Array
     */
    public function authenticate($username, $password) {
        $url = rtrim($this->url, '/') . '/auth';
        $options['body'] = compact('username', 'password');
        return $this->send('post', $url, $options);
    }

    /**
     * @param array $query
     * @return Array
     */
    public function get(Array $query = []) {
        $url = rtrim($this->url, '/') . '/' . $this->getResource();
        $options['query'] = array_replace_recursive($this->params, $query);
        return $this->send('get', $url, $options);
    }

    /**
     * @param int $id
     * @param array $query
     * @return Array
     */
    public function show($id, Array $query = []) {
        $url = rtrim($this->url, '/') . '/' . $this->getResource() . '/' . $id;
        $options['query'] = array_replace_recursive($this->params, $query);
        return $this->send('get', $url, $options);
    }

    /**
     * @param Array $body
     * @return Array
     */
    public function store(Array $body = []) {
        $url = rtrim($this->url, '/') . '/' . $this->getResource();
        $options['body'] = array_replace_recursive($this->params, $body);
        return $this->send('post', $url, $options);
    }

    /**
     * @param int $id
     * @param Array $body
     * @return Array
     */
    public function update($id, Array $body = []) {
        $url = rtrim($this->url, '/') . '/' . $this->getResource() . '/' . $id;
        $options['body'] = array_replace_recursive($this->params, $body);
        return $this->send('put', $url, $options);
    }

    /**
     * @param int $id
     * @param Array $body
     * @return Array
     */
    public function delete($id, Array $body = []) {
        $url = rtrim($this->url, '/') . '/' . $this->getResource() . '/' . $id;
        $options['body'] = array_replace_recursive($this->params, $body);
        return $this->send('delete', $url, $options);
    }

    /**
     * @return Array
     */
    public function options() {
        $url = rtrim($this->url, '/') . '/' . $this->getResource();
        return $this->send('options', $url);
    }

    /**
     * @param array $bulk
     * @return Array
     */
    public function bulk(Array $bulk) {
        $url = rtrim($this->url, '/') . '/' . $this->getResource() . '/_bulk';
        $body['bulk'] = $bulk;
        $options['body'] = array_replace_recursive($this->params, $body);
        return $this->send('post', $url, $options);
    }

    /**
     * @param $method
     * @param $url
     * @param $options
     * @return Array
     */
    public function send($method, $url, $options = []) {
        try {
            $request = $this->client->createRequest($method, $url, $options);
            $response = $this->client->send($request);
            return $this->handleResponse($response);
        } catch (ServerException $e) {
            return $this->handleResponse($e->getResponse());
        } catch (ClientException $e) {
            return $this->handleResponse($e->getResponse());
        }
    }

    /**
     * @param ResponseInterface $response
     * @return Array
     * @throws Exception
     * @throws UnauthorizedHttpException
     * @throws NotFoundException
     * @throws RequestTimeoutException
     * @throws UnauthorizedException
     * @throws UnknownException
     * @throws ValidationException
     */
    protected function handleResponse(ResponseInterface $response)
    {
        switch ($response->getStatusCode()) {

            case 401:
                throw new UnauthorizedException($response);

            case 404:
                throw new NotFoundException($response);

            case 408:
                throw new RequestTimeoutException($response);

            case 422:
                try{
                    $data = $response->json();
                } catch (\RuntimeException $e) {
                    $this->throwError($response);
                }
                $e = new ValidationException($data['message']);
                $e->setErrors($data['errors']);
                throw $e;

            case 200:

                try{
                    $data = $response->json();
                } catch (\RuntimeException $e) {
                    $this->throwError($response);
                }

                if (isset($data['code']) && is_numeric($data['code']) && isset($data['message'])) {

                    switch ($data['code'])
                    {
                        case 401:
                            throw new UnauthorizedException($response);

                        case 404:
                            throw new NotFoundException($response);

                        case 408:
                            throw new RequestTimeoutException($response);

                        case 422:
                            $e = new ValidationException($data['message']);
                            $e->setErrors($data['errors']);
                            throw $e;

                        default:
                            $this->throwError($response);
                    }
                }

                return $response->json();

            case 204:
                try{
                    $data = $response->json();
                } catch (\RuntimeException $e) {
                    $this->throwError($response);
                }
                return $data;

            default:
                $this->throwError($response);
                exit;
        }
    }

    protected function throwError($response)
    {
        if (strpos($_SERVER['HTTP_HOST'], '.komparu.dev') !== FALSE || strpos($_SERVER['HTTP_HOST'], '.komparu.test') !== FALSE || !class_exists('\Log')) {
            file_put_contents('errorapi.html', $response->getBody());
            echo "<html><head><title>" . $response->getReasonPhrase() . "</title><link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>"
                 . "<style>*{font-family:'Open Sans', Arial;color:#333;}#trace{padding:1em;border-radius:5px;background:#fff;opacity:.9;}#trace p{line-height:1.5em;padding:.5em 1em;margin:0;}"
                 . "#trace p:nth-child(6){background:yellow;}}</style></head><body style='margin:0;background:red;height:100%;'><div style='padding:1em 3em;'>";
            echo "<h1>" . $response->getReasonPhrase() . " <br /><a href='" . $response->getEffectiveUrl() . "' style='word-wrap: break-word;overflow: hidden;color:blue;'> " . urldecode($response->getEffectiveUrl()) . "</a></h1>";
            echo "<iframe src='/errorapi.html' width='100%' height='50%' style='border:0;margin-bottom:3em;'></iframe>";
            $exception = new \Exception(urldecode($response->getReasonPhrase() . " => " . $response->getEffectiveUrl()));
            echo "<div id='trace'><p>" . str_replace("\n", "</p><p>", $exception->getTraceAsString()) . "</p></div>";
            echo "</div></body></html>";
            exit;
        }
        else
        {
            \Log::error($response->getBody());
        }
    }

    /**
     * @param string $method
     * @param mixed $param
     * @return $this
     */
    public function __call($method, $params) {
        $this->params[$method] = current($params);
        return $this;
    }

}
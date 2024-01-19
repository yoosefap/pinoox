<?php
/**
 *      ****  *  *     *  ****  ****  *    *
 *      *  *  *  * *   *  *  *  *  *   *  *
 *      ****  *  *  *  *  *  *  *  *    *
 *      *     *  *   * *  *  *  *  *   *  *
 *      *     *  *    **  ****  ****  *    *
 * @author   Pinoox
 * @link https://www.pinoox.com/
 * @license  https://opensource.org/licenses/MIT MIT License
 */

namespace Pinoox\Component\Http;

use Pinoox\Component\Helpers\HelperArray;
use Pinoox\Component\Kernel\Exception;
use Pinoox\Component\Router\Collection;
use Pinoox\Component\Router\Route;
use Pinoox\Component\Validation\Factory as ValidationFactory;
use Pinoox\Component\Validation\Validation;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request as RequestSymfony;
use Symfony\Component\Routing\RequestContext;

class Request extends RequestSymfony
{
    public InputBag $json;

    public function initialize(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->initJsonData();
    }

    public function value(string $key, mixed $default = null, string $validation = ''): mixed
    {
        return HelperArray::parseParam(
            $this->all(),
            $key,
            $default,
            $validation
        );
    }

    public function get(string|array $keys, mixed $default = null, string $validation = ''): array
    {
        return HelperArray::parseParams(
            $this->all(),
            $keys,
            $default,
            $validation
        );
    }

    private function initJsonData(): void
    {
        $data = [];
        if (!empty($this->getContent()))
            $data = $this->toArray();

        $this->json = new InputBag($data);
    }

    private RequestContext $context;
    private ValidationFactory $validation;

    /**
     * get current Route
     *
     * @return array|null
     */
    public function route(): ?\Pinoox\Component\Router\Route
    {
        return @$this->attributes->get('_router');
    }

    /**
     * get current Collection
     *
     * @return Collection|null
     */
    public function collection(): Collection|null
    {
        return @$this->route()->getCollection();
    }

    public static function create(string $uri, string $method = 'GET', array $parameters = [], array $cookies = [], array $files = [], array $server = [], $content = null): static
    {
        $server = array_replace([
            'SERVER_NAME' => 'localhost',
            'SERVER_PORT' => 80,
            'HTTP_HOST' => 'localhost',
            'HTTP_USER_AGENT' => 'Pinoox',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'REMOTE_ADDR' => '127.0.0.1',
            'SCRIPT_NAME' => '',
            'SCRIPT_FILENAME' => '',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_TIME' => time(),
            'REQUEST_TIME_FLOAT' => microtime(true),
        ], $server);
        return parent::create($uri, $method, $parameters, $cookies, $files, $server, $content);
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    public function query($keys, $default = null, $validation = null, $removeNull = false): array
    {
        return HelperArray::parseParams(
            $this->query->all(),
            $keys,
            $default,
            $validation,
            $removeNull
        );
    }

    public function queryOne($key, $default = null, $validation = null): mixed
    {
        return HelperArray::parseParam(
            $this->query->all(),
            $key,
            $default,
            $validation,
        );
    }

    public function request($keys, $default = null, $validation = null, $removeNull = false): array
    {
        return HelperArray::parseParams(
            $this->request->all(),
            $keys,
            $default,
            $validation,
            $removeNull
        );
    }

    public function requestOne($key, $default = null, $validation = null): mixed
    {
        return HelperArray::parseParam(
            $this->json->all(),
            $key,
            $default,
            $validation,
        );
    }

    public function attributes($keys, $default = null, $validation = null, $removeNull = false): array
    {
        return HelperArray::parseParams(
            $this->attributes->all(),
            $keys,
            $default,
            $validation,
            $removeNull
        );
    }

    public function attributesOne($key, $default = null, $validation = null) : mixed
    {
        return HelperArray::parseParam(
            $this->attributes->all(),
            $key,
            $default,
            $validation,
        );
    }


    public function json($keys, $default = null, $validation = null, $removeNull = false): array
    {
        return HelperArray::parseParams(
            $this->json->all(),
            $keys,
            $default,
            $validation,
            $removeNull
        );
    }

    public function jsonOne($key, $default = null, $validation = null)  : mixed
    {
        return HelperArray::parseParam(
            $this->json->all(),
            $key,
            $default,
            $validation,
        );
    }

    public static function take(): static
    {
        return static::createFromGlobals();
    }

    public function setValidation(ValidationFactory $validation): void
    {
        $this->validation = $validation;
    }

    public function getValidation(): ValidationFactory
    {
        return $this->validation;
    }

    public function validate(array $rules, array $data = [])
    {
        return $this->validate($data, $rules)->validate();
    }

    public function validation(array $rules, array $data = []): Validation
    {
        if (empty($data)) {
            $data = HelperArray::parseParams(
                $this->all(),
                array_keys($rules),
            );
        }
        return $this->getValidation()->make($data, $rules);
    }

    public function all(): array
    {
        return array_replace(
            $this->attributes->all(),
            $this->request->all(),
            $this->query->all(),
            $this->json->all(),
        );
    }

    public function getContext(): RequestContext
    {
        if (empty($this->context)) {
            $this->context = new RequestContext();
            $this->context->setBaseUrl($this->getBaseUrl());
            $this->context->setPathInfo($this->getPathInfo());
            $this->context->setMethod($this->getMethod());
            $this->context->setHost($this->getHost());
            $this->context->setScheme($this->getScheme());
            $this->context->setHttpPort($this->isSecure() || null === $this->getPort() ? 80 : $this->getPort());
            $this->context->setHttpsPort($this->isSecure() && null !== $this->getPort() ? $this->getPort() : 443);
            $this->context->setQueryString($this->server->get('QUERY_STRING', ''));
        }
        return $this->context;
    }
}
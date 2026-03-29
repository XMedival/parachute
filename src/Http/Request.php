<?php

namespace Parachute\Http;

use UnexpectedValueException;
use Parachute\Support\Arr;

class Request
{
    protected const METHODS = [
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'DELETE',
        'CONNECT',
        'OPTIONS',
        'TRACE',
        'PATCH',
    ];

    public Arr $headers;

    public Arr $query;

    public Arr $post;

    public Arr $cookies;

    public Arr $files;

    public string $path;

    public string $host;

    public string $remote;

    public string $method;

    public string|null $body;


    public function __construct() {}

    public static function capture(): static
    {
        $r = new static();
        $r->method = self::parseMethod($_SERVER['REQUEST_METHOD']);
        $r->headers = self::parseHeaders($_SERVER);
        $r->query = new Arr($_GET);
        $r->post = new Arr($_POST);
        $r->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $r->host = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
        $r->remote = $_SERVER['REMOTE_ADDR'] . ':' . $_SERVER['REMOTE_PORT'];
        $r->cookies = new Arr($_COOKIE);
        $r->files = new Arr();
        foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                $r->files[$key] = [];
                foreach ($file['name'] as $i => $name) {
                    $r->files[$key][] = File::fromUpload([
                        'name' => $file['name'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'size' => $file['size'][$i],
                        'type' => $file['type'][$i],
                        'error' => $file['error'][$i],
                    ]);
                }
            } else {
                $r->files[$key] = File::fromUpload($file);
            }
        }
        return $r;
    }

    protected static function parseMethod(string $method): string
    {
        if (in_array($method, self::METHODS)) {
            return $method;
        }

        throw new UnexpectedValueException('Unexpected request method: ' . $method);
    }

    protected static function parseHeaders(array $server): Arr
    {
        $headers = new Arr();
        foreach ($server as $key => $values) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(strtr(substr($key, 5), '_', '-'));
                $headers[$name] = $values;
            }
        }

        return $headers;
    }
}

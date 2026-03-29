<?php

namespace Parachute\Http;

class Response
{
    private const STATUS_MESSAGES = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    protected $statusCode = 200;
    protected $statusMessage = 'OK';
    protected $headers = [];
    protected $body;

    public function __construct($body = '', $statusCode = 200, array $headers = [])
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
        $this->statusMessage = self::STATUS_MESSAGES[$statusCode] ?? throw new \InvalidArgumentException("Invalid status code: $statusCode");
        $this->headers = $headers;
    }

    public function send()
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        echo $this->body;
    }

    public static function json($data, $statusCode = 200, array $headers = [])
    {
        $headers['Content-Type'] = 'application/json';
        return new self(json_encode($data), $statusCode, $headers);
    }

    public static function redirect($url, $statusCode = 302)
    {
        $response = new self('', $statusCode, ['Location' => $url]);
        return $response;
    }

    public static function view($template, $data = [], $statusCode = 200, array $headers = [])
    {
        ob_start();
        extract($data);
        include $template;
        $body = ob_get_clean();
        return new self($body, $statusCode, $headers);
    }

    public static function download(File $file, ?string $name = null, array $headers = [])
    {
        if ($file === null || !file_exists($file->path)) {
            return new self('File not found', 404);
        }
        $headers['Content-Type'] = $file->type;
        $headers['Content-Length'] = $file->size;
        $headers['Content-Disposition'] = 'attachment; filename="' . ($name ?? $file->name) . '"';
        return new self(file_get_contents($file->path), 200, $headers);
    }
}

<?php

declare(strict_types=1);

namespace System\Core;

final class Response
{
    private string $content;
    private int $status;
    private array $headers;

    public function __construct(string $content = '', int $status = 200, array $headers = [])
    {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function make(string $content = '', int $status = 200, array $headers = []): self
    {
        return new self($content, $status, $headers);
    }

    public static function redirect(string $url, int $status = 302): self
    {
        return new self('', $status, ['Location' => $url]);
    }

    public static function noContent(array $headers = []): self
    {
        return new self('', 204, $headers);
    }

    public static function created(mixed $data, array $headers = []): self
    {
        return self::json($data, 201, $headers);
    }

    public static function json(mixed $data, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json; charset=UTF-8';
        return new self((string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $status, $headers);
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        $content = $this->content;
        $contentType = '';
        foreach ($this->headers as $name => $value) {
            if (strcasecmp($name, 'Content-Type') === 0) {
                $contentType = (string) $value;
                break;
            }
        }

        if ($contentType === '' && stripos($content, '<html') !== false) {
            $content .= EnvironmentBadge::render();
        }

        echo $content;
    }
}

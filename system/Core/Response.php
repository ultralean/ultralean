<?php

declare(strict_types=1);

namespace System\Core;

final class Response
{
    private string $content;
    private int $status;
    private array $headers;

    public function __construct(
        string $content = '',
        int $status = 200,
        array $headers = []
    ) {
        $this->content = $content;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function make(
        string $content = '',
        int $status = 200,
        array $headers = []
    ): self {
        return new self($content, $status, $headers);
    }

    public static function redirect(
        string $url,
        int $status = 302
    ): self {
        return new self('', $status, ['Location' => $url]);
    }

    public static function noContent(array $headers = []): self
    {
        return new self('', 204, $headers);
    }

    public static function created(
        mixed $data,
        array $headers = []
    ): self {
        return self::json($data, 201, $headers);
    }

    public static function json(
        mixed $data,
        int $status = 200,
        array $headers = []
    ): self {
        $headers['Content-Type'] = 'application/json; charset=UTF-8';

        return new self(
            (string) json_encode(
                $data,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            $status,
            $headers
        );
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

        if (
            $contentType === '' &&
            stripos($content, '<html') !== false
        ) {
            $content .= EnvironmentBadge::render();
        }

        echo $content;
    }


    /**
     * Preserve submitted input for a redirect.
     *
     * Usage:
     * return back()->withData();
     * return back()->withData($data);
     */
    public function withData(?array $data = null): self
    {
        if (!in_array($this->status, [301, 302, 303, 307, 308], true)) {
            throw new \LogicException(
                'withData() requires a redirect response.'
            );
        }

        $locationKey = null;

        foreach ($this->headers as $name => $value) {
            if (strcasecmp((string) $name, 'Location') === 0) {
                $locationKey = $name;
                break;
            }
        }

        if ($locationKey === null) {
            throw new \LogicException('Redirect location is missing.');
        }

        $location = $this->headers[$locationKey];

        if (!is_string($location) || $location === '') {
            throw new \LogicException('Invalid redirect location.');
        }

        // Never leak form identifiers to external websites.

        $parts = parse_url($location);

        $currentHost = (string) ($_SERVER['HTTP_HOST'] ?? '');

        if (
            $parts === false ||
            $location === '' ||
            preg_match('/[\r\n\\\\]/', $location) ||
            isset($parts['user']) ||
            isset($parts['pass']) ||
            (
                isset($parts['scheme']) &&
                !in_array(
                    strtolower($parts['scheme']),
                    ['http', 'https'],
                    true
                )
            ) ||
            (
                isset($parts['host']) &&
                (
                    !isset($parts['scheme']) ||
                    !isset($parts['host']) ||
                    $currentHost === '' ||
                    strcasecmp(
                        $parts['host'],
                        preg_replace('/:\d+$/', '', $currentHost)
                    ) !== 0
                )
            ) ||
            (
                isset($parts['scheme']) &&
                (
                    !isset($parts['host']) ||
                    $currentHost === '' ||
                    strcasecmp(
                        $parts['host'],
                        preg_replace('/:\d+$/', '', $currentHost)
                    ) !== 0
                )
            ) ||
            (
                !isset($parts['host']) &&
                (
                    isset($parts['scheme']) ||
                    str_starts_with($location, '//')
                )
            )
        ) {
            throw new \LogicException(
                'Old input can only be attached to a local redirect.'
            );
        }


        $request = \request();

        $formKey = $request->input('_form_key');
        $formName = $request->input('_form_name');

        if (
            !is_string($formKey) ||
            !preg_match('/^[a-f0-9]{64}$/D', $formKey) ||
            !is_string($formName)
        ) {
            throw new \RuntimeException(
                'Cannot preserve old input: missing form identification. Use csrf_field().'
            );
        }

        FormState::preserve(
            \session(),
            $formKey,
            $formName,
            $data ?? $request->input()
        );

        $this->headers[$locationKey] = self::appendOldIdentifier(
            $location,
            $formKey
        );

        return $this;
    }

    /**
     * Add the old-input identifier without losing the URL fragment.
     */
    private static function appendOldIdentifier(
        string $url,
        string $identifier
    ): string {
        $fragment = '';

        $position = strpos($url, '#');

        if ($position !== false) {
            $fragment = substr($url, $position);
            $url = substr($url, 0, $position);
        }

        $position = strpos($url, '?');

        $path = $position === false
            ? $url
            : substr($url, 0, $position);

        $query = $position === false
            ? ''
            : substr($url, $position + 1);

        $params = [];

        if ($query !== '') {
            foreach (explode('&', $query) as $part) {
                $key = explode('=', $part, 2)[0];

                if (rawurldecode($key) !== '_old' && $part !== '') {
                    $params[] = $part;
                }
            }
        }

        $params[] = '_old=' . rawurlencode($identifier);

        return $path . '?' . implode('&', $params) . $fragment;
    }
}

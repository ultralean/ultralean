<?php

declare(strict_types=1);

namespace System\Core;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Stringable;

/**
 * All application date/time operations live here.
 * Persisted model timestamps are always represented as UTC ISO-8601 values.
 */
final class Clock implements Stringable
{
    private readonly DateTimeImmutable $value;

    private function __construct(DateTimeImmutable $value)
    {
        $this->value = $value;
    }

    public static function now(): self
    {
        return self::fromDate(new DateTimeImmutable('now', new DateTimeZone(self::applicationTimezone())));
    }

    public static function nowUtc(): self
    {
        return self::fromDate(new DateTimeImmutable('now', new DateTimeZone('UTC')));
    }

    public static function from(DateTimeInterface|string $value, ?string $timezone = null): self
    {
        if ($value instanceof DateTimeInterface) {
            return self::fromDate(new DateTimeImmutable($value->format('Y-m-d H:i:s.uP')));
        }

        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException('Date/time value cannot be empty.');
        }

        return self::fromDate(new DateTimeImmutable($value, new DateTimeZone($timezone ?? 'UTC')));
    }

    public static function applicationTimezone(): string
    {
        return (string) config('app.timezone', 'UTC');
    }

    public static function toUtc(DateTimeInterface|string $value, ?string $fromTimezone = null): self
    {
        return self::from($value, $fromTimezone)->inTimezone('UTC');
    }

    public static function toApplicationTimezone(DateTimeInterface|string $value): self
    {
        return self::from($value, 'UTC')->inApplicationTimezone();
    }

    public static function formatUtc(DateTimeInterface|string $value): string
    {
        return self::toUtc($value)->iso8601();
    }

    public static function formatApplication(DateTimeInterface|string $value, string $format = 'Y-m-d H:i:sP'): string
    {
        return self::toApplicationTimezone($value)->format($format);
    }

    public function inApplicationTimezone(): self
    {
        return self::fromDate($this->value->setTimezone(new DateTimeZone(self::applicationTimezone())));
    }

    public function inTimezone(string $timezone): self
    {
        return self::fromDate($this->value->setTimezone(new DateTimeZone($timezone)));
    }

    public function utc(): self
    {
        return $this->inTimezone('UTC');
    }

    public function format(string $format = 'Y-m-d H:i:sP'): string
    {
        return $this->value->format($format);
    }

    public function iso8601(): string
    {
        return $this->value->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }

    public function dateTime(): DateTimeImmutable
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->iso8601();
    }

    private static function fromDate(DateTimeImmutable $value): self
    {
        return new self($value);
    }
}

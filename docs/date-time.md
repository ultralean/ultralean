---
layout: docs
key: date-time
title: Date and time
---

# Date and time

ultralean uses one class for date/time operations:

```text
System\Core\Clock
```

There is no separate timestamp class.

## Application timezone

Set:

```php
'timezone' => 'Asia/Karachi',
```

Examples:

```text
UTC
Asia/Karachi
Europe/London
America/New_York
```

## Current time

```php
Clock::now();
```

returns a Clock value in the configured application timezone.

```php
Clock::nowUtc();
```

returns UTC.

## Conversion

```php
Clock::from($value);
Clock::toUtc($value);
Clock::toApplicationTimezone($value);
```

Convert to an arbitrary timezone:

```php
Clock::from($value)->inTimezone('America/New_York');
```

## Formatting

```php
Clock::formatUtc($value);
Clock::formatApplication($value);
```

Instance methods:

```php
$clock->format('Y-m-d H:i:sP');
$clock->iso8601();
$clock->dateTime();
```

## Storage rule

Framework/model timestamps are stored in UTC ISO-8601 form:

```text
2026-09-24T04:30:00Z
```

The UTC `Z` marker is part of the stored representation.

Do not silently convert database values to the application timezone and then pretend that was the stored value. The raw value remains available.

## Model records

```php
$user = User::findRecord(1);
```

Then:

```php
$user->created_at->iso8601();
$user->created_at->inApplicationTimezone()->format();
$user->created_at->utc()->format();
$user->created_at->inTimezone('Europe/London')->format();
```

This makes timezone conversion explicit.

## Browser display

For large lists of timestamps, it is often efficient to send UTC values to the browser and let JavaScript format them for the user's local timezone. This avoids repeatedly converting thousands of values in PHP when the browser can perform the presentation conversion.

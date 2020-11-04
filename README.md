# Validate

A declarative validator

## Easy to use

Validate's validators are easy to use and to extend.

They are all available as static functions under `Validate\V`, so they can be easily found.

### Example

```php
use Validate\V;

V::and(V::string(), V::minLength(5))('Hello'); // true
```

### Extend

```php
use Validate\V;

$notNull = static fn ($input): bool => $input !== null; // Equivalent to `V::not(V::null))`

$notNull(null); // false
V::not($notNull)(null); // true because it is not not null
```

## Contribute

Feel free to add some common validation rules

## Inspiration

I wanted to create a declarative validation library, so I looked around to see what did exist, so I came across [v8n](https://github.com/imbrn/v8n).

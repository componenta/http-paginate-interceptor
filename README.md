# Componenta HTTP Paginate Interceptor

HTTP interceptor for `componenta/interceptor` that turns `PaginatorInterface` into `Componenta\Http\ResourcePaginator` with `prev` / `next` links.

**[Русская документация](README.ru.md)**

## Boundary

This package contains one attribute: `#[Paginate]`. The attribute itself implements `InterceptorInterface`, so it does not need a separate `#[Intercept(...)]`.

It does not fetch data and does not create paginators. The route handler must return `Componenta\Stdlib\PaginatorInterface`.

## Installation

```bash
composer require componenta/http-paginate-interceptor
```

## Quick Start

```php
use Componenta\Interceptor\Http\Attribute\Respond;
use Componenta\Interceptor\Http\Paginate;

final class PostController
{
    #[Respond(200, 'application/json')]
    #[Paginate]
    public function index(PostListQuery $query): PaginatorInterface
    {
        return $this->posts->list($query);
    }
}
```

If the result does not implement `PaginatorInterface`, `#[Paginate]` returns it unchanged.

## Response Fields

By default `ResourcePaginator` receives:

```php
count, prev, next, range, page, results
```

You can keep only selected metadata fields. `results` is always added automatically:

```php
use Componenta\Interceptor\Http\Paginate;

#[Paginate(Paginate::FIELD_COUNT, Paginate::FIELD_NEXT)]
public function index(): PaginatorInterface {}
```

## HTTP Request In Context

`prev` and `next` generation requires the current `ServerRequestInterface`. `componenta/router-app` passes the HTTP request into callable context when executing route handlers. If `#[Paginate]` receives a paginator without that request attribute, it throws `LogicException`.

## Scope

`#[Paginate]` is restricted to `Scope::HTTP`.

## License

MIT

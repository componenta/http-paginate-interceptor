# Componenta HTTP Paginate Interceptor

HTTP-перехватчик для `componenta/interceptor`, который превращает `PaginatorInterface` в `Componenta\Http\ResourcePaginator` с `prev`/`next` ссылками.

**[English documentation](README.md)**

## Граница пакета

Пакет содержит один атрибут `#[Paginate]`. Этот атрибут сам реализует `InterceptorInterface`, поэтому ему не нужен отдельный `#[Intercept(...)]`.

Пакет не выполняет выборку данных и не создает пагинатор. Обработчик маршрута должен вернуть объект `Componenta\Stdlib\PaginatorInterface`.

## Установка

```bash
composer require componenta/http-paginate-interceptor
```

## Быстрый старт

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

Если результат не реализует `PaginatorInterface`, `#[Paginate]` возвращает его без изменений.

## Поля ответа

По умолчанию `ResourcePaginator` получает поля:

```php
count, prev, next, range, page, results
```

Можно оставить только часть служебных полей. `results` всегда добавляется автоматически:

```php
use Componenta\Interceptor\Http\Paginate;

#[Paginate(Paginate::FIELD_COUNT, Paginate::FIELD_NEXT)]
public function index(): PaginatorInterface {}
```

## HTTP-запрос в контексте

Для генерации `prev` и `next` нужен текущий `ServerRequestInterface`. `componenta/router-app` передает HTTP-запрос в callable context при выполнении обработчика маршрута. Если `#[Paginate]` получает пагинатор без request attribute, он бросает `LogicException`.

## Область выполнения

`#[Paginate]` ограничен областью `Scope::HTTP`.

## Лицензия

MIT

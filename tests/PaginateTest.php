<?php

declare(strict_types=1);

use Componenta\Http\ResourcePaginator;
use Componenta\Interceptor\CallableContext;
use Componenta\Interceptor\CallableContextInterface;
use Componenta\Interceptor\ContextHandlerInterface;
use Componenta\Interceptor\Http\Paginate;
use Componenta\Interceptor\Scope;
use Componenta\Stdlib\Paginator;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

final readonly class PaginateFixedResultHandler implements ContextHandlerInterface
{
    public function __construct(private mixed $result) {}

    public function handle(CallableContextInterface $context): mixed
    {
        return $this->result;
    }
}

it('wraps paginator into HTTP resource paginator', function () {
    $paginator = new Paginator(['a', 'b'], limit: 2, offset: 2, totalCount: 6);
    $request = new ServerRequest('GET', '/posts?limit=2&offset=2');
    $context = (new CallableContext(static fn() => null))
        ->withAttribute(ServerRequestInterface::class, $request);
    $interceptor = new Paginate();

    $result = $interceptor->intercept($context, new PaginateFixedResultHandler($paginator));

    expect($result)->toBeInstanceOf(ResourcePaginator::class)
        ->and($result->toArray())->toMatchArray([
            'count' => 6,
            'range' => [3, 4],
            'page' => 2,
            'results' => ['a', 'b'],
        ])
        ->and($result->toArray()['prev'])->toBe('/posts?limit=2')
        ->and($result->toArray()['next'])->toContain('offset=4');
});

it('returns non-paginator result unchanged', function () {
    $context = new CallableContext(static fn() => null);
    $interceptor = new Paginate();

    $result = $interceptor->intercept($context, new PaginateFixedResultHandler(['items' => []]));

    expect($result)->toBe(['items' => []]);
});

it('requires request attribute for paginator results', function () {
    $context = new CallableContext(static fn() => null);
    $interceptor = new Paginate();
    $paginator = new Paginator(['a']);

    expect(fn() => $interceptor->intercept($context, new PaginateFixedResultHandler($paginator)))
        ->toThrow(LogicException::class, ServerRequestInterface::class);
});

it('declares HTTP scope', function () {
    expect((new Paginate())->scopes->contains(Scope::HTTP))->toBeTrue();
});

<?php

declare(strict_types=1);

namespace Componenta\Interceptor\Http;

use Attribute;
use Componenta\Http\ResourcePaginator;
use Componenta\Interceptor\CallableContextInterface;
use Componenta\Interceptor\ContextHandlerInterface;
use Componenta\Interceptor\InterceptorInterface;
use Componenta\Interceptor\Scope;
use Componenta\Scope\ScopedInterface;
use Componenta\Scope\Scopes;
use Componenta\Stdlib\PaginatorInterface;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
final class Paginate implements InterceptorInterface, ScopedInterface
{
    public Scopes $scopes {
        get => Scopes::of(Scope::HTTP);
    }

    public const string FIELD_COUNT = 'count';
    public const string FIELD_PREV = 'prev';
    public const string FIELD_NEXT = 'next';
    public const string FIELD_RANGE = 'range';
    public const string FIELD_PAGE = 'page';
    private const string FIELD_RESULTS = 'results';

    public const array DEFAULT_FIELDS = [
        self::FIELD_COUNT,
        self::FIELD_PREV,
        self::FIELD_NEXT,
        self::FIELD_RANGE,
        self::FIELD_PAGE,
        self::FIELD_RESULTS,
    ];

    /** @var array<array-key, string> */
    private array $fields;

    /**
     * @param string ...$fields Fields to include in the resource paginator output.
     */
    public function __construct(string ...$fields)
    {
        $this->fields = $fields === [] ? self::DEFAULT_FIELDS : [...$fields, self::FIELD_RESULTS];
    }

    public function intercept(CallableContextInterface $context, ContextHandlerInterface $handler): mixed
    {
        $result = $handler->handle($context);

        if (!$result instanceof PaginatorInterface) {
            return $result;
        }

        $request = $context->getAttribute(ServerRequestInterface::class);

        if (!$request instanceof ServerRequestInterface) {
            throw new LogicException(sprintf(
                '%s requires a %s attribute in the callable context.',
                self::class,
                ServerRequestInterface::class,
            ));
        }

        return new ResourcePaginator($result, $request->getUri(), $this->fields);
    }

}

<?php
declare(strict_types=1);

namespace Boesing\TypedArrays;

use InvalidArgumentException;

/**
 * @template         TValue
 * @template-extends ArrayInterface<int,TValue>
 */
interface OrderedListInterface extends ArrayInterface
{
    /**
     * @psalm-param TValue $element
     * @psalm-return OrderedListInterface<TValue>
     * @psalm-immutable
     */
    public function add($element): OrderedListInterface;

    /**
     * @psalm-return TValue|null
     */
    public function at(int $position);

    /**
     * @psalm-param  Closure(TValue $value):bool $callback
     * @psalm-return OrderedListInterface<TValue>
     * @psalm-immutable
     */
    public function filter(callable $callback): OrderedListInterface;

    /**
     * @psalm-param  (Closure(TValue $a,TValue $b):int)|null $callback
     * @psalm-return OrderedListInterface<TValue>
     * @psalm-immutable
     */
    public function sort(?callable $callback = null): OrderedListInterface;

    /**
     * @psalm-param  list<OrderedListInterface<TValue>> $stack
     * @psalm-return OrderedListInterface<TValue>
     * @psalm-immutable
     */
    public function merge(...$stack): OrderedListInterface;

    /**
     * @template     TNewValue
     * @psalm-param  Closure(TValue $a):TNewValue $callback
     *
     * @psalm-return OrderedListInterface<TNewValue>
     * @psalm-immutable
     */
    public function map(callable $callback): OrderedListInterface;

    /**
     * @psalm-param  OrderedListInterface<TValue> $other
     * @psalm-param  (Closure(TValue $a,TValue $b):int)|null $valueComparator
     * @psalm-return OrderedListInterface<TValue>
     * @psalm-immutable
     */
    public function intersect(OrderedListInterface $other, ?callable $valueComparator = null): OrderedListInterface;

    /**
     * @psalm-param  OrderedListInterface<TValue> $other
     * @psalm-param  (Closure(TValue $a,TValue $b):int)|null $valueComparator
     * @psalm-return OrderedListInterface<TValue>
     * @psalm-immutable
     */
    public function diff(OrderedListInterface $other, ?callable $valueComparator = null): OrderedListInterface;

    /**
     * @psalm-param  Closure(TValue $value):non-empty-string $keyGenerator
     * @psalm-return MapInterface<TValue>
     */
    public function toMap(callable $keyGenerator): MapInterface;

    /**
     * @psalm-param  TValue $element
     * @psalm-return OrderedListInterface<TValue>
     * @psalm-immutable
     */
    public function removeElement($element): OrderedListInterface;

    /**
     * @psalm-param (Closure(TValue $value):non-empty-string)|null $unificationIdentifierGenerator
     * @psalm-param (Closure(TValue $value,TValue $other):TValue)|null  $callback
     *
     * @psalm-return OrderedListInterface<TValue>
     * @psalm-immutable
     */
    public function unify(
        ?callable $unificationIdentifierGenerator = null,
        ?callable $callback = null
    ): OrderedListInterface;

    /**
     * @psalm-param TValue|Closure(int $index):TValue $value
     * @psalm-return OrderedListInterface<TValue>
     * @psalm-immutable
     * @throws InvalidArgumentException if start index does is not fitting in the current list state.
     */
    public function fill(int $startIndex, int $amount, $value): OrderedListInterface;
}

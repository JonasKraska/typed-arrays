<?php
declare(strict_types=1);

namespace Boesing\TypedArrays;

use Webmozart\Assert\Assert;
use function array_combine;
use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function mhash;
use function serialize;
use function sort;
use const MHASH_SHA256;
use const SORT_NATURAL;

/**
 * @template            TValue
 * @template-extends    Array_<int,TValue>
 * @template-implements OrderedListInterface<TValue>
 */
abstract class OrderedList extends Array_ implements OrderedListInterface
{
    /**
     * @psalm-param list<TValue> $data
     */
    final public function __construct(array $data)
    {
        /** @psalm-suppress RedundantCondition */
        Assert::isList($data);
        parent::__construct($data);
    }

    public function merge(...$stack): OrderedListInterface
    {
        $instance = clone $this;
        $values = array_map(static function (OrderedListInterface $list): array {
            return $list->toNativeArray();
        }, $stack);

        $instance->data = array_values(array_merge($this->data, ...$values));

        return $instance;
    }

    public function map(callable $callback): OrderedListInterface
    {
        return new GenericOrderedList(array_values(
            array_map($callback, $this->data)
        ));
    }

    public function add($element): OrderedListInterface
    {
        $instance = clone $this;
        $instance->data[] = $element;

        return $instance;
    }

    public function at(int $position)
    {
        return $this->data[$position] ?? null;
    }

    public function sort(?callable $callback = null): OrderedListInterface
    {
        $data = $this->data;
        $instance = clone $this;
        if ($callback === null) {
            sort($data, SORT_NATURAL);
            $instance->data = $data;

            return $instance;
        }

        usort($data, $callback);
        $instance->data = $data;

        return $instance;
    }

    public function diff(OrderedListInterface $other, ?callable $valueComparator = null): OrderedListInterface
    {
        $diff1 = array_udiff(
            $this->toNativeArray(),
            $other->toNativeArray(),
            $valueComparator ?? $this->valueComparator()
        );
        $diff2 = array_udiff(
            $other->toNativeArray(),
            $this->toNativeArray(),
            $valueComparator ?? $this->valueComparator()
        );

        $instance = clone $this;
        $instance->data = array_values(array_merge(
            $diff1,
            $diff2
        ));

        return $instance;
    }

    public function intersect(OrderedListInterface $other, ?callable $valueComparator = null): OrderedListInterface
    {
        $instance = clone $this;
        $instance->data = array_values(array_uintersect(
            $instance->data,
            $other->toNativeArray(),
            $valueComparator ?? $this->valueComparator()
        ));

        return $instance;
    }

    public function toHashmap(callable $keyGenerator): HashmapInterface
    {
        $keys = array_map($keyGenerator, $this->data);
        Assert::allStringNotEmpty($keys);

        $combined = array_combine(
            $keys,
            $this->data
        );

        /**
         * Integerish strings are converted to integer when used as array keys
         *
         * @link https://3v4l.org/Y2ld5
         */
        Assert::allStringNotEmpty(array_keys($combined));

        return new GenericHashmap($combined);
    }

    public function remove($element): OrderedListInterface
    {
        /** @psalm-suppress MissingClosureParamType */
        return $this->filter(
            static function ($value) use ($element): bool {
                return $value !== $element;
            }
        );
    }

    public function filter(callable $callback): OrderedListInterface
    {
        $instance = clone $this;
        $instance->data = array_values(
            array_filter($this->data, $callback)
        );

        return $instance;
    }

    public function unify(
        ?callable $unificationIdentifierGenerator = null,
        ?callable $callback = null
    ): OrderedListInterface {
        /** @psalm-suppress MissingClosureParamType */
        $unificationIdentifierGenerator = $unificationIdentifierGenerator
            ?? static function ($value): string {
                return mhash(MHASH_SHA256, serialize($value));
            };

        $instance = clone $this;

        /** @psalm-var HashmapInterface<TValue> $unified */
        $unified = new GenericHashmap([]);

        foreach ($instance->data as $value) {
            $identifier = $unificationIdentifierGenerator($value);
            $unique = $unified->get($identifier) ?? $value;

            if ($callback) {
                $unique = $callback($unique, $value);
            }

            $unified = $unified->put($identifier, $unique);
        }

        $instance->data = $unified->toOrderedList()->toNativeArray();

        return $instance;
    }
}

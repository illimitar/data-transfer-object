<?php

namespace Spatie\DataTransferObject\Casters;

use ArrayAccess;
use LogicException;
use Spatie\DataTransferObject\Caster;
use Traversable;

class ArrayCaster implements Caster
{
    /** @var array */
    private $types;

    /** @var string */
    private $itemType;

    public function __construct(array $types, string $itemType)
    {
        $this->types = $types;
        $this->itemType = $itemType;
    }

    /**
     * @return array|ArrayAccess
     */
    public function cast($value)
    {
        foreach ($this->types as $type) {
            if ($type == 'array') {
                return $this->mapInto([], $value);
            }

            if (is_subclass_of($type, ArrayAccess::class)) {
                return $this->mapInto(new $type(), $value);
            }
        }

        throw new LogicException(
            "Caster [ArrayCaster] may only be used to cast arrays or objects that implement ArrayAccess."
        );
    }

    /**
     * @param array|ArrayAccess $destination
     * @param mixed $items
     *
     * @return array|ArrayAccess
     */
    private function mapInto($destination, $items)
    {
        if ($destination instanceof ArrayAccess && ! is_subclass_of($destination, Traversable::class)) {
            throw new LogicException(
                "Caster [ArrayCaster] may only be used to cast ArrayAccess objects that are traversable."
            );
        }

        foreach ($items as $key => $item) {
            $destination[$key] = $this->castItem($item);
        }

        return $destination;
    }

    private function castItem($data)
    {
        if ($data instanceof $this->itemType) {
            return $data;
        }

        if (is_array($data)) {
            return new $this->itemType($data);
        }

        throw new LogicException(
            "Caster [ArrayCaster] each item must be an array or an instance of the specified item type [{$this->itemType}]."
        );
    }
}

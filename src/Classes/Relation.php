<?php

namespace Fwt\Crud\Classes;

use Fwt\Crud\Enums\IdType;

class Relation
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $model,
        public IdType $type = IdType::UnsignedBigInteger,
        public bool $isOneToOne = false,
    )
    {
        //
    }
}

<?php

namespace Fwt\Crud\Attributes;

use Attribute;
use Fwt\Crud\Enums\IdType;


#[Attribute]
class PivotTable
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $model,
        public string $tableName = '',
        public IdType $parentType = IdType::UnsignedBigInteger,
        public IdType $foreignType = IdType::UnsignedBigInteger,
        public array $pivotColumns = [],
    )
    {
        //
    }
}

<?php

namespace Fwt\Crud\Classes;

use Fwt\Crud\Enums\ColumnType;

class Column
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $columnName,
        public ColumnType $columnType = ColumnType::String,
        public mixed $defaultValue = null,
        public bool $isUnique = false,
        public bool $isNullable = false,
        public bool $isShowInView = true,
    )
    {
        //
    }
}

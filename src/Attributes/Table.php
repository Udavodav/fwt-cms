<?php

namespace Fwt\Crud\Attributes;


use Fwt\Crud\Enums\IdType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    /**
     * Model attribute for generate CRUD interface.
     *
     * @param string|null $tableName Name table in database, default model name-s
     *
     * @param array $columns List columns in table
     * @param array $relationFields List columns in table with foreign key
     */
    public function __construct(
        public string $tableName = '', //Вместо этого можно подставить имя модели
        public IdType $idType = IdType::UnsignedBigInteger,
        public array $columns = [],
        public array $relations = []
    )
    {
        //
    }
}

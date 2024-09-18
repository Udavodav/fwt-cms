<?php

namespace Fwt\Crud\Enums;

enum ColumnType : string
{
    case String = 'string';
    case Integer = 'integer';
    case Date = 'date';
    case Text = 'text';

    public function convertToHtml()
    {
        return match ($this) {
            ColumnType::String, ColumnType::Text => 'text',
            ColumnType::Integer => 'number',
            ColumnType::Date => 'date',
        };
    }
}

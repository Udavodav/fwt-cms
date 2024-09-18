<?php

namespace Fwt\Crud\Enums;

enum IdType : string
{
    case UnsignedBigInteger = 'id';
    case String = 'string';
    case UUID = 'uuid';
    case ULID = 'ulid';
}

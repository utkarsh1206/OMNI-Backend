<?php

namespace App\Enums;

enum PermissionAction: int
{
    case VIEW = 1;
    case CREATE = 2;    
    case DELETE = 3;
    case DOWNLOAD = 4;
    case EDIT = 5;
    case ENABLE = 6;
    case MERGE = 7;
    case TRANSFER = 8;
}


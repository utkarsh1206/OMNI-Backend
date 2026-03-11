<?php

namespace App\Enums;

enum PermissionAction: int
{
    case VIEW = 1;
    case CREATE = 2; 
    case EDIT = 3;   
    case DELETE = 4;
    case DOWNLOAD = 5;
    case ENABLE = 6;
    case MERGE = 7;
    case TRANSFER = 8;
}


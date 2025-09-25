<?php

namespace App\Enums;

enum ChatbotMessageStatus: int
{
    case ACTIVE = 1;
    case TRANSFFERED = 2;    
    case DISCONNECTED = 3;
}


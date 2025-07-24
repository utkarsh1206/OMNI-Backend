<?php

namespace App\Enums;

enum ProviderType: int
{
    case SMS = 1;
    case API = 2;    
    case CHATBOT = 3;
}


<?php

namespace App\Enums;

enum NewsStatus: string
{
    case NEW = 'new';
    case CONTENT_PARSING = 'content_parsing';
    case CONTENT_ERROR = 'content_error';
    case CONTENT_PARSED = 'content_parsed';
    case AI_GENERATING = 'ai_generating';
    case AI_ERROR = 'ai_error';
    case DONE = 'done';
}

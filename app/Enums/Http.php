<?php

namespace App\Enums;

enum Http: int
{
    case SUCCESS = 200;

    case UNAUTHENTICATED = 401;
    case  THROTTLE = 429;

    case FORBIDDEN = 403;

    case VALIDATION_ERROR = 422;
    case NOT_FOUND = 404;
    case  NOT_VERIFIED = 409;
    case BAD_REQUEST = 400;
    case SERVER_ERROR = 500;
    case TIMEOUT_ERROR = 504;
}

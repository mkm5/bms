<?php declare(strict_types=1);

namespace App\Config;

enum CommunicationType: string
{
    case OTHER = 'other';
    case EMAIL = 'email';
    case PHONE_WORK = 'phone_work';
    case PHONE_PERSONAL = 'phone_personal';
}

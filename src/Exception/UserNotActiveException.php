<?php declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Throwable;

final class UserNotActiveException extends CustomUserMessageAccountStatusException
{
    private const string MESSAGE = 'User is inactive';

    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        return parent::__construct(self::MESSAGE, [], $code, $previous);
    }
}

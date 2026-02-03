<?php declare(strict_types=1);

namespace App\ValueResolver;

use App\Config\FormStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use ValueError;

class FormStatusFromNameResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== FormStatus::class) {
            return [];
        }

        $value = $request->attributes->get($argument->getName());
        if (!is_string($value)) {
            return [];
        }

        try {
            yield FormStatus::fromName($value);
        } catch (ValueError $e) {
            throw new NotFoundHttpException(sprintf('The status "%s" is not valid.', $value));
        }
    }
}

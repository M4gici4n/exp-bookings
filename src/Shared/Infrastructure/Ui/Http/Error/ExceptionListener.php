<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Ui\Http\Error;

use App\Shared\Domain\Exception\DomainException;
use App\Shared\Domain\Exception\ErrorStatus;
use App\Shared\Domain\Exception\ValidationFailedException;
use App\Shared\Infrastructure\Ui\Http\Error\MalformedRequestBodyException;
use App\Shared\Infrastructure\Ui\Http\Response\ResponseBuilder;
use App\Shared\Infrastructure\Ui\Http\Response\ValidationErrorItem;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

final class ExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ResponseBuilder $responseBuilder,
        private readonly LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $event->setResponse($this->buildResponse($event->getThrowable()));
    }

    private function buildResponse(Throwable $exception): Response
    {
        if ($exception instanceof ValidationFailedException) {
            return $this->responseBuilder->validationError(
                message: 'The request contains invalid fields.',
                errors: $this->toErrorItems($exception),
            );
        }

        if ($exception instanceof MalformedRequestBodyException) {
            return $this->responseBuilder->error(
                code: 'MALFORMED_REQUEST_BODY',
                message: 'Request body is not valid JSON: ' . $exception->getMessage(),
                status: Response::HTTP_BAD_REQUEST,
            );
        }

        if ($exception instanceof DomainException) {
            return $this->responseBuilder->error(
                code: $exception->errorCode(),
                message: $exception->getMessage(),
                status: $this->httpStatusFor($exception->errorStatus()),
            );
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $this->responseBuilder->error(
                code: 'HTTP_ERROR',
                message: $exception->getMessage() !== '' ? $exception->getMessage() : 'HTTP error.',
                status: $exception->getStatusCode(),
            );
        }

        $this->logger->error('Unhandled exception', ['exception' => $exception]);

        return $this->responseBuilder->error(
            code: 'INTERNAL_SERVER_ERROR',
            message: 'An unexpected error occurred.',
            status: Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    /** @return ValidationErrorItem[] */
    private function toErrorItems(ValidationFailedException $exception): array
    {
        $items = [];

        foreach ($exception->errors() as $error) {
            $items[] = new ValidationErrorItem(
                $error->code->value,
                $error->target,
                $error->message,
            );
        }

        return $items;
    }

    private function httpStatusFor(ErrorStatus $status): int
    {
        return match ($status) {
            ErrorStatus::NotFound => Response::HTTP_NOT_FOUND,
            ErrorStatus::Conflict => Response::HTTP_CONFLICT,
            ErrorStatus::InvalidArgument => Response::HTTP_UNPROCESSABLE_ENTITY,
            ErrorStatus::Internal => Response::HTTP_INTERNAL_SERVER_ERROR,
        };
    }
}

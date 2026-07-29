<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Ui\Http\Response;

use App\Shared\Infrastructure\Ui\Http\Response\ValidationErrorItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ResponseBuilder
{
    public function data(?array $data, int $status = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse(['data' => $data], $status);
    }

    public function created(array $data, string $location): JsonResponse
    {
        $response = $this->data($data, Response::HTTP_CREATED);
        $response->headers->set('Location', $location);

        return $response;
    }

    public function error(
        string $code,
        string $message,
        int $status,
        ?string $target = null,
        ?array $details = null,
    ): JsonResponse {
        $error = ['code' => $code, 'message' => $message];

        if ($target !== null) {
            $error['target'] = $target;
        }

        if ($details !== null) {
            $error['details'] = $details;
        }

        return new JsonResponse(['error' => $error], $status);
    }

    /** @param ValidationErrorItem[] $errors */
    public function validationError(
        string $message,
        array $errors,
        string $target = 'request_body',
    ): JsonResponse {
        return $this->error(
            code: 'VALIDATION_FAILED',
            message: $message,
            status: Response::HTTP_UNPROCESSABLE_ENTITY,
            target: $target,
            details: ['errors' => array_map($this->serializeErrorItem(...), $errors)],
        );
    }

    private function serializeErrorItem(ValidationErrorItem $item): array
    {
        return [
            'code' => $item->code,
            'target' => $item->target,
            'message' => $item->message,
        ];
    }
}

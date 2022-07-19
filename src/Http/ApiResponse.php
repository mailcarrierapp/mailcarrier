<?php

namespace MailCarrier\MailCarrier\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

class ApiResponse
{
    /**
     * Send a success request.
     */
    public static function success($data = null, int $httpStatus = JsonResponse::HTTP_OK): JsonResponse
    {
        return new JsonResponse([
            'response' => 'success',
            'data' => $data,
        ], $httpStatus, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Send a error request.
     */
    public static function error(
        string $errorKey,
        string $message,
        int $httpStatus = JsonResponse::HTTP_BAD_REQUEST,
        array $meta = []): JsonResponse
    {
        return new JsonResponse([
            'response' => 'error',
            'key' => $errorKey,
            'message' => $message,
        ] + $meta, $httpStatus, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Download a string content as file.
     */
    public static function downloadContent(string $content, string $fileName): Response
    {
        $contentType = MimeTypes::getDefault()
            ->getMimeTypes(Str::afterLast($fileName, '.'))[0] ??
            'application/octet-stream';

        $headers = [
            'Content-type'        => $contentType,
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName),
        ];

        return new Response($content, 200, $headers);
    }
}

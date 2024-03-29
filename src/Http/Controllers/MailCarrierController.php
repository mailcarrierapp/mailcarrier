<?php

namespace MailCarrier\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use MailCarrier\Actions\Attachments\Download;
use MailCarrier\Actions\SendMail;
use MailCarrier\Dto\AttachmentDto;
use MailCarrier\Dto\SendMailDto;
use MailCarrier\Enums\ApiErrorKey;
use MailCarrier\Exceptions\AttachmentNotDownloadableException;
use MailCarrier\Exceptions\AttachmentNotFoundException;
use MailCarrier\Exceptions\MissingVariableException;
use MailCarrier\Http\ApiResponse;
use MailCarrier\Http\Requests\SendMailRequest;
use MailCarrier\Models\Attachment;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MailCarrierController extends Controller
{
    public function __construct()
    {
        $middleware = [
            ...Config::get('mailcarrier.api_endpoint.extra_middleware', []),
            Config::get('mailcarrier.api_endpoint.auth_guard'),
        ];

        $this
            ->middleware(array_filter(array_unique($middleware)))
            ->only('send');
    }

    /**
     * Send the email.
     */
    public function send(SendMailRequest $request, SendMail $sendMail): JsonResponse
    {
        try {
            $sendMail->run(
                new SendMailDto([
                    ...$request->safe()->except(['recipients', 'attachments']),
                    'recipients' => !$request->has('recipients')
                        ? null
                        : $request->collect('recipients')
                            ->map(fn (array $recipient, int $i) => [
                                ...$recipient,
                                'attachments' => Collection::make($request->file("recipients.{$i}.attachments"))
                                    ->map(fn (UploadedFile $file) => AttachmentDto::fromUploadedFile($file))
                                    ->all(),
                            ])
                            ->all(),
                    'attachments' => Collection::make($request->file('attachments'))
                        ->map(fn (UploadedFile $file) => AttachmentDto::fromUploadedFile($file))
                        ->all(),
                ])
            );
        } catch (MissingVariableException $e) {
            report($e);

            return ApiResponse::error(
                $e->getErrorKey()->value,
                $e->getMessage(),
                JsonResponse::HTTP_PRECONDITION_FAILED
            );
        } catch (Exception $e) {
            report($e);

            return ApiResponse::error(
                ApiErrorKey::UnexpectedError->value,
                $e->getMessage(),
                $e instanceof HttpException ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return ApiResponse::success();
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(Attachment $attachment, Download $download): Response|JsonResponse
    {
        try {
            return $download
                ->run($attachment)
                ->asDownload();
        } catch (AttachmentNotFoundException|AttachmentNotDownloadableException $e) {
            return ApiResponse::error(
                $e->getErrorKey()->value,
                $e->getMessage(),
                JsonResponse::HTTP_PRECONDITION_FAILED
            );
        } catch (Exception $e) {
            report($e);

            return ApiResponse::error(
                ApiErrorKey::UnexpectedError->value,
                $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}

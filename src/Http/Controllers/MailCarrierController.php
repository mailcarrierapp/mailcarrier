<?php

namespace MailCarrier\MailCarrier\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use MailCarrier\MailCarrier\Actions\Attachments\Download;
use MailCarrier\MailCarrier\Actions\SendMail;
use MailCarrier\MailCarrier\Dto\SendMailDto;
use MailCarrier\MailCarrier\Enums\ApiErrorKey;
use MailCarrier\MailCarrier\Exceptions\AttachmentNotDownloadableException;
use MailCarrier\MailCarrier\Exceptions\AttachmentNotFoundException;
use MailCarrier\MailCarrier\Exceptions\MissingVariableException;
use MailCarrier\MailCarrier\Http\ApiResponse;
use MailCarrier\MailCarrier\Http\Requests\SendMailRequest;
use MailCarrier\MailCarrier\Models\Attachment;

class MailCarrierController extends Controller
{
    public function __construct()
    {
        $middleware = [
            ...Config::get('mailcarrier.api_endpoint.extra_middleware', []),
            Config::get('mailcarrier.api_endpoint.auth_guard'),
        ];

        $this->middleware(array_filter(array_unique($middleware)));
    }

    /**
     * Send the email.
     */
    public function send(SendMailRequest $request, SendMail $sendMail): JsonResponse
    {
        try {
            $sendMail->run(
                new SendMailDto($request->validated())
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
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return ApiResponse::success(httpStatus: JsonResponse::HTTP_NO_CONTENT);
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

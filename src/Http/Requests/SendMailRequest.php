<?php

namespace MailCarrier\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule;
use MailCarrier\Rules\ContactRule;

class SendMailRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $attachmentsMimeTypes = null;

        if ($allowedMimeTypes = Config::get('mailcarrier.attachments.mimetypes')) {
            $attachmentsMimeTypes = 'mimetypes:' . implode(',', $allowedMimeTypes);
        }

        return [
            'enqueue' => 'required|boolean',
            'template' => 'required|exists:\MailCarrier\MailCarrier\Models\Template,slug',
            'trigger' => 'sometimes|string|max:255',
            'subject' => 'required|string|max:255',
            'sender' => ['sometimes', new ContactRule()],
            'cc' => ['sometimes', new ContactRule()],
            'bcc' => ['sometimes', new ContactRule()],
            'variables' => 'sometimes|array',

            // Attachments as files
            'attachments' => 'sometimes|array',
            'attachments.*' => array_filter([
                'file',
                'max:' . Config::get('mailcarrier.attachments.max_size'),
                $attachmentsMimeTypes,
            ]),

            // Remote attachments from disks
            'remoteAttachments' => 'sometimes|array',
            'remoteAttachments.*.resource' => 'required',
            'remoteAttachments.*.disk' => [
                'sometimes',
                Rule::in(array_unique([
                    Config::get('mailcarrier.attachments.disk'),
                    ...Config::get('mailcarrier.attachments.additional_disks'),
                ])),
            ],

            'recipient' => 'required_without:recipients|prohibits:recipients|email|max:255',

            // Multipe recipients with their variables
            'recipients' => 'required_without:recipient|prohibits:recipient|array|min:1',
            'recipients.*' => [
                // Apply rule only when recipients is an array of objects
                Rule::when($this->has('recipients') && !is_array($this->json('recipients.0')), 'required|email'),
            ],
            'recipients.*.recipient' => [
                // Apply rule only when recipients is an array of objects
                Rule::when($this->has('recipients') && is_array($this->json('recipients.0')), 'required|email'),
            ],
            'recipients.*.variables' => 'sometimes|array',
            'recipients.*.cc' => ['sometimes', new ContactRule()],
            'recipients.*.bcc' => ['sometimes', new ContactRule()],

            // Recipients attachments
            'recipients.*.attachments' => 'sometimes|array',
            'recipients.*.attachments.*' => array_filter([
                'file',
                'max:' . Config::get('mailcarrier.attachments.max_size'),
                $attachmentsMimeTypes,
            ]),

            // Recipients remote attachments from disks
            'recipients.*.remoteAttachments' => 'sometimes|array',
            'recipients.*.remoteAttachments.*.resource' => 'required',
            'recipients.*.remoteAttachments.*.disk' => [
                'sometimes',
                Rule::in(array_unique([
                    Config::get('mailcarrier.attachments.disk'),
                    ...Config::get('mailcarrier.attachments.additional_disks'),
                ])),
            ],
        ];
    }

    /**
     * Prepare input for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'enqueue' => false,
        ]);

        // Cast 'enqueue' from string to bool, e.g. for form-data
        $enqueue = $this->input('enqueue');
        if (!is_null($enqueue) && !is_bool($enqueue)) {
            $this->merge([
                'enqueue' => filter_var($enqueue, FILTER_VALIDATE_BOOL),
            ]);
        }

        // Wrap recipient array list into a structured data
        if (is_array($this->input('recipients')) && !is_array($this->json('recipients.0'))) {
            $this->merge([
                'recipients' => array_map(fn (string $recipient) => [
                    'recipient' => $recipient,
                ], $this->input('recipients')),
            ]);
        }

        // Wrap remote attachments array list into a structured data
        if (is_array($this->input('remoteAttachments')) && !is_array($this->json('remoteAttachments.0'))) {
            $this->merge([
                'remoteAttachments' => array_map(fn (string $attachment) => [
                    'resource' => $attachment,
                ], $this->input('remoteAttachments')),
            ]);
        }
    }
}

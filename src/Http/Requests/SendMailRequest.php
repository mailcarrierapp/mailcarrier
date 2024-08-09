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
            'template' => 'required|exists:\MailCarrier\Models\Template,slug',
            'trigger' => 'sometimes|string|max:255',
            'subject' => 'required|string|max:255',
            'sender' => ['sometimes', new ContactRule()],
            'cc' => 'sometimes|array',
            'bcc' => 'sometimes|array',
            'cc.*' => [new ContactRule()],
            'bcc.*' => [new ContactRule()],
            'variables' => 'sometimes|array',
            'tags' => 'sometimes|array',
            'metadata' => 'sometimes|array',

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
            'remoteAttachments.*.name' => 'sometimes|string',
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
                Rule::when($this->has('recipients') && !is_array($this->input('recipients.0')), 'required|email'),
            ],
            'recipients.*.email' => [
                // Apply rule only when recipients is an array of objects
                Rule::when($this->has('recipients') && is_array($this->input('recipients.0')), 'required|email'),
            ],
            'recipients.*.variables' => 'sometimes|array',
            'recipients.*.cc' => 'sometimes|array',
            'recipients.*.cc.*' => [new ContactRule()],
            'recipients.*.bcc' => 'sometimes|array',
            'recipients.*.bcc.*' => [new ContactRule()],

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

        // Wrap cc array list
        if (
            !is_null($this->input('cc'))
            && (!is_array($this->input('cc')) || !array_is_list($this->input('cc')))
        ) {
            $this->merge([
                'cc' => [$this->input('cc')],
            ]);
        }

        // Wrap bcc array list
        if (
            !is_null($this->input('bcc'))
            && (!is_array($this->input('bcc')) || !array_is_list($this->input('bcc')))
        ) {
            $this->merge([
                'bcc' => [$this->input('bcc')],
            ]);
        }

        // Wrap recipient array list into a structured data
        if (is_array($this->input('recipients')) && !is_array($this->json('recipients.0'))) {
            $this->merge([
                'recipients' => $this->collect('recipients')
                    ->filter()
                    ->map(fn (string $recipient) => [
                        'email' => $recipient,
                    ])
                    ->toArray(),
            ]);
        }

        // Wrap recipient cc and bcc
        $recipients = $this->input('recipients');

        if (is_array($recipients)) {
            foreach ($recipients as $i => $recipient) {
                if (
                    array_key_exists('cc', $recipient)
                    && (!is_array($this->input('cc')) || !array_is_list($this->input('cc')))
                ) {
                    $recipients[$i]['cc'] = [$recipient['cc']];
                }

                if (
                    array_key_exists('bcc', $recipient)
                    && (!is_array($this->input('bcc')) || !array_is_list($this->input('bcc')))
                ) {
                    $recipients[$i]['bcc'] = [$recipient['bcc']];
                }
            }

            $this->merge([
                'recipients' => $recipients,
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

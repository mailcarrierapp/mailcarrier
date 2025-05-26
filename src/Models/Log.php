<?php

namespace MailCarrier\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Config;
use MailCarrier\Dto\ContactDto;
use MailCarrier\Dto\LogTemplateDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Models\Casts\CollectionOfContacts;
use MailCarrier\Models\Concerns\IsUuid;

/**
 * @property int|null $template_id
 * @property \MailCarrier\Enums\LogStatus $status
 * @property string|null $trigger
 * @property string|null $subject
 * @property string|null $message_id
 * @property \MailCarrier\Dto\ContactDto $sender
 * @property \MailCarrier\Dto\ContactDto|null $replyTo
 * @property \Illuminate\Support\Collection<MailCarrier\Dto\ContactDto>|null $cc
 * @property \Illuminate\Support\Collection<MailCarrier\Dto\ContactDto>|null $bcc
 * @property string $recipient
 * @property \MailCarrier\Dto\LogTemplateDto $template_frozen
 * @property array<string, mixed>|null $variables
 * @property string|null $error
 * @property int $tries
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $last_try_at
 * @property array|null $tags
 * @property array|null $metadata
 * @property-read \MailCarrier\Models\Template|null $template
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \MailCarrier\Models\Attachment> $attachments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \MailCarrier\Models\LogEvent> $events
 */
class Log extends Model
{
    use HasFactory;
    use IsUuid;
    use MassPrunable;

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_id',
        'status',
        'trigger',
        'subject',
        'cc',
        'bcc',
        'sender',
        'recipient',
        'replyTo',
        'template_frozen',
        'variables',
        'error',
        'tries',
        'tags',
        'metadata',
        'last_try_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => LogStatus::class,
        'sender' => ContactDto::class,
        'replyTo' => ContactDto::class,
        'cc' => CollectionOfContacts::class,
        'bcc' => CollectionOfContacts::class,
        'template_frozen' => LogTemplateDto::class,
        'variables' => 'array',
        'tags' => 'array',
        'metadata' => 'json',
        'last_try_at' => 'datetime',
    ];

    /**
     * Get the log's template.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get the log's attachments.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Get the log's events.
     */
    public function events(): HasMany
    {
        return $this->hasMany(LogEvent::class);
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): EloquentBuilder
    {
        $prunablePeriod = Config::get('mailcarrier.logs.prunable_period');

        return static::query()
            ->when(
                $prunablePeriod,
                fn (EloquentBuilder $query, string $period) => $query->where('created_at', '<=', Carbon::now()->sub(...explode(' ', $period)))
            );
    }

    public function isFailed(): bool
    {
        return $this->status === LogStatus::Failed;
    }

    public function isPending(): bool
    {
        return $this->status === LogStatus::Pending;
    }

    public function isSent(): bool
    {
        return $this->status === LogStatus::Sent;
    }
}

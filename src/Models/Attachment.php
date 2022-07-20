<?php

namespace MailCarrier\MailCarrier\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MailCarrier\MailCarrier\Enums\AttachmentLogStrategy;
use MailCarrier\MailCarrier\Facades\MailCarrier;
use MailCarrier\MailCarrier\Models\Concerns\IsUuid;

/**
 * @property string $log_id
 * @property \MailCarrier\MailCarrier\Enums\AttachmentLogStrategy $strategy
 * @property string $name
 * @property int $size
 * @property string|null $content
 * @property string|null $path
 * @property string|null $disk
 * @property-read string $readableSize
 */
class Attachment extends Model
{
    use HasFactory;
    use IsUuid;

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
        'log_id',
        'strategy',
        'name',
        'content',
        'size',
        'path',
        'disk',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'content',
        'path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'strategy' => AttachmentLogStrategy::class,
        'content' => 'encrypted',
    ];

    /**
     * Get the attachment's log.
     */
    public function log(): BelongsTo
    {
        return $this->belongsTo(Log::class);
    }

    /**
     * Get the size in a readable format.
     */
    public function readableSize(): string
    {
        return MailCarrier::humanBytes($this->size);
    }

    /**
     * Check if the attachment can be downloaded.
     */
    public function canBeDownloaded(): bool
    {
        return $this->strategy !== AttachmentLogStrategy::None;
    }
}

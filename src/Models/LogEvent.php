<?php

namespace MailCarrier\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MailCarrier\Models\Concerns\IsUuid;

/**
 * @property string $id
 * @property string $log_id
 * @property string $name
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property-read \MailCarrier\Models\Log $log
 */
class LogEvent extends Model
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
        'name',
        'created_at',
    ];

    /**
     * Get the log that owns the event.
     */
    public function log(): BelongsTo
    {
        return $this->belongsTo(Log::class);
    }
}

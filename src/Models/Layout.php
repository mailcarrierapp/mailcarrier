<?php

namespace MailCarrier\MailCarrier\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * @property int $id
 * @property int|null $user_id
 * @property bool $is_locked
 * @property string $name
 * @property string $content
 * @property-read \MailCarrier\MailCarrier\Models\User|null $user
 */
class Layout extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'is_locked',
        'name',
        'content',
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
        'is_locked' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (Layout $model) {
            if ($model->is_locked) {
                throw new PreconditionFailedHttpException('Layout is locked.');
            }
        });
    }

    /**
     * Get the layout's author user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the templates using this layout.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }
}

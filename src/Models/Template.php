<?php

namespace MailCarrier\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $layout_id
 * @property bool $is_locked
 * @property string $name
 * @property string $slug
 * @property string $content
 * @property array|null $tags
 * @property-read \MailCarrier\Models\User|null $user
 * @property-read \MailCarrier\Models\Layout|null $layout
 */
class Template extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'template_id',
        'is_locked',
        'name',
        'slug',
        'content',
        'tags',
        'description',
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
        'tags' => 'array',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array<int, string>
     */
    protected $with = [
        'layout',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleting(function (Template $model) {
            if ($model->is_locked) {
                throw new PreconditionFailedHttpException('Template is locked.');
            }
        });
    }

    /**
     * Get the template's author user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template's layout.
     */
    public function layout(): BelongsTo
    {
        return $this->belongsTo(Layout::class);
    }

    /**
     * Get the template hash.
     */
    public function getHash(): string
    {
        return md5($this->content . $this->layout?->content);
    }
}

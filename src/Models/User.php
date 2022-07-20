<?php

namespace MailCarrier\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use MailCarrier\Enums\Role;

/**
 * @property int $id
 * @property string|null $oauth_id
 * @property string $name
 * @property string $email
 * @property string|null $picture_url
 * @property string[] $roles
 */
class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'oauth_id',
        'name',
        'last_name',
        'email',
        'picture_url',
        'roles',
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
        'roles' => 'array',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'roles' => '[]',
    ];

    /**
     * Determine if the user can access Filament.
     */
    public function canAccessFilament(): bool
    {
        return $this->isUser();
    }

    /**
     * Get the user's name.
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }

    /**
     * Get the user's avatar.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->picture_url;
    }

    /**
     * Check if user is an admin.
     */
    public function isUser(): bool
    {
        return is_array($this->roles) && in_array(Role::User->value, $this->roles);
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return is_array($this->roles) && in_array(Role::Admin->value, $this->roles);
    }
}

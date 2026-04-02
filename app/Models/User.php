<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mission_id',
        'eglise_locale_id',
        'role_id',
        'identifiant_public',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    public function egliseLocale(): BelongsTo
    {
        return $this->belongsTo(EgliseLocale::class, 'eglise_locale_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function notificationsInternes(): HasMany
    {
        return $this->hasMany(NotificationInterne::class, 'user_id')
            ->orderByDesc('created_at');
    }

    public function estUtilisateurMission(): bool
    {
        return $this->mission_id !== null && $this->eglise_locale_id === null;
    }

    public function estUtilisateurEglise(): bool
    {
        return $this->eglise_locale_id !== null;
    }

    public function estAdministrateurMission(): bool
    {
        return $this->hasRole('admin_mission');
    }

    public function hasRole(string $name): bool
    {
        if ($this->relationLoaded('role')) {
            return $this->role !== null && $this->role->name === $name;
        }

        return $this->role()->where('name', $name)->exists();
    }

    public function hasPermission(string $name): bool
    {
        if ($this->role_id === null) {
            return false;
        }

        if ($this->relationLoaded('role')) {
            if ($this->role === null) {
                return false;
            }

            if ($this->role->relationLoaded('permissions')) {
                return $this->role->permissions->contains('name', $name);
            }

            // Fallback: si le rôle est chargé sans ses permissions, requêter en base.
            return $this->role()
                ->whereHas('permissions', fn ($q) => $q->where('permissions.name', $name))
                ->exists();
        }

        return $this->role()->whereHas('permissions', fn ($q) => $q->where('permissions.name', $name))->exists();
    }

    public function libelleRole(): string
    {
        if ($this->relationLoaded('role')) {
            return $this->role?->label ?? '—';
        }

        return $this->role()->value('label') ?? '—';
    }
}

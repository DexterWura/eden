<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'points' => 'integer',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function startups()
    {
        return $this->hasMany(Startup::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function featurePayments()
    {
        return $this->hasMany(FeaturePayment::class);
    }

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }

    public function hasProFeature(string $featureKey): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        $pro = app(\App\Services\ProFeatureService::class);
        if (! $pro->isProFeature($featureKey)) {
            return true;
        }
        return $this->featurePayments()
            ->where('feature_key', $featureKey)
            ->where('status', FeaturePayment::STATUS_PAID)
            ->exists();
    }

    public function hasBloggingAccess(): bool
    {
        return $this->hasProFeature('blogging');
    }
}

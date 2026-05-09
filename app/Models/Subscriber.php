<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriberFactory> */
    use HasFactory;

    protected $fillable = [
        'email',
        'verified_at',
        'verify_token',
        'unsubscribe_token',
    ];

    protected $hidden = [
        'verify_token',
        'unsubscribe_token',
    ];

    protected static function booted(): void
    {
        static::creating(function (Subscriber $subscriber): void {
            $subscriber->verify_token ??= Str::random(48);
            $subscriber->unsubscribe_token ??= Str::random(48);
        });
    }

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    /** @param  Builder<Subscriber>  $query */
    public function scopeVerified(Builder $query): void
    {
        $query->whereNotNull('verified_at');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function markVerified(): void
    {
        if (! $this->isVerified()) {
            $this->forceFill(['verified_at' => now()])->save();
        }
    }
}

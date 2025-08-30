<?php

namespace Modules\Api\Models;

use Illuminate\Database\Eloquent\Model;

final class OutboxMessage extends Model
{
    protected $fillable = ['type','payload','status','attempts','available_at','last_error'];
    protected $casts = ['payload' => 'array', 'available_at' => 'datetime'];

    public static function queue(string $type, array $payload, ?\DateTimeInterface $when = null): self
    {
        return static::create([
            'type'         => $type,
            'payload'      => $payload,
            'status'       => 'pending',
            'available_at' => $when,
        ]);
    }
}

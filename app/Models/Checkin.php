<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    protected $fillable = [
        'id_ticket',
        'id_user',
        'verification_date',
        'reason',
        'validation_type',
        'is_validated',
    ];

    protected $casts = [
        'verification_date' => 'datetime',
        'is_validated' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'id_ticket');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}

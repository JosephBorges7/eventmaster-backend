<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    protected $fillable = [
        'price',
        'initial_date',
        'end_date',
        'quantity',
    ];

    protected $casts = [
        'initial_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'id_batch');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizerRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'cpf',
        'email',
        'phone_number',
        'reason',
        'status',
        'status_changed_at',
    ];
}

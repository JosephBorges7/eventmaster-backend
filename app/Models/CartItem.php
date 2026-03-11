<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    // Adicione os campos que você quer salvar
    protected $fillable = [
        'user_id', 
        'ticket_id', 
        'quantity'
    ];

    // Relacionamento para facilitar a listagem depois
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }
}
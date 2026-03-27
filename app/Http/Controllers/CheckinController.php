<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string|exists:tickets,ticket_code',
        ]);

        $ticket = Ticket::where('ticket_code', $request->ticket_code)->firstOrFail();

        if ($ticket->is_validated) {
            return response()->json(['message' => 'Este ingresso já foi utilizado.'], 400);
        }

        $ticket->update(['is_validated' => true]);

        $checkin = $ticket->checkins()->create([
            'id_user' => auth()->id()
        ]);

        return response()->json([
            'message' => 'Check-in realizado com sucesso!',
            'ticket' => $ticket,
            'checkin' => $checkin
        ], 201);
    }
}
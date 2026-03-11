<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        // Certifique-se de que o Model CartItem tem o método ticket()
        $items = CartItem::with('ticket.event')
            ->where('user_id', Auth::id())
            ->get();
            
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_ticket' => 'required|exists:tickets,id',
            'quantity' => 'integer|min:1'
        ]);

        // Evita duplicados: se já existe, ele atualiza a quantidade
        $item = CartItem::updateOrCreate(
            [
                'user_id'   => Auth::id(), 
                'ticket_id' => $request->id_ticket
            ],
            [
                // Aqui você pode decidir se soma ou se apenas define o valor enviado
                'quantity'  => $request->quantity ?? 1 
            ]
        );

        return response()->json([
            'message' => 'Adicionado ao carrinho!', 
            'item' => $item
        ]);
    }

    public function destroy($id)
    {
        $item = CartItem::where('user_id', Auth::id())->where('id', $id)->first();

        if (!$item) {
            return response()->json(['message' => 'Item não encontrado'], 404);
        }

        $item->delete();
        return response()->json(['message' => 'Item removido']);
    }
}
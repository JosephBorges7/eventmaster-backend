<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * List the authenticated user's cart items.
     */
    public function index(Request $request): JsonResponse
    {
        $items = $request->user()
            ->cartItems()
            ->with(['event.category', 'event.local', 'batch', 'ticketType'])
            ->orderBy('created_at')
            ->get();

        $items->transform(fn (CartItem $item) => $this->formatCartItem($item));

        return response()->json([
            'items' => $items,
        ]);
    }

    /**
     * Add an item to the cart (or increase quantity if same event/batch/ticket type).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_event' => ['required', 'integer', 'exists:events,id'],
            'id_batch' => ['required', 'integer', 'exists:batches,id'],
            'id_ticket_type' => ['required', 'integer', 'exists:ticket_types,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('The given data was invalid.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $data = $validator->validated();

        /** @var CartItem|null $existing */
        $existing = $user->cartItems()
            ->where('id_event', $data['id_event'])
            ->where('id_batch', $data['id_batch'])
            ->where('id_ticket_type', $data['id_ticket_type'])
            ->first();

        if ($existing) {
            $existing->quantity += $data['quantity'];
            $existing->save();
            $item = $existing;
        } else {
            $item = CartItem::create([
                'id_user' => $user->id,
                'id_event' => $data['id_event'],
                'id_batch' => $data['id_batch'],
                'id_ticket_type' => $data['id_ticket_type'],
                'quantity' => $data['quantity'],
            ]);
        }

        $item->load(['event.category', 'event.local', 'batch', 'ticketType']);

        return response()->json([
            'message' => __('Item added to cart.'),
            'item' => $this->formatCartItem($item),
        ], 201);
    }

    /**
     * Update a cart item's quantity.
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->id_user !== $request->user()->id) {
            return response()->json(['message' => __('Record not found.')], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => __('The given data was invalid.'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $cartItem->quantity = $validator->validated()['quantity'];
        $cartItem->save();
        $cartItem->load(['event.category', 'event.local', 'batch', 'ticketType']);

        return response()->json([
            'message' => __('Cart updated successfully.'),
            'item' => $this->formatCartItem($cartItem),
        ]);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->id_user !== $request->user()->id) {
            return response()->json(['message' => __('Record not found.')], 404);
        }

        $cartItem->delete();

        return response()->json([
            'message' => __('Item removed from cart.'),
        ]);
    }

    /**
     * Format cart item for JSON response.
     *
     * @return array<string, mixed>
     */
    private function formatCartItem(CartItem $item): array
    {
        $item->loadMissing(['event.category', 'event.local', 'batch', 'ticketType']);
        $event = $item->event;
        $batch = $item->batch;
        $subtotal = (float) $batch->price * $item->quantity;

        return [
            'id' => $item->id,
            'id_event' => $item->id_event,
            'id_batch' => $item->id_batch,
            'id_ticket_type' => $item->id_ticket_type,
            'quantity' => $item->quantity,
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'date' => $event->date->format('Y-m-d'),
                'time' => $event->time,
                'category' => $event->category,
                'local' => $event->local,
            ],
            'batch' => [
                'id' => $batch->id,
                'price' => (float) $batch->price,
                'initial_date' => $batch->initial_date->format('Y-m-d'),
                'end_date' => $batch->end_date->format('Y-m-d'),
            ],
            'ticket_type' => $item->ticketType->only('id', 'name'),
            'subtotal' => round($subtotal, 2),
        ];
    }
}

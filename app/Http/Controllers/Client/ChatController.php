<?php

namespace App\Http\Controllers\Client;

use App\Events\Client\ChatClosed;
use App\Events\Client\MessageSent;
use App\Events\Client\SupportRequested;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Shop\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function latest()
    {
        $chat = Chat::where('user_id', Auth::id())->where('status', 'accepted')->orderBy('created_at', 'desc')->first();
        if (!$chat || $chat->closed_at)
            return response()->json(['success' => false, 'message' => 'There is no active chat.']);
        return response()->json(['success' => true, 'data' => new ChatResource($chat)]);
    }

    public function store(Order $order, Request $request)
    {
        $order->chat()->create([
            'user_id' => $order->buyer->id,
        ]);

        broadcast(new SupportRequested($order));

        return response()->json([
            'status' => 'ok',
            'message' => 'Support Requested',
            'data' => [
                'message' => ''
            ]
        ]);
    }

    public function send(Chat $chat, Request $request)
    {
        $messageToSend = $request->post('message');
        $message = $chat->messages()->create([
            'sender_id' => Auth::id(),
            'message' => $messageToSend,
        ]);
        broadcast(new MessageSent($message))->toOthers();
    }

    public function messages(Chat $chat)
    {
        return MessageResource::collection($chat->messages);
    }

    public function join(Chat $chat, Request $request)
    {
        $chat->update([
            'admin_id' => Auth::user()->id,
        ]);
    }

    public function close(Chat $chat)
    {
        $chat->update([
            'closed_at' => now()
        ]);
        broadcast(new ChatClosed($chat))->toOthers();
    }
}

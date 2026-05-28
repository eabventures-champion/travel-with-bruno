<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\ChatMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // If admin, show list of customers who have messaged or been messaged
        if ($user->hasAnyRole(['Super Admin', 'Operations Admin']) || $user->user_type === 'admin') {
            $conversations = User::whereHas('receivedMessages', function($q) use ($user) {
                $q->where('sender_id', $user->id);
            })->orWhereHas('sentMessages', function($q) use ($user) {
                $q->where('receiver_id', $user->id);
            })->get();
            
            return view('admin.chat.index', compact('conversations'));
        }

        // If customer, show messages with admin
        $admin = User::role('Super Admin')->first() ?? User::role('Operations Admin')->first(); // Assuming one primary admin for now
        $messages = ChatMessage::where(function($q) use ($user, $admin) {
            $q->where('sender_id', $user->id)->where('receiver_id', $admin->id);
        })->orWhere(function($q) use ($user, $admin) {
            $q->where('sender_id', $admin->id)->where('receiver_id', $user->id);
        })->orderBy('created_at', 'asc')->get();

        return view('customer.chat', compact('messages', 'admin'));
    }

    public function show(User $receiver)
    {
        $sender = Auth::user();
        
        $messages = ChatMessage::where(function($q) use ($sender, $receiver) {
            $q->where('sender_id', $sender->id)->where('receiver_id', $receiver->id);
        })->orWhere(function($q) use ($sender, $receiver) {
            $q->where('sender_id', $receiver->id)->where('receiver_id', $sender->id);
        })->orderBy('created_at', 'asc')->get();

        // Mark as read
        ChatMessage::where('sender_id', $receiver->id)
            ->where('receiver_id', $sender->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->ajax()) {
            return response()->json($messages);
        }

        return view('admin.chat.show', compact('messages', 'receiver'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        $receiver = User::find($request->receiver_id);
        $receiver->notify(new ChatMessageNotification($message));

        if ($request->ajax()) {
            return response()->json($message);
        }

        return back()->with('success', 'Message sent!');
    }
}

@extends('admin::layouts.master')

@section('content')
<div class="container-fluid">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h1 class="h3 mb-0" style="font-family: 'Outfit', sans-serif; color: var(--text-main); font-weight: 800;">Chat with {{ $receiver->name }}</h1>
        <a href="{{ route('chat.index') }}" class="btn btn-secondary btn-sm" style="border-radius: 10px; padding: 8px 15px;">
            <i class="fas fa-arrow-left"></i> Back to Conversations
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div id="chat-box" style="height: 500px; overflow-y: auto; background: var(--bg-main); border: 1px solid var(--border); border-radius: 16px; padding: 25px; margin-bottom: 25px; display: flex; flex-direction: column; gap: 15px;">
                @foreach($messages as $msg)
                    @php $isMe = $msg->sender_id == Auth::id(); @endphp
                    <div style="display: flex; flex-direction: column; align-items: {{ $isMe ? 'flex-end' : 'flex-start' }};">
                        <div style="
                            padding: 12px 18px; 
                            border-radius: {{ $isMe ? '20px 20px 0 20px' : '20px 20px 20px 0' }}; 
                            background: {{ $isMe ? 'var(--primary)' : 'var(--bg-card)' }}; 
                            color: {{ $isMe ? '#ffffff' : 'var(--text-main)' }}; 
                            max-width: 75%;
                            box-shadow: var(--shadow-sm);
                            font-size: 0.95rem;
                            line-height: 1.5;
                            border: {{ $isMe ? 'none' : '1px solid var(--border)' }};
                        ">
                            {{ $msg->message }}
                        </div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 5px; font-weight: 600;">
                            {{ $msg->created_at->format('h:i A') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('chat.store') }}" method="POST">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">
                <div style="display: flex; gap: 10px;">
                    <input type="text" name="message" class="form-control" placeholder="Type your message..." required autofocus 
                        style="height: 50px; border-radius: 12px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); padding: 0 20px;">
                    <button class="btn btn-primary" type="submit" style="padding: 0 30px; border-radius: 12px; font-weight: 800; height: 50px;">
                        Send
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const chatBox = document.getElementById('chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;
</script>
@endpush
@endsection

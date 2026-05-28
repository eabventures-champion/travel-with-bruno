@extends('admin::layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Customer Conversations</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            @if($conversations->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-comments-slash fa-3x text-muted mb-3"></i>
                    <p>No conversations yet.</p>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Start a New Chat
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Email</th>
                                <th>Last Message</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($conversations as $customer)
                                <tr>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>
                                        @php
                                            $lastMsg = App\Models\ChatMessage::where(function($q) use ($customer) {
                                                $q->where('sender_id', Auth::id())->where('receiver_id', $customer->id);
                                            })->orWhere(function($q) use ($customer) {
                                                $q->where('sender_id', $customer->id)->where('receiver_id', Auth::id());
                                            })->orderBy('created_at', 'desc')->first();
                                        @endphp
                                        {{ $lastMsg ? Str::limit($lastMsg->message, 50) : 'No messages' }}
                                    </td>
                                    <td>
                                        @php
                                            $unreadCount = App\Models\ChatMessage::where('sender_id', $customer->id)
                                                ->where('receiver_id', Auth::id())
                                                ->whereNull('read_at')
                                                ->count();
                                        @endphp
                                        @if($unreadCount > 0)
                                            <span class="badge badge-danger">{{ $unreadCount }} New</span>
                                        @else
                                            <span class="badge badge-secondary">Read</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('chat.show', $customer->id) }}" class="btn btn-primary btn-sm">Chat</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

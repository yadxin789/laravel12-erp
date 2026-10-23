@php
    $mine = $message->user_id === auth()->id();
    $author = $message->user;
    $deleted = $message->trashed();
@endphp

<div class="d-flex {{ $mine ? 'justify-content-end' : 'justify-content-start' }} mb-3 chat-item" data-id="{{ $message->id }}">
    @unless ($mine)
        <div class="flex-shrink-0 me-2">
            @if ($author?->avatar_url)
                <img src="{{ $author->avatar_url }}" class="rounded-circle" width="34" height="34" alt="{{ $author->name }}">
            @else
                <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                      style="width:34px;height:34px;">{{ $author?->initial ?? '?' }}</span>
            @endif
        </div>
    @endunless

    <div style="max-width: 70%;">
        @unless ($mine)
            <div class="small text-muted mb-1">
                {{ $author?->name ?? '已删除用户' }}
                @if ($author?->position)
                    <span class="badge bg-light text-secondary border">{{ $author->position }}</span>
                @endif
                @if ($author?->isOnline())
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">在线</span>
                @endif
            </div>
        @endunless

        <div class="{{ $mine ? 'bg-primary text-white' : 'bg-body-tertiary border' }} rounded-3 px-3 py-2">
            @if ($deleted)
                <span class="fst-italic {{ $mine ? 'text-white-50' : 'text-muted' }}">该消息已被撤回</span>
            @else
                @if ($message->content)
                    <div style="white-space: pre-wrap; word-break: break-word;">{{ $message->content }}</div>
                @endif

                @if ($message->hasFile())
                    <div class="mt-2">
                        @if ($message->isImage())
                            <a href="{{ route('app.chat.file', $message) }}" target="_blank" rel="noopener">
                                <img src="{{ route('app.chat.file', $message) }}" class="rounded border" style="max-width: 200px;" alt="{{ $message->file_name }}">
                            </a>
                        @else
                            <a href="{{ route('app.chat.file', $message) }}"
                               class="badge {{ $mine ? 'bg-white text-primary' : 'bg-light text-secondary border' }} text-decoration-none">
                                <i class="fa-solid fa-paperclip me-1"></i>{{ $message->file_name }}
                            </a>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <div class="small text-muted mt-1 d-flex align-items-center gap-2">
            <span>{{ $message->created_at?->format('m-d H:i') }}</span>
            @if ($mine && ! $deleted)
                <form method="POST" action="{{ route('app.chat.destroy', $message) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-link btn-sm text-danger p-0" title="撤回">撤回</button>
                </form>
            @endif
        </div>
    </div>

    @if ($mine)
        <div class="flex-shrink-0 ms-2">
            @if (auth()->user()->avatar_url)
                <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" width="34" height="34" alt="我">
            @else
                <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center"
                      style="width:34px;height:34px;">{{ auth()->user()->initial }}</span>
            @endif
        </div>
    @endif
</div>

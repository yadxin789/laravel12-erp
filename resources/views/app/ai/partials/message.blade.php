@php
    $mine = $message->role === 'user';
    $content = (string) $message->content;
    // html_input=strip：即使模型返回 HTML 标签也不会被执行
    $html = $mine
        ? nl2br(e($content))
        : \Illuminate\Support\Str::markdown($content, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
@endphp

<div class="mb-3 d-flex {{ $mine ? 'justify-content-end' : 'justify-content-start' }}">
    <div class="{{ $mine ? 'bg-primary text-white' : 'bg-body-tertiary border' }} rounded-3 p-3" style="max-width: 78%;">
        @unless ($mine)
            <div class="fw-semibold small mb-1"><i class="fa-solid fa-robot me-1"></i>AI 助手</div>
        @endunless

        <div class="ai-content" style="font-size: .95rem;">
            {!! $html !!}

            @foreach ($message->attachments as $att)
                <div class="mt-2">
                    @if ($att->is_image)
                        <a href="{{ route('app.ai.chat.attachments.preview', $att) }}" target="_blank" rel="noopener">
                            <img src="{{ route('app.ai.chat.attachments.preview', $att) }}"
                                 alt="{{ $att->original_name }}" class="rounded border" style="max-width: 220px;">
                        </a>
                    @else
                        <a href="{{ route('app.ai.chat.attachments.download', $att) }}"
                           class="badge {{ $mine ? 'bg-white text-primary' : 'bg-light text-secondary border' }} text-decoration-none">
                            <i class="fa-solid fa-paperclip me-1"></i>{{ $att->original_name }}
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="small mt-1 {{ $mine ? 'text-white-50' : 'text-muted' }}">
            {{ $message->created_at?->format('H:i') }}
            @unless ($mine)
                @if (data_get($message->meta, 'model_name'))
                    <span class="badge bg-light text-secondary border ms-1">{{ data_get($message->meta, 'model_name') }}</span>
                @endif
                @if (data_get($message->meta, 'provider'))
                    <span class="badge bg-light text-secondary border ms-1">{{ data_get($message->meta, 'provider') }}</span>
                @endif
            @endunless
        </div>
    </div>
</div>

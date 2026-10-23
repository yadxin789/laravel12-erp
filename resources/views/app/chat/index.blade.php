@extends('layouts.business')

@section('title', '群聊')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="page-title mb-0">
            <i class="fa-solid fa-comments"></i>群聊
            <span class="badge bg-light text-secondary border align-middle">{{ $members->count() }} 位成员</span>
        </h2>
        <div class="d-flex gap-2">
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-regular fa-bell me-1"></i>消息中心
            </a>
            <form method="POST" action="{{ route('app.chat.clear') }}" onsubmit="return confirm('仅清空你自己看到的群聊记录，其他成员不受影响，确定？')">
                @csrf
                <button class="btn btn-outline-warning btn-sm"><i class="fa-solid fa-broom me-1"></i>清空我的记录</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fa-solid fa-users me-2"></i>全员群聊</div>
                <div class="card-body" id="chat-window" style="height: 28rem; overflow-y: auto;">
                    @forelse ($messages as $message)
                        @include('app.chat.partials.message', ['message' => $message])
                    @empty
                        <p class="erp-empty" id="chat-empty">还没有消息，来说点什么吧。</p>
                    @endforelse
                </div>
                <div class="card-footer">
                    <div id="attach-tag" class="mb-2"></div>
                    <form id="chat-form" class="d-flex align-items-end gap-2">
                        @csrf
                        <label class="btn btn-outline-secondary mb-0" title="发送图片或文件（≤10MB）">
                            <i class="fa-solid fa-paperclip"></i>
                            <input type="file" id="file-input" name="file" hidden
                                   accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar">
                        </label>
                        <textarea id="chat-input" class="form-control" rows="2" placeholder="输入消息，Enter 发送，Shift+Enter 换行…"></textarea>
                        <button type="submit" class="btn btn-primary" id="send-btn">
                            <i class="fa-solid fa-paper-plane me-1"></i>发送
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="fa-solid fa-address-book me-2"></i>成员（点击发起私聊）</div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 32rem; overflow-y: auto;">
                        @foreach ($members as $m)
                            <a href="{{ $m->id === auth()->id() ? '#' : route('app.chat.private', $m->id) }}"
                               class="list-group-item list-group-item-action d-flex align-items-center gap-2 {{ $m->id === auth()->id() ? 'disabled' : '' }}">
                                @if ($m->avatar_url)
                                    <img src="{{ $m->avatar_url }}" class="rounded-circle" width="30" height="30" alt="{{ $m->name }}">
                                @else
                                    <span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center"
                                          style="width:30px;height:30px;">{{ $m->initial }}</span>
                                @endif
                                <div class="flex-fill">
                                    <div class="fw-semibold">{{ $m->name }}{{ $m->id === auth()->id() ? '（我）' : '' }}</div>
                                    <small class="text-muted">{{ $m->position ?: \App\Support\RoleCatalog::label($m->roles->first()) ?: '成员' }}</small>
                                </div>
                                @if ($m->isOnline())
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">在线</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const win = document.getElementById('chat-window');
const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');
const fileInput = document.getElementById('file-input');
const attachTag = document.getElementById('attach-tag');
const sendBtn = document.getElementById('send-btn');
const csrf = document.querySelector('#chat-form input[name=_token]').value;
const sendUrl = '{{ route('app.chat.send') }}';
const listUrl = '{{ route('app.chat.messages') }}';
let lastId = {{ $messages->last()?->id ?? 0 }};

win.scrollTop = win.scrollHeight;

fileInput.addEventListener('change', () => {
    attachTag.innerHTML = fileInput.files.length
        ? `<span class="badge bg-light text-secondary border"><i class="fa-solid fa-paperclip me-1"></i>${fileInput.files[0].name}</span>`
        : '';
});

input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); form.requestSubmit(); }
});

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text && !fileInput.files.length) return;

    const fd = new FormData();
    fd.append('content', text);
    if (fileInput.files.length) fd.append('file', fileInput.files[0]);

    sendBtn.disabled = true;
    try {
        const res = await fetch(sendUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd,
        });
        const data = await res.json();
        if (data.success) {
            input.value = '';
            fileInput.value = '';
            attachTag.innerHTML = '';
            await appendMessages([data.message]);
        } else {
            alert(data.error || '发送失败');
        }
    } catch (err) {
        alert('网络异常，请稍后重试');
    } finally {
        sendBtn.disabled = false;
        win.scrollTop = win.scrollHeight;
    }
});

async function appendMessages(list) {
    for (const m of list) {
        if (!m || m.id <= lastId) continue;
        lastId = m.id;
        document.getElementById('chat-empty')?.remove();
        const wrap = document.createElement('div');
        wrap.innerHTML = await renderBubble(m);
        win.appendChild(wrap.firstElementChild);
    }
    win.scrollTop = win.scrollHeight;
}

async function renderBubble(m) {
    const mine = m.is_my_message;
    const avatar = m.avatar_url
        ? `<img src="${m.avatar_url}" class="rounded-circle" width="34" height="34">`
        : `<span class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;">${escapeHtml((m.user_name || '?').slice(0, 1))}</span>`;
    const fileHtml = m.file_url
        ? (m.is_image
            ? `<div class="mt-2"><a href="${m.file_url}" target="_blank"><img src="${m.file_url}" class="rounded border" style="max-width:200px;"></a></div>`
            : `<div class="mt-2"><a href="${m.file_url}" class="badge ${mine ? 'bg-white text-primary' : 'bg-light text-secondary border'} text-decoration-none"><i class="fa-solid fa-paperclip me-1"></i>${escapeHtml(m.file_name || '附件')}</a></div>`)
        : '';

    return `<div class="d-flex ${mine ? 'justify-content-end' : 'justify-content-start'} mb-3">
        ${mine ? '' : `<div class="flex-shrink-0 me-2">${avatar}</div>`}
        <div style="max-width:70%;">
            ${mine ? '' : `<div class="small text-muted mb-1">${escapeHtml(m.user_name)}</div>`}
            <div class="${mine ? 'bg-primary text-white' : 'bg-body-tertiary border'} rounded-3 px-3 py-2">
                ${m.content ? `<div style="white-space:pre-wrap;word-break:break-word;">${escapeHtml(m.content)}</div>` : ''}
                ${fileHtml}
            </div>
            <div class="small text-muted mt-1">${escapeHtml(m.full_time || '')}</div>
        </div>
        ${mine ? `<div class="flex-shrink-0 ms-2">${avatar}</div>` : ''}
    </div>`;
}

function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

// 轮询新消息
async function poll() {
    try {
        const res = await fetch(listUrl + '?after=' + lastId, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.success && data.messages?.length) await appendMessages(data.messages);
    } catch (e) { /* 忽略轮询失败 */ }
}
setInterval(poll, 5000);
</script>
@endpush

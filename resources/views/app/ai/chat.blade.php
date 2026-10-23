@extends('layouts.business')

@section('title', '智能助手')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h2 class="page-title mb-0">
            <i class="fa-solid fa-comments"></i>智能助手
            <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle align-middle">
                {{ $provider?->name ?? '未配置供应商' }}
            </span>
        </h2>
        <a href="{{ route('app.ai.analysis-center') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-chart-simple me-1"></i>分析中心
        </a>
    </div>

    <div class="alert alert-light border d-flex flex-wrap align-items-center gap-2 py-2 mb-3">
        <i class="fa-solid {{ $skills['icon'] }} text-primary"></i>
        <strong>{{ $skills['label'] }}技能包</strong>
        <span class="text-muted small">{{ $skills['focus'] }}</span>
        <span class="ms-auto text-muted small">数据范围：{{ $skills['data'] }}</span>
    </div>

    <div class="row g-3">
        {{-- 会话列表 --}}
        <div class="col-lg-3">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-folder-open me-2"></i>我的会话</span>
                    <form method="POST" action="{{ route('app.ai.chat.conversations.store') }}">
                        @csrf
                        <button class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i>新建</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 26rem; overflow-y: auto;">
                        @forelse ($conversations as $c)
                            <a href="{{ route('app.ai.chat', ['conversation' => $c->id]) }}"
                               class="list-group-item list-group-item-action {{ $conversation && $conversation->id === $c->id ? 'active' : '' }}">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <span class="text-truncate" style="max-width: 10rem;" title="{{ $c->title }}">
                                        @if ($c->is_pinned)
                                            <i class="fa-solid fa-thumbtack small"></i>
                                        @endif
                                        {{ $c->title }}
                                    </span>
                                    <small class="text-muted {{ $conversation && $conversation->id === $c->id ? 'text-white-50' : '' }}">
                                        {{ $c->message_count }}
                                    </small>
                                </div>
                                <small class="{{ $conversation && $conversation->id === $c->id ? 'text-white-50' : 'text-muted' }}">
                                    {{ $c->model?->name ?? '默认模型' }} · {{ $c->last_message_at?->format('m-d H:i') ?? '—' }}
                                </small>
                            </a>
                        @empty
                            <div class="p-3 text-muted small">还没有会话，点「新建」或直接提问即可。</div>
                        @endforelse
                    </div>
                </div>
                @if ($conversation)
                    <div class="card-footer d-flex flex-wrap gap-1">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#rename-box">
                            <i class="fa-solid fa-pen me-1"></i>重命名
                        </button>
                        <a href="{{ route('app.ai.chat.conversations.export', $conversation) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-file-export me-1"></i>导出
                        </a>
                        <form method="POST" action="{{ route('app.ai.chat.conversations.clear', $conversation) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-broom me-1"></i>清空</button>
                        </form>
                        <form method="POST" action="{{ route('app.ai.chat.conversations.destroy', $conversation) }}"
                              onsubmit="return confirm('删除后不可恢复，确定删除该会话？')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can me-1"></i>删除</button>
                        </form>
                        <div class="collapse w-100 mt-2" id="rename-box">
                            <form method="POST" action="{{ route('app.ai.chat.conversations.rename', $conversation) }}" class="d-flex gap-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="title" class="form-control form-control-sm" value="{{ $conversation->title }}" maxlength="200">
                                <button class="btn btn-sm btn-primary">保存</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- 对话区 --}}
        <div class="col-lg-9">
            <div class="card h-100">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <span><i class="fa-solid fa-robot me-2"></i>{{ $conversation?->title ?? '新会话' }}</span>
                    <div class="d-flex align-items-center gap-2">
                        <select id="model-select" class="form-select form-select-sm" style="width: auto;">
                            @foreach ($models as $m)
                                <option value="{{ $m->id }}" {{ $currentModel && $currentModel->id === $m->id ? 'selected' : '' }}>
                                    {{ $m->name }}（{{ $m->provider?->name }}）
                                </option>
                            @endforeach
                        </select>
                        <span class="badge bg-light text-secondary border">{{ $setting->rate_limit_minute }} 次/分</span>
                    </div>
                </div>

                <div class="card-body" id="chat-window" style="height: 26rem; overflow-y: auto; background: var(--bs-body-bg);">
                    @foreach ($messages as $m)
                        @include('app.ai.partials.message', ['message' => $m])
                    @endforeach
                    <div id="typing" class="text-muted small mt-2" style="display: none;">
                        <i class="fa-solid fa-circle-notch fa-spin me-1"></i>AI 正在思考…
                    </div>
                </div>

                <div class="card-footer">
                    <div id="quick-prompts" class="mb-2 d-flex flex-wrap gap-1">
                        @foreach ($quickPrompts as $q)
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-prompt">{{ $q }}</button>
                        @endforeach
                    </div>

                    <div id="attach-preview" class="d-flex flex-wrap gap-2 mb-2"></div>

                    <form id="chat-form" class="d-flex align-items-end gap-2">
                        @csrf
                        <input type="hidden" name="conversation_id" id="conversation-id" value="{{ $conversation?->id }}">
                        <label class="btn btn-outline-secondary mb-0" title="上传图片或文档（最多 5 个，单个 ≤10MB）">
                            <i class="fa-solid fa-paperclip"></i>
                            <input type="file" id="file-input" name="files[]" multiple hidden
                                   accept="image/*,.pdf,.txt,.csv,.xlsx,.xls,.docx">
                        </label>
                        <textarea id="chat-input" class="form-control" rows="2" placeholder="输入问题，Enter 发送，Shift+Enter 换行…"></textarea>
                        <button type="submit" class="btn btn-primary" id="send-btn">
                            <i class="fa-solid fa-paper-plane me-1"></i>发送
                        </button>
                    </form>
                    <div class="form-text">AI 回答基于本系统数据，重要决策请人工复核。</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
const window_ = document.getElementById('chat-window');
const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');
const sendBtn = document.getElementById('send-btn');
const typing = document.getElementById('typing');
const fileInput = document.getElementById('file-input');
const previewBox = document.getElementById('attach-preview');
const conversationId = document.getElementById('conversation-id');
const modelSelect = document.getElementById('model-select');
const csrf = document.querySelector('#chat-form input[name=_token]').value;

window_.scrollTop = window_.scrollHeight;

// 快捷问题
document.querySelectorAll('.quick-prompt').forEach((btn) => {
    btn.addEventListener('click', () => {
        input.value = btn.textContent.trim();
        input.focus();
    });
});

// 附件选择预览
fileInput.addEventListener('change', () => renderPreviews(fileInput.files));

function renderPreviews(files) {
    previewBox.innerHTML = '';
    Array.from(files).slice(0, 5).forEach((f) => {
        const tag = document.createElement('span');
        tag.className = 'badge bg-light text-secondary border';
        const isImage = f.type.startsWith('image/');
        tag.innerHTML = `<i class="fa-solid ${isImage ? 'fa-image' : 'fa-file-lines'} me-1"></i>`
            + escapeHtml(f.name) + ` <small>(${Math.round(f.size / 1024)}KB)</small>`;
        previewBox.appendChild(tag);
    });
    if (files.length > 5) {
        previewBox.insertAdjacentHTML('beforeend', '<span class="text-danger small">最多 5 个文件</span>');
    }
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, (c) => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));
}

// 极简 Markdown 渲染（代码块 / 标题 / 列表 / 粗体 / 表格分隔）
function renderMarkdown(text) {
    const parts = [];
    const re = /```(\w*)\n?([\s\S]*?)```/g;
    let last = 0, match;
    while ((match = re.exec(text)) !== null) {
        parts.push({ type: 'text', value: text.slice(last, match.index) });
        parts.push({ type: 'code', lang: match[1], value: match[2] });
        last = re.lastIndex;
    }
    parts.push({ type: 'text', value: text.slice(last) });

    return parts.map((p) => {
        if (p.type === 'code') {
            return `<pre class="bg-body-tertiary border rounded p-2 small"><code>${escapeHtml(p.value)}</code></pre>`;
        }
        return escapeHtml(p.value)
            .replace(/^### (.*)$/gm, '<h6 class="fw-bold mt-2 mb-1">$1</h6>')
            .replace(/^## (.*)$/gm, '<h5 class="fw-bold mt-2 mb-1">$1</h5>')
            .replace(/^# (.*)$/gm, '<h4 class="fw-bold mt-2 mb-1">$1</h4>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/^- (.*)$/gm, '<li>$1</li>')
            .replace(/(<li>.*<\/li>)/s, '<ul class="mb-1">$1</ul>')
            .replace(/\n/g, '<br>');
    }).join('');
}

function bubble(role, html, time, extra) {
    const mine = role === 'user';
    const wrap = document.createElement('div');
    wrap.className = 'mb-3 d-flex ' + (mine ? 'justify-content-end' : 'justify-content-start');
    wrap.innerHTML = `
        <div class="${mine ? 'bg-primary text-white' : 'bg-body-tertiary border'} rounded-3 p-3"
             style="max-width: 78%;">
            <div class="${mine ? '' : 'fw-semibold small mb-1'}">
                ${mine ? '' : '<i class="fa-solid fa-robot me-1"></i>AI 助手'}
            </div>
            <div class="ai-content" style="font-size: .95rem;">${html}</div>
            <div class="small mt-1 ${mine ? 'text-white-50' : 'text-muted'}">
                ${escapeHtml(time || '')} ${extra ? '<span class="ms-1">' + extra + '</span>' : ''}
            </div>
        </div>`;
    return wrap;
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text && fileInput.files.length === 0) return;

    const fd = new FormData();
    fd.append('message', text);
    fd.append('conversation_id', conversationId.value || '');
    fd.append('model_id', modelSelect.value);
    Array.from(fileInput.files).slice(0, 5).forEach((f) => fd.append('files[]', f));

    const attachHtml = Array.from(fileInput.files).slice(0, 5).map((f) =>
        `<span class="badge bg-light text-secondary border"><i class="fa-solid fa-paperclip me-1"></i>${escapeHtml(f.name)}</span>`
    ).join(' ');

    window_.appendChild(bubble('user', escapeHtml(text).replace(/\n/g, '<br>') + (attachHtml ? '<div class="mt-1">' + attachHtml + '</div>' : ''), nowTime()));
    input.value = '';
    fileInput.value = '';
    previewBox.innerHTML = '';
    window_.scrollTop = window_.scrollHeight;

    typing.style.display = 'block';
    sendBtn.disabled = true;

    try {
        const res = await fetch('{{ route('app.ai.chat.ask') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd,
        });
        const data = await res.json();

        if (!res.ok || !data.success) {
            window_.appendChild(bubble('assistant',
                `<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>${escapeHtml(data.error || '请求失败')}</span>`, nowTime()));
        } else {
            window_.appendChild(bubble('assistant', renderMarkdown(data.reply), data.timestamp || nowTime(),
                `<span class="badge bg-light text-secondary border">${escapeHtml(data.model_name || '')}</span>`));
            if (data.conversation_id && !conversationId.value) {
                conversationId.value = data.conversation_id;
                history.replaceState(null, '', '{{ route('app.ai.chat') }}?conversation=' + data.conversation_id);
            }
        }
    } catch (err) {
        window_.appendChild(bubble('assistant', '<span class="text-danger">网络异常，请稍后重试。</span>', nowTime()));
    } finally {
        typing.style.display = 'none';
        sendBtn.disabled = false;
        window_.scrollTop = window_.scrollHeight;
    }
});

input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        form.requestSubmit();
    }
});

function nowTime() {
    const d = new Date();
    return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
}
</script>
@endpush

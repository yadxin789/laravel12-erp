@extends('layouts.business')

@section('title', 'AI 智能助手')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow flex flex-col" style="height: calc(100vh - 140px);">
        <!-- 头部 -->
        <div class="border-b p-4 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold">🤖 AI 智能助手</h2>
                <p class="text-sm text-gray-500">问库存、查订单、看经营概览，支持自然语言</p>
            </div>
            <form method="POST" action="{{ route('app.ai.clear') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-red-600">清空对话</button>
            </form>
        </div>

        <!-- 聊天记录 -->
        <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50">
            @if(empty($history))
                <div class="text-center text-gray-400 py-10">
                    <p class="text-4xl mb-3">👋</p>
                    <p>你好！我是 ERP 智能助手，可以帮你：</p>
                    <div class="mt-4 text-left max-w-md mx-auto space-y-2 text-sm">
                        <div class="bg-white p-3 rounded shadow-sm">📦 "深圳仓还有多少电阻？"</div>
                        <div class="bg-white p-3 rounded shadow-sm">⚠️ "哪些物料库存不足？"</div>
                        <div class="bg-white p-3 rounded shadow-sm">📋 "待审批的采购订单有哪些？"</div>
                        <div class="bg-white p-3 rounded shadow-sm">📊 "今天经营概况怎么样？"</div>
                        <div class="bg-white p-3 rounded shadow-sm">🏭 "有哪些在制工单？"</div>
                    </div>
                </div>
            @else
                @foreach($history as $msg)
                    <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] p-3 rounded-lg {{ $msg['role'] === 'user' ? 'bg-blue-600 text-white' : 'bg-white shadow' }}">
                            <div class="text-sm whitespace-pre-wrap">{{ $msg['content'] }}</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- 输入区 -->
        <div class="border-t p-4">
            <form id="chat-form" class="flex gap-2">
                @csrf
                <input type="text" id="chat-input" name="message" placeholder="输入你的问题..."
                       class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       autocomplete="off">
                <button type="submit" id="chat-send" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    发送
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');
const box = document.getElementById('chat-box');
const sendBtn = document.getElementById('chat-send');

function addMsg(content, role) {
    const div = document.createElement('div');
    div.className = 'flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
    div.innerHTML = `<div class="max-w-[80%] p-3 rounded-lg ${role === 'user' ? 'bg-blue-600 text-white' : 'bg-white shadow'}">
        <div class="text-sm whitespace-pre-wrap"></div>
    </div>`;
    div.querySelector('div').textContent = content;
    box.appendChild(div);
    box.scrollTop = box.scrollHeight;
}

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;

    addMsg(msg, 'user');
    input.value = '';
    sendBtn.disabled = true;
    sendBtn.textContent = '思考中...';

    try {
        const resp = await fetch('{{ route('app.ai.ask') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: msg })
        });
        const data = await resp.json();
        addMsg(data.reply || '暂无回复', 'assistant');
    } catch (err) {
        addMsg('网络错误，请稍后重试', 'assistant');
    }

    sendBtn.disabled = false;
    sendBtn.textContent = '发送';
    input.focus();
});

box.scrollTop = box.scrollHeight;
</script>
@endsection

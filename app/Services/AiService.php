<?php

namespace App\Services;

use App\Models\System\AiSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected AiSetting $config;
    protected ?AiToolService $tools = null;
    protected ?\App\Models\System\AiProvider $provider = null;
    protected ?\App\Models\System\AiModel $model = null;

    public function __construct(?AiSetting $config = null)
    {
        $this->config = $config ?: AiSetting::current();
    }

    public function setTools(AiToolService $tools): self
    {
        $this->tools = $tools;
        return $this;
    }

    /**
     * 指定本次调用使用的供应商（多供应商支持）
     */
    public function useProvider(?\App\Models\System\AiProvider $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * 指定本次调用使用的模型
     */
    public function useModel(?\App\Models\System\AiModel $model): self
    {
        $this->model = $model;
        if ($model && !$this->provider) {
            $this->provider = $model->provider;
        }
        return $this;
    }

    /**
     * 生效的 API Key：优先当前供应商，回退全局配置
     */
    public function apiKey(): ?string
    {
        if ($this->provider && $this->provider->hasRealApiKey()) {
            return $this->provider->api_key;
        }

        return $this->config->api_key;
    }

    /**
     * 生效的 Base URL
     */
    public function baseUrl(): string
    {
        return rtrim((string) ($this->provider?->base_url ?: $this->config->base_url), '/');
    }

    /**
     * 生效的模型标识
     */
    public function modelKey(): string
    {
        if ($this->model) {
            return $this->model->model_id;
        }

        return (string) $this->config->model;
    }

    /**
     * 是否可用
     */
    public function isAvailable(): bool
    {
        $key = $this->apiKey();

        if ($this->provider) {
            return $this->provider->enabled && $key && !str_starts_with((string) $key, 'sk-xxxx');
        }

        return $this->config->enabled
            && $key
            && !str_starts_with((string) $key, 'sk-xxxx');
    }

    /**
     * 文本对话（chat completion）
     *
     * @param string $userMessage 用户消息
     * @param string|null $systemPrompt 系统提示词
     * @param array $history 历史消息 [['role'=>'user','content'=>'...'], ...]
     * @return string|null AI 回复
     */
    public function chat(string $userMessage, ?string $systemPrompt = null, array $history = []): ?string
    {
        if (!$this->isAvailable()) {
            Log::warning('AI 未配置或未启用', ['provider' => $this->config->provider]);
            return null;
        }

        $messages = [];
        if ($systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        }
        $messages = array_merge($messages, $history);
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        try {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey(),
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->baseUrl() . '/chat/completions', [
                'model' => $this->modelKey(),
                'messages' => $messages,
                'temperature' => (float) $this->config->temperature,
                'max_tokens' => $this->config->max_tokens,
            ]);

            if ($resp->successful()) {
                return $resp->json('choices.0.message.content');
            }

            Log::error('AI API 调用失败', [
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('AI API 异常: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 视觉理解（图片识别 / OCR）
     *
     * @param string $imageBase64 base64 编码的图片（不含 data: 前缀）
     * @param string $prompt 提示词（如"提取这张发票的所有信息"）
     * @return string|null
     */
    public function vision(string $imageBase64, string $prompt): ?string
    {
        if (!$this->isAvailable() || !$this->config->vision_enabled) {
            Log::warning('AI 视觉未启用');
            return null;
        }

        try {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->config->api_key,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post(rtrim($this->config->base_url, '/') . '/chat/completions', [
                'model' => $this->config->vision_model ?: $this->config->model,
                'messages' => [[
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        ['type' => 'image_url', 'image_url' => ['url' => 'data:image/jpeg;base64,' . $imageBase64]],
                    ],
                ]],
                'max_tokens' => $this->config->max_tokens,
            ]);

            if ($resp->successful()) {
                return $resp->json('choices.0.message.content');
            }

            Log::error('AI 视觉调用失败', [
                'status' => $resp->status(),
                'body' => $resp->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('AI 视觉异常: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 测试连接
     */
    public function testConnection(): array
    {
        if (!$this->config->api_key || str_starts_with($this->config->api_key, 'sk-xxxx')) {
            return ['ok' => false, 'message' => '请先填写 API Key'];
        }

        $result = $this->chat('你好，请回复"连接成功"四个字。');
        if ($result) {
            return ['ok' => true, 'message' => '连接成功！AI 回复：' . $result];
        }
        return ['ok' => false, 'message' => '连接失败，请检查 API Key 和网络'];
    }

    /**
     * 测试指定供应商的连通性（不落库、不影响全局配置）
     */
    public function testForProvider(\App\Models\System\AiProvider $provider, ?string $modelId = null): array
    {
        if (!$provider->hasRealApiKey()) {
            return ['ok' => false, 'message' => '该供应商尚未填写 API Key'];
        }

        $modelId = $modelId
            ?: $provider->models()->where('is_default', true)->value('model_id')
            ?: $provider->models()->where('enabled', true)->value('model_id');

        $fakeModel = $modelId ? new \App\Models\System\AiModel([
            'model_id' => $modelId,
            'name' => $modelId,
        ]) : null;
        if ($fakeModel) {
            $fakeModel->setRelation('provider', $provider);
        }

        $service = (new self($this->config))->useProvider($provider);
        if ($fakeModel) {
            $service->useModel($fakeModel);
        }

        try {
            $reply = $service->chat('你好，请只回复四个字：连接成功。');
            if ($reply) {
                return ['ok' => true, 'message' => '连接成功（' . ($modelId ?: '默认模型') . '）：' . mb_substr($reply, 0, 60)];
            }

            return ['ok' => false, 'message' => '调用失败，请检查 API Key、Base URL 或网络'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => '异常：' . $e->getMessage()];
        }
    }

    /**
     * 带附件的对话：图片走视觉模型，文本文件抽取内容拼进提示词
     *
     * @param array $attachments AiAttachment[]（含图片与文本文件）
     */
    public function chatWithAttachments(string $userMessage, array $history = [], array $attachments = [], ?string $systemPrompt = null): ?string
    {
        if (!$this->isAvailable()) {
            return '⚠️ AI 助手尚未配置。请前往 后台 → AI 智能助手 设置 API Key 并启用。';
        }

        $images = array_values(array_filter($attachments, fn ($a) => $a->is_image));
        $files = array_values(array_filter($attachments, fn ($a) => !$a->is_image));

        // 图片理解：使用视觉模型，走 base64 image_url
        if ($images && $this->config->vision_enabled) {
            $visionModel = \App\Models\System\AiModel::usable()
                ->vision()
                ->where('provider_id', $this->provider?->id)
                ->first();

            $content = [['type' => 'text', 'text' => $userMessage]];
            foreach ($images as $att) {
                $path = $att->absolutePath();
                if (!$path || !is_file($path)) {
                    continue;
                }
                $base64 = base64_encode(file_get_contents($path));
                $content[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => 'data:image/jpeg;base64,' . $base64],
                ];
            }

            try {
                $resp = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey(),
                    'Content-Type' => 'application/json',
                ])->timeout(120)->post($this->baseUrl() . '/chat/completions', [
                    'model' => $visionModel?->model_id ?: $this->modelKey(),
                    'messages' => array_filter([
                        $systemPrompt ? ['role' => 'system', 'content' => $systemPrompt] : null,
                        ['role' => 'user', 'content' => $content],
                    ]),
                    'max_tokens' => $this->config->max_tokens,
                ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('AI 视觉调用异常: ' . $e->getMessage());
            }
        }

        // 文本附件：把抽取出的原文拼进用户消息
        $prompt = $userMessage;
        foreach ($files as $att) {
            if (!empty($att->extracted_text)) {
                $prompt .= "\n\n以下是用户上传文件《{$att->original_name}》的原文内容，请直接据此回答，不要声称看不到文件：\n"
                    . mb_substr($att->extracted_text, 0, 20000);
            }
        }

        return $this->setTools($this->tools ?: new AiToolService())
            ->chatWithTools($prompt, $history, $systemPrompt);
    }

    /**
     * 审批建议分析
     * 分析订单数据并给出 AI 审批建议
     */
    public function analyzeApproval(string $orderType, array $orderData, array $context = []): ?string
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $systemPrompt = "你是一个电子工厂ERP的审批助手。请根据订单数据给出审批建议，包括："
            . "1) 是否建议批准/拒绝 2) 风险点 3) 注意事项。用简洁的中文回答，不超过200字。";

        $userPrompt = "请分析以下{$orderType}：\n" . json_encode($orderData, JSON_UNESCAPED_UNICODE);
        if ($context) {
            $userPrompt .= "\n\n补充信息：" . json_encode($context, JSON_UNESCAPED_UNICODE);
        }

        return $this->chat($userPrompt, $systemPrompt);
    }

    /**
     * 当前提供商信息
     */
    public function providerInfo(): array
    {
        return AiSetting::providers()[$this->config->provider] ?? [];
    }

    /**
     * 带工具调用的对话（Function Calling）
     * AI 可根据用户问题自动查询 ERP 内部数据
     *
     * @param string $userMessage 用户问题
     * @param array $history 历史消息
     * @return string|null AI 最终回复
     */
    public function chatWithTools(string $userMessage, array $history = [], ?string $systemPrompt = null): ?string
    {
        if (!$this->isAvailable()) {
            return '⚠️ AI 助手尚未配置。请前往 后台 → AI 智能助手 设置 API Key 并启用。';
        }

        $systemPrompt = $systemPrompt ?: ("你是一个电子工厂ERP系统的智能助手。你可以调用工具查询库存、订单、物料等数据。"
            . "请用简洁的中文回答用户问题。如果需要查询数据，先调用对应工具，再基于结果回答。"
            . "回答时不要编造数据，所有数字必须来自工具返回。");

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $userMessage]]
        );

        $payload = [
            'model' => $this->modelKey(),
            'messages' => $messages,
            'temperature' => (float) $this->config->temperature,
            'max_tokens' => $this->config->max_tokens,
        ];

        // 如果有工具，添加 function calling
        if ($this->tools) {
            $payload['tools'] = $this->tools->definitions();
            $payload['tool_choice'] = 'auto';
        }

        $baseUrl = $this->baseUrl();

        // 最多循环 3 轮（防止无限调用）
        for ($i = 0; $i < 3; $i++) {
            try {
                $resp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey(),
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post($baseUrl . '/chat/completions', $payload);

                if (!$resp->successful()) {
                    Log::error('AI 工具调用失败', ['status' => $resp->status(), 'body' => $resp->body()]);
                    return 'AI 服务调用失败，请检查配置。';
                }

                $data = $resp->json();
                $message = $data['choices'][0]['message'] ?? null;

                if (!$message) return null;

                // 如果 AI 要求调用工具
                if (!empty($message['tool_calls'])) {
                    $messages[] = $message;

                    foreach ($message['tool_calls'] as $toolCall) {
                        $fnName = $toolCall['function']['name'] ?? '';
                        $fnArgs = json_decode($toolCall['function']['arguments'] ?? '{}', true) ?: [];

                        Log::info('AI 调用工具: ' . $fnName, $fnArgs);

                        $result = $this->tools ? $this->tools->call($fnName, $fnArgs) : '工具不可用';

                        $messages[] = [
                            'role' => 'tool',
                            'tool_call_id' => $toolCall['id'],
                            'content' => $result,
                        ];
                    }

                    // 把更新后的 messages 放入 payload，继续下一轮
                    $payload['messages'] = $messages;
                    continue;
                }

                // 没有工具调用，直接返回最终回复
                return $message['content'] ?? null;

            } catch (\Exception $e) {
                Log::error('AI chatWithTools 异常: ' . $e->getMessage());
                return 'AI 服务异常：' . $e->getMessage();
            }
        }

        return '查询超时，请换个问法试试。';
    }
}

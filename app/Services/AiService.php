<?php

namespace App\Services;

use App\Models\System\AiSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    protected AiSetting $config;
    protected ?AiToolService $tools = null;

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
     * 是否可用
     */
    public function isAvailable(): bool
    {
        return $this->config->enabled
            && $this->config->api_key
            && !str_starts_with($this->config->api_key, 'sk-xxxx');
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
                'Authorization' => 'Bearer ' . $this->config->api_key,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post(rtrim($this->config->base_url, '/') . '/chat/completions', [
                'model' => $this->config->model,
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
    public function chatWithTools(string $userMessage, array $history = []): ?string
    {
        if (!$this->isAvailable()) {
            return '⚠️ AI 助手尚未配置。请前往 后台 → AI 智能助手 设置 API Key 并启用。';
        }

        $systemPrompt = "你是一个电子工厂ERP系统的智能助手。你可以调用工具查询库存、订单、物料等数据。"
            . "请用简洁的中文回答用户问题。如果需要查询数据，先调用对应工具，再基于结果回答。"
            . "回答时不要编造数据，所有数字必须来自工具返回。";

        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $history,
            [['role' => 'user', 'content' => $userMessage]]
        );

        $payload = [
            'model' => $this->config->model,
            'messages' => $messages,
            'temperature' => (float) $this->config->temperature,
            'max_tokens' => $this->config->max_tokens,
        ];

        // 如果有工具，添加 function calling
        if ($this->tools) {
            $payload['tools'] = $this->tools->definitions();
            $payload['tool_choice'] = 'auto';
        }

        $baseUrl = rtrim($this->config->base_url, '/');

        // 最多循环 3 轮（防止无限调用）
        for ($i = 0; $i < 3; $i++) {
            try {
                $resp = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->config->api_key,
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

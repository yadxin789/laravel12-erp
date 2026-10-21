<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $fillable = [
        'provider', 'api_key', 'base_url', 'model',
        'vision_enabled', 'vision_model', 'temperature', 'max_tokens', 'enabled',
    ];

    protected $casts = [
        'vision_enabled' => 'boolean',
        'enabled' => 'boolean',
        'temperature' => 'decimal:2',
    ];

    /**
     * 获取当前 AI 配置（单行）
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'provider' => 'deepseek',
            'base_url' => 'https://api.deepseek.com/v1',
            'model' => 'deepseek-chat',
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);
    }

    /**
     * 支持的 AI 提供商列表
     */
    public static function providers(): array
    {
        return [
            'deepseek' => [
                'name' => 'DeepSeek（深度求索）',
                'base_url' => 'https://api.deepseek.com/v1',
                'price' => '输入 ¥1/百万token · 输出 ¥2/百万token',
                'free_quota' => '新用户注册送 ¥10 额度',
                'best_for' => '性价比最高，推荐首选',
                'models' => [
                    'deepseek-chat' => 'DeepSeek-V3（通用对话）',
                    'deepseek-reasoner' => 'DeepSeek-R1（推理模型）',
                ],
                'vision_models' => [
                    'deepseek-vl' => 'DeepSeek-VL（视觉理解）',
                ],
                'docs' => 'https://platform.deepseek.com/api_keys',
            ],
            'qwen' => [
                'name' => '通义千问（阿里云）',
                'base_url' => 'https://dashscope.aliyuncs.com/compatible-mode/v1',
                'price' => 'Turbo ¥0.3/百万 · Plus ¥0.8/百万 · Max ¥2/百万',
                'free_quota' => '新用户送 100万 token 免费额度',
                'best_for' => '阿里云生态企业，稳定性好',
                'models' => [
                    'qwen-turbo' => 'Qwen-Turbo（快速低成本）',
                    'qwen-plus' => 'Qwen-Plus（均衡）',
                    'qwen-max' => 'Qwen-Max（最强）',
                ],
                'vision_models' => [
                    'qwen-vl-plus' => 'Qwen-VL-Plus（视觉）',
                    'qwen-vl-max' => 'Qwen-VL-Max（视觉最强）',
                ],
                'docs' => 'https://dashscope.console.aliyun.com/apiKey',
            ],
            'doubao' => [
                'name' => '豆包（字节跳动）',
                'base_url' => 'https://ark.cn-beijing.volces.com/api/v3',
                'price' => 'Pro-4k ¥0.8/百万 · Pro-32k ¥2/百万',
                'free_quota' => '新用户送 50万 token 体验额度',
                'best_for' => '火山引擎生态，视觉能力强',
                'models' => [
                    'doubao-pro-4k' => 'Doubao-Pro-4k',
                    'doubao-pro-32k' => 'Doubao-Pro-32k',
                    'doubao-pro-128k' => 'Doubao-Pro-128k',
                ],
                'vision_models' => [
                    'doubao-vision-pro' => 'Doubao-Vision-Pro',
                ],
                'docs' => 'https://console.volcengine.com/ark/region:ark+cn-beijing/apiKey',
            ],
            'hunyuan' => [
                'name' => '腾讯元宝（混元）',
                'base_url' => 'https://api.hunyuan.cloud.tencent.com/v1',
                'price' => 'Lite 免费 · Pro ¥2/百万 · Standard ¥0.5/百万',
                'free_quota' => 'Hunyuan-Lite 永久免费调用',
                'best_for' => '腾讯云生态，有免费模型可用',
                'models' => [
                    'hunyuan-lite' => 'Hunyuan-Lite（免费）',
                    'hunyuan-pro' => 'Hunyuan-Pro',
                    'hunyuan-standard' => 'Hunyuan-Standard',
                ],
                'vision_models' => [
                    'hunyuan-vision' => 'Hunyuan-Vision',
                ],
                'docs' => 'https://console.cloud.tencent.com/hunyuan/api-key',
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\AiSetting;
use App\Services\AiService;
use Illuminate\Http\Request;

class AiSettingController extends Controller
{
    public function index()
    {
        $setting = AiSetting::current();
        $providers = AiSetting::providers();
        $providerInfo = $providers[$setting->provider] ?? null;

        return view('admin.ai-settings.index', compact('setting', 'providers', 'providerInfo'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:deepseek,qwen,doubao,hunyuan',
            'api_key' => 'nullable|string|max:500',
            'base_url' => 'nullable|string|max:500',
            'model' => 'required|string|max:100',
            'vision_enabled' => 'boolean',
            'vision_model' => 'nullable|string|max:100',
            'temperature' => 'required|numeric|min:0|max:2',
            'max_tokens' => 'required|integer|min:100|max:8000',
            'enabled' => 'boolean',
        ]);

        $setting = AiSetting::current();

        // 如果没填 API Key，保留原值
        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }

        $setting->update($validated);

        return back()->with('success', 'AI 设置已保存');
    }

    /**
     * 测试连接
     */
    public function test(AiService $ai)
    {
        $result = $ai->testConnection();
        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}

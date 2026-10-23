<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\AiModel;
use App\Models\System\AiProvider;
use App\Models\System\AiSetting;
use App\Services\AiService;
use Illuminate\Http\Request;

/**
 * 管理员 AI 设置：多供应商 + 模型目录 + 生成参数与限流
 * 对齐参考项目 AiController 的设置部分
 */
class AiSettingController extends Controller
{
    public function index()
    {
        $setting = AiSetting::current();
        $catalog = AiSetting::providers();

        // 目录里有的供应商，若表里缺失则自动补齐（默认停用）
        AiProvider::syncFromCatalog();

        $providerRows = AiProvider::with('models')->orderBy('id')->get()->keyBy('key');
        $models = AiModel::with('provider')->orderBy('provider_id')->orderBy('sort_order')->get();
        $defaultModel = AiModel::usableDefault();
        $activeProvider = AiProvider::active();

        return view('admin.ai-settings.index', compact(
            'setting', 'catalog', 'providerRows', 'models', 'defaultModel', 'activeProvider'
        ));
    }

    /**
     * 全局生成参数与限流
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'nullable|string|max:40',
            'api_key' => 'nullable|string|max:500',
            'base_url' => 'nullable|string|max:500',
            'model' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:100|max:8000',
            'rate_limit_minute' => 'nullable|integer|min:1|max:1000',
            'rate_limit_daily' => 'nullable|integer|min:1|max:100000',
            'enabled' => 'boolean',
            'vision_enabled' => 'boolean',
            'source' => 'nullable|string|max:20',
        ]);

        $setting = AiSetting::current();

        // 未勾选的开关也要能关闭
        $validated['enabled'] = $request->boolean('enabled');
        $validated['vision_enabled'] = $request->boolean('vision_enabled');

        // 未填字段回退原值
        $validated['temperature'] = $validated['temperature'] ?? $setting->temperature ?? 0.7;
        $validated['max_tokens'] = $validated['max_tokens'] ?? $setting->max_tokens ?? 2000;
        $validated['rate_limit_minute'] = $validated['rate_limit_minute'] ?? $setting->rate_limit_minute ?? 30;
        $validated['rate_limit_daily'] = $validated['rate_limit_daily'] ?? $setting->rate_limit_daily ?? 200;

        // 空 Key 表示保持不变
        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }

        // provider 变更时，模型自动切到该供应商的第一个可用模型
        if (!empty($validated['provider'])) {
            $catalog = AiSetting::providers();
            $providerModels = array_keys($catalog[$validated['provider']]['models'] ?? []);
            if (empty($validated['model']) || ($providerModels && !in_array($validated['model'], $providerModels, true))) {
                $validated['model'] = $providerModels[0] ?? ($setting->model ?: 'deepseek-chat');
            }

            // 同步到供应商表
            $row = AiProvider::where('key', $validated['provider'])->first();
            if ($row) {
                $row->update(array_filter([
                    'enabled' => true,
                    'base_url' => $validated['base_url'] ?? null,
                    'api_key' => $validated['api_key'] ?? null,
                ], fn ($v) => $v !== null));
                $row->markAsDefault();
                $row->syncToLegacySetting();
            }
        }

        unset($validated['source']);
        $setting->update($validated);

        return back()->with('success', 'AI 设置已保存');
    }

    /**
     * 保存某个供应商的密钥 / 地址
     */
    public function saveProvider(Request $request, string $provider)
    {
        $row = AiProvider::where('key', $provider)->firstOrFail();

        $validated = $request->validate([
            'api_key' => 'nullable|string|max:500',
            'base_url' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:500',
        ]);

        $data = [];
        if (!empty($validated['api_key'])) {
            $data['api_key'] = trim($validated['api_key']);
            $data['enabled'] = true;
        }
        if (!empty($validated['base_url'])) {
            $data['base_url'] = trim($validated['base_url']);
        }
        if (array_key_exists('note', $validated)) {
            $data['note'] = $validated['note'];
        }

        $row->update($data);
        $row->syncToLegacySetting();

        return back()->with('success', $row->name . ' 配置已保存');
    }

    public function toggleProvider(string $provider)
    {
        $row = AiProvider::where('key', $provider)->firstOrFail();
        $row->update(['enabled' => !$row->enabled]);
        $row->syncToLegacySetting();

        return back()->with('success', $row->name . ($row->enabled ? ' 已启用。' : ' 已停用。'));
    }

    public function setDefaultProvider(string $provider)
    {
        $row = AiProvider::where('key', $provider)->firstOrFail();
        $row->markAsDefault();
        $row->syncToLegacySetting();

        return back()->with('success', '默认供应商已设为：' . $row->name);
    }

    public function removeProvider(string $provider)
    {
        $row = AiProvider::where('key', $provider)->firstOrFail();
        $row->update(['api_key' => null, 'enabled' => false]);

        return back()->with('success', $row->name . ' 的密钥已清除并停用。');
    }

    /**
     * 测试指定供应商连通性
     */
    public function testProvider(Request $request, string $provider, AiService $ai)
    {
        $row = AiProvider::where('key', $provider)->firstOrFail();
        $result = $ai->testForProvider($row, $request->input('model_id'));

        return back()->with($result['ok'] ? 'success' : 'error', $row->name . '：' . $result['message']);
    }

    /**
     * 按目录补齐该供应商的预设模型
     */
    public function syncProviderModels(string $provider)
    {
        $row = AiProvider::where('key', $provider)->firstOrFail();
        $catalog = $row->catalog();

        $added = 0;
        foreach (($catalog['models'] ?? []) as $id => $name) {
            $exists = $row->models()->where('model_id', $id)->exists();
            if (!$exists) {
                $row->models()->create([
                    'model_id' => $id,
                    'name' => $name,
                    'enabled' => true,
                    'sort_order' => $added + 1,
                ]);
                $added++;
            }
        }
        foreach (($catalog['vision_models'] ?? []) as $id => $name) {
            $exists = $row->models()->where('model_id', $id)->exists();
            if (!$exists) {
                $row->models()->create([
                    'model_id' => $id,
                    'name' => $name,
                    'is_vision' => true,
                    'enabled' => true,
                    'sort_order' => 50 + $added,
                ]);
                $added++;
            }
        }

        $row->syncToLegacySetting();

        return back()->with('success', $row->name . '：已补齐 ' . $added . ' 个模型');
    }

    public function storeModel(Request $request)
    {
        $validated = $request->validate([
            'provider_id' => 'required|integer|exists:ai_providers,id',
            'model_id' => 'required|string|max:120',
            'name' => 'required|string|max:150',
            'enabled' => 'boolean',
            'is_vision' => 'boolean',
        ]);

        $validated['enabled'] = $request->boolean('enabled', true);
        $validated['is_vision'] = $request->boolean('is_vision');

        $model = AiModel::create($validated);

        return back()->with('success', '模型「' . $model->name . '」已添加');
    }

    public function updateModel(Request $request, AiModel $model)
    {
        $validated = $request->validate([
            'model_id' => 'required|string|max:120',
            'name' => 'required|string|max:150',
            'enabled' => 'boolean',
            'is_vision' => 'boolean',
        ]);

        $validated['enabled'] = $request->boolean('enabled');
        $validated['is_vision'] = $request->boolean('is_vision');

        $model->update($validated);

        return back()->with('success', '模型「' . $model->name . '」已更新');
    }

    public function setDefaultModel(AiModel $model)
    {
        $model->markAsDefault();

        return back()->with('success', '默认模型已设为：' . $model->name);
    }

    public function destroyModel(AiModel $model)
    {
        $name = $model->name;
        $model->delete();

        return back()->with('success', '模型「' . $name . '」已删除');
    }

    /**
     * 测试当前默认通道
     */
    public function test(AiService $ai)
    {
        $provider = AiProvider::active();
        $result = $provider
            ? $ai->testForProvider($provider)
            : $ai->testConnection();

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}

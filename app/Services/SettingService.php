<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    protected ?array $settings = null;

    public function get(string $key, $default = null)
    {
        $this->load();
        return $this->settings[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        try {
            if (\Schema::hasTable('settings')) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => is_array($value) || is_object($value) ? json_encode($value) : (string) $value]
                );
                $this->settings = null;
                Cache::forget('eden_settings');
                return;
            }
        } catch (\Throwable $e) {
            // fall through to file
        }
        $path = storage_path('app/settings.json');
        $data = file_exists($path) ? json_decode(file_get_contents($path), true) ?? [] : [];
        $data[$key] = $value;
        if (! is_dir(dirname($path))) {
            @mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        $this->settings = null;
        Cache::forget('eden_settings');
    }

    protected function load(): void
    {
        if ($this->settings !== null) {
            return;
        }
        try {
            $this->settings = Cache::remember('eden_settings', 3600, function () {
                if (\Schema::hasTable('settings')) {
                    return Setting::pluck('value', 'key')->map(function ($v) {
                        $d = json_decode($v, true);
                        return $d !== null ? $d : $v;
                    })->toArray();
                }
                $path = storage_path('app/settings.json');
                if (file_exists($path)) {
                    return json_decode(file_get_contents($path), true) ?? [];
                }
                return [];
            });
        } catch (\Throwable $e) {
            $this->settings = [];
        }
    }
}

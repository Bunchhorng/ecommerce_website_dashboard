<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    protected array $defaults = [
        'storeName' => 'E-KHMER',
        'supportEmail' => 'support@e-khmer.com',
        'supportPhone' => '',
        'currency' => 'USD',
        'locale' => 'en',
        'lowStockThreshold' => 5,
        'emailOrderNotifications' => true,
        'emailLowStockAlerts' => true,
    ];

    protected array $casts = [
        'lowStockThreshold' => 'int',
        'emailOrderNotifications' => 'bool',
        'emailLowStockAlerts' => 'bool',
    ];

    public function show()
    {
        return $this->response($this->loadAll());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'storeName' => ['sometimes', 'string', 'max:120'],
            'supportEmail' => ['sometimes', 'email', 'max:190'],
            'supportPhone' => ['sometimes', 'string', 'max:30'],
            'currency' => ['sometimes', 'string', 'max:5', Rule::in(['USD', 'EUR', 'KHR'])],
            'locale' => ['sometimes', 'string', 'max:5', Rule::in(['en', 'km'])],
            'lowStockThreshold' => ['sometimes', 'integer', 'min:0', 'max:999'],
            'emailOrderNotifications' => ['sometimes', 'boolean'],
            'emailLowStockAlerts' => ['sometimes', 'boolean'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $this->castStored($key, $value));
        }

        return $this->response($this->loadAll());
    }

    protected function loadAll(): array
    {
        $records = Setting::all()->keyBy('key');

        $result = [];
        foreach ($this->defaults as $key => $default) {
            $stored = $records->get($key)?->value;
            $result[$key] = $stored !== null ? $this->castFromStored($key, $stored) : $default;
        }

        return $result;
    }

    protected function castStored(string $key, mixed $value): mixed
    {
        return match ($this->casts[$key] ?? null) {
            'bool' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    protected function castFromStored(string $key, mixed $value): mixed
    {
        return match ($this->casts[$key] ?? null) {
            'bool' => (bool) $value,
            'int' => (int) $value,
            default => (string) $value,
        };
    }

    protected function response(array $settings)
    {
        return response()->json(['data' => $settings]);
    }
}
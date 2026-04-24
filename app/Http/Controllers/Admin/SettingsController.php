<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private array $carWashKeys = [
        'CAR_WASH_SPONSOR_NAME',
        'CAR_WASH_SPONSOR_LOGO_URL',
        'CAR_WASH_SPONSOR_LINK_URL',
        'CAR_WASH_OVERRIDE_COLOR',
    ];

    private array $quizKeys = [
        'QUIZ_SPONSOR_NAME',
        'QUIZ_SPONSOR_LOGO_URL',
        'QUIZ_SPONSOR_LINK_URL',
    ];

    private array $beachKeys = [
        'BEACH_SPONSOR_NAME',
        'BEACH_SPONSOR_LOGO_URL',
        'BEACH_SPONSOR_LINK_URL',
    ];

    public function carWash()
    {
        $values = collect($this->carWashKeys)->mapWithKeys(fn($k) => [$k => env($k, '')]);
        return view('admin.settings.car-wash', compact('values'));
    }

    public function updateCarWash(Request $request)
    {
        $request->validate([
            'CAR_WASH_SPONSOR_NAME'     => 'nullable|string|max:255',
            'CAR_WASH_SPONSOR_LOGO_URL' => 'nullable|url|max:500',
            'CAR_WASH_SPONSOR_LINK_URL' => 'nullable|url|max:500',
            'CAR_WASH_OVERRIDE_COLOR'   => 'nullable|in:,green,yellow,red',
        ]);

        $this->updateEnvKeys($this->carWashKeys, $request);

        return back()->with('success', 'Configuración del lavadero actualizada.');
    }

    public function quiz()
    {
        $values = collect($this->quizKeys)->mapWithKeys(fn($k) => [$k => env($k, '')]);
        return view('admin.settings.quiz', compact('values'));
    }

    public function updateQuiz(Request $request)
    {
        $request->validate([
            'QUIZ_SPONSOR_NAME'     => 'nullable|string|max:255',
            'QUIZ_SPONSOR_LOGO_URL' => 'nullable|url|max:500',
            'QUIZ_SPONSOR_LINK_URL' => 'nullable|url|max:500',
        ]);

        $this->updateEnvKeys($this->quizKeys, $request);

        return back()->with('success', 'Configuración del quiz actualizada.');
    }

    public function beaches()
    {
        $values = collect($this->beachKeys)->mapWithKeys(fn($k) => [$k => env($k, '')]);
        return view('admin.settings.beaches', compact('values'));
    }

    public function updateBeaches(Request $request)
    {
        $request->validate([
            'BEACH_SPONSOR_NAME'     => 'nullable|string|max:255',
            'BEACH_SPONSOR_LOGO_URL' => 'nullable|url|max:500',
            'BEACH_SPONSOR_LINK_URL' => 'nullable|url|max:500',
        ]);

        $this->updateEnvKeys($this->beachKeys, $request);

        return back()->with('success', 'Configuración de playas actualizada.');
    }

    private function updateEnvKeys(array $keys, Request $request): void
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($keys as $key) {
            $value = $request->input($key, '');
            $escaped = $this->escapeEnvValue($value);

            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$escaped}", $envContent);
            } else {
                $envContent .= "\n{$key}={$escaped}";
            }
        }

        file_put_contents($envPath, $envContent);

        // Clear config cache so new values take effect
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }

    private function escapeEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }
        // Wrap in quotes if contains spaces or special chars
        if (preg_match('/[\s#"\'\\\\]/', $value)) {
            $value = '"' . addslashes($value) . '"';
        }
        return $value;
    }
}

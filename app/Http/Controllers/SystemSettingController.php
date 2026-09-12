<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSystemSettingRequest;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SystemSetting::class);

        return view('settings.index', [
            'settings' => SystemSetting::getSettings(),
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function update(UpdateSystemSettingRequest $request): RedirectResponse
    {
        $settings = SystemSetting::getSettings();
        $this->authorize('update', $settings);
        $data = $request->safe()->only(['company_name', 'date_format', 'time_zone']);

        // A new upload replaces the stored file; remove_logo clears branding without a replacement.
        if ($request->boolean('remove_logo') && ! $request->hasFile('logo')) {
            $this->deleteStoredLogo($settings->logo);
            $data['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            $this->deleteStoredLogo($settings->logo);
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        }

        $settings->update($data);
        SystemSetting::forgetCache();

        return redirect()->route('system-settings.index')->with('success', 'Settings saved successfully.');
    }

    private function deleteStoredLogo(?string $path): void
    {
        if (filled($path) && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

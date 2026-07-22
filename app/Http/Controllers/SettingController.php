<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const DEFAULTS = [
        'app_name' => [
            'group' => 'general',
            'type' => 'string',
            'value' => 'UCC LabTech Borrowing Management System',
        ],
        'institution_name' => [
            'group' => 'general',
            'type' => 'string',
            'value' => 'University of Caloocan City',
        ],
        'support_email' => [
            'group' => 'general',
            'type' => 'string',
            'value' => '',
        ],
        'support_phone' => [
            'group' => 'general',
            'type' => 'string',
            'value' => '',
        ],
        'timezone' => [
            'group' => 'general',
            'type' => 'string',
            'value' => 'Asia/Manila',
        ],
        'date_format' => [
            'group' => 'general',
            'type' => 'string',
            'value' => 'M d, Y h:i A',
        ],
        'max_items_per_borrowing' => [
            'group' => 'borrowing',
            'type' => 'integer',
            'value' => 10,
        ],
        'max_borrow_days' => [
            'group' => 'borrowing',
            'type' => 'integer',
            'value' => 7,
        ],
        'default_borrow_time' => [
            'group' => 'borrowing',
            'type' => 'string',
            'value' => '08:00',
        ],
        'default_return_time' => [
            'group' => 'borrowing',
            'type' => 'string',
            'value' => '17:00',
        ],
        'allow_weekend_borrowing' => [
            'group' => 'borrowing',
            'type' => 'boolean',
            'value' => false,
        ],
        'auto_mark_overdue' => [
            'group' => 'borrowing',
            'type' => 'boolean',
            'value' => true,
        ],
        'email_notifications' => [
            'group' => 'notifications',
            'type' => 'boolean',
            'value' => true,
        ],
        'borrower_notifications' => [
            'group' => 'notifications',
            'type' => 'boolean',
            'value' => true,
        ],
        'maintenance_notifications' => [
            'group' => 'notifications',
            'type' => 'boolean',
            'value' => true,
        ],
    ];

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('manage settings'), 403);

        $this->ensureDefaultsExist();

        return view('settings.index', [
            'settings' => Setting::allAsArray(),
            'timezones' => [
                'Asia/Manila',
                'Asia/Singapore',
                'Asia/Tokyo',
                'UTC',
            ],
            'dateFormats' => [
                'M d, Y h:i A' => now()->format('M d, Y h:i A'),
                'F d, Y h:i A' => now()->format('F d, Y h:i A'),
                'Y-m-d H:i' => now()->format('Y-m-d H:i'),
                'm/d/Y h:i A' => now()->format('m/d/Y h:i A'),
            ],
            'systemInformation' => [
                'Laravel' => app()->version(),
                'PHP' => PHP_VERSION,
                'Environment' => app()->environment(),
                'Debug Mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('manage settings'), 403);

        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:150'],
            'institution_name' => ['required', 'string', 'max:150'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'timezone' => ['required', Rule::in(timezone_identifiers_list())],
            'date_format' => ['required', 'string', 'max:50'],
            'max_items_per_borrowing' => ['required', 'integer', 'min:1', 'max:100'],
            'max_borrow_days' => ['required', 'integer', 'min:1', 'max:365'],
            'default_borrow_time' => ['required', 'date_format:H:i'],
            'default_return_time' => ['required', 'date_format:H:i'],
            'allow_weekend_borrowing' => ['nullable', 'boolean'],
            'auto_mark_overdue' => ['nullable', 'boolean'],
            'email_notifications' => ['nullable', 'boolean'],
            'borrower_notifications' => ['nullable', 'boolean'],
            'maintenance_notifications' => ['nullable', 'boolean'],
        ]);

        $booleanKeys = [
            'allow_weekend_borrowing',
            'auto_mark_overdue',
            'email_notifications',
            'borrower_notifications',
            'maintenance_notifications',
        ];

        DB::transaction(function () use ($request, $validated, $booleanKeys) {
            foreach (self::DEFAULTS as $key => $definition) {
                $value = in_array($key, $booleanKeys, true)
                    ? $request->boolean($key)
                    : ($validated[$key] ?? null);

                Setting::setValue(
                    $key,
                    $value,
                    $definition['group'],
                    $definition['type']
                );
            }
        });

        return back()->with('success', 'System settings updated successfully.');
    }

    private function ensureDefaultsExist(): void
    {
        foreach (self::DEFAULTS as $key => $definition) {
            Setting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'group' => $definition['group'],
                    'type' => $definition['type'],
                    'value' => Setting::serializeValue(
                        $definition['value'],
                        $definition['type']
                    ),
                ]
            );
        }

        Setting::flushSettingsCache();
    }
}

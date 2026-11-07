<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ImpersonationManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __invoke(ImpersonationManager $impersonation): View
    {
        $cards = collect(config('admin_settings.cards', []))
            ->filter(fn (array $card): bool => $impersonation->active()
                ? (bool) $impersonation->effectiveUser()?->hasPermissionTo($card['permission'])
                : Gate::allows($card['permission']))
            ->values();

        abort_if($cards->isEmpty(), 403);

        return view('admin.settings.index', compact('cards'));
    }
}

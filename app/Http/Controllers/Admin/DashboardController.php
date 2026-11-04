<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditEvent;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'inactiveUsers' => User::query()->where('is_active', false)->count(),
            'activeRoles' => Role::query()->where('is_active', true)->count(),
            'recentEvents' => AdminAuditEvent::query()->with('actor')->latest('created_at')->limit(8)->get(),
        ]);
    }
}

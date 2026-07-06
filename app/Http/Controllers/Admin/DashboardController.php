<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;

class DashboardController extends Controller
{
    public function __invoke(AdminDashboardService $dashboard)
    {
        return view('admin.dashboard.index', ['dashboard' => $dashboard->get()]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepository;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private DashboardRepository $dashboard) {}

    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'stats'          => $this->dashboard->stats(),
            'topProducts'    => $this->dashboard->topProducts(),
            'recentActivity' => $this->dashboard->recentActivity(),
            'settings'       => $this->dashboard->settings(),
        ]);
    }
}

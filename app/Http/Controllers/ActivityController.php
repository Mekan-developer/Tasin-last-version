<?php

namespace App\Http\Controllers;

use App\Repositories\ActivityRepository;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller
{
    public function __construct(private ActivityRepository $repo) {}

    public function index(): Response
    {
        $filters = request()->only(['search', 'region', 'device', 'period']);
        return Inertia::render('Activity/Index', [
            'logs'    => $this->repo->paginate($filters),
            'stats'   => $this->repo->stats(),
            'filters' => $filters,
        ]);
    }
}

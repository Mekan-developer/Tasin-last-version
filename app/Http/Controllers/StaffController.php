<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffPayRequest;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Requests\UpdateStaffRequest;
use App\Models\Staff;
use App\Repositories\StaffRepository;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function __construct(private StaffRepository $repo) {}

    public function index(): Response
    {
        $filters = request()->only(['search', 'department', 'is_active']);
        return Inertia::render('Staff/Index', [
            'staff'   => $this->repo->paginate($filters),
            'stats'   => $this->repo->stats(),
            'filters' => $filters,
        ]);
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $this->repo->create($request->validated());
        return back()->with('success', 'Сотрудник добавлен.');
    }

    public function update(UpdateStaffRequest $request, Staff $staff): RedirectResponse
    {
        $this->repo->update($staff, $request->validated());
        return back()->with('success', 'Сотрудник обновлён.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $this->repo->delete($staff);
        return back()->with('success', 'Сотрудник удалён.');
    }

    public function pay(StaffPayRequest $request, Staff $staff): RedirectResponse
    {
        $this->repo->pay($staff, (float) $request->amount, $request->type);
        return back()->with('success', 'Операция выполнена.');
    }
}

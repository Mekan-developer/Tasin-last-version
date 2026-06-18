<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSlideRequest;
use App\Http\Requests\UpdateSlideRequest;
use App\Models\Slide;
use App\Repositories\SlideRepository;
use App\Traits\ConvertsToWebp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SlideController extends Controller
{
    use ConvertsToWebp;

    public function __construct(private SlideRepository $repo) {}

    public function index(): Response
    {
        return Inertia::render('Slides/Index', [
            'slides' => $this->repo->all(),
        ]);
    }

    public function store(StoreSlideRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        if ($request->hasFile('image')) {
            $data['image'] = $this->storeAsWebp($request->file('image'), 'slides');
        }
        $this->repo->create($data);
        return back()->with('success', 'Слайд добавлен.');
    }

    public function update(UpdateSlideRequest $request, Slide $slide): RedirectResponse
    {
        $data = $request->safe()->except('image');
        if ($request->hasFile('image')) {
            if ($slide->image) {
                Storage::disk('public')->delete($slide->image);
            }
            $data['image'] = $this->storeAsWebp($request->file('image'), 'slides');
        }
        $this->repo->update($slide, $data);
        return back()->with('success', 'Слайд обновлён.');
    }

    public function destroy(Slide $slide): RedirectResponse
    {
        if ($slide->image) {
            Storage::disk('public')->delete($slide->image);
        }
        $this->repo->delete($slide);
        return back()->with('success', 'Слайд удалён.');
    }
}

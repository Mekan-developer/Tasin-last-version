<?php

namespace App\Repositories;

use App\Models\Slide;
use Illuminate\Database\Eloquent\Collection;

class SlideRepository
{
    public function all(): Collection
    {
        return Slide::orderBy('order')->get();
    }

    public function create(array $data): Slide
    {
        return Slide::create($data);
    }

    public function update(Slide $slide, array $data): void
    {
        $slide->update($data);
    }

    public function delete(Slide $slide): void
    {
        $slide->delete();
    }
}

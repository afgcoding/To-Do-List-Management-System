<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    // Tags with linked task counts.
    public function index(): View
    {
        $this->authorize('viewAny', Tag::class);
        $tags = Tag::query()
            ->withCount('tasks')
            ->latest()
            ->paginate(12);

        return view('tags.index', compact('tags'));
    }

    public function create(): RedirectResponse
    {
        $this->authorize('create', Tag::class);

        return redirect()->route('tags.index');
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $this->authorize('create', Tag::class);
        Tag::query()->create($request->validated());

        return redirect()->route('tags.index')->with('success', 'Tag created successfully!');
    }

    public function show(Tag $tag): RedirectResponse
    {
        $this->authorize('view', $tag);

        return redirect()->route('tags.index', ['edit' => $tag->id]);
    }

    public function edit(Tag $tag): RedirectResponse
    {
        $this->authorize('update', $tag);

        return redirect()->route('tags.index', ['edit' => $tag->id]);
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $this->authorize('update', $tag);
        $tag->update($request->validated());

        return redirect()->route('tags.index')->with('success', 'Tag updated successfully!');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $this->authorize('delete', $tag);
        $tag->delete();

        return redirect()->route('tags.index')->with('success', 'Tag deleted successfully!');
    }
}

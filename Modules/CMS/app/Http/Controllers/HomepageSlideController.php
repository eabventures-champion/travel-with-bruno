<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CMS\Models\HomepageSlide;
use Illuminate\Support\Facades\Storage;

class HomepageSlideController extends Controller
{
    public function index()
    {
        $slides = HomepageSlide::orderBy('order')->get();
        return view('cms::slides.index', compact('slides'));
    }

    public function create()
    {
        return view('cms::slides.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'order' => 'integer',
        ]);

        $data = $request->except('image');
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('homepage/slides', 'public');
            $data['image_path'] = $path;
        }

        HomepageSlide::create($data);

        return redirect()->route('admin.slides.index')->with('success', 'Slide created successfully.');
    }

    public function edit(HomepageSlide $slide)
    {
        return view('cms::slides.edit', compact('slide'));
    }

    public function update(Request $request, HomepageSlide $slide)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'order' => 'integer',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            // Delete old image
            if ($slide->image_path) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $path = $request->file('image')->store('homepage/slides', 'public');
            $data['image_path'] = $path;
        }

        $slide->update($data);

        return redirect()->route('admin.slides.index')->with('success', 'Slide updated successfully.');
    }

    public function destroy(HomepageSlide $slide)
    {
        if ($slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
        }
        $slide->delete();

        return redirect()->route('admin.slides.index')->with('success', 'Slide deleted successfully.');
    }

    public function toggleStatus(HomepageSlide $slide)
    {
        $slide->update(['is_active' => !$slide->is_active]);
        return back()->with('success', 'Status updated.');
    }
}

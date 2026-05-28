<?php

namespace Modules\Tourism\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Tourism\Models\TourismCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = TourismCategory::withCount('packages')->latest()->paginate(10);
        return view('tourism::categories.index', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:tourism_categories,slug',
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        TourismCategory::create($validated);

        return redirect()->route('admin.tourism.categories.index')->with('success', 'Category created successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = TourismCategory::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:tourism_categories,slug,' . $id,
            'description' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $category->update($validated);

        return redirect()->route('admin.tourism.categories.index')->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = TourismCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.tourism.categories.index')->with('success', 'Category deleted successfully');
    }
}

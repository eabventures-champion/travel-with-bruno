<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserTypeController extends Controller
{
    public function index()
    {
        $userTypes = UserType::latest()->get();
        return view('admin::user-types.index', compact('userTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Ensure slug is unique
        $count = UserType::where('slug', $validated['slug'])->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . ($count + 1);
        }

        UserType::create($validated);

        return redirect()->route('admin.user-types.index')->with('success', 'User type created successfully.');
    }

    public function update(Request $request, $id)
    {
        $userType = UserType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $count = UserType::where('slug', $validated['slug'])->where('id', '!=', $id)->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . ($count + 1);
        }

        $userType->update($validated);

        return redirect()->route('admin.user-types.index')->with('success', 'User type updated successfully.');
    }

    public function toggleStatus($id)
    {
        $userType = UserType::findOrFail($id);
        $userType->update(['is_active' => !$userType->is_active]);

        return redirect()->route('admin.user-types.index')->with('success', 'Status updated.');
    }

    public function destroy($id)
    {
        $userType = UserType::findOrFail($id);
        $userType->delete();

        return redirect()->route('admin.user-types.index')->with('success', 'User type deleted.');
    }
}

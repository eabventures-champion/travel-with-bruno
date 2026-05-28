<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['roles', 'profile'])->latest()->get(); // Get all for live filtering or keep paginate if we do server side. User said "Live filter", client side is smoother for moderate lists.
        $roles = \Spatie\Permission\Models\Role::all();
        $userTypes = UserType::all();
        return view('admin::users.index', compact('users', 'roles', 'userTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $userTypes = UserType::where('is_active', true)->get();
        return view('admin::users.create', compact('userTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|string|in:admin,staff,agent,driver,vendor,customer',
            'status' => 'required|string|in:active,inactive,suspended',
            'address' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'id_document' => 'nullable|string|max:255',
            'travel_preferences' => 'nullable|string',
            'bio' => 'nullable|string',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return redirect()->route('admin.users.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $userTypes = UserType::where('is_active', true)->get();
        return view('admin::users.edit', compact('user', 'userTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $id,
            'user_type' => 'required|string|in:admin,staff,agent,driver,vendor,customer',
            'status' => 'required|string|in:active,inactive,suspended',
            'password' => 'nullable|string|min:8|confirmed',
            'address' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'id_document' => 'nullable|string|max:255',
            'travel_preferences' => 'nullable|string',
            'bio' => 'nullable|string',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting Super Admin
        if ($user->hasRole('Super Admin')) {
            return redirect()->route('admin.users.index')->with('error', 'Cannot delete the Super Admin account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully');
    }
    public function checkUniqueness(Request $request)
    {
        $email = $request->email;
        $phone = $request->phone;
        $userId = $request->user_id;

        $exists = false;
        $message = "";

        if ($email) {
            $query = User::where('email', $email);
            if ($userId) {
                $query->where('id', '!=', $userId);
            }
            if ($query->exists()) {
                $exists = true;
                $message = "This email is already registered to another account.";
            }
        }

        if (!$exists && $phone) {
            $query = User::where('phone', $phone);
            if ($userId) {
                $query->where('id', '!=', $userId);
            }
            if ($query->exists()) {
                $exists = true;
                $message = "This phone number is already registered to another account.";
            }
        }

        return response()->json([
            'exists' => $exists,
            'message' => $message
        ]);
    }
}

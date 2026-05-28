<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'bio' => 'sometimes|string',
            'address' => 'sometimes|string',
            'emergency_contact' => 'sometimes|string',
            'nationality' => 'sometimes|string',
            'date_of_birth' => 'sometimes|date',
            'travel_preferences' => 'sometimes|array',
        ]);

        $userData = $request->only(['name', 'phone', 'bio', 'address', 'emergency_contact', 'nationality', 'travel_preferences']);
        if ($request->has('date_of_birth')) {
            $userData['dob'] = $request->date_of_birth;
        }
        
        $user->update($userData);
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            $user->clearMediaCollection('avatars');
            $user->addMediaFromRequest('avatar')
                ->toMediaCollection('avatars');
            
            $user->update([
                'avatar_url' => $user->getFirstMediaUrl('avatars')
            ]);
        }

        return response()->json([
            'message' => 'Avatar uploaded successfully',
            'avatar_url' => $user->avatar_url
        ]);
    }
}

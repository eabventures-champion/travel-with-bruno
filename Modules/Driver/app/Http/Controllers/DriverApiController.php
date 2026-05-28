<?php

namespace Modules\Driver\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DriverApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('driver::index');
    }

    public function toggleStatus(Request $request)
    {
        $user = auth()->user();
        
        if ($user && $user->chauffeurProfile) {
            $user->chauffeurProfile->update([
                'is_online' => $request->is_online
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => 'Status updated successfully',
                'is_online' => $user->chauffeurProfile->is_online
            ]);
        }
        
        return response()->json(['success' => false, 'message' => 'Not a chauffeur'], 403);
    }
}

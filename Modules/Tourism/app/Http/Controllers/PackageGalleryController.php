<?php

namespace Modules\Tourism\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Tourism\Models\TourismPackage;
use Modules\Tourism\Models\TourismPackageImage;
use Illuminate\Support\Facades\Storage;

class PackageGalleryController extends Controller
{
    public function index($packageId)
    {
        $images = TourismPackageImage::where('package_id', $packageId)
            ->with('user')
            ->latest()
            ->get()
            ->map(function($img) {
                return [
                    'id' => $img->id,
                    'url' => asset('storage/' . $img->image_path),
                    'user_name' => $img->user->name ?? 'Guest',
                    'caption' => $img->caption,
                    'created_at' => $img->created_at->diffForHumans(),
                ];
            });

        return response()->json($images);
    }

    public function store(Request $request, $packageId)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            'caption' => 'nullable|string|max:255',
        ]);

        $package = TourismPackage::findOrFail($packageId);
        $user = auth()->user();

        // Check if user is Super Admin
        $isAdmin = $user && $user->hasRole('Super Admin');

        if (!$isAdmin) {
            return response()->json(['message' => 'Unauthorized. Only the Super Admin can upload images for tour packages.'], 403);
        }

        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('tour-gallery/' . $packageId, 'public');

                $image = TourismPackageImage::create([
                    'package_id' => $packageId,
                    'user_id' => $user->id,
                    'image_path' => $path,
                    'caption' => $request->caption,
                ]);

                $uploadedImages[] = [
                    'id' => $image->id,
                    'url' => asset('storage/' . $path),
                    'user_name' => $user->name,
                    'caption' => $image->caption,
                    'created_at' => $image->created_at->diffForHumans(),
                ];
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($uploadedImages);
        }

        return back()->with('success', count($uploadedImages) . ' images uploaded successfully.');
    }

    public function publicGallery()
    {
        $packages = TourismPackage::where('status', 'active')
            ->has('uploads')
            ->with(['uploads' => function($q) {
                $q->latest();
            }, 'uploads.user'])
            ->get();

        $transferZones = \App\Models\TransferZone::where('is_active', true)->orderBy('name')->get();
        $guestTypes = \Modules\Tourism\Models\TourismGuestType::where('status', 'active')->get();

        return view('tourism::gallery', compact('packages', 'transferZones', 'guestTypes'));
    }

    public function adminGallery($packageId)
    {
        $package = TourismPackage::with('uploads.user')->findOrFail($packageId);
        return view('tourism::packages.gallery', compact('package'));
    }

    public function destroy($packageId, $imageId)
    {
        $user = auth()->user();
        if (!$user->hasRole('Super Admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $image = TourismPackageImage::where('package_id', $packageId)->findOrFail($imageId);
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Image deleted successfully']);
        }

        return back()->with('success', 'Image deleted successfully');
    }
}

<?php

namespace Modules\Booking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingDocument;
use Illuminate\Support\Facades\Storage;

class BookingDocumentController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document' => 'required|file|max:10240', // 10MB limit
            'shared_with' => 'required|in:customer,driver,both',
        ]);

        $file = $request->file('document');
        $path = $file->store('booking_documents', 'public');

        $booking->documents()->create([
            'title' => $request->title,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'shared_with' => $request->shared_with,
        ]);

        // Notify relevant parties
        if ($request->shared_with === 'customer' || $request->shared_with === 'both') {
            if ($booking->user) {
                $booking->user->notify(new \App\Notifications\BookingDocumentShared($booking, $request->title));
            }
        }

        if ($request->shared_with === 'driver' || $request->shared_with === 'both') {
            if ($booking->chauffeur && $booking->chauffeur->user) {
                $booking->chauffeur->user->notify(new \App\Notifications\BookingDocumentShared($booking, $request->title));
            }
        }

        return back()->with('success', 'Document shared successfully.');
    }

    public function download(BookingDocument $document)
    {
        // Check permissions
        $user = auth()->user();
        $booking = $document->booking;

        $canDownload = false;

        if ($user->hasAnyRole(['Super Admin', 'Operations Admin'])) {
            $canDownload = true;
        } elseif ($user->id === $booking->user_id && ($document->shared_with === 'customer' || $document->shared_with === 'both')) {
            $canDownload = true;
        } elseif ($booking->chauffeur && $user->id === $booking->chauffeur->user_id && ($document->shared_with === 'driver' || $document->shared_with === 'both')) {
            $canDownload = true;
        }

        if (!$canDownload) {
            abort(403, 'Unauthorized access to this document.');
        }

        return Storage::disk('public')->download($document->file_path, $document->title . '.' . $document->file_type);
    }

    public function destroy(BookingDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}

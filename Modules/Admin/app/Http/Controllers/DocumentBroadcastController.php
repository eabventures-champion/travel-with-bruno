<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BroadcastDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentBroadcastController extends Controller
{
    public function index()
    {
        $documents = BroadcastDocument::with('creator')->latest()->get();
        $customers = User::role('Customer')->get();
        $chauffeurs = User::role('Driver')->get();
        
        return view('admin::documents.index', compact('documents', 'customers', 'chauffeurs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document' => 'required|file|max:10000', // 10MB
            'target_audience' => 'required|in:all,customers,drivers,selected',
            'user_ids' => 'required_if:target_audience,selected|array',
        ]);

        $file = $request->file('document');
        $path = $file->store('broadcasts', 'public');

        $document = BroadcastDocument::create([
            'title' => $request->title,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'target_audience' => $request->target_audience,
            'created_by' => auth()->id()
        ]);

        if ($request->target_audience === 'selected') {
            $document->users()->attach($request->user_ids);
        }

        return back()->with('success', 'Document broadcasted successfully!');
    }

    public function destroy(BroadcastDocument $document)
    {
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();

        return back()->with('success', 'Broadcasted document removed.');
    }

    public function download(BroadcastDocument $document)
    {
        // Simple permission check could be added here if needed
        return Storage::disk('public')->download($document->file_path, $document->title . '.' . $document->file_type);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reflection;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReflectionController extends Controller
{
    /**
     * Display a listing of the user's reflections.
     */
    public function index()
    {
        $reflections = Reflection::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reflections);
    }

    /**
     * Store a newly created reflection.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'date'         => 'required|date',
            'mood'         => 'required|string',
            'achievements' => 'nullable|string',
            'difficulties' => 'nullable|string',
            'learnings'    => 'nullable|string',
            'improvements' => 'nullable|string',
            'gratitude'    => 'nullable|string',
            'tags'         => 'nullable|array',
        ]);

        $reflection = Reflection::create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));

        // إضافة إشعار
        Notification::create([
            'user_id' => Auth::id(),
            'title' => 'New Reflection Saved',
            'message' => "Successfully recorded your thoughts for {$reflection->date}.",
            'type' => 'success',
            'target_route' => '/reflections'
        ]);

        return response()->json([
            'message'    => 'Reflection created successfully',
            'reflection' => $reflection
        ], 201);
    }

    /**
     * Display the specified reflection.
     */
    public function show($id)
    {
        $reflection = Reflection::where('user_id', Auth::id())->findOrFail($id);
        return response()->json($reflection);
    }

    /**
     * Update the specified reflection.
     */
    public function update(Request $request, $id)
    {
        $reflection = Reflection::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title'        => 'sometimes|required|string|max:255',
            'date'         => 'sometimes|required|date',
            'mood'         => 'sometimes|required|string',
            'achievements' => 'nullable|string',
            'difficulties' => 'nullable|string',
            'learnings'    => 'nullable|string',
            'improvements' => 'nullable|string',
            'gratitude'    => 'nullable|string',
            'tags'         => 'nullable|array',
        ]);

        $reflection->update($validated);

        // إضافة إشعار
        Notification::create([
            'user_id' => Auth::id(),
            'title' => 'Reflection Updated',
            'message' => "Your changes to the reflection on {$reflection->date} have been saved.",
            'type' => 'info',
            'target_route' => '/reflections'
        ]);

        return response()->json([
            'message'    => 'Reflection updated successfully',
            'reflection' => $reflection
        ]);
    }

    /**
     * Remove the specified reflection from storage.
     */
    public function destroy($id)
    {
        $reflection = Reflection::where('user_id', Auth::id())->findOrFail($id);
        $date = $reflection->date;
        $reflection->delete();

        // إضافة إشعار عند الحذف
        Notification::create([
            'user_id' => Auth::id(),
            'title' => 'Reflection Deleted',
            'message' => "Reflection for {$date} was removed.",
            'type' => 'warning'
        ]);

        return response()->json([
            'message' => 'Reflection deleted successfully'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\Course;
use App\Models\Notification;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    // 1. جلب كل مصادر كورس معين
    public function index(Request $request)
    {
        $request->validate([
            'resourceable_id'   => 'required',
            'resourceable_type' => 'required|string',
        ]);

        $resources = Resource::where('resourceable_id', $request->resourceable_id)
                             ->where('resourceable_type', $request->resourceable_type)
                             ->latest()->get();
        return response()->json($resources);
    }

    // 2. إضافة مصدر جديد
    public function store(Request $request)
    {
        try {
            $request->validate([
                'resourceable_id'   => 'required',
                'resourceable_type' => 'required|string',
                'title'       => 'required|string',
                'type'        => 'required|string',
                'url'         => 'required|string',
                'description' => 'nullable|string',
            ]);

            $resource = new Resource();
            $resource->resourceable_id   = $request->resourceable_id;
            $resource->resourceable_type = $request->resourceable_type;
            $resource->title       = $request->title;
            $resource->type        = $request->type;
            $resource->url         = $request->url;
            $resource->description = $request->description;
            
            $resource->save();

            // إضافة إشعار
            Notification::create([
                'user_id' => auth()->id(),
                'title' => 'New Resource Added',
                'message' => "Added material: {$resource->title}",
                'type' => 'success',
                'target_route' => $resource->resourceable_type === 'App\Models\Course' ? "/courses/{$resource->resourceable_id}" : null
            ]);

            return response()->json($resource, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. تحديث المصدر
    public function update(Request $request, $id)
    {
        $resource = Resource::findOrFail($id);

        if ($request->has('title')) {
            $resource->title = $request->title;
        }
        if ($request->has('type')) {
            $resource->type = $request->type;
        }
        if ($request->has('url')) {
            $resource->url = $request->url;
        }
        if ($request->has('description')) {
            $resource->description = $request->description;
        }

        $resource->save();

        // إضافة إشعار
        Notification::create([
            'user_id' => auth()->id(),
            'title' => 'Resource Updated',
            'message' => "Successfully updated: {$resource->title}",
            'type' => 'info'
        ]);

        return response()->json($resource);
    }

    // 4. حذف مصدر
    public function destroy($id)
    {
        $resource = Resource::findOrFail($id);
        $resourceName = $resource->title;
        $resource->delete();

        // إضافة إشعار عند الحذف
        Notification::create([
            'user_id' => auth()->id(),
            'title' => 'Resource Deleted',
            'message' => "Successfully removed material: {$resourceName}",
            'type' => 'warning'
        ]);

        return response()->json(['message' => 'Resource deleted']);
    }
}

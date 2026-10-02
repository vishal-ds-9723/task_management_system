<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Store a newly created media file
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,avi,mov,webm|max:512000', // 500MB max
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'collection_name' => 'nullable|string|default:default',
        ]);

        try {
            $file = $request->file('file');
            $collection = $request->input('collection_name', 'task-media');

            // Generate unique filename
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = "public/{$collection}";

            // Store file
            $storedPath = $file->storeAs(
                $path,
                $filename,
                'local'
            );

            // Determine file type
            $mimeType = $file->getMimeType();
            $type = $this->determineType($mimeType);

            // Create media record
            $media = Media::create([
                'model_type' => $validated['model_type'],
                'model_id' => $validated['model_id'],
                'collection_name' => $collection,
                'name' => $file->getClientOriginalName(),
                'file_name' => $filename,
                'mime_type' => $mimeType,
                'disk' => 'local',
                'path' => "{$collection}/{$filename}",
                'size' => $file->getSize(),
                'type' => $type,
                'metadata' => [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'media' => $media,
                'url' => $media->getUrl(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Destroy a media file
     */
    public function destroy(Media $media)
    {
        try {
            // Check authorization
            $model = $media->model;
            if ($model && $model::class === 'App\Models\Task') {
                if ($model->created_by !== auth()->id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized to delete this media',
                    ], 403);
                }
            }

            // Delete file from storage
            if (Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
            }

            // Delete media record
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete media: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get media for a model
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'collection_name' => 'nullable|string',
        ]);

        try {
            $query = Media::where('model_type', $validated['model_type'])
                ->where('model_id', $validated['model_id']);

            if ($request->has('collection_name')) {
                $query->where('collection_name', $validated['collection_name']);
            }

            $media = $query->orderBy('order_column')->get();

            return response()->json([
                'success' => true,
                'data' => $media,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve media: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Determine file type based on MIME type
     */
    private function determineType($mimeType)
    {
        if (strpos($mimeType, 'image') !== false) {
            return 'image';
        } elseif (strpos($mimeType, 'video') !== false) {
            return 'video';
        } else {
            return 'document';
        }
    }
}

<?php

namespace App\Support\Concerns;

use App\Models\Media;
use App\Models\Task;
use Illuminate\Support\Facades\Storage;

/**
 * Shared task-media file handling for Strategist/Designer/Developer task
 * controllers & services — the storage/Media-record logic was previously
 * copy-pasted in all three with minor drift.
 */
trait StoresTaskMedia
{
    /**
     * Store uploaded files against a task's media collection.
     *
     * @param  \Illuminate\Http\UploadedFile[]  $files
     * @return array{0: int, 1: string[]} [uploadedCount, failedFileNames]
     */
    protected function storeUploadedTaskMedia(Task $task, array $files, string $collection = 'task-media', array $extraMetadata = []): array
    {
        $uploadedCount = 0;
        $failedFiles = [];
        $user = \Illuminate\Support\Facades\Auth::user();

        foreach ($files as $file) {
            try {
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
                $filename = time() . '_' . uniqid() . '_' . $safeName;

                if (!$file->storeAs($collection, $filename, 'public')) {
                    $failedFiles[] = $file->getClientOriginalName();
                    continue;
                }

                $mimeType = $file->getMimeType() ?: 'application/octet-stream';
                $ext = strtolower($file->getClientOriginalExtension());
                $isVideo = str_contains($mimeType, 'video') || in_array($ext, ['mp4', 'mov', 'webm', 'mkv', 'avi', 'm4v', '3gp', 'ts', 'ogv', 'wmv', 'flv']);
                $isImage = str_contains($mimeType, 'image') || in_array($ext, ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'svg', 'bmp']);
                $type = $isVideo ? 'video' : ($isImage ? 'image' : ($ext === 'pdf' ? 'pdf' : 'document'));

                $metadata = array_merge([
                    'uploaded_by_id'   => $user?->id,
                    'uploaded_by_name' => $user?->name,
                    'uploaded_by_role' => $user?->role,
                    'revision_count'   => (int) ($task->revision_count ?? 0),
                    'is_reference'     => ($collection === 'task-reference'),
                ], $extraMetadata);

                Media::create([
                    'model_type'      => Task::class,
                    'model_id'        => $task->id,
                    'collection_name' => $collection,
                    'name'            => $file->getClientOriginalName(),
                    'file_name'       => $filename,
                    'mime_type'       => $mimeType,
                    'disk'            => 'public',
                    'path'            => "{$collection}/{$filename}",
                    'size'            => $file->getSize(),
                    'type'            => $type,
                    'metadata'        => $metadata,
                ]);

                $uploadedCount++;
            } catch (\Exception $e) {
                $failedFiles[] = $file->getClientOriginalName();
            }
        }

        return [$uploadedCount, $failedFiles];
    }

    /**
     * Delete a media record (and its file) if it belongs to the given task.
     *
     * @return bool  true if a matching record was found and removed
     */
    protected function deleteTaskMediaRecord(Task $task, int $mediaId): bool
    {
        $media = Media::find($mediaId);

        if (!$media || $media->model_type !== Task::class || $media->model_id !== $task->id) {
            return false;
        }

        if (Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        // Clean up pre-existing mirrored copies from the old Hostinger symlink
        // workaround; new uploads no longer create them (see MediaController::stream).
        $mirrored = storage_path($media->path);
        if (file_exists($mirrored)) {
            unlink($mirrored);
        }

        $media->delete();

        return true;
    }
}

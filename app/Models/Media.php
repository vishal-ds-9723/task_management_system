<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'path',
        'size',
        'type',
        'metadata',
        'order_column',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    protected $appends = ['url', 'thumb_url'];

    /**
     * Get the model this media belongs to
     */
    public function model()
    {
        return $this->morphTo();
    }

    public function getUrlAttribute()
    {
        return $this->getUrl();
    }

    public function getThumbUrlAttribute()
    {
        return $this->getThumbUrl();
    }

    /**
     * Get the full URL to this media
     */
    public function getUrl($conversion = null)
    {
        if ($this->id) {
            if (request()) {
                return request()->root() . '/api/v1/media/' . $this->id . '/view';
            }
            return url('/api/v1/media/' . $this->id . '/view');
        }
        $cleanPath = str_replace('\\', '/', $this->path);
        if (request()) {
            return request()->root() . '/storage/' . ltrim($cleanPath, '/');
        }
        return asset('storage/' . ltrim($cleanPath, '/'));
    }

    /**
     * Get the thumbnail URL
     */
    public function getThumbUrl()
    {
        return $this->getUrl();
    }

    /**
     * Get file extension in lowercase
     */
    public function getExtension()
    {
        $name = $this->file_name ?: $this->name ?: $this->path ?: '';
        return strtolower(pathinfo($name, PATHINFO_EXTENSION));
    }

    /**
     * Check if media is an image
     */
    public function isImage()
    {
        $ext = $this->getExtension();
        $mime = strtolower((string) ($this->mime_type ?? ''));
        $type = strtolower((string) ($this->type ?? ''));
        return in_array($type, ['image', 'jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'svg', 'bmp'])
            || in_array($ext, ['jpg', 'jpeg', 'jfif', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'avif'])
            || str_starts_with($mime, 'image/');
    }

    /**
     * Check if media is a video (including videos uploaded as documents/files)
     */
    public function isVideo()
    {
        $ext = $this->getExtension();
        $mime = strtolower((string) ($this->mime_type ?? ''));
        $type = strtolower((string) ($this->type ?? ''));
        return in_array($type, ['video', 'mp4', 'webm', 'mov', 'mkv', 'avi', 'm4v', '3gp', 'ts', 'ogv', 'wmv', 'flv'])
            || in_array($ext, ['mp4', 'webm', 'mov', 'mkv', 'avi', 'm4v', '3gp', 'ts', 'ogv', 'wmv', 'flv', 'quicktime'])
            || str_starts_with($mime, 'video/')
            || in_array($mime, ['application/mp4', 'application/x-matroska', 'video/quicktime', 'video/mp4', 'video/webm', 'video/x-msvideo']);
    }

    /**
     * Check if media is a PDF document
     */
    public function isPdf()
    {
        $ext = $this->getExtension();
        $mime = strtolower((string) ($this->mime_type ?? ''));
        $type = strtolower((string) ($this->type ?? ''));
        return $type === 'pdf' || $ext === 'pdf' || $mime === 'application/pdf';
    }

    /**
     * Get icon based on media type
     */
    public function getIcon()
    {
        if ($this->isImage()) return '🖼️';
        if ($this->isVideo()) return '🎥';
        if ($this->isPdf()) return '📄';
        return '📎';
    }

    /**
     * Formatted human-readable file size
     */
    public function getFormattedSize()
    {
        $bytes = (int) $this->size;
        if ($bytes <= 0) return '';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / pow(1024, $power), 1) . ' ' . ($units[$power] ?? 'B');
    }
}

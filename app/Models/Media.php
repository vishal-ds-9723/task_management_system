<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    public const COLLECTION_DELIVERABLE = 'task-media';
    public const COLLECTION_REFERENCE   = 'task-reference';

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

    protected function casts(): array
    {
        return [
            'metadata' => 'json',
        ];
    }

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
        $cleanPath = str_replace('\\', '/', $this->path);

        return route('media.stream', ['path' => ltrim($cleanPath, '/')]);
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

    /**
     * Determine if this media is a reference/brief asset received from client or strategist
     */
    public function isReference(): bool
    {
        if (in_array($this->collection_name, ['task-reference', 'reference-media', 'brief-assets'])) {
            return true;
        }

        $meta = is_array($this->metadata) ? $this->metadata : [];
        if (!empty($meta['is_reference'])) {
            return true;
        }

        if (($meta['uploaded_by_role'] ?? null) === 'strategist' && ($meta['type'] ?? null) === 'reference') {
            return true;
        }

        return false;
    }

    /**
     * Determine if this media is a designer's deliverable/submission
     */
    public function isDeliverable(): bool
    {
        return !$this->isReference();
    }

    public function getUploaderName(): ?string
    {
        $meta = is_array($this->metadata) ? $this->metadata : [];
        return $meta['uploaded_by_name'] ?? null;
    }

    public function getUploaderRole(): ?string
    {
        $meta = is_array($this->metadata) ? $this->metadata : [];
        return $meta['uploaded_by_role'] ?? null;
    }

    public function getRevisionCount(): int
    {
        $meta = is_array($this->metadata) ? $this->metadata : [];
        return (int) ($meta['revision_count'] ?? 0);
    }

    public function getVersionBadgeLabel(): string
    {
        if ($this->isReference()) {
            $rev = $this->getRevisionCount();
            return $rev > 0 ? "Rev #{$rev} Reference" : "Reference Asset";
        }

        $rev = $this->getRevisionCount();
        if ($rev > 0) {
            return "Revision #{$rev} (v" . ($rev + 1) . ")";
        }

        return "Initial (v1)";
    }
}


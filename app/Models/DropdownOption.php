<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DropdownOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'label',
        'value',
        'group_name',
        'icon',
        'color',
        'order_index',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'order_index' => 'integer',
            'is_active'   => 'boolean',
        ];
    }

    /**
     * Scope for category.
     */
    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for active records.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Get active options for a category.
     */
    public static function getOptions(string $category): Collection
    {
        return static::forCategory($category)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get active options grouped by group_name.
     */
    public static function getGrouped(string $category): Collection
    {
        return static::forCategory($category)
            ->active()
            ->ordered()
            ->get()
            ->groupBy(fn ($item) => $item->group_name ?: 'General');
    }

    /**
     * Get array of values for validation or dropdown matching.
     */
    public static function getValues(string $category, array $fallback = []): array
    {
        $dbValues = static::forCategory($category)
            ->active()
            ->pluck('value')
            ->toArray();

        return array_values(array_unique(array_merge($dbValues, $fallback)));
    }

    /**
     * Check if a type is a social media type.
     */
    public static function isSocialType(?string $type): bool
    {
        if (empty($type)) return false;
        
        $opt = static::forCategory('task_type')->where('value', $type)->first();
        if ($opt && $opt->group_name) {
            return strtolower($opt->group_name) === 'social media';
        }

        return in_array($type, Task::SOCIAL_TYPES, true);
    }

    /**
     * Check if a type is design work.
     */
    public static function isDesignType(?string $type): bool
    {
        if (empty($type)) return false;

        $opt = static::forCategory('task_type')->where('value', $type)->first();
        if ($opt && $opt->group_name) {
            return in_array(strtolower($opt->group_name), ['design work', 'creative & graphics design'], true);
        }

        return in_array($type, Task::DESIGN_TYPES, true);
    }

    /**
     * Check if a type is dev work.
     */
    public static function isDevType(?string $type): bool
    {
        if (empty($type)) return false;

        $opt = static::forCategory('task_type')->where('value', $type)->first();
        if ($opt && $opt->group_name) {
            return in_array(strtolower($opt->group_name), ['web & technical', 'development'], true);
        }

        return in_array(strtolower($type), Task::DEV_TYPES, true);
    }
}

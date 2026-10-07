<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DropdownOptionController extends Controller
{
    /**
     * List dropdown options, optionally filtered by category.
     */
    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');

        if ($category) {
            $options = DropdownOption::forCategory($category)->ordered()->get();
            return response()->json([
                'success' => true,
                'category' => $category,
                'options' => $options,
            ]);
        }

        $all = DropdownOption::ordered()->get()->groupBy('category');
        return response()->json([
            'success' => true,
            'categories' => $all,
        ]);
    }

    /**
     * Store a new dropdown option.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category'    => ['required', 'string', 'max:50'],
            'label'       => ['required', 'string', 'max:100'],
            'value'       => ['nullable', 'string', 'max:100'],
            'group_name'  => ['nullable', 'string', 'max:100'],
            'icon'        => ['nullable', 'string', 'max:100'],
            'color'       => ['nullable', 'string', 'max:30'],
            'order_index' => ['nullable', 'integer'],
        ]);

        $category = trim($validated['category']);
        $label = trim($validated['label']);

        // Determine value code: if category is requested_by, keep clean Title Case, otherwise slug
        if (!empty($validated['value'])) {
            $value = trim($validated['value']);
        } elseif ($category === 'urgent_requested_by') {
            $value = ucwords($label);
        } else {
            $value = Str::slug($label, '_');
        }

        // Check if value already exists in this category
        $existing = DropdownOption::where('category', $category)->where('value', $value)->first();
        if ($existing) {
            if (!$existing->is_active) {
                $existing->update([
                    'is_active'  => true,
                    'label'      => $label,
                    'group_name' => $validated['group_name'] ?? $existing->group_name,
                    'icon'       => $validated['icon'] ?? $existing->icon,
                    'color'      => $validated['color'] ?? $existing->color,
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Option restored and re-activated.',
                    'option'  => $existing,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "An option with value '{$value}' already exists in this category.",
            ], 422);
        }

        $maxOrder = DropdownOption::forCategory($category)->max('order_index') ?? 0;
        $orderIndex = $validated['order_index'] ?? ($maxOrder + 1);

        $option = DropdownOption::create([
            'category'    => $category,
            'label'       => $label,
            'value'       => $value,
            'group_name'  => $validated['group_name'] ?? null,
            'icon'        => $validated['icon'] ?? null,
            'color'       => $validated['color'] ?? null,
            'order_index' => $orderIndex,
            'is_active'   => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Option created successfully.',
            'option'  => $option,
        ], 201);
    }

    /**
     * Update an existing dropdown option.
     */
    public function update(Request $request, DropdownOption $dropdownOption): JsonResponse
    {
        $validated = $request->validate([
            'label'       => ['sometimes', 'required', 'string', 'max:100'],
            'value'       => ['sometimes', 'required', 'string', 'max:100'],
            'group_name'  => ['nullable', 'string', 'max:100'],
            'icon'        => ['nullable', 'string', 'max:100'],
            'color'       => ['nullable', 'string', 'max:30'],
            'is_active'   => ['nullable', 'boolean'],
            'order_index' => ['nullable', 'integer'],
        ]);

        // If value is being changed, ensure uniqueness
        if (isset($validated['value']) && $validated['value'] !== $dropdownOption->value) {
            $conflict = DropdownOption::where('category', $dropdownOption->category)
                ->where('value', $validated['value'])
                ->where('id', '!=', $dropdownOption->id)
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => "An option with value '{$validated['value']}' already exists.",
                ], 422);
            }
        }

        $dropdownOption->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Option updated successfully.',
            'option'  => $dropdownOption->fresh(),
        ]);
    }

    /**
     * Delete an option.
     */
    public function destroy(DropdownOption $dropdownOption): JsonResponse
    {
        $category = $dropdownOption->category;
        $dropdownOption->delete();

        return response()->json([
            'success' => true,
            'message' => 'Option deleted successfully.',
            'category' => $category,
        ]);
    }

    /**
     * Batch reorder options.
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:dropdown_options,id'],
            'items.*.order_index' => ['required', 'integer'],
        ]);

        foreach ($validated['items'] as $item) {
            DropdownOption::where('id', $item['id'])->update(['order_index' => $item['order_index']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Options reordered successfully.',
        ]);
    }
}

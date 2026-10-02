<?php

namespace App\Services\Strategist;

use App\Models\ShootDay;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShootDayService
{
    public function store(array $validated): JsonResponse
    {
        $validated['created_by'] = auth()->id();
        ShootDay::create($validated);

        return response()->json(['success' => true, 'message' => 'Shoot day scheduled successfully!']);
    }

    public function update(ShootDay $shootDay, array $validated): JsonResponse
    {
        $shootDay->update($validated);

        return response()->json(['success' => true, 'message' => 'Shoot day updated!']);
    }

    /**
     * Delete shoot day.
     */
    public function destroy(ShootDay $shootDay): JsonResponse
    {
        $shootDay->delete();

        return response()->json(['success' => true, 'message' => 'Shoot day removed.']);
    }
}


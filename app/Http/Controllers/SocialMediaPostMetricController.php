<?php

namespace App\Http\Controllers;

use App\Models\SocialMediaPost;
use App\Models\SocialMediaPostMetric;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SocialMediaPostMetricController extends Controller
{
    public function store(Request $request, SocialMediaPost $post): RedirectResponse
    {
        $validated = $request->validate($this->rules($post, null, false));
        $data = $this->normalizedMetricData($validated);

        $metric = SocialMediaPostMetric::query()
            ->where('social_media_post_id', $post->id)
            ->whereDate('snapshot_date', $validated['snapshot_date'])
            ->where('paid_promotion', (bool) $request->input('paid_promotion', false))
            ->first();

        if ($metric) {
            $metric->update(array_merge($data, [
                'updated_by' => $request->user()->id,
            ]));

            return back()->with('success', 'Social metrics snapshot updated successfully.');
        }

        $post->metrics()->create(array_merge($data, [
            'snapshot_date' => $validated['snapshot_date'],
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]));

        return back()->with('success', 'Social metrics snapshot saved successfully.');
    }

    public function update(Request $request, SocialMediaPostMetric $metric): RedirectResponse
    {
        $validated = $request->validate($this->rules($metric->socialMediaPost, $metric));
        $data = $this->normalizedMetricData($validated);

        if (
            $this->sourceSnapshotExists(
                postId: (int) $metric->social_media_post_id,
                snapshotDate: (string) $validated['snapshot_date'],
                paidPromotion: (bool) $data['paid_promotion'],
                ignoreMetricId: (int) $metric->id
            )
        ) {
            return back()
                ->withErrors([
                    'snapshot_date' => 'A snapshot for this date and source already exists for this post.',
                ])
                ->withInput();
        }

        $metric->update(array_merge($data, [
            'snapshot_date' => $validated['snapshot_date'],
            'updated_by' => $request->user()->id,
        ]));

        return back()->with('success', 'Social metrics snapshot updated successfully.');
    }

    public function destroy(SocialMediaPostMetric $metric): RedirectResponse
    {
        $metric->delete();

        return back()->with('success', 'Social metrics snapshot deleted successfully.');
    }

    private function rules(
        SocialMediaPost $post,
        ?SocialMediaPostMetric $metric = null,
        bool $enforceUniqueSnapshotDate = true
    ): array
    {
        return [
            'snapshot_date' => $this->snapshotDateRules($post, $metric, $enforceUniqueSnapshotDate),
            'views' => ['nullable', 'integer', 'min:0'],
            'impressions' => ['nullable', 'integer', 'min:0'],
            'reach' => ['nullable', 'integer', 'min:0'],
            'likes' => ['nullable', 'integer', 'min:0'],
            'comments' => ['nullable', 'integer', 'min:0'],
            'shares' => ['nullable', 'integer', 'min:0'],
            'profile_visits' => ['nullable', 'integer', 'min:0'],
            'paid_promotion' => ['required', 'boolean'],
            'ad_spend_inr' => ['nullable', 'numeric', 'min:0', 'required_if:paid_promotion,1'],
            'target_area' => ['nullable', 'string', 'max:255', 'required_if:paid_promotion,1'],
        ];
    }

    private function snapshotDateRules(
        SocialMediaPost $post,
        ?SocialMediaPostMetric $metric,
        bool $enforceUnique
    ): array {
        $rules = [
            'required',
            'date',
        ];

        if ($enforceUnique) {
            $rules[] = Rule::unique('social_media_post_metrics', 'snapshot_date')
                ->where(fn($query) => $query->where('social_media_post_id', $post->id)
                    ->where('paid_promotion', (bool) request('paid_promotion', false)))
                ->ignore($metric?->id);
        }

        return $rules;
    }

    private function normalizedMetricData(array $validated): array
    {
        $paidPromotion = (bool) ($validated['paid_promotion'] ?? false);

        return [
            'views' => $validated['views'] ?? null,
            'impressions' => $validated['impressions'] ?? null,
            'reach' => $validated['reach'] ?? null,
            'likes' => $validated['likes'] ?? null,
            'comments' => $validated['comments'] ?? null,
            'shares' => $validated['shares'] ?? null,
            'profile_visits' => $validated['profile_visits'] ?? null,
            'paid_promotion' => $paidPromotion,
            'ad_spend_inr' => $paidPromotion ? ($validated['ad_spend_inr'] ?? null) : null,
            'target_area' => $paidPromotion ? ($validated['target_area'] ?? null) : null,
        ];
    }

    private function sourceSnapshotExists(int $postId, string $snapshotDate, bool $paidPromotion, ?int $ignoreMetricId = null): bool
    {
        $query = SocialMediaPostMetric::query()
            ->where('social_media_post_id', $postId)
            ->whereDate('snapshot_date', $snapshotDate)
            ->where('paid_promotion', $paidPromotion);

        if ($ignoreMetricId) {
            $query->where('id', '!=', $ignoreMetricId);
        }

        return $query->exists();
    }
}

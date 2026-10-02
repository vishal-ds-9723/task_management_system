<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientMonthlySchedule extends Model
{
    protected $fillable = [
        'client_id',
        'month',
        'year',
        'posts',
        'reels',
        'stories',
        'carousel',
        'videos',
        'guides',
        'collections',
        'other',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get total planned content count
     */
    public function getTotalContent()
    {
        return $this->posts + $this->reels + $this->stories + $this->carousel + $this->videos + $this->guides + $this->collections + $this->other;
    }

    /**
     * Get month name
     */
    public function getMonthName()
    {
        return \Carbon\Carbon::create(null, $this->month, 1)->format('F');
    }

    /**
     * Get "Month Year" label
     */
    public function getMonthYearLabel()
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * Get the effective schedule for a client in a given month.
     * If no schedule exists for the exact month, falls back to the most recent
     * previous schedule (carry-forward behavior). Returns null if no schedule
     * has ever been set for this client.
     *
     * @param  int  $clientId
     * @param  int  $month  (1–12)
     * @param  int  $year
     * @return static|null
     */
    public static function getEffectiveSchedule(int $clientId, int $month, int $year): ?self
    {
        // 1. Try exact match first
        $exact = static::where('client_id', $clientId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($exact) {
            return $exact;
        }

        // 2. Fall back to the most recent schedule before this month
        return static::where('client_id', $clientId)
            ->where(function ($q) use ($month, $year) {
                $q->where('year', '<', $year)
                  ->orWhere(function ($q2) use ($month, $year) {
                      $q2->where('year', $year)->where('month', '<', $month);
                  });
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();
    }
}

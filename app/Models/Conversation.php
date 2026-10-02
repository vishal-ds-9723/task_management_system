<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Conversation extends Model
{
    protected $fillable = [
        'is_group', 'name', 'created_by',
        'user_one_id', 'user_two_id', 'last_message_at',
    ];

    protected $casts = [
        'is_group'        => 'boolean',
        'last_message_at' => 'datetime',
    ];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot('joined_at', 'left_at')
            ->withTimestamps()
            ->wherePivotNull('left_at');
    }

    /**
     * In a 1:1 chat, returns the other user. In a group, returns the first non-self participant
     * (rarely used for groups — groups should rely on participants() directly).
     */
    public function otherParticipant(int $currentUserId): ?User
    {
        if ($this->is_group) {
            return $this->participants->firstWhere('id', '!=', $currentUserId);
        }
        return $this->user_one_id === $currentUserId ? $this->userTwo : $this->userOne;
    }

    public function hasParticipant(int $userId): bool
    {
        // For 1:1 chats, prefer the cheap column check; fall back to the pivot for group support.
        if (! $this->is_group) {
            if ($this->user_one_id === $userId || $this->user_two_id === $userId) {
                return true;
            }
        }
        return DB::table('conversation_participants')
            ->where('conversation_id', $this->id)
            ->where('user_id', $userId)
            ->whereNull('left_at')
            ->exists();
    }

    /**
     * IDs of every active participant — used to fan out broadcasts.
     */
    public function activeParticipantIds(): array
    {
        return DB::table('conversation_participants')
            ->where('conversation_id', $this->id)
            ->whereNull('left_at')
            ->pluck('user_id')
            ->all();
    }

    /**
     * IDs of every active participant except the given user.
     */
    public function recipientIds(int $exceptUserId): array
    {
        return array_values(array_filter(
            $this->activeParticipantIds(),
            fn ($id) => $id !== $exceptUserId
        ));
    }

    /**
     * Find or create a 1:1 conversation between two users (canonical order: smaller id first).
     */
    public static function between(int $userA, int $userB): self
    {
        [$one, $two] = $userA < $userB ? [$userA, $userB] : [$userB, $userA];

        $existing = self::where('is_group', false)
            ->where('user_one_id', $one)
            ->where('user_two_id', $two)
            ->first();

        if ($existing) return $existing;

        return DB::transaction(function () use ($one, $two) {
            $convo = self::create([
                'is_group'    => false,
                'user_one_id' => $one,
                'user_two_id' => $two,
            ]);
            $now = now();
            DB::table('conversation_participants')->insertOrIgnore([
                ['conversation_id' => $convo->id, 'user_id' => $one, 'joined_at' => $now, 'created_at' => $now, 'updated_at' => $now],
                ['conversation_id' => $convo->id, 'user_id' => $two, 'joined_at' => $now, 'created_at' => $now, 'updated_at' => $now],
            ]);
            return $convo;
        });
    }

    /**
     * Create a group conversation with the given member IDs (creator is included).
     */
    public static function createGroup(string $name, int $creatorId, array $memberIds): self
    {
        return DB::transaction(function () use ($name, $creatorId, $memberIds) {
            $convo = self::create([
                'is_group'   => true,
                'name'       => $name,
                'created_by' => $creatorId,
            ]);

            $ids = array_values(array_unique(array_merge([$creatorId], $memberIds)));
            $now = now();
            $rows = array_map(fn ($id) => [
                'conversation_id' => $convo->id,
                'user_id'         => $id,
                'joined_at'       => $now,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], $ids);

            DB::table('conversation_participants')->insert($rows);

            return $convo;
        });
    }
}

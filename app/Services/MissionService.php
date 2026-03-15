<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MissionService
{
    public static function handleNewStore($userId)
    {
        $missions = DB::table('missions')
            ->where('type', 'new_store')
            ->where('active', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        foreach ($missions as $mission) {

            $progress = DB::table('mission_progress')
                ->where('mission_id', $mission->id)
                ->where('user_id', $userId)
                ->first();

            if (!$progress) {

    $completed = 1 >= $mission->target;

    DB::table('mission_progress')->insert([
        'mission_id' => $mission->id,
        'user_id' => $userId,
        'progress' => 1,
        'completed' => $completed,
        'completed_at' => $completed ? now() : null,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    // jika target langsung tercapai (misal target = 1)
    if ($completed) {

        $alreadyRewarded = DB::table('mission_rewards')
            ->where('mission_id', $mission->id)
            ->where('user_id', $userId)
            ->exists();

        if (!$alreadyRewarded) {

            DB::table('mission_rewards')->insert([
                'mission_id' => $mission->id,
                'user_id' => $userId,
                'reward_amount' => $mission->reward_amount,
                'reward_date' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

        }

        continue;
    }

    continue;
}

            if ($progress->completed) {
                continue;
            }

            $newProgress = $progress->progress + 1;

            $completed = $newProgress >= $mission->target;

            DB::table('mission_progress')
                ->where('id', $progress->id)
                ->update([
                    'progress' => $newProgress,
                    'completed' => $completed,
                    'completed_at' => $completed ? now() : null,
                    'updated_at' => now()
                ]);

            if ($completed) {

    $alreadyRewarded = DB::table('mission_rewards')
        ->where('mission_id', $mission->id)
        ->where('user_id', $userId)
        ->exists();

    if (!$alreadyRewarded) {

        DB::table('mission_rewards')->insert([
            'mission_id' => $mission->id,
            'user_id' => $userId,
            'reward_amount' => $mission->reward_amount,
            'reward_date' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

    }
}
        }
    }
}
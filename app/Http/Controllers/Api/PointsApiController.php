<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointsApiController extends Controller
{
    /**
     * Redeem (deduct) user points
     * POST /api/v1/users/{user}/points/redeem
     */
public function redeem(Request $request, User $user)
    {
        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1'],
        ]);
        $redeem = (int) $data['points'];

        $result = DB::transaction(function () use ($user, $redeem) {
            // Atomic decrement with guard to prevent negative balances
            $affected = DB::table('users')
                ->where('id', $user->id)
                ->where('points', '>=', $redeem)
                ->decrement('points', $redeem);

            if ($affected === 0) {
                abort(422, 'Insufficient points');
            }

            $remaining = (int) DB::table('users')->where('id', $user->id)->value('points');

            return [
                'user_id'   => (int) $user->id,
                'redeemed'  => $redeem,
                'remaining' => $remaining,
            ];
        });


        return response()->json([
            'message' => 'Points redeemed successfully.',
            'data'    => $result,
        ], 200);
    }

    /**
     * Add user points
     * POST /api/v1/users/{user}/points/add
     */
    public function add(Request $request, User $user)
    {
        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1'],
        ]);

        $result = DB::transaction(function () use ($user, $data) {
            $u = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            $u->points += (int) $data['points'];
            $u->save();

            return [
                'user_id'  => $u->id,
                'added'    => (int) $data['points'],
                'balance'  => (int) $u->points,
            ];
        });

        return response()->json([
            'message' => 'Points added successfully.',
            'data'    => $result,
        ], 200);
    }

    /**
     * Show User Point
     * GET api/v1/user/{user}
     */
    public function show(User $user)
    {
        return response()->json([
            'data' => [
                'user_id' => (int) $user->id,
                'points'  => (int) ($user->points ?? 0),
            ],
        ]);
    }
}

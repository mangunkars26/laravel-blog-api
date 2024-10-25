<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class DonationController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'campaign_id' => 'required|exists:campaigns,id',
            'amount' => 'required|numeric'
        ]);

        $user = JWTAuth::parseToken()->authenticate();

        $donation = Donation::create([
            'user_id' => $user->id,
            'campaign_id' => $request->campaign_id,
            'amount' => $request->amount,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Donation created succesfully',
            'data' => $donation
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        $donation = Donation::find($id);

        if (!$donation) {
            return response()->json([
                'success' => false,
                'message' => ' DOnation not found',
                'data' => null
            ], 404);
        }

        $donation->status = $request->status;
        $donation->save();

        return response()->json([
            'success' => true,
            'message' => 'DOnation sattsu upgraded',
            'data' => $donation
        ], 201);
    }
}

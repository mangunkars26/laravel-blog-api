<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $campaign = Campaign::query();

        if ($request->has('stastus')) {
            $campaigns = $campaign->where('status', $request->status);
        }

        if ($request->has('search')) {
            $campaigns->where('title','LIKE','%'. $request->search .'%');
        }

        if ($request->has('sort')) {
            $campaigns->orderBy($request->sort, 'asc');
        }

        $campaigns = $campaigns->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Cmapigns fetched succesfully'
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string',
            'description' => 'required|string',
            'target_amount' => 'required|numeric',
            'deadline' => 'required|date'
        ]);

        $campaign = Campaign::create($request->all());

        return response()->json([
            'success'=> true,
            'message' => 'Campaign sukses dibuat',
            'data' => $campaign
        ]);
    }
}

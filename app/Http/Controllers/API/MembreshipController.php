<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;

class MembreshipController extends Controller
{
    public function list()
    {
        $memberships = Membership::where('status', 1)
            ->orderByDesc('is_popular')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $memberships
        ]);
    }
}

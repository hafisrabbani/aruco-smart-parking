<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class EntryGateController extends Controller
{
    public function entryGate(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|numeric',
        ]);

        $result = User::findOrFail($data['id']);
        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }
}

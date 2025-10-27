<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Parking;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class EntryGateController extends Controller
{
    public function entryGate(Request $request)
    {
        $maxCapcity = Setting::first()->parking_capacity;
        $currentCapacity = Parking::whereNull('exit_time')->count();
        if($currentCapacity >= $maxCapcity){
            return response()->json([
                'status' => 'error',
                'message' => 'Parking is full'
            ], 403);
        }
        $data = $request->validate([
            'id' => 'required|numeric',
        ]);

        $check = Parking::whereNull('exit_time')->where('user_id', $data['id'])->first();
        if($check){
            return response()->json([
                'status' => 'error',
                'message' => 'User is already parked in'
            ], 400);
        }else{
            Parking::create([
                'entry_time' => now(),
                'user_id' => $data['id'],
            ]);
        }

        $result = User::findOrFail($data['id']);
        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    public function exitGate(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|numeric',
        ]);

        $currentParking = Parking::where('user_id', $data['id'])->whereNull('exit_time')->first();
        if(!$currentParking){
            return response()->json([
                'status' => 'error',
                'message' => 'No active parking found for this user'
            ], 404);
        }

        $currentParking->exit_time = now();
        $currentParking->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Exit recorded successfully'
        ]);
    }

    public function getCapcity()
    {
        $max = Setting::first()->parking_capacity;
        $current = Parking::whereNull('exit_time')->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'max_capacity' => $max,
                'current_capacity' => $current,
                'available_capacity' => $max - $current,
            ]
        ]);
    }
}

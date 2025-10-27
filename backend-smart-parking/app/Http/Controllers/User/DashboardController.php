<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Parking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $auth = auth()->user();
        $isParking = Parking::where('user_id', $auth->id)->first();
        return view('user.dashboard', [
            'user' => $auth,
            'isParking' => $isParking,
        ]);
    }

    public function getHistoryParking()
    {
        $auth = auth()->user();
        $parkings = Parking::where('user_id', $auth->id)->get();
        return view('user.history', [
            'user' => $auth,
            'parkings' => $parkings,
        ]);
    }
}

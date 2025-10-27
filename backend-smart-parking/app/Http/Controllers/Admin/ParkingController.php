<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parking;
use Illuminate\Http\Request;

class ParkingController extends Controller
{
    public function index(Request $request)
    {
        $query = Parking::query();

        // Filter tanggal jika ada
        if ($request->filled('start_date')) {
            $query->whereDate('entry_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('entry_time', '<=', $request->end_date);
        }

        // Urutkan: yang belum keluar dulu, lalu yang terbaru
        $parkings = $query
            ->orderByRaw('CASE WHEN exit_time IS NULL THEN 0 ELSE 1 END')
            ->orderBy('entry_time', 'desc')
            ->with('user')
            ->get();

        return view('admin.history', [
            'parkings' => $parkings,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
    }
}

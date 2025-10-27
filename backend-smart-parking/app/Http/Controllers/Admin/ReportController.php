<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Parking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Total parkir hari ini
        $todayTotal = Parking::whereDate('entry_time', $today)->count();

        // Total parkir keseluruhan
        $totalParking = Parking::count();

        // Kendaraan yang masih parkir
        $stillParking = Parking::whereNull('exit_time')->count();

        // User aktif hari ini (distinct user_id yang masuk hari ini)
        $activeUsers = Parking::whereDate('entry_time', $today)
            ->distinct()
            ->count('user_id');

        // Rata-rata durasi parkir (dalam jam) -- MySQL / MariaDB
        $averageDuration = Parking::whereNotNull('exit_time')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, entry_time, exit_time)) / 3600 as avg_hours'))
            ->value('avg_hours');

        $averageDuration = $averageDuration !== null ? round($averageDuration, 2) : 0;

        // Data parkir 7 hari terakhir (untuk grafik)
        $last7Days = Parking::select(
            DB::raw('DATE(entry_time) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('AVG(TIMESTAMPDIFF(SECOND, entry_time, exit_time)) / 3600 as avg_duration')
        )
            ->where('entry_time', '>=', Carbon::now()->subDays(6)->startOfDay()) // last 7 days including today
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        // Parkir terbaru (10 data)
        $recentParkings = Parking::with('user')
            ->orderBy('entry_time', 'desc')
            ->take(10)
            ->get();

        return view('admin.report', [
            'todayTotal' => $todayTotal,
            'totalParking' => $totalParking,
            'stillParking' => $stillParking,
            'activeUsers' => $activeUsers,
            'averageDuration' => $averageDuration,
            'last7Days' => $last7Days,
            'recentParkings' => $recentParkings,
        ]);
    }
}

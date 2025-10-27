@extends('admin.layout.app')

@section('content')
    <div class="max-w-7xl mx-auto p-6">
        <h1 class="text-3xl font-bold mb-6">Parking Report Dashboard</h1>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white shadow rounded-lg p-4 text-center">
                <h2 class="text-gray-600 text-sm">Total Hari Ini</h2>
                <p class="text-3xl font-bold text-blue-600">{{ $todayTotal }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-4 text-center">
                <h2 class="text-gray-600 text-sm">Total Parkir</h2>
                <p class="text-3xl font-bold text-green-600">{{ $totalParking }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-4 text-center">
                <h2 class="text-gray-600 text-sm">Masih Parkir</h2>
                <p class="text-3xl font-bold text-yellow-600">{{ $stillParking }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-4 text-center">
                <h2 class="text-gray-600 text-sm">User Aktif Hari Ini</h2>
                <p class="text-3xl font-bold text-indigo-600">{{ $activeUsers }}</p>
            </div>
        </div>

        {{-- Average Duration --}}
        <div class="bg-white shadow rounded-lg p-4 mb-8">
            <h2 class="text-lg font-semibold mb-2">Rata-rata Durasi Parkir</h2>
            <p class="text-2xl font-bold text-gray-800">{{ $averageDuration ?? 0 }} jam</p>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Jumlah Parkir 7 Hari Terakhir</h2>
                <canvas id="parkingChart"></canvas>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold mb-4">Rata-rata Durasi Parkir</h2>
                <canvas id="durationChart"></canvas>
            </div>
        </div>

        {{-- Recent Table --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold mb-4">Parkir Terbaru</h2>
            <table class="w-full border-collapse">
                <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="border-b p-3">User</th>
                    <th class="border-b p-3">Masuk</th>
                    <th class="border-b p-3">Keluar</th>
                    <th class="border-b p-3">Durasi (jam)</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($recentParkings as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="border-b p-3">{{ $p->user->name ?? '-' }}</td>
                        <td class="border-b p-3">{{ $p->entry_time }}</td>
                        <td class="border-b p-3">{{ $p->exit_time ?? 'Masih parkir' }}</td>
                        <td class="border-b p-3">
                            @if($p->exit_time)
                                {{ round((strtotime($p->exit_time) - strtotime($p->entry_time)) / 3600, 2) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const dates = {!! json_encode($last7Days->pluck('date')) !!};
        const totals = {!! json_encode($last7Days->pluck('total')) !!};
        const durations = {!! json_encode($last7Days->pluck('avg_duration')->map(fn($v) => $v ? round($v,2) : 0)) !!};


        new Chart(document.getElementById('parkingChart'), {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Jumlah Parkir',
                    data: totals,
                }]
            }
        });

        new Chart(document.getElementById('durationChart'), {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Rata-rata Durasi (jam)',
                    data: durations,
                }]
            }
        });
    </script>
@endpush

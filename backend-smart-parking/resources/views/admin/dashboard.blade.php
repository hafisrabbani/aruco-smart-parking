@extends('admin.layout.app')

@section('content')
    <div class="container mx-auto py-10 px-4">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold mb-2">Welcome, {{ $admin->name }} 👋</h1>
            <p class="text-gray-600">You are logged in as <b>{{ $admin->email }}</b></p>
        </div>

        {{-- Status Section --}}
        <div class="bg-white shadow-lg rounded-xl p-8 mb-10">
            <h2 class="text-2xl font-semibold mb-4 flex items-center justify-center gap-2">
                🚗 Parking Capacity Overview
            </h2>

            @php
                $capacityUsed = $capacity > 0 ? ($totalParking / $capacity) * 100 : 0;
                $statusColor = $capacityUsed < 60 ? 'bg-green-500' : ($capacityUsed < 85 ? 'bg-yellow-500' : 'bg-red-500');
            @endphp

            <div class="text-center mb-4">
                <p class="text-lg text-gray-600">
                    Total Parked Cars:
                    <span class="font-semibold text-gray-800">{{ $totalParking }}</span>
                    /
                    <span class="font-semibold text-gray-800">{{ $capacity }}</span> slots
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    Capacity Usage: <b>{{ number_format($capacityUsed, 1) }}%</b>
                </p>
            </div>

            {{-- Progress Bar --}}
            <div class="w-full bg-gray-200 rounded-full h-5 mb-4">
                <div class="{{ $statusColor }} h-5 rounded-full transition-all duration-700" style="width: {{ $capacityUsed }}%"></div>
            </div>

            {{-- Status Message --}}
            <div class="text-center mt-2">
                @if($capacityUsed < 60)
                    <span class="text-green-600 font-medium">✅ Plenty of space available</span>
                @elseif($capacityUsed < 85)
                    <span class="text-yellow-600 font-medium">⚠️ Parking nearing capacity</span>
                @else
                    <span class="text-red-600 font-medium">🚫 Parking full or critical!</span>
                @endif
            </div>
        </div>

        {{-- Quick Navigation --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <a href="{{ route('admin.users.index') }}"
               class="bg-white shadow hover:shadow-lg rounded-xl p-6 text-center transition group">
                <div class="text-4xl mb-3 text-blue-600 group-hover:scale-110 transition-transform">👥</div>
                <h2 class="text-lg font-semibold text-gray-800">User Management</h2>
                <p class="text-sm text-gray-500 mt-1">View all registered users</p>
            </a>

            <a href="{{ route('admin.parking.index') }}"
               class="bg-white shadow hover:shadow-lg rounded-xl p-6 text-center transition group">
                <div class="text-4xl mb-3 text-indigo-600 group-hover:scale-110 transition-transform">🅿️</div>
                <h2 class="text-lg font-semibold text-gray-800">Parking History</h2>
                <p class="text-sm text-gray-500 mt-1">Monitor parking entries and exits</p>
            </a>

            <a href="{{ route('admin.setting') }}"
               class="bg-white shadow hover:shadow-lg rounded-xl p-6 text-center transition group">
                <div class="text-4xl mb-3 text-yellow-600 group-hover:scale-110 transition-transform">⚙️</div>
                <h2 class="text-lg font-semibold text-gray-800">Setting Capacity</h2>
                <p class="text-sm text-gray-500 mt-1">Adjust maximum parking slots</p>
            </a>

            <a href="{{ route('admin.report.index') }}"
               class="bg-white shadow hover:shadow-lg rounded-xl p-6 text-center transition group">
                <div class="text-4xl mb-3 text-green-600 group-hover:scale-110 transition-transform">📊</div>
                <h2 class="text-lg font-semibold text-gray-800">Reports</h2>
                <p class="text-sm text-gray-500 mt-1">View parking analytics and insights</p>
            </a>
        </div>
    </div>
@endsection

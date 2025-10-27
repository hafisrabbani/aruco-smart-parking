@extends('admin.layout.app')

@section('content')
    <div class="max-w-6xl mx-auto bg-white shadow rounded-lg p-6 mt-10">

        <h2 class="text-2xl font-bold mb-6">Parking History</h2>

        {{-- Filter Form --}}
        <form action="{{ route('admin.parking.index') }}" method="GET" class="flex flex-wrap gap-4 mb-6 items-center">
            <div>
                <label for="start_date" class="block text-gray-700 mb-1">Start Date</label>
                <input type="date" id="start_date" name="start_date"
                       value="{{ $start_date }}" class="border border-gray-300 rounded p-2">
            </div>
            <div>
                <label for="end_date" class="block text-gray-700 mb-1">End Date</label>
                <input type="date" id="end_date" name="end_date"
                       value="{{ $end_date }}" class="border border-gray-300 rounded p-2">
            </div>
            <div>
                <br>
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    Filter
                </button>
            </div>
            <div>
                <br>
                <a href="{{ route('admin.parking.index') }}"
                   class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition">
                    Reset
                </a>
            </div>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded">
                <thead class="bg-gray-100">
                <tr>
                    <th class="text-left py-3 px-4 border-b">#</th>
                    <th class="text-left py-3 px-4 border-b">User</th>
                    <th class="text-left py-3 px-4 border-b">Entry Time</th>
                    <th class="text-left py-3 px-4 border-b">Exit Time</th>
                    <th class="text-left py-3 px-4 border-b">Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($parkings as $index => $parking)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4">{{ $index + 1 }}</td>
                        <td class="py-3 px-4">{{ $parking->user->name ?? '-' }}</td>
                        <td class="py-3 px-4">{{ \Carbon\Carbon::parse($parking->entry_time)->format('d M Y H:i') }}</td>
                        <td class="py-3 px-4">
                            @if ($parking->exit_time)
                                {{ \Carbon\Carbon::parse($parking->exit_time)->format('d M Y H:i') }}
                            @else
                                <span class="text-yellow-700 bg-yellow-100 px-2 py-1 rounded text-sm">Still Parked</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if ($parking->exit_time)
                                <span class="text-green-700 bg-green-100 px-2 py-1 rounded text-sm">Completed</span>
                            @else
                                <span class="text-blue-700 bg-blue-100 px-2 py-1 rounded text-sm">Active</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-500">No parking records found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

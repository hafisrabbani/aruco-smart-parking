@extends('user.layouts.app')

@section('title', 'Parking History')

@section('content')
    <div class="max-w-5xl mx-auto px-6 py-10">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Parking History</h1>

        @if($parkings->isEmpty())
            <div class="bg-white rounded-xl shadow p-6 text-center">
                <p class="text-gray-500">No parking history found.</p>
            </div>
        @else
            <div class="overflow-x-auto bg-white rounded-xl shadow">
                <table class="min-w-full border border-gray-200 rounded-xl overflow-hidden">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 text-left text-gray-700 font-semibold">#</th>
                        <th class="py-3 px-4 text-left text-gray-700 font-semibold">Entry Time</th>
                        <th class="py-3 px-4 text-left text-gray-700 font-semibold">Exit Time</th>
                        <th class="py-3 px-4 text-left text-gray-700 font-semibold">Duration</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @foreach($parkings as $index => $parking)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 text-gray-600">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 text-gray-800">{{ \Carbon\Carbon::parse($parking->entry_time)->format('d M Y, H:i') }}</td>
                            <td class="py-3 px-4 text-gray-800">
                                {{ $parking->exit_time ? \Carbon\Carbon::parse($parking->exit_time)->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-gray-700">
                                @if($parking->exit_time)
                                    @php
                                        $entry = \Carbon\Carbon::parse($parking->entry_time);
                                        $exit = \Carbon\Carbon::parse($parking->exit_time);
                                        $diff = $entry->diff($exit);
                                    @endphp
                                    {{ $diff->h }}h {{ $diff->i }}m
                                @else
                                    <span class="text-yellow-500 font-semibold">In progress</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

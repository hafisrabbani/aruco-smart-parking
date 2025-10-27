@extends('admin.layout.app')

@section('content')
    <div class="max-w-5xl mx-auto bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6">User List</h1>

        <table class="w-full border-collapse">
            <thead>
            <tr class="bg-gray-100 text-left">
                <th class="border-b p-3">#</th>
                <th class="border-b p-3">Name</th>
                <th class="border-b p-3">Email</th>
                <th class="border-b p-3">Total Parking</th>
                <th class="border-b p-3">Current Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($users as $index => $user)
                <tr class="hover:bg-gray-50">
                    <td class="border-b p-3">{{ $index + 1 }}</td>
                    <td class="border-b p-3">{{ $user->name }}</td>
                    <td class="border-b p-3">{{ $user->email }}</td>
                    <td class="border-b p-3">{{ $user->parkings_count }}</td>
                    <td class="border-b p-3">
                        @if($user->parkings->isNotEmpty())
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                    Sedang parkir
                                </span>
                        @else
                            <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm">
                                    Tidak parkir
                                </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center p-4 text-gray-500">Tidak ada data user</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection

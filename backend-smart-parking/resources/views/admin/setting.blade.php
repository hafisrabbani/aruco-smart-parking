@extends('admin.layout.app')

@section('content')
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-6 mt-10">
        <h2 class="text-2xl font-bold mb-4 text-center">System Settings</h2>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.setting.update') }}" method="POST">
            @csrf
            <div class="mb-5">
                <label for="parking_capacity" class="block text-gray-700 font-medium mb-2">
                    Parking Capacity
                </label>
                <input type="number" name="parking_capacity" id="parking_capacity"
                       value="{{ old('parking_capacity', $setting->parking_capacity) }}"
                       class="w-full border border-gray-300 rounded p-2 focus:ring focus:ring-blue-300"
                       min="1" required>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">
                Save Changes
            </button>
        </form>
    </div>
@endsection

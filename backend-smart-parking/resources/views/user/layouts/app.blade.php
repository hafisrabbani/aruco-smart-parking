<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Smart Parking' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

{{-- Navbar --}}
<nav class="bg-white shadow p-4 flex justify-between items-center">
    <div class="font-bold text-xl text-gray-800">Smart Parking</div>

    <div class="flex items-center space-x-6">
        <a href="{{ route('user.dashboard') }}"
           class="text-gray-700 font-medium hover:text-blue-600 {{ request()->routeIs('user.dashboard') ? 'text-blue-600 font-semibold' : '' }}">
            Dashboard
        </a>

        <a href="{{ route('user.dashboard.history') }}"
           class="text-gray-700 font-medium hover:text-blue-600 {{ request()->routeIs('user.history') ? 'text-blue-600 font-semibold' : '' }}">
            History
        </a>

        <form action="{{ route('user.logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900">
                Logout
            </button>
        </form>
    </div>
</nav>

{{-- Main Content --}}
<main class="flex-grow container mx-auto py-10 px-4">
    @yield('content')
</main>

{{-- Footer --}}
<footer class="bg-white shadow mt-auto py-3 text-center text-gray-500 text-sm">
    &copy; {{ date('Y') }} Smart Parking. All rights reserved.
</footer>

@stack('scripts')
</body>
</html>

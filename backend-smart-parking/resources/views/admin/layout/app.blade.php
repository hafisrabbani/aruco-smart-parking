<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Admin Dashboard - Smart Parking' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

{{-- Navbar --}}
<nav class="bg-white shadow p-4 flex justify-between items-center">
    <div class="font-bold text-xl">Admin Dashboard</div>

    <div class="flex items-center space-x-6">
        <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-gray-900 font-medium">
            Dashboard
        </a>

        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit"
                    class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900">
                Logout
            </button>
        </form>
    </div>
</nav>

{{-- Content --}}
<main class="flex-grow p-6">
    @yield('content')
</main>

{{-- Footer --}}
<footer class="bg-white shadow mt-auto p-4 text-center text-sm text-gray-500">
    © {{ date('Y') }} Smart Parking. All rights reserved.
</footer>

@stack('scripts')
</body>
</html>

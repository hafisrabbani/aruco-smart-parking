<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Smart Parking</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Admin Login</h2>

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.login.post') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="email" class="block text-gray-700">Email</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email') }}"
                   class="w-full border border-gray-300 rounded p-2 mt-1 focus:ring focus:ring-blue-300">
        </div>

        <div class="mb-6">
            <label for="password" class="block text-gray-700">Password</label>
            <input type="password" id="password" name="password"
                   class="w-full border border-gray-300 rounded p-2 mt-1 focus:ring focus:ring-blue-300">
        </div>

        <button type="submit"
                class="bg-blue-600 text-white w-full py-2 rounded hover:bg-blue-700 transition">
            Login
        </button>
    </form>
</div>

</body>
</html>

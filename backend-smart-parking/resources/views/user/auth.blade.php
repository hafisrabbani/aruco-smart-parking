<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Parking UNESA - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Smooth fade-in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in-out forwards;
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen text-gray-800">

<div class="bg-white shadow-lg rounded-2xl p-10 w-full max-w-md fade-in">
    {{-- Logo --}}
    <div class="flex justify-center mb-6">
        <img
            src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/f4/State_University_of_Surabaya_logo.png/250px-State_University_of_Surabaya_logo.png"
            alt="UNESA Logo"
            class="w-16 h-16 opacity-90"
        >
    </div>

    {{-- Title --}}
    <div class="text-center mb-6">
        <h1 class="text-2xl font-semibold tracking-tight mb-1">Welcome to</h1>
        <h2 class="text-3xl font-bold text-gray-900">Smart Parking UNESA</h2>
        <p class="text-gray-500 mt-2 text-sm">Access the UNESA parking system securely via Google login.</p>
    </div>

    {{-- Error message --}}
    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-3 rounded-lg mb-5 text-left border border-red-100">
            <ul class="list-disc ml-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Google Login Button --}}
    <a href="{{ route('user.google.redirect') }}"
       class="w-full flex items-center justify-center gap-3 bg-gray-900 text-white py-3 rounded-lg font-medium shadow-sm hover:bg-gray-800 transition-all duration-200">
        <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" alt="Google">
        Continue with Google
    </a>

    {{-- Divider --}}
    <div class="my-8 border-t border-gray-200"></div>

    {{-- Footer --}}
    <p class="text-xs text-gray-400 text-center">
        © {{ date('Y') }} Smart Parking UNESA. All rights reserved.
    </p>
</div>

</body>
</html>

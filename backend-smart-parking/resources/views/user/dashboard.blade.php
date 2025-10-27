@extends('user.layouts.app')

@section('content')
    <div class="text-center">
        <h1 class="text-3xl font-bold mb-3">Welcome, {{ $user->name }} 👋</h1>
        <p class="text-gray-600 mb-8">
            You are logged in with <b>{{ $user->email }}</b>
        </p>

        <div class="bg-white shadow rounded-lg p-6 max-w-md mx-auto mb-8">
            <h2 class="text-lg font-semibold mb-3">Parking Status</h2>
            @if($isParking)
                <div class="bg-green-100 text-green-800 p-4 rounded">
                    You are currently parked. Enjoy your stay!
                </div>
            @else
                <div class="bg-yellow-100 text-yellow-800 p-4 rounded">
                    You are not parked. Find a spot and park your vehicle!
                </div>
            @endif
        </div>

        {{-- Marker Section --}}
        <div class="flex flex-col items-center justify-center mt-10">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Your Parking Marker</h3>
            <div
                class="marker bg-white shadow-lg border border-gray-200 rounded-xl p-6 flex items-center justify-center"
                style="width: 200px; height: 200px;"
            >
                {{-- Marker SVG will be inserted here --}}
            </div>
            <p class="text-sm text-gray-500 mt-3">
                Marker ID: <span id="marker-id">{{ $user->id }}</span>
            </p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function generateMarkerSvg(width, height, bits, fixPdfArtifacts = true) {
            var svg = document.createElement('svg');
            svg.setAttribute('viewBox', '0 0 ' + (width + 2) + ' ' + (height + 2));
            svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svg.setAttribute('shape-rendering', 'crispEdges');

            // Background rect
            var rect = document.createElement('rect');
            rect.setAttribute('x', 0);
            rect.setAttribute('y', 0);
            rect.setAttribute('width', width + 2);
            rect.setAttribute('height', height + 2);
            rect.setAttribute('fill', 'black');
            svg.appendChild(rect);

            // "Pixels"
            for (var i = 0; i < height; i++) {
                for (var j = 0; j < width; j++) {
                    var white = bits[i * height + j];
                    if (!white) continue;

                    var pixel = document.createElement('rect');;
                    pixel.setAttribute('width', 1);
                    pixel.setAttribute('height', 1);
                    pixel.setAttribute('x', j + 1);
                    pixel.setAttribute('y', i + 1);
                    pixel.setAttribute('fill', 'white');
                    svg.appendChild(pixel);

                    if (!fixPdfArtifacts) continue;

                    if ((j < width - 1) && (bits[i * height + j + 1])) {
                        pixel.setAttribute('width', 1.5);
                    }

                    if ((i < height - 1) && (bits[(i + 1) * height + j])) {
                        var pixel2 = document.createElement('rect');;
                        pixel2.setAttribute('width', 1);
                        pixel2.setAttribute('height', 1.5);
                        pixel2.setAttribute('x', j + 1);
                        pixel2.setAttribute('y', i + 1);
                        pixel2.setAttribute('fill', 'white');
                        svg.appendChild(pixel2);
                    }
                }
            }

            return svg;
        }

        var dict;

        function generateArucoMarker(width, height, dictName, id) {
            console.log('Generate ArUco marker ' + dictName + ' ' + id);

            var bytes = dict[dictName][id];
            var bits = [];
            var bitsCount = width * height;

            // Parse marker's bytes
            for (var byte of bytes) {
                var start = bitsCount - bits.length;
                for (var i = Math.min(7, start - 1); i >= 0; i--) {
                    bits.push((byte >> i) & 1);
                }
            }

            return generateMarkerSvg(width, height, bits);
        }

        // Fetch markers dict
        var loadDict = fetch('/dict.json').then(function(res) {
            return res.json();
        }).then(function(json) {
            dict = json;
        });

        function init() {
            function updateMarker() {
                var markerId = {{ $user->id }};
                var size = 40; // mm
                var dictName = '6x6_1000';
                var width = Number(6);
                var height = Number(6);

                // Wait until dict data is loaded
                loadDict.then(function() {
                    var svg = generateArucoMarker(width, height, dictName, markerId, size);
                    svg.setAttribute('width', size + 'mm');
                    svg.setAttribute('height', size + 'mm');
                    document.querySelector('.marker').innerHTML = svg.outerHTML;
                    document.querySelector('.marker-id').innerHTML = 'ID ' + markerId;
                });
            }

            updateMarker();
        }


        init();
    </script>
@endpush

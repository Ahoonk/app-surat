<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Surat</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex flex-col bg-no-repeat bg-center"
      style="background-image: url('{{ asset('storage/logos/background.png') }}');
             background-size: cover;
             background-position: center;
             background-attachment: fixed;">

    <!-- OVERLAY -->
    <div class="min-h-screen w-full bg-black/50 flex flex-col">

        <!-- JUDUL TENGAH ATAS -->
        <div class="w-full py-10 text-center">
            <div class="text-white font-extrabold tracking-wide 
                        text-4xl md:text-5xl uppercase"
                 style="font-family: 'Georgia', serif;">
                APLIKASI SURAT ELEKTRONIK
            </div>
        </div>

        <!-- CENTER CONTENT -->
        <div class="flex flex-1 items-center justify-center px-6">

            <div class="max-w-5xl w-full bg-white/95 backdrop-blur-md 
                        rounded-2xl 
                        shadow-[0_30px_70px_-20px_rgba(0,0,0,0.7)] 
                        border border-white/20
                        overflow-hidden flex">

                <!-- LEFT SIDE LOGIN -->
                <div class="w-1/2 p-12 flex flex-col justify-center">

                    <!-- LOGO PERUSAHAAN (DIPERBESAR) -->
                    <div class="text-center mb-6">
                        <img src="{{ asset('storage/logos/aldera.png') }}"
                             class="mx-auto h-20 object-contain"
                             alt="Logo">
                    </div>

                    <!-- NAMA PERUSAHAAN -->
                    <h2 class="text-2xl font-semibold text-center mb-8">
                        Aldera Saddatech Karya
                    </h2>

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-sm mb-1">Email</label>
                            <input type="email"
                                   name="email"
                                   required
                                   class="w-full border rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        </div>

                        <div>
                            <label class="block text-sm mb-1">Password</label>
                            <input type="password"
                                   name="password"
                                   required
                                   class="w-full border rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        </div>

                        <!-- LOGIN BUTTON -->
                        <button type="submit"
                                class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
                            LOGIN
                        </button>

                        <!-- REGISTER BUTTON -->
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="block w-full text-center mt-3 
                                      border border-blue-600 
                                      text-blue-600 py-2 rounded-md 
                                      hover:bg-blue-50 transition">
                                REGISTER
                            </a>
                        @endif

                    </form>

                </div>

                <!-- RIGHT SIDE IMAGE -->
                <div class="w-1/2">
                    <img src="{{ asset('storage/logos/gedung.png') }}"
                         class="w-full h-full object-cover"
                         alt="Gedung">
                </div>

            </div>

        </div>

    </div>

</body>
</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#dc2626">

        <title>{{ config('app.name', 'Laravel') }}</title>
         
        <link rel="manifest" href="/manifest.json">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            @media print {

                nav,
                header {
                    display: none !important;
                }

                .no-print {
                    display: none !important;
                }

                .print-only {
                    display: block !important;
                }

                body {
                    background: #ffffff !important;
                }

                .bg-gray-100 {
                    background: #ffffff !important;
                }
            }

            .print-only {
                display: none;
            }

            /* LOADER */
            #pageLoader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255,255,255,0.85);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                visibility: hidden;
            }

            .spinner {
                border: 6px solid #eee;
                border-top: 6px solid #dc2626;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 0.8s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-gray-100 shadow">
                    <div class="w-full py-2 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- LOADING OVERLAY -->
        <div id="pageLoader">
            <div style="text-align:center;">
                <div class="spinner"></div>
                <p style="margin-top:10px; font-weight:500;">
                    AI lagi mikir keras… ngopi dulu ☕
                </p>
            </div>
        </div>

        <script>
            // show saat pindah halaman
            window.addEventListener("beforeunload", function () {
                document.getElementById("pageLoader").style.visibility = "visible";
            });

            // hide saat selesai load
            window.addEventListener("load", function () {
                document.getElementById("pageLoader").style.visibility = "hidden";
            });

            // khusus klik menu fee sales (biar langsung muncul)
            document.addEventListener("DOMContentLoaded", function () {
                let feeMenu = document.querySelector('a[href*="sales-fees"]');

                if (feeMenu) {
                    feeMenu.addEventListener("click", function () {
                        document.getElementById("pageLoader").style.visibility = "visible";
                    });
                }
            });
        </script>

        <script>
         if ('serviceWorker' in navigator) {
              navigator.serviceWorker.register('/service-worker.js')
             .then(function(registration) {
              console.log('Service Worker registered:', registration);
           })
             .catch(function(error) {
              console.log('Service Worker registration failed:', error);
       });
}
</script>
    </body>
</html>
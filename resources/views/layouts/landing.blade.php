<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="IPESA SM - Tu tienda de pinturas de confianza. Pinturas vinílicas, esmaltes, impermeabilizantes y más.">
    <title>@yield('title', 'IPESA SM — Pinturas y Recubrimientos')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-white text-gray-900 antialiased">

    @yield('content')

    @stack('scripts')
</body>
</html>

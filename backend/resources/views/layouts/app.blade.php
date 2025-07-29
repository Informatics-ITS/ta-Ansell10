<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Pastikan csrf-token ada di sini -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Laravel App</title>
</head>
<body>
    <div id="app">
        @yield('content')
    </div>
</body>
</html>

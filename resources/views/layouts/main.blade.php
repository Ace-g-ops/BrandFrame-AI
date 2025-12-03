<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" charset="UTF-8">
  <title>@yield('title', 'BrandFrameAI')</title>
   <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
 @yield('content')
</body>
</html>
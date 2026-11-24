<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="https://is3.cloudhost.id/alfa/smp/public/logo.png" type="image/png">
<link rel="apple-touch-icon" href="https://is3.cloudhost.id/alfa/smp/public/logo.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
<script>
    // This application is intentionally light-only. Clear a legacy Flux choice
    // before Flux scripts can read it, so system dark mode cannot change the UI.
    try {
        localStorage.setItem('flux.appearance', 'light');
    } catch (_) {}

    document.documentElement.classList.remove('dark');
    document.documentElement.style.colorScheme = 'light';
</script>

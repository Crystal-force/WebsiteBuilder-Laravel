<!doctype html>
<html lang="{{$bootstrapData->get('language')}}">
    <head>
        <title class="dst">Weebalo</title>

        <base href="{{ $htmlBaseUri }}">

        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500&display=swap" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="/client/favicon.png">
        <link rel="apple-touch-icon" href="/client/favicon.png">
        <link rel="manifest" href="client/manifest.json">
        <meta name="theme-color" content="{{$bootstrapData->getSelectedTheme('colors.--be-accent-default')}}">

        <style id="be-css-variables">
            :root {!! $bootstrapData->getSelectedTheme()->getColorsForCss() !!}
        </style>

        @yield('angular-styles')

        @if (file_exists($customCssPath))
            @if ($content = file_get_contents($customCssPath))
                <style>{!! $content !!}</style>
            @endif
        @endif

        @yield('head-end')
	</head>

    <body class="{{$bootstrapData->getSelectedTheme('name') === 'dark' ? 'be-dark-mode' : 'be-light-mode'}}">
        <app-root>
            @yield('before-loaded-content')
        </app-root>

        <script>
            window.bootstrapData = "{!! $bootstrapData->getEncoded() !!}";
        </script>

        @yield('angular-scripts')

        @if (file_exists($customHtmlPath))
            @if ($content = file_get_contents($customHtmlPath))
                {!! $content !!}
            @endif
        @endif

        @if ($code = $settings->get('analytics.tracking_code'))
            <script>
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

                ga('create', '{{ $settings->get('analytics.tracking_code') }}', 'auto');
                ga('send', 'pageview');
            </script>
        @endif

        {{-- <noscript>You need to have javascript enabled in order to use <strong>{{config('app.name')}}</strong>.</noscript> --}}

        @yield('body-end')
	</body>
</html>

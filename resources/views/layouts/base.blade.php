<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    @include('layouts.parts.favicon')

    @hasSection('title')
        <title>@yield('title') - Ustawoteka</title>
    @else
        <title>Ustawoteka</title>
    @endif

    {{-- TODO <meta name="description" content=""> --}}
    {{-- TODO <meta name="keywords" content=""> --}}
    <meta name="author" content="ius vitae">

    <link rel="manifest" href="/manifest.json"/>

    <link rel="stylesheet" href="/vendor/material-components-web.min.css">

    <link rel="stylesheet" type="text/css" href="{{ mix('/css/app.css') }}">

    <link
        href="https://fonts.googleapis.com/css?family=Playfair+Display:400,400i,700,700i%7cWork+Sans:100,400,500,700%7cPT+Serif:400i,500i,700i"
        rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/vendor/pe-icon-7-stroke/css/pe-icon-7-stroke.css">

    @yield('head_content')
    <script src="/js/Tooltip.js"></script>

    <style type="text/css">
        .tree-default span {
            white-space:normal !important;
            height: auto!important;
        }
        .tree-anchor {
            height: auto !important;
        }
        .tree-default li > ins {
            vertical-align:top;
        }
        .tree-leaf {
            height: auto;
        }
        .tree-leaf span{
            height: auto !important;
        }
        li.tree-open ul.tree-children{
            max-height: fit-content!important;
        }

        li.tree-closed ul.tree-children{
            max-height: 0!important;
        }

    </style>

    <script src="/sw_update.js" async></script>
</head>
@hasSection('body_class')
    <body class="@yield('body_class')">
    @else
        <body>
        @endif

        @yield('body_content')
        @yield('body_last')
        @yield('body_script')

        </body>

</html>

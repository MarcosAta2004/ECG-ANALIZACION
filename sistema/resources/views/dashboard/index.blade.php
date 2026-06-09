@props(['dir'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dir ?? false ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ env('APP_NAME') }}</title>

    @include('layouts.header._head')
</head>

<body>

    {{-- Loader --}}
    <div id="loading">
        @include('layouts._body_loader')
    </div>

    {{-- Sidebar --}}
    @include('layouts._body_sidebar')

    <main class="main-content">

        {{-- Header --}}
        <div class="position-relative">
            @include('layouts._body_header')
            @include('layouts.sub-header')
        </div>

        {{-- CONTENIDO DINÁMICO --}}
        <div class="container-fluid content-inner mt-n5 py-0" id="page_layout">
            @yield('content')
        </div>

        {{-- Footer --}}
        @include('layouts._body_footer')

    </main>

    {{-- Modal --}}
    <div class="modal fade" id="formModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="formTitle">Modal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="main_form"></div>
                </div>

            </div>
        </div>
    </div>

    {{-- Scripts --}}
    @include('layouts._scripts')
    @include('layouts._app_toast')

</body>

</html>
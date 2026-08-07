@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if ($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if ($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if (!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer (always include) --}}
        @include('adminlte::partials.footer.footer')

        {{-- Right Control Sidebar --}}
        @if ($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
    <script>
        (function() {
            var hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            function tick(el) {
                var now = new Date(new Date().toLocaleString('en-US', {
                    timeZone: 'Asia/Jakarta'
                }));
                el.textContent = hari[now.getDay()] + ', ' + now.getDate() + ' ' + bulan[now.getMonth()] + ' ' + now
                    .getFullYear() +
                    '  ·  ' + String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2,
                    '0') + ':' + String(now.getSeconds()).padStart(2, '0') + ' WIB';
            }

            function initClock() {
                var navRight = document.querySelector('.main-header .navbar-nav.ml-auto');
                if (!navRight) return;
                var li = document.createElement('li');
                li.className = 'nav-item d-flex align-items-center px-3';
                var span = document.createElement('span');
                span.style.cssText = 'font-size:0.88rem;font-weight:500;white-space:nowrap;';
                li.appendChild(span);
                navRight.insertBefore(li, navRight.firstChild);
                tick(span);
                setInterval(function() {
                    tick(span);
                }, 1000);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initClock);
            } else {
                initClock();
            }
        })();
    </script>
@stop

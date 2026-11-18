@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@if($layoutHelper->isLayoutTopnavEnabled())
    @php($def_container_class = 'container')
@else
    @php($def_container_class = 'container-fluid')
@endif

<div class="{{ $layoutHelper->makeContentWrapperClasses() }}">
    @if($preloaderHelper->isPreloaderEnabled('cwrapper'))
        @include('adminlte::partials.common.preloader')
    @endif

    @hasSection('content_header')
        <div class="content-header">
            <div class="{{ config('adminlte.classes_content_header') ?: $def_container_class }}">
                @yield('content_header')
            </div>
        </div>
    @endif

    <div class="content">
        <div class="{{ config('adminlte.classes_content') ?: $def_container_class }}">
            @stack('content')
            @yield('content')
        </div>
    </div>
</div>

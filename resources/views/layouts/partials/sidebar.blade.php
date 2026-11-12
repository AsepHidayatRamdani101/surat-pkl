<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
        <span class="brand-text font-weight-light">PKL App</span>
    </a>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column">
                <li class="nav-item">
                    <a href="{{ url('/dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @if(auth()->check() && auth()->user()->role === 'panitia')
                    <li class="nav-item has-treeview {{ request()->is('sertifikat/*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->is('sertifikat/*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-certificate"></i><p>Sertifikat <i class="right fas fa-angle-left"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('sertifikat.admin.index') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Cetak Sertifikat</p></a></li>
                            <li class="nav-item"><a href="{{ route('sertifikat.setting.edit') }}" class="nav-link"><i class="far fa-circle nav-icon"></i><p>Pengaturan Sertifikat</p></a></li>
                        </ul>
                    </li>
                @endif
                {{-- Tambahkan menu lain sesuai kebutuhan --}}
            </ul>
        </nav>
    </div>
</aside>

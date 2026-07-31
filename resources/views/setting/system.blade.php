@extends('adminlte::page')

@section('title', 'System Setting')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">System Setting</h1>
        </div>
    </div>
@endsection

@section('content')
    @php
        function fmtSize(int $bytes): string
        {
            if ($bytes >= 1048576) {
                return round($bytes / 1048576, 2) . ' MB';
            }
            if ($bytes >= 1024) {
                return round($bytes / 1024, 2) . ' KB';
            }
            return $bytes . ' B';
        }
    @endphp

    <div class="container-fluid pt-3">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        {{-- Clear All --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-0"><i class="fas fa-broom mr-2 text-danger"></i>Bersihkan Semua Cache & Sesi</h5>
                    <small class="text-muted">Menjalankan cache:clear, view:clear, config:clear, route:clear, dan menghapus
                        semua file sesi.</small>
                </div>
                <form method="POST" action="{{ route('setting.clear-all') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"
                        onclick="return confirm('Yakin ingin membersihkan semua cache & sesi?')">
                        <i class="fas fa-trash-alt mr-1"></i> Bersihkan Semua
                    </button>
                </form>
            </div>
        </div>

        <div class="row">

            {{-- Application Cache --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-database mr-2 text-primary"></i>Cache Aplikasi</h6>
                        <small class="text-muted">storage/framework/cache/data</small>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border rounded py-3">
                                    <div class="h4 mb-0 text-primary">{{ number_format($info['cache']['files']) }}</div>
                                    <small class="text-muted">File</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded py-3">
                                    <div class="h4 mb-0 text-primary">{{ fmtSize($info['cache']['size']) }}</div>
                                    <small class="text-muted">Ukuran</small>
                                </div>
                            </div>
                        </div>
                        @include('setting._progress', ['size' => $info['cache']['size']])
                    </div>
                    <div class="card-footer bg-white">
                        <form method="POST" action="{{ route('setting.clear-cache') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                <i class="fas fa-trash mr-1"></i> Bersihkan Cache Aplikasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sessions --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-user-clock mr-2 text-warning"></i>Sesi (Session)</h6>
                        <small class="text-muted">storage/framework/sessions</small>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border rounded py-3">
                                    <div class="h4 mb-0 text-warning">{{ number_format($info['sessions']['files']) }}</div>
                                    <small class="text-muted">Sesi Aktif</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded py-3">
                                    <div class="h4 mb-0 text-warning">{{ fmtSize($info['sessions']['size']) }}</div>
                                    <small class="text-muted">Ukuran</small>
                                </div>
                            </div>
                        </div>
                        @include('setting._progress', ['size' => $info['sessions']['size']])
                        <p class="text-muted small mb-0 mt-2">
                            <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
                            Membersihkan sesi akan memaksa semua pengguna logout.
                        </p>
                    </div>
                    <div class="card-footer bg-white">
                        <form method="POST" action="{{ route('setting.clear-sessions') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-warning w-100"
                                onclick="return confirm('Semua pengguna akan di-logout. Lanjutkan?')">
                                <i class="fas fa-trash mr-1"></i> Bersihkan Sesi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- View Cache --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-file-code mr-2 text-info"></i>Cache View (Compiled)</h6>
                        <small class="text-muted">storage/framework/views</small>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-6">
                                <div class="border rounded py-3">
                                    <div class="h4 mb-0 text-info">{{ number_format($info['views']['files']) }}</div>
                                    <small class="text-muted">File</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded py-3">
                                    <div class="h4 mb-0 text-info">{{ fmtSize($info['views']['size']) }}</div>
                                    <small class="text-muted">Ukuran</small>
                                </div>
                            </div>
                        </div>
                        @include('setting._progress', ['size' => $info['views']['size']])
                    </div>
                    <div class="card-footer bg-white">
                        <form method="POST" action="{{ route('setting.clear-views') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-info w-100">
                                <i class="fas fa-trash mr-1"></i> Bersihkan Cache View
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Config & Route Cache --}}
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="fas fa-cogs mr-2 text-secondary"></i>Cache Config & Route</h6>
                        <small class="text-muted">bootstrap/cache</small>
                    </div>
                    <div class="card-body">
                        {{-- Config --}}
                        <div class="d-flex align-items-center justify-content-between mb-3 p-2 border rounded">
                            <div>
                                <div class="font-weight-bold">Config Cache</div>
                                <small class="text-muted">bootstrap/cache/config.php</small>
                            </div>
                            <div class="text-right">
                                @if ($info['config']['cached'])
                                    <span class="badge badge-success d-block mb-1">Aktif</span>
                                    <small class="text-muted">{{ fmtSize($info['config']['size']) }}</small>
                                @else
                                    <span class="badge badge-secondary">Tidak ada</span>
                                @endif
                            </div>
                        </div>
                        {{-- Route --}}
                        <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                            <div>
                                <div class="font-weight-bold">Route Cache</div>
                                <small class="text-muted">bootstrap/cache/routes-v7.php</small>
                            </div>
                            <div class="text-right">
                                @if ($info['routes']['cached'])
                                    <span class="badge badge-success d-block mb-1">Aktif</span>
                                    <small class="text-muted">{{ fmtSize($info['routes']['size']) }}</small>
                                @else
                                    <span class="badge badge-secondary">Tidak ada</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="row">
                            <div class="col-6 pr-1">
                                <form method="POST" action="{{ route('setting.clear-config') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="fas fa-trash mr-1"></i> Clear Config
                                    </button>
                                </form>
                            </div>
                            <div class="col-6 pl-1">
                                <form method="POST" action="{{ route('setting.clear-routes') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="fas fa-trash mr-1"></i> Clear Route
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /row --}}

        {{-- Optimization Tips --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-lightbulb mr-2 text-warning"></i>Tips Optimasi</h6>
            </div>
            <div class="card-body">
                <ul class="mb-0 pl-3">
                    <li class="mb-1">Jalankan <code>php artisan config:cache</code> di production agar konfigurasi dimuat
                        lebih cepat.</li>
                    <li class="mb-1">Jalankan <code>php artisan route:cache</code> di production untuk mempercepat
                        pencocokan route.</li>
                    <li class="mb-1">Cache view (<code>php artisan view:cache</code>) sudah dikompilasi otomatis saat
                        pertama kali diakses.</li>
                    <li class="mb-1">Sesi dengan jumlah besar (>500 file) dapat memperlambat filesystem — pertimbangkan
                        driver <code>database</code> atau <code>redis</code>.</li>
                    <li>Cache aplikasi yang besar bisa jadi tanda banyak data di-cache tanpa TTL — periksa penggunaan
                        <code>Cache::put</code> di kode.</li>
                </ul>
            </div>
        </div>

    </div>
@endsection

@extends('adminlte::page')

@section('title', 'Dashboard Siswa')

@php
    $activeSection = $activeSection ?? 'overview';
    $materiViewed = session('siswa_materi_seen', false);
    $sectionTitles = [
        'overview' => 'Dashboard Siswa',
        'absensi' => 'Lihat Absensi',
        'materi' => 'Materi Pembekalan',
        'tugas' => 'Kerjakan Tugas',
        'nilai' => 'Lihat Nilai Tugas',
        'sikap' => 'Catatan Sikap',
    ];
    $sectionDescriptions = [
        'overview' => 'Ringkasan seluruh informasi pembekalan PKL siswa.',
        'absensi' => 'Pantau riwayat kehadiran selama pembekalan PKL.',
        'materi' => 'Pelajari materi pembekalan sebelum mulai mengerjakan tugas.',
        'tugas' => 'Lihat tugas pembekalan dan kirim jawaban langsung.',
        'nilai' => 'Pantau hasil penilaian tugas pembekalan.',
        'sikap' => 'Lihat catatan sikap yang diberikan pembimbing.',
    ];
    $currentTitle = $sectionTitles[$activeSection] ?? 'Dashboard Siswa';
    $currentDescription = $sectionDescriptions[$activeSection] ?? $sectionDescriptions['overview'];
@endphp

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">{{ $currentTitle }}</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        @php
            $showOverview = $activeSection === 'overview';
            $showAbsensi = $activeSection === 'absensi';
            $showMateri = $activeSection === 'materi';
            $showTugas = $activeSection === 'tugas';
            $showNilai = $activeSection === 'nilai';
            $showSikap = $activeSection === 'sikap';
        @endphp
        @if (!$siswa)
            <div class="callout callout-warning">
                Data siswa tidak ditemukan untuk akun ini. Pastikan username akun siswa sama dengan NIS di data siswa.
            </div>
        @else
            @if ($showOverview)
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body py-3">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div class="mb-2 mb-md-0">
                                <h5 class="mb-1">Selamat datang, {{ $siswa->nama_siswa }}</h5>
                                <small class="text-muted">{{ $currentDescription }}</small>
                            </div>
                            <div class="dashboard-anchor-nav">
                                <a href="{{ route('dashboard.siswa.absensi') }}"
                                    class="btn btn-sm btn-outline-primary mb-1">Absensi</a>
                                <a href="{{ route('dashboard.siswa.materi') }}"
                                    class="btn btn-sm btn-outline-secondary mb-1">Materi</a>
                                <a href="{{ route('dashboard.siswa.tugas') }}"
                                    class="btn btn-sm btn-outline-success mb-1 {{ $materiViewed ? '' : 'disabled' }}"
                                    @if (!$materiViewed) aria-disabled="true" title="Lihat materi terlebih dahulu" @endif>Kerjakan
                                    Tugas</a>
                                <a href="{{ route('dashboard.siswa.nilai') }}"
                                    class="btn btn-sm btn-outline-warning mb-1">Nilai Tugas</a>
                                <a href="{{ route('dashboard.siswa.sikap') }}"
                                    class="btn btn-sm btn-outline-info mb-1">Catatan Sikap</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($showOverview)
                <div class="row dashboard-identity-row">
                    <div class="col-md-4">
                        <div class="small-box bg-info shadow-sm dashboard-metric-box dashboard-identity-box">
                            <div class="inner">
                                <h3>{{ $siswa->nama_siswa }}</h3>
                                <p>Nama Siswa</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="small-box bg-success shadow-sm dashboard-metric-box dashboard-identity-box">
                            <div class="inner">
                                <h3>{{ $siswa->kelas->nama_kelas ?? '-' }}</h3>
                                <p>Kelas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chalkboard"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="small-box bg-warning shadow-sm dashboard-metric-box dashboard-identity-box">
                            <div class="inner">
                                <h3>{{ $siswa->kelas->jurusan->nama_jurusan ?? '-' }}</h3>
                                <p>Jurusan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row dashboard-summary-row">
                    <div class="col-md-3 col-6">
                        <div class="small-box bg-primary shadow-sm dashboard-metric-box dashboard-summary-box">
                            <div class="inner">
                                <h3>{{ $summary['total_sesi'] }}</h3>
                                <p>Total Sesi Pembekalan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="small-box bg-success shadow-sm dashboard-metric-box dashboard-summary-box">
                            <div class="inner">
                                <h3>{{ $summary['hadir'] }}</h3>
                                <p>Absensi Hadir</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="small-box bg-warning shadow-sm dashboard-metric-box dashboard-summary-box">
                            <div class="inner">
                                <h3>{{ $summary['avg_nilai'] !== null ? $summary['avg_nilai'] : '-' }}</h3>
                                <p>Rata-rata Nilai Tugas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-6">
                        <div class="small-box bg-danger shadow-sm dashboard-metric-box dashboard-summary-box">
                            <div class="inner">
                                <h3>{{ $summary['progres'] }}%</h3>
                                <p>Kemajuan Pembekalan</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle mr-1 text-primary"></i>Ringkasan Informasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <strong>NIS:</strong> {{ $siswa->nis }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Status Siswa:</strong> {{ $siswa->status }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Surat Izin:</strong>
                                @if ($hasSuratIzin)
                                    <span class="badge badge-success">Sudah Isi</span>
                                @else
                                    <span class="badge badge-secondary">Belum Isi</span>
                                @endif
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Tempat PKL:</strong>
                                @if ($tempatPkl && $tempatPkl->perusahaan)
                                    <span class="badge badge-success">{{ $tempatPkl->perusahaan->nama_perusahaan }}</span>
                                @elseif ($hasTempatPkl)
                                    <span class="badge badge-warning">Belum ada perusahaan</span>
                                @else
                                    <span class="badge badge-secondary">Belum Pilih</span>
                                @endif
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Pembimbing PKL:</strong>
                                @if ($pembimbing)
                                    <span class="badge badge-info">{{ $pembimbing->nama_pembimbing }}</span>
                                @else
                                    <span class="badge badge-secondary">Belum Ditentukan</span>
                                @endif
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Pembimbing Perusahaan:</strong>
                                @if ($pembimbingPerusahaan)
                                    <span class="badge badge-info">{{ $pembimbingPerusahaan->nama_pembimbing }}</span>
                                @else
                                    <span class="badge badge-secondary">Belum Ditentukan</span>
                                @endif
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Tugas Dikerjakan:</strong>
                                {{ $summary['tugas_selesai'] }} / {{ $summary['total_sesi'] }}
                            </div>
                            <div class="col-md-4 mb-2">
                                <strong>Penilaian Sikap Terakhir:</strong>
                                @if ($summary['latest_sikap'])
                                    <span
                                        class="badge badge-info">{{ ucwords(str_replace('_', ' ', $summary['latest_sikap'])) }}</span>
                                @else
                                    <span class="badge badge-secondary">Belum Dinilai</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line mr-1 text-success"></i>Grafik Kemajuan Selama
                            Pembekalan PKL</h5>
                    </div>
                    <div class="card-body">
                        @if (count($chartLabels) > 0)
                            <canvas id="progressChart" height="95"></canvas>
                        @else
                            <p class="text-muted mb-0">Data pembekalan belum tersedia.</p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($showAbsensi)
                <div class="card shadow-sm border-0 mb-3" id="absensi-siswa">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user-check mr-1 text-primary"></i>Lihat Absensi</h5>
                        <small class="text-muted">Riwayat kehadiran selama sesi pembekalan PKL yang telah diikuti.</small>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="absensiTable" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Pembimbing</th>
                                    <th>Topik Pembekalan</th>
                                    <th>Absensi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bimbingan as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                        </td>
                                        <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                        <td>{{ $item->topik_pembekalan ?? '-' }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $item->status_absensi === 'hadir' ? 'badge-success' : ($item->status_absensi === 'izin' ? 'badge-warning' : 'badge-danger') }}">
                                                {{ ucfirst($item->status_absensi ?? '-') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($showMateri)
                <div class="elearning-section mb-4" id="materi-siswa">
                    <div class="elearning-section-header mb-4">
                        <div>
                            <h4 class="elearning-section-title"><i class="fas fa-book-open mr-2"></i>Materi Pembekalan
                            </h4>
                            <p class="elearning-section-desc">Pelajari materi berikut sebelum mengerjakan tugas.</p>
                        </div>
                        <div class="elearning-search-wrap">
                            <div class="elearning-search-inner">
                                <i class="fas fa-search elearning-search-icon"></i>
                                <input type="text" id="materiSearch" class="elearning-search-input"
                                    placeholder="Cari materi...">
                            </div>
                        </div>
                    </div>
                    <div class="row" id="materiList">
                        @php
                            $coverGradients = [
                                'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)',
                                'linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%)',
                                'linear-gradient(135deg, #f59e0b 0%, #ef4444 100%)',
                                'linear-gradient(135deg, #10b981 0%, #3b82f6 100%)',
                                'linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%)',
                                'linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%)',
                            ];
                            $coverIcons = [
                                'fa-laptop-code',
                                'fa-chalkboard-teacher',
                                'fa-lightbulb',
                                'fa-brain',
                                'fa-rocket',
                                'fa-book',
                            ];
                        @endphp
                        @forelse ($materi as $i => $item)
                            @php
                                $gradient = $coverGradients[$i % count($coverGradients)];
                                $icon = $coverIcons[$i % count($coverIcons)];
                                $types = [];
                                if (!empty($item->isi_materi)) {
                                    $types[] = 'TEXT';
                                }
                                if ($item->file_pdf_path) {
                                    $types[] = 'PDF';
                                }
                                if (!empty($item->video_url)) {
                                    $types[] = 'VIDEO';
                                }
                                $typesStr = count($types) ? implode(' + ', $types) : 'KONTEN';
                            @endphp
                            <div class="col-lg-4 col-md-6 mb-4 materi-card"
                                data-search="{{ strtolower(trim($item->topik . ' ' . ($item->isi_materi ?? '') . ' ' . ($item->catatan ?? ''))) }}">
                                <div class="elearning-card">
                                    <div class="elearning-cover" style="background: {{ $gradient }};">
                                        <i class="fas {{ $icon }} elearning-cover-icon"></i>
                                        <div class="elearning-cover-overlay"></div>
                                        <div class="elearning-cover-meta">
                                            <span class="elearning-type-badge">{{ $typesStr }}</span>
                                            <span class="elearning-date-badge"><i
                                                    class="fas fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_materi)->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="elearning-card-body">
                                        <h5 class="elearning-card-title">{{ $item->topik }}</h5>

                                        @if (!empty($item->isi_materi))
                                            <p class="elearning-card-desc">
                                                {{ \Illuminate\Support\Str::limit($item->isi_materi, 120) }}</p>
                                        @elseif(!empty($item->catatan))
                                            <p class="elearning-card-desc">
                                                {{ \Illuminate\Support\Str::limit($item->catatan, 120) }}</p>
                                        @else
                                            <p class="elearning-card-desc text-muted">Klik akses materi untuk melihat
                                                konten lengkap.</p>
                                        @endif

                                        <div class="elearning-card-actions">
                                            <a href="{{ route('dashboard.siswa.materi.detail', $item->id) }}"
                                                class="elearning-btn elearning-btn-open">
                                                <i class="fas fa-book-open mr-1"></i> Buka Materi
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="elearning-empty-state materi-empty-state">
                                    <i class="fas fa-book-open"></i>
                                    <p>Belum ada materi pembekalan yang tersedia.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            @if ($showTugas)
                <div class="elearning-section mb-4" id="tugas-siswa">
                    <div class="elearning-section-header mb-4">
                        <div>
                            <h4 class="elearning-section-title"><i class="fas fa-tasks mr-2 text-success"></i>Kerjakan
                                Tugas</h4>
                            <p class="elearning-section-desc">Daftar tugas yang diberikan pembimbing selama pembekalan PKL.
                            </p>
                        </div>
                        <a href="{{ route('dashboard.siswa.kerjakan-tugas') }}" class="elearning-btn elearning-btn-open"
                            style="background: linear-gradient(135deg,#10b981,#3b82f6); white-space: nowrap;">
                            <i class="fas fa-pen-alt mr-1"></i> Kerjakan Semua Tugas
                        </a>
                    </div>
                    <div class="row">
                        @forelse ($tugasPembekalan as $index => $tugas)
                            @php
                                $jawaban = $tugas->jawabanSiswa->first();
                                $sudahDijawab = $jawaban && $jawaban->submitted_at;
                                $nilaiTugas = $jawaban?->nilaiTugas?->nilai;
                                $isDeadlinePassed =
                                    $tugas->deadline && \Carbon\Carbon::parse($tugas->deadline)->isPast();
                            @endphp
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="elearning-card">
                                    <div class="elearning-cover"
                                        style="background: {{ ['linear-gradient(135deg,#10b981,#3b82f6)', 'linear-gradient(135deg,#f59e0b,#ef4444)', 'linear-gradient(135deg,#6366f1,#8b5cf6)'][$index % 3] }}; {{ $isDeadlinePassed ? 'opacity: 0.6;' : '' }}">
                                        <i class="fas fa-clipboard-list elearning-cover-icon"></i>
                                        <div class="elearning-cover-overlay"></div>
                                        <div class="elearning-cover-meta">
                                            <span
                                                class="elearning-type-badge">{{ $isDeadlinePassed ? '✕ Melewati Deadline' : ($sudahDijawab ? '✓ Terjawab' : 'Belum Dijawab') }}</span>
                                            <span class="elearning-date-badge">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                {{ \Carbon\Carbon::parse($tugas->tanggal_tugas)->format('d M Y') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="elearning-card-body">
                                        <h5 class="elearning-card-title">{{ $tugas->judul_tugas }}</h5>
                                        <p class="elearning-card-desc">
                                            @if ($tugas->materi)
                                                <span
                                                    class="badge badge-light border text-muted mr-1">{{ $tugas->materi->topik }}</span>
                                            @endif
                                            {{ count($tugas->soal_essay ?? []) }} soal essay
                                        </p>
                                        @if ($tugas->deadline)
                                            <div class="mb-2" style="font-size:0.82rem; color:#64748b;">
                                                <i
                                                    class="fas fa-flag mr-1 {{ $isDeadlinePassed ? 'text-danger' : 'text-warning' }}"></i>
                                                Deadline:
                                                {{ \Carbon\Carbon::parse($tugas->deadline)->format('d M Y H:i') }}
                                            </div>
                                        @endif
                                        @if ($sudahDijawab && $nilaiTugas !== null)
                                            <div class="mb-2" style="font-size:0.82rem; color:#64748b;">
                                                <i class="fas fa-star text-warning mr-1"></i> Nilai: <strong
                                                    class="text-success">{{ $nilaiTugas }}</strong>
                                            </div>
                                        @endif
                                        <div class="elearning-card-actions">
                                            @if ($isDeadlinePassed)
                                                <button class="elearning-btn elearning-btn-text"
                                                    style="width: 100%; justify-content: center; opacity: 0.6; cursor: not-allowed;"
                                                    disabled title="Deadline sudah lewat">
                                                    <i class="fas fa-lock mr-1"></i> Deadline Lewat
                                                </button>
                                            @else
                                                <a href="{{ route('dashboard.siswa.kerjakan-tugas') }}#soal-{{ $index + 1 }}"
                                                    class="elearning-btn {{ $sudahDijawab ? 'elearning-btn-text' : 'elearning-btn-open' }}"
                                                    style="{{ $sudahDijawab ? '' : 'background: linear-gradient(135deg,#10b981,#3b82f6);' }} width: 100%; justify-content: center;">
                                                    <i
                                                        class="fas {{ $sudahDijawab ? 'fa-edit' : 'fa-pen-alt' }} mr-1"></i>
                                                    {{ $sudahDijawab ? 'Ubah Jawaban' : 'Kerjakan' }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="elearning-empty-state tugas-empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                    <p>Belum ada tugas dari panitia pembekalan.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            @if ($showNilai)
                <div class="card shadow-sm border-0 mb-3" id="nilai-siswa">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-star mr-1 text-warning"></i>Lihat Nilai Tugas</h5>
                        <small class="text-muted">Hasil penilaian tugas pembekalan yang sudah diperiksa pembimbing.</small>
                    </div>
                    <div class="card-body table-responsive">
                        @php
                            $nilaiItems = $tugasPembekalan->filter(function ($tugas) {
                                $jawaban = $tugas->jawabanSiswa->first();
                                return $jawaban && $jawaban->nilaiTugas;
                            });
                        @endphp

                        @if ($nilaiItems->isEmpty())
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-inbox mr-2"></i> Belum ada nilai tugas.
                            </div>
                        @else
                            <table id="nilaiTable" class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal Tugas</th>
                                        <th>Judul Tugas</th>
                                        <th>Materi</th>
                                        <th>Nilai</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($nilaiItems as $index => $tugas)
                                        @php
                                            $jawaban = $tugas->jawabanSiswa->first();
                                            $nilai = $jawaban?->nilaiTugas;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $tugas->tanggal_tugas ? \Carbon\Carbon::parse($tugas->tanggal_tugas)->format('d-m-Y') : '-' }}
                                            </td>
                                            <td>{{ $tugas->judul_tugas ?? '-' }}</td>
                                            <td>{{ $tugas->materi?->topik ?? '-' }}</td>
                                            <td><span class="badge badge-success">{{ $nilai?->nilai ?? '-' }}</span></td>
                                            <td>{{ $nilai?->catatan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endif

            @if ($showSikap)
                <div class="card shadow-sm border-0 mb-3" id="sikap-siswa">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-notes-medical mr-1 text-info"></i>Lihat Catatan Sikap</h5>
                        <small class="text-muted">Catatan pembimbing terkait sikap, kedisiplinan, dan perkembangan selama
                            pembekalan.</small>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="sikapTable" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Pembimbing</th>
                                    <th>Penilaian Sikap</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bimbingan->filter(fn($row) => !empty($row->penilaian_sikap) || !empty($row->catatan))->values() as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->tanggal_bimbingan ? \Carbon\Carbon::parse($item->tanggal_bimbingan)->format('d-m-Y') : '-' }}
                                        </td>
                                        <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                        <td>
                                            @if ($item->penilaian_sikap)
                                                <span
                                                    class="badge badge-info">{{ ucwords(str_replace('_', ' ', $item->penilaian_sikap)) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $item->catatan ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        #absensi-siswa,
        #tugas-siswa,
        #nilai-siswa,
        #sikap-siswa {
            scroll-margin-top: 90px;
        }

        .dashboard-anchor-nav .btn {
            margin-right: 6px;
        }

        .card-header h5 {
            font-weight: 600;
        }

        .dashboard-metric-box {
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .dashboard-metric-box .inner {
            padding: 16px;
            padding-right: 78px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .dashboard-metric-box h3 {
            font-size: 1.45rem;
            line-height: 1.25;
            margin-bottom: 6px;
        }

        .dashboard-metric-box p {
            margin-bottom: 0;
        }

        .dashboard-metric-box .icon {
            top: 10px;
            right: 14px;
            font-size: 54px;
        }

        .dashboard-identity-box {
            min-height: 132px;
        }

        .dashboard-identity-box h3 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dashboard-summary-box {
            min-height: 120px;
        }

        /* -------- TUGAS CARD -------- */
        .tugas-card-inner {
            border-radius: 16px;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        }

        .tugas-card-inner:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }

        .tugas-title {
            font-weight: 700;
            color: #1f2937;
        }

        .tugas-empty-state {
            background: #f8fafc;
            border: 1px dashed #d1d5db;
            border-radius: 14px;
        }

        /* -------- E-LEARNING SECTION HEADER -------- */
        .elearning-section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .elearning-section-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .elearning-section-desc {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .elearning-search-wrap {
            min-width: 220px;
        }

        .elearning-search-inner {
            position: relative;
        }

        .elearning-search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .elearning-search-input {
            width: 100%;
            padding: 0.55rem 1rem 0.55rem 2.2rem;
            border-radius: 999px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            font-size: 0.875rem;
            outline: none;
            transition: border-color 0.15s;
        }

        .elearning-search-input:focus {
            border-color: #6366f1;
        }

        /* -------- E-LEARNING COURSE CARD -------- */
        .elearning-card {
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 12px rgba(15, 23, 42, 0.07);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .elearning-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.13);
        }

        .elearning-cover {
            position: relative;
            height: 156px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .elearning-cover-icon {
            font-size: 3.5rem;
            color: rgba(255, 255, 255, 0.35);
            position: absolute;
            right: 24px;
            bottom: 12px;
        }

        .elearning-cover-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.08);
        }

        .elearning-cover-meta {
            position: absolute;
            top: 14px;
            left: 14px;
            right: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.25rem;
            z-index: 2;
        }

        .elearning-type-badge {
            font-size: 0.72rem;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(4px);
            color: #fff;
            border-radius: 999px;
            padding: 3px 12px;
            letter-spacing: 0.04em;
        }

        .elearning-date-badge {
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.85);
        }

        .elearning-card-body {
            padding: 1.1rem 1.2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .elearning-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .elearning-card-desc {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.55;
            flex: 1;
            margin-bottom: 1rem;
        }

        .elearning-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            margin-top: auto;
        }

        .elearning-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.38rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.15s, transform 0.12s;
            cursor: pointer;
        }

        .elearning-btn:hover {
            opacity: 0.85;
            transform: scale(1.03);
            text-decoration: none;
        }

        .elearning-btn-pdf {
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            color: #fff;
        }

        .elearning-btn-video {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
        }

        .elearning-btn-text {
            background: #f1f5f9;
            color: #475569;
        }

        .elearning-btn-open {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            width: 100%;
            justify-content: center;
        }

        .elearning-empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            background: #f8fafc;
            border: 1.5px dashed #e2e8f0;
            border-radius: 18px;
            color: #94a3b8;
        }

        .elearning-empty-state i {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 0.75rem;
        }

        .materi-empty-state,
        .tugas-empty-state {
            background: #f8fafc;
            border: 1px dashed #d1d5db;
            border-radius: 14px;
        }

        @media (max-width: 576px) {
            .elearning-section-header {
                flex-direction: column;
            }

            .elearning-search-wrap {
                width: 100%;
            }

            .elearning-cover {
                height: 130px;
            }
        }

        @media (max-width: 576px) {
            .dashboard-anchor-nav .btn {
                margin-bottom: 6px;
            }

            .dashboard-metric-box {
                min-height: 110px;
            }

            .dashboard-metric-box h3 {
                font-size: 1.2rem;
            }
        }
    </style>
@endsection

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script>
        const successMessage = @json(session('success'));
        const errorMessage = @json(session('error'));

        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: successMessage,
                timer: 1800,
                showConfirmButton: false
            });
        }

        if (errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi kesalahan',
                text: errorMessage,
            });
        }

        $(function() {
            if ($.fn.DataTable) {
                ['#absensiTable', '#materiTable', '#tugasTable', '#nilaiTable', '#sikapTable'].forEach(function(
                    selector) {
                    if ($(selector).length) {
                        if ($.fn.DataTable.isDataTable(selector)) {
                            $(selector).DataTable().destroy();
                        }

                        $(selector).DataTable({
                            pageLength: 10,
                            lengthChange: true,
                            ordering: true,
                            searching: true,
                            responsive: true,
                            autoWidth: false,
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                                emptyTable: selector === '#absensiTable' ?
                                    'Belum ada data absensi pembekalan.' : selector ===
                                    '#materiTable' ?
                                    'Belum ada materi pembekalan.' : selector === '#tugasTable' ?
                                    'Belum ada tugas dari pembimbing.' : selector ===
                                    '#nilaiTable' ?
                                    'Nilai tugas belum tersedia.' : 'Catatan sikap belum tersedia.'
                            }
                        });
                    }
                });
            }

            function bindCardSearch(inputSelector, cardSelector, emptySelector) {
                const $input = $(inputSelector);
                const $cards = $(cardSelector);
                const $empty = $(emptySelector);

                if (!$input.length || !$cards.length) {
                    return;
                }

                $input.on('input', function() {
                    const keyword = ($(this).val() || '').toLowerCase().trim();
                    let visibleCount = 0;

                    $cards.each(function() {
                        const haystack = String($(this).data('search') || '').toLowerCase();
                        const isMatch = keyword === '' || haystack.indexOf(keyword) !== -1;
                        $(this).toggle(isMatch);
                        if (isMatch) {
                            visibleCount++;
                        }
                    });

                    if ($empty.length) {
                        $empty.toggle(visibleCount === 0);
                    }
                });
            }

            bindCardSearch('#materiSearch', '.materi-card', '.materi-empty-state');
            bindCardSearch('#tugasSearch', '.tugas-card', '.tugas-empty-state');
        });

        const labels = @json($chartLabels ?? []);
        const chartData = @json($chartProgres ?? []);

        if (labels.length > 0) {
            const ctx = document.getElementById('progressChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Kemajuan Kehadiran (%)',
                        data: chartData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.15)',
                        tension: 0.25,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection

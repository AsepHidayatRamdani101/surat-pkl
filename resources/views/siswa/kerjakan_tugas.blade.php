@extends('adminlte::page')

@section('title', 'Kerjakan Tugas')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard.siswa.tugas') }}" class="btn btn-sm btn-outline-secondary mr-3">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <h1 class="m-0">Kerjakan Tugas</h1>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        @php
            // Hitung total soal dari semua soal_essay di setiap tugas
            $totalSoal = $tugasList->sum(fn($t) => count($t->soal_essay ?? []));
            $sudahDijawab = $tugasList->filter(fn($t) => $t->jawabanSiswa->first()?->submitted_at)->count();
            $progres = $tugasList->count() > 0 ? (int) round(($sudahDijawab / $tugasList->count()) * 100) : 0;
        @endphp

        {{-- Hero --}}
        <div class="kerjakan-hero mb-4">
            <div class="kerjakan-hero-left">
                <div class="kerjakan-hero-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div>
                    <h3 class="kerjakan-hero-title">Tugas Pembekalan PKL</h3>
                    <p class="kerjakan-hero-sub">Jawab semua pertanyaan dengan baik lalu kirim jawabanmu.</p>
                </div>
            </div>
            <div class="kerjakan-hero-stats">
                <div class="kerjakan-stat">
                    <div class="kerjakan-stat-val">{{ $tugasList->count() }}</div>
                    <div class="kerjakan-stat-label">Total Tugas</div>
                </div>
                <div class="kerjakan-stat">
                    <div class="kerjakan-stat-val">{{ $sudahDijawab }}</div>
                    <div class="kerjakan-stat-label">Terjawab</div>
                </div>
                <div class="kerjakan-stat">
                    <div class="kerjakan-stat-val">{{ $tugasList->count() - $sudahDijawab }}</div>
                    <div class="kerjakan-stat-label">Belum Dijawab</div>
                </div>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="kerjakan-progress-wrap mb-4">
            <div class="d-flex justify-content-between mb-1">
                <small class="text-muted">Progress jawaban</small>
                <small class="text-muted font-weight-bold">{{ $progres }}%</small>
            </div>
            <div class="progress" style="height: 8px; border-radius: 999px; background: #e2e8f0;">
                <div class="progress-bar" role="progressbar"
                    style="width: {{ $progres }}%; background: linear-gradient(135deg,#10b981,#3b82f6); border-radius: 999px;"
                    aria-valuenow="{{ $progres }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        @if ($tugasList->isEmpty())
            <div class="kerjakan-empty">
                <i class="fas fa-clipboard-list"></i>
                <p>Belum ada tugas yang diberikan.</p>
            </div>
        @else
            <form method="POST" action="{{ route('dashboard.siswa.kerjakan-tugas.store') }}" id="formKerjakanTugas">
                @csrf

                <div class="row">
                    <div class="col-lg-8">
                        @foreach ($tugasList as $i => $tugas)
                            @php
                                $jawaban = $tugas->jawabanSiswa->first();
                                $sudahDijawabItem = $jawaban && $jawaban->submitted_at;
                                $nomor = $i + 1;
                                $nilaiTugas = $jawaban?->nilaiTugas?->nilai;
                                $isDeadlinePassed =
                                    $tugas->deadline && \Carbon\Carbon::parse($tugas->deadline)->isPast();
                            @endphp
                            <div class="soal-card mb-4" id="soal-{{ $nomor }}">
                                <div class="soal-header">
                                    <div class="soal-number">{{ $nomor }}</div>
                                    <div class="soal-meta">
                                        @if ($tugas->materi)
                                            <span class="soal-topik">{{ $tugas->materi->topik }}</span>
                                        @endif
                                        <span class="soal-tanggal">
                                            <i class="fas fa-calendar-alt mr-1"></i>
                                            {{ \Carbon\Carbon::parse($tugas->tanggal_tugas)->format('d M Y') }}
                                            @if ($tugas->deadline)
                                                &nbsp;·&nbsp;<i
                                                    class="fas fa-flag mr-1 {{ $isDeadlinePassed ? 'text-danger' : 'text-warning' }}"></i>Deadline:
                                                {{ \Carbon\Carbon::parse($tugas->deadline)->format('d M Y H:i') }}
                                            @endif
                                        </span>
                                    </div>
                                    @if ($isDeadlinePassed)
                                        <span class="soal-status-badge" style="background: #fecaca; color: #991b1b;">
                                            <i class="fas fa-times-circle mr-1"></i> Melewati Deadline
                                        </span>
                                    @elseif ($sudahDijawabItem)
                                        <span class="soal-status-badge soal-done">
                                            <i class="fas fa-check-circle mr-1"></i> Sudah Dijawab
                                        </span>
                                    @else
                                        <span class="soal-status-badge soal-pending">
                                            <i class="fas fa-clock mr-1"></i> Belum Dijawab
                                        </span>
                                    @endif
                                </div>

                                <div class="soal-body">
                                    <h5 class="mb-2" style="font-weight:700; color:#1e293b;">{{ $tugas->judul_tugas }}
                                    </h5>

                                    @if ($tugas->deskripsi_tugas)
                                        <p class="text-muted mb-3">{{ $tugas->deskripsi_tugas }}</p>
                                    @endif

                                    @if (!empty($tugas->soal_essay))
                                        <div class="soal-pertanyaan mb-3">
                                            <div class="soal-pertanyaan-label">Pertanyaan Essay</div>
                                            <ol style="padding-left: 1.3rem; line-height: 1.8;">
                                                @foreach ($tugas->soal_essay as $soal)
                                                    <li class="mb-1">{{ $soal }}</li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @endif

                                    @if ($isDeadlinePassed)
                                        <div class="alert alert-danger mb-3" style="margin-bottom: 1rem;">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            <strong>Deadline sudah lewat</strong> — Tugas ini tidak dapat diedit atau
                                            dikirim lagi.
                                        </div>
                                    @endif
                                    <div class="soal-jawaban-wrap">
                                        <label class="soal-jawaban-label" for="jawaban_{{ $tugas->id }}">
                                            <i class="fas fa-pen mr-1"></i> Jawaban Kamu
                                        </label>
                                        <textarea name="jawaban[{{ $tugas->id }}]" id="jawaban_{{ $tugas->id }}" class="soal-jawaban-textarea"
                                            rows="6" placeholder="Jawab semua pertanyaan di atas..." {{ $isDeadlinePassed ? 'disabled' : '' }}>{{ old('jawaban.' . $tugas->id, $jawaban?->jawaban_text ?? '') }}</textarea>
                                    </div>

                                    @if ($nilaiTugas !== null)
                                        <div class="soal-nilai-row">
                                            <i class="fas fa-star mr-1 text-warning"></i>
                                            Nilai: <span class="soal-nilai-val">{{ $nilaiTugas }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        {{-- Tombol Kirim --}}
                        <div class="kerjakan-submit-wrap">
                            <button type="submit" class="kerjakan-submit-btn" id="btnKirimJawaban">
                                <i class="fas fa-paper-plane mr-2"></i> Kirim Semua Jawaban
                            </button>
                            <a href="{{ route('dashboard.siswa.tugas') }}" class="kerjakan-back-btn">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                        </div>
                    </div>

                    {{-- Sidebar navigasi soal --}}
                    <div class="col-lg-4">
                        <div class="soal-nav-card">
                            <div class="soal-nav-title">
                                <i class="fas fa-list mr-2"></i> Navigasi Soal
                            </div>
                            <div class="soal-nav-grid">
                                @foreach ($tugasList as $i => $tugas)
                                    <a href="#soal-{{ $i + 1 }}"
                                        class="soal-nav-btn {{ $tugas->jawabanSiswa->first()?->submitted_at ? 'soal-nav-done' : 'soal-nav-pending' }}">
                                        {{ $i + 1 }}
                                    </a>
                                @endforeach
                            </div>
                            <div class="soal-nav-legend mt-3">
                                <span class="soal-nav-legend-item">
                                    <span class="soal-nav-dot soal-nav-dot-done"></span> Sudah Dijawab
                                </span>
                                <span class="soal-nav-legend-item">
                                    <span class="soal-nav-dot soal-nav-dot-pending"></span> Belum Dijawab
                                </span>
                            </div>
                        </div>

                        <div class="soal-nav-card mt-3">
                            <div class="soal-nav-title">
                                <i class="fas fa-info-circle mr-2"></i> Petunjuk
                            </div>
                            <ul class="soal-petunjuk-list">
                                <li>Isi jawaban pada masing-masing kolom.</li>
                                <li>Klik tombol <strong>Kirim Semua Jawaban</strong> untuk menyimpan.</li>
                                <li>Jawaban yang sudah dikirim masih bisa diubah.</li>
                                <li>Jawaban kosong tidak akan disimpan.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection

@section('css')
    <style>
        .kerjakan-hero {
            background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%);
            border-radius: 20px;
            padding: 1.75rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            box-shadow: 0 8px 28px rgba(16, 185, 129, 0.22);
        }

        .kerjakan-hero-left {
            display: flex;
            align-items: center;
            gap: 1.1rem;
        }

        .kerjakan-hero-icon {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .kerjakan-hero-title {
            color: #fff;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .kerjakan-hero-sub {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .kerjakan-hero-stats {
            display: flex;
            gap: 1.25rem;
        }

        .kerjakan-stat {
            text-align: center;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            padding: 0.7rem 1.1rem;
            min-width: 70px;
        }

        .kerjakan-stat-val {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .kerjakan-stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.72rem;
        }

        .soal-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(15, 23, 42, 0.07);
            overflow: hidden;
        }

        .soal-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.4rem;
            border-bottom: 1px solid #f1f5f9;
            flex-wrap: wrap;
        }

        .soal-number {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .soal-meta {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .soal-topik {
            font-size: 0.8rem;
            font-weight: 600;
            color: #1e293b;
        }

        .soal-tanggal {
            font-size: 0.75rem;
            color: #64748b;
        }

        .soal-status-badge {
            font-size: 0.72rem;
            font-weight: 600;
            border-radius: 999px;
            padding: 3px 10px;
            white-space: nowrap;
        }

        .soal-done {
            background: #d1fae5;
            color: #065f46;
        }

        .soal-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .soal-body {
            padding: 1.4rem;
        }

        .soal-pertanyaan-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.4rem;
        }

        .soal-pertanyaan-text {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 500;
            line-height: 1.6;
            margin-bottom: 0.75rem;
        }

        .soal-jawaban-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.4rem;
            display: block;
        }

        .soal-jawaban-textarea {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            font-size: 0.9rem;
            color: #374151;
            resize: vertical;
            outline: none;
            transition: border-color .15s;
            background: #f8fafc;
        }

        .soal-jawaban-textarea:focus {
            border-color: #6366f1;
            background: #fff;
        }

        .soal-nilai-row {
            margin-top: 0.85rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        .soal-nilai-val {
            font-weight: 700;
            font-size: 1rem;
            color: #10b981;
        }

        .kerjakan-submit-wrap {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem 0 2rem;
        }

        .kerjakan-submit-btn {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%);
            color: #fff;
            border: none;
            border-radius: 999px;
            padding: 0.75rem 2.2rem;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 4px 18px rgba(16, 185, 129, 0.32);
            cursor: pointer;
            transition: opacity .15s, transform .12s;
        }

        .kerjakan-submit-btn:hover {
            opacity: 0.88;
            transform: scale(1.02);
        }

        .kerjakan-back-btn {
            display: inline-flex;
            align-items: center;
            color: #64748b;
            font-size: 0.875rem;
            text-decoration: none;
            transition: color .15s;
        }

        .kerjakan-back-btn:hover {
            color: #1e293b;
            text-decoration: none;
        }

        .soal-nav-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(15, 23, 42, 0.07);
            padding: 1.25rem;
            position: sticky;
            top: 70px;
        }

        .soal-nav-title {
            font-weight: 700;
            font-size: 0.9rem;
            color: #1e293b;
            margin-bottom: 0.9rem;
        }

        .soal-nav-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.4rem;
        }

        .soal-nav-btn {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: opacity .15s;
        }

        .soal-nav-btn:hover {
            opacity: 0.75;
            text-decoration: none;
        }

        .soal-nav-done {
            background: #d1fae5;
            color: #065f46;
        }

        .soal-nav-pending {
            background: #f1f5f9;
            color: #475569;
        }

        .soal-nav-legend {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .soal-nav-legend-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.78rem;
            color: #64748b;
        }

        .soal-nav-dot {
            width: 10px;
            height: 10px;
            border-radius: 3px;
            display: inline-block;
        }

        .soal-nav-dot-done {
            background: #10b981;
        }

        .soal-nav-dot-pending {
            background: #cbd5e1;
        }

        .soal-petunjuk-list {
            font-size: 0.82rem;
            color: #64748b;
            padding-left: 1.2rem;
            margin-bottom: 0;
            line-height: 1.8;
        }

        .kerjakan-empty {
            text-align: center;
            padding: 4rem 1rem;
            background: #fff;
            border-radius: 18px;
            color: #94a3b8;
        }

        .kerjakan-empty i {
            font-size: 3rem;
            display: block;
            margin-bottom: 0.75rem;
        }

        @media (max-width: 576px) {
            .kerjakan-hero {
                flex-direction: column;
                align-items: flex-start;
            }

            .kerjakan-hero-stats {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
@endsection

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const successMessage = @json(session('success'));
        const errorMessage = @json(session('error'));

        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: successMessage,
                timer: 2200,
                showConfirmButton: false
            });
        }

        if (errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: errorMessage
            });
        }

        document.getElementById('btnKirimJawaban')?.addEventListener('click', function(e) {
            const allTextareas = document.querySelectorAll('.soal-jawaban-textarea');
            const isEmpty = Array.from(allTextareas).every(t => t.value.trim() === '');

            if (isEmpty) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Belum Ada Jawaban',
                    text: 'Isi minimal satu jawaban sebelum mengirim.',
                });
            }
        });
    </script>
@endsection

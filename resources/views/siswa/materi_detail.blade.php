@extends('adminlte::page')

@section('title', $materi->topik)

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard.siswa.materi') }}" class="btn btn-sm btn-outline-secondary mr-3">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
                <h1 class="m-0">Materi Pembekalan</h1>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="materi-detail-hero mb-4"
            style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border-radius: 20px; padding: 2.5rem 2rem; position: relative; overflow: hidden;">
            <i class="fas fa-book-open"
                style="position: absolute; right: 2rem; top: 50%; transform: translateY(-50%); font-size: 6rem; color: rgba(255,255,255,0.12);"></i>
            <div style="position: relative; z-index: 2;">
                <div class="mb-2">
                    @php
                        $types = [];
                        if (!empty($materi->isi_materi)) {
                            $types[] = 'TEXT';
                        }
                        if ($materi->file_pdf_path) {
                            $types[] = 'PDF';
                        }
                        if (!empty($materi->video_url)) {
                            $types[] = 'VIDEO';
                        }
                    @endphp
                    @foreach ($types as $type)
                        <span
                            style="background: rgba(255,255,255,0.2); color: #fff; font-size: 0.72rem; font-weight: 700; border-radius: 999px; padding: 3px 12px; margin-right: 6px;">{{ $type }}</span>
                    @endforeach
                    <span style="color: rgba(255,255,255,0.7); font-size: 0.82rem;">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ \Carbon\Carbon::parse($materi->tanggal_materi)->format('d M Y') }}
                    </span>
                </div>
                <h2 style="color: #fff; font-weight: 700; font-size: 1.75rem; margin-bottom: 0.5rem;">{{ $materi->topik }}
                </h2>
                @if (!empty($materi->catatan))
                    <p style="color: rgba(255,255,255,0.8); margin-bottom: 0;">{{ $materi->catatan }}</p>
                @endif
            </div>
        </div>

        <div class="row">
            {{-- Kolom utama --}}
            <div class="col-lg-8">

                {{-- ISI MATERI / TEKS --}}
                @if (!empty($materi->isi_materi))
                    <div class="materi-section-card mb-4">
                        <div class="materi-section-header">
                            <span class="materi-section-icon" style="background: linear-gradient(135deg,#10b981,#3b82f6);">
                                <i class="fas fa-align-left"></i>
                            </span>
                            <div>
                                <h6 class="materi-section-title">Isi Materi</h6>
                                <small class="text-muted">Bacaan materi pembekalan PKL.</small>
                            </div>
                        </div>
                        <div class="materi-content-body">
                            {!! nl2br(e($materi->isi_materi)) !!}
                        </div>
                    </div>
                @endif
                {{-- VIDEO --}}
                @if (!empty($materi->video_url))
                    <div class="materi-section-card mb-4">
                        <div class="materi-section-header">
                            <span class="materi-section-icon" style="background: linear-gradient(135deg,#6366f1,#8b5cf6);">
                                <i class="fas fa-play"></i>
                            </span>
                            <div>
                                <h6 class="materi-section-title">Video Materi</h6>
                                <small class="text-muted">Tonton video penjelasan materi di bawah ini.</small>
                            </div>
                        </div>
                        <div class="materi-video-wrap">
                            @php
                                $videoUrl = $materi->video_url;
                                $embedUrl = null;
                                if (
                                    preg_match(
                                        '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/',
                                        $videoUrl,
                                        $m,
                                    )
                                ) {
                                    $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
                                } elseif (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $m)) {
                                    $embedUrl = 'https://player.vimeo.com/video/' . $m[1];
                                }
                            @endphp
                            @if ($embedUrl)
                                <div class="ratio ratio-16x9">
                                    <iframe src="{{ $embedUrl }}" allowfullscreen
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        title="{{ $materi->topik }}"></iframe>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <a href="{{ $videoUrl }}" target="_blank" class="materi-action-btn"
                                        style="background: linear-gradient(135deg,#6366f1,#8b5cf6);">
                                        <i class="fas fa-external-link-alt mr-2"></i> Buka Video di Tab Baru
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif



                {{-- PDF --}}
                @if ($materi->file_pdf_path)
                    <div class="materi-section-card mb-4">
                        <div class="materi-section-header">
                            <span class="materi-section-icon" style="background: linear-gradient(135deg,#ef4444,#f97316);">
                                <i class="fas fa-file-pdf"></i>
                            </span>
                            <div>
                                <h6 class="materi-section-title">File PDF</h6>
                                <small class="text-muted">Dokumen materi dalam format PDF.</small>
                            </div>
                        </div>
                        <div class="materi-pdf-wrap">
                            <iframe src="{{ asset('storage/' . $materi->file_pdf_path) }}"
                                class="materi-pdf-frame"></iframe>
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ asset('storage/' . $materi->file_pdf_path) }}" target="_blank"
                                class="materi-action-btn" style="background: linear-gradient(135deg,#ef4444,#f97316);">
                                <i class="fas fa-external-link-alt mr-2"></i> Buka PDF di Tab Baru
                            </a>
                        </div>
                    </div>
                @endif

                @if (empty($materi->isi_materi) && empty($materi->file_pdf_path) && empty($materi->video_url))
                    <div class="materi-section-card mb-4 text-center py-4 text-muted">
                        <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                        Konten materi belum tersedia.
                    </div>
                @endif
            </div>

            {{-- Sidebar info --}}
            <div class="col-lg-4">
                <div class="materi-section-card mb-4">
                    <div class="materi-section-header">
                        <span class="materi-section-icon" style="background: linear-gradient(135deg,#f59e0b,#ef4444);">
                            <i class="fas fa-info"></i>
                        </span>
                        <div>
                            <h6 class="materi-section-title">Info Materi</h6>
                        </div>
                    </div>
                    <ul class="list-unstyled mb-0">
                        <li class="materi-info-row">
                            <span class="materi-info-label"><i class="fas fa-calendar-alt mr-1 text-muted"></i>
                                Tanggal</span>
                            <span
                                class="materi-info-value">{{ \Carbon\Carbon::parse($materi->tanggal_materi)->format('d M Y') }}</span>
                        </li>
                        <li class="materi-info-row">
                            <span class="materi-info-label"><i class="fas fa-layer-group mr-1 text-muted"></i> Tipe</span>
                            <span class="materi-info-value">
                                @foreach ($types as $type)
                                    <span class="badge badge-secondary mr-1">{{ $type }}</span>
                                @endforeach
                            </span>
                        </li>
                        @if (!empty($materi->catatan))
                            <li class="materi-info-row">
                                <span class="materi-info-label"><i class="fas fa-sticky-note mr-1 text-muted"></i>
                                    Catatan</span>
                                <span class="materi-info-value">{{ $materi->catatan }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="d-flex flex-column gap-2" style="gap: 0.6rem;">
                    <a href="{{ route('dashboard.siswa.tugas') }}" class="sidebar-btn sidebar-btn-primary">
                        <i class="fas fa-tasks mr-2"></i> Lanjut Kerjakan Tugas
                    </a>
                    <a href="{{ route('dashboard.siswa.materi') }}" class="sidebar-btn sidebar-btn-outline">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Materi
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        .materi-section-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(15, 23, 42, 0.07);
            padding: 1.5rem;
        }

        .materi-section-header {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-bottom: 1.1rem;
        }

        .materi-section-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .materi-section-title {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0;
        }

        .materi-content-body {
            font-size: 0.95rem;
            line-height: 1.75;
            color: #374151;
            white-space: pre-wrap;
        }

        .materi-video-wrap iframe {
            width: 100%;
            border: none;
            border-radius: 12px;
            min-height: 320px;
        }

        .materi-pdf-wrap {
            margin-top: 0.5rem;
        }

        .materi-pdf-frame {
            width: 100%;
            height: 480px;
            border: none;
            border-radius: 12px;
            background: #f8fafc;
        }

        .materi-action-btn {
            display: inline-flex;
            align-items: center;
            color: #fff;
            border-radius: 999px;
            padding: 0.5rem 1.4rem;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            transition: opacity .15s;
        }

        .materi-action-btn:hover {
            opacity: 0.85;
            color: #fff;
            text-decoration: none;
        }

        .materi-info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f1f5f9;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .materi-info-row:last-child {
            border-bottom: 0;
        }

        .materi-info-label {
            color: #64748b;
            white-space: nowrap;
        }

        .materi-info-value {
            color: #1e293b;
            text-align: right;
        }

        .materi-detail-hero {
            box-shadow: 0 8px 28px rgba(99, 102, 241, 0.22);
        }

        .sidebar-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            transition: opacity .15s, transform .12s;
            width: 100%;
        }

        .sidebar-btn:hover {
            opacity: 0.85;
            transform: scale(1.02);
            text-decoration: none;
        }

        .sidebar-btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #3b82f6 100%);
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
        }

        .sidebar-btn-outline {
            background: #f8fafc;
            color: #475569 !important;
            border: 1.5px solid #e2e8f0;
        }
    </style>
@endsection

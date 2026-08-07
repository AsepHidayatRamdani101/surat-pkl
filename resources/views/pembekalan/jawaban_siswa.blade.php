@extends('adminlte::page')

@section('title', 'Jawaban Tugas Siswa')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Jawaban Tugas Siswa</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('pembekalan.jawaban-siswa') }}">
                    <div class="form-row align-items-end">
                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Kelompok Bimbingan</label>
                            <select name="kelompok_id" class="form-control form-control-sm">
                                <option value="">Semua Kelompok</option>
                                @foreach ($kelompokOptions as $kelompok)
                                    <option value="{{ $kelompok->id }}"
                                        {{ (string) $filters['kelompok_id'] === (string) $kelompok->id ? 'selected' : '' }}>
                                        {{ $kelompok->nama_kelompok }}
                                        @if ($kelompok->pembimbing)
                                            - {{ $kelompok->pembimbing->nama_pembimbing }}
                                        @endif
                                        ({{ $kelompok->siswa_count }} siswa)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Guru Pembimbing</label>
                            <select name="pembimbing_id" class="form-control form-control-sm">
                                <option value="">Semua Pembimbing</option>
                                @foreach ($pembimbingOptions as $pembimbing)
                                    <option value="{{ $pembimbing->id }}"
                                        {{ (string) $filters['pembimbing_id'] === (string) $pembimbing->id ? 'selected' : '' }}>
                                        {{ $pembimbing->nama_pembimbing }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Materi</label>
                            <select name="materi_id" class="form-control form-control-sm">
                                <option value="">Semua Materi</option>
                                @foreach ($materiOptions as $materi)
                                    <option value="{{ $materi->id }}"
                                        {{ (string) $filters['materi_id'] === (string) $materi->id ? 'selected' : '' }}>
                                        {{ $materi->topik }}
                                        @if ($materi->tanggal_materi)
                                            ({{ \Carbon\Carbon::parse($materi->tanggal_materi)->format('d/m/Y') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Status Pengerjaan</label>
                            <select name="status" class="form-control form-control-sm">
                                <option value="">Semua Status</option>
                                <option value="belum" {{ ($filters['status'] ?? '') === 'belum' ? 'selected' : '' }}>Belum
                                </option>
                                <option value="proses" {{ ($filters['status'] ?? '') === 'proses' ? 'selected' : '' }}>
                                    Proses</option>
                                <option value="selesai" {{ ($filters['status'] ?? '') === 'selesai' ? 'selected' : '' }}>
                                    Selesai</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row mt-1 align-items-end">
                        <div class="col-md-9 mb-2">
                            <input type="text" name="keyword" class="form-control form-control-sm"
                                placeholder="Cari siswa, materi, judul tugas, atau isi jawaban"
                                value="{{ $filters['keyword'] }}">
                        </div>
                        <div class="col-md-3 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.jawaban-siswa') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                    <input type="hidden" name="filtered" value="1">
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex align-items-center">
                <div>
                    <h5 class="mb-0">Daftar Jawaban Siswa</h5>
                    @if ($isFiltered)
                        <small class="text-muted">{{ $rows->count() }} data</small>
                    @endif
                </div>
                @if ($canInputNilai)
                    <button type="submit" form="bulkNilaiForm" class="btn btn-sm btn-primary ml-auto">
                        Simpan Nilai
                    </button>
                @endif
            </div>
            <div class="card-body table-responsive">
                @if (!$isFiltered)
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-filter fa-2x mb-2 d-block"></i>
                        Gunakan filter di atas lalu klik <strong>Filter</strong> untuk menampilkan data.
                    </div>
                @else
                    @if ($canInputNilai)
                        <form method="POST" action="{{ route('pembekalan.jawaban-siswa.nilai.bulk-store') }}"
                            id="bulkNilaiForm">
                            @csrf
                        </form>
                    @endif
                    <table id="jawabanTable" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="width: 95px;">Tanggal Submit</th>
                                <th style="width: 190px;">Siswa</th>
                                <th style="width: 200px;">Materi</th>
                                <th style="width: 170px;">Tugas</th>
                                <th style="width: 90px;">Status</th>
                                <th style="width: 120px;">Nilai</th>
                                <th style="width: 160px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $item)
                                <tr>
                                    <td>
                                        @if ($item->submitted_at)
                                            {{ \Carbon\Carbon::parse($item->submitted_at)->format('d-m-Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->siswa->nama_siswa ?? '-' }}
                                        @if ($item->siswa && $item->siswa->kelas)
                                            <br><small class="text-muted">{{ $item->siswa->kelas->nama_kelas }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item->tugasPembekalan?->materi?->topik ?? '-' }}
                                        @if ($item->tugasPembekalan?->materi?->tanggal_materi)
                                            <br><small
                                                class="text-muted">{{ \Carbon\Carbon::parse($item->tugasPembekalan->materi->tanggal_materi)->format('d-m-Y') }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $item->tugasPembekalan?->judul_tugas ?? '-' }}</td>
                                    <td>
                                        @if ($item->status === 'selesai')
                                            <span class="badge badge-success">Selesai</span>
                                        @elseif ($item->status === 'proses')
                                            <span class="badge badge-warning text-white">Proses</span>
                                        @else
                                            <span class="badge badge-secondary">Belum</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->nilaiTugas?->nilai ?? '-' }}</td>
                                    <td>
                                        @if ($item->jawaban_id)
                                            <button type="button" class="btn btn-xs btn-info" data-toggle="modal"
                                                data-target="#modalJawaban{{ $item->jawaban_id }}">
                                                Lihat Jawaban
                                            </button>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                {{-- Empty state handled by DataTables language.emptyTable. --}}
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Modals di luar overflow container agar tidak terblokir iOS Safari --}}
    @if ($isFiltered)
        @foreach ($rows->filter(fn($r) => $r->jawaban_id) as $item)
            <div class="modal fade" id="modalJawaban{{ $item->jawaban_id }}" tabindex="-1" role="dialog"
                aria-labelledby="modalJawabanLabel{{ $item->jawaban_id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalJawabanLabel{{ $item->jawaban_id }}">Detail
                                Jawaban
                                Siswa
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-2">
                                <strong>Siswa:</strong> {{ $item->siswa->nama_siswa ?? '-' }}
                            </div>

                            <div class="form-group">
                                <label class="mb-1"><strong>File Soal Upload</strong></label>
                                @if (is_array($item->tugasPembekalan?->soal_files) && count($item->tugasPembekalan->soal_files) > 0)
                                    <div class="soal-file-list mb-0">
                                        @foreach ($item->tugasPembekalan->soal_files as $idx => $path)
                                            @php
                                                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                                $fileUrl = asset('storage/' . $path);
                                                $fileName = basename($path);
                                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp']);
                                                $isPdf = $extension === 'pdf';
                                            @endphp
                                            <div class="soal-file-item">
                                                <div
                                                    class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                                                    <div>
                                                        <strong>File {{ $idx + 1 }}</strong>
                                                        <div class="text-muted small">{{ $fileName }}
                                                        </div>
                                                    </div>
                                                    <a href="{{ $fileUrl }}" target="_blank"
                                                        class="btn btn-xs btn-outline-primary mt-2 mt-sm-0">
                                                        <i class="fas fa-external-link-alt mr-1"></i>Buka File
                                                    </a>
                                                </div>

                                                @if ($isImage)
                                                    <img data-src="{{ $fileUrl }}"
                                                        alt="File soal {{ $idx + 1 }}" loading="lazy"
                                                        class="soal-file-preview-img">
                                                @elseif ($isPdf)
                                                    <iframe
                                                        data-src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=1"
                                                        class="soal-file-preview-pdf"
                                                        title="Preview File {{ $idx + 1 }}"></iframe>
                                                @else
                                                    <div class="text-muted small">Preview tidak tersedia untuk
                                                        format ini.</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted">Tidak ada file soal upload.</div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="mb-1"><strong>Butir Soal Hasil Parsing</strong></label>
                                @if (is_array($item->tugasPembekalan?->soal_parsed_prompts) && count($item->tugasPembekalan->soal_parsed_prompts) > 0)
                                    <ol class="pl-3 mb-0">
                                        @foreach ($item->tugasPembekalan->soal_parsed_prompts as $parsed)
                                            <li class="mb-1">{{ $parsed }}</li>
                                        @endforeach
                                    </ol>
                                @else
                                    <div class="text-muted">Belum ada hasil parsing tersimpan.</div>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="mb-1"><strong>Jawaban Siswa</strong></label>
                                @if (!empty(trim((string) $item->jawaban_text)))
                                    <div class="jawaban-rendered border rounded p-2 bg-light">
                                        {!! $item->jawaban_text !!}
                                    </div>
                                @else
                                    <div class="text-muted">-</div>
                                @endif
                            </div>

                            @if ($canInputNilai)
                                <hr>
                                @if ($item->submitted_at)
                                    <div class="form-row">
                                        <div class="col-md-4 mb-2">
                                            <label class="mb-1">Nilai (0 - 100)</label>
                                            <input type="number" name="nilai[{{ $item->jawaban_id }}]"
                                                form="bulkNilaiForm" class="form-control form-control-sm" min="0"
                                                max="100" step="0.01"
                                                value="{{ $item->nilaiTugas?->nilai ?? '' }}">
                                        </div>
                                        <div class="col-md-8 mb-2">
                                            <label class="mb-1">Catatan Penilaian</label>
                                            <input type="text" name="catatan[{{ $item->jawaban_id }}]"
                                                form="bulkNilaiForm" class="form-control form-control-sm"
                                                value="{{ $item->nilaiTugas?->catatan ?? '' }}"
                                                placeholder="Catatan untuk siswa (opsional)">
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-secondary py-2 mb-0">
                                        Jawaban belum disubmit, nilai belum bisa diinput.
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        .jawaban-rendered {
            max-height: 360px;
            overflow: auto;
        }

        .jawaban-rendered table {
            width: 100%;
            border-collapse: collapse;
        }

        .jawaban-rendered th,
        .jawaban-rendered td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
        }

        .jawaban-rendered p {
            margin-bottom: .5rem;
        }

        .jawaban-rendered small {
            color: #6b7280;
        }

        .jawaban-rendered ul,
        .jawaban-rendered ol {
            padding-left: 1.2rem;
            margin-bottom: .5rem;
        }

        .soal-file-list {
            display: grid;
            gap: .85rem;
        }

        .soal-file-item {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: .75rem;
        }

        .soal-file-preview-img {
            display: block;
            width: 100%;
            max-height: 360px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .soal-file-preview-pdf {
            width: 100%;
            height: 360px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
        }
    </style>
@endsection

@section('plugins.Datatables', true)

@section('js')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

    @if ($isFiltered)
        <script>
            // Redirect to clean URL on browser reload so data doesn't auto-load
            if (performance.getEntriesByType('navigation')[0]?.type === 'reload') {
                window.location.replace('{{ route('pembekalan.jawaban-siswa') }}');
            }
        </script>
    @endif

    @if ($isFiltered)
        <script>
            $(function() {
                if (!$.fn.DataTable) {
                    console.error('DataTables library gagal dimuat.');
                    return;
                }

                if ($.fn.DataTable.isDataTable('#jawabanTable')) {
                    $('#jawabanTable').DataTable().destroy();
                }

                $('#jawabanTable').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    autoWidth: false,
                    order: [
                        [0, 'desc']
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: 'Belum ada jawaban tugas siswa.'
                    }
                });

                // Load iframe/img assets only when the modal is first opened
                $(document).on('show.bs.modal', '.modal', function() {
                    $(this).find('iframe[data-src]').each(function() {
                        $(this).attr('src', $(this).data('src')).removeAttr('data-src');
                    });
                    $(this).find('img[data-src]').each(function() {
                        $(this).attr('src', $(this).data('src')).removeAttr('data-src');
                    });
                });
            });
        </script>
    @endif
@endsection

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
                        <div class="col-md-4 mb-2">
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

                        <div class="col-md-4 mb-2">
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

                        <div class="col-md-4 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.jawaban-siswa') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>

                    <div class="form-row mt-1">
                        <div class="col-md-12">
                            <input type="text" name="keyword" class="form-control form-control-sm"
                                placeholder="Cari siswa, materi, judul tugas, atau isi jawaban"
                                value="{{ $filters['keyword'] }}">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex align-items-center">
                <div>
                    <h5 class="mb-0">Daftar Jawaban Siswa</h5>
                    <small class="text-muted">{{ $jawaban->count() }} data</small>
                </div>
                @if ($canInputNilai)
                    <button type="submit" form="bulkNilaiForm" class="btn btn-sm btn-primary ml-auto">
                        Simpan Nilai
                    </button>
                @endif
            </div>
            <div class="card-body table-responsive">
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
                            <th style="width: 120px;">Nilai</th>
                            <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jawaban as $item)
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
                                <td>{{ $item->tugasPembekalan->judul_tugas ?? '-' }}</td>
                                <td>{{ $item->nilaiTugas?->nilai ?? '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-info" data-toggle="modal"
                                        data-target="#modalJawaban{{ $item->id }}">
                                        Lihat Jawaban
                                    </button>
                                </td>
                            </tr>
                        @empty
                            {{-- Empty state handled by DataTables language.emptyTable. --}}
                        @endforelse
                    </tbody>
                </table>

                @foreach ($jawaban as $item)
                    <div class="modal fade" id="modalJawaban{{ $item->id }}" tabindex="-1" role="dialog"
                        aria-labelledby="modalJawabanLabel{{ $item->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalJawabanLabel{{ $item->id }}">Detail Jawaban Siswa
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <strong>Siswa:</strong> {{ $item->siswa->nama_siswa ?? '-' }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Materi:</strong> {{ $item->tugasPembekalan?->materi?->topik ?? '-' }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Judul Tugas:</strong> {{ $item->tugasPembekalan->judul_tugas ?? '-' }}
                                    </div>
                                    <div class="mb-2">
                                        <strong>Tanggal Submit:</strong>
                                        @if ($item->submitted_at)
                                            {{ \Carbon\Carbon::parse($item->submitted_at)->format('d-m-Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                    <div class="mb-2">
                                        <strong>Lampiran:</strong>
                                        @if ($item->lampiran_path)
                                            <a href="{{ asset('storage/' . $item->lampiran_path) }}" target="_blank">Lihat
                                                Lampiran</a>
                                        @else
                                            -
                                        @endif
                                    </div>

                                    <div class="form-group">
                                        <label class="mb-1"><strong>Jawaban Siswa</strong></label>
                                        <textarea class="form-control form-control-sm" rows="8" readonly>{{ $item->jawaban_text ?? '-' }}</textarea>
                                    </div>

                                    @if ($canInputNilai)
                                        <hr>
                                        @if ($item->submitted_at)
                                            <div class="form-row">
                                                <div class="col-md-4 mb-2">
                                                    <label class="mb-1">Nilai (0 - 100)</label>
                                                    <input type="number" name="nilai[{{ $item->id }}]"
                                                        form="bulkNilaiForm" class="form-control form-control-sm"
                                                        min="0" max="100" step="0.01"
                                                        value="{{ $item->nilaiTugas?->nilai ?? '' }}">
                                                </div>
                                                <div class="col-md-8 mb-2">
                                                    <label class="mb-1">Catatan Penilaian</label>
                                                    <input type="text" name="catatan[{{ $item->id }}]"
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
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
@endsection

@section('plugins.Datatables', true)

@section('js')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

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
        });
    </script>
@endsection

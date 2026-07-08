@extends('adminlte::page')

@section('title', 'Kelompok Bimbingan')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Kelompok Bimbingan</h1>
        </div>
    </div>
@endsection
@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Kelompok Bimbingan</h1>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-3">
        @if ($canManageKelompok)
            <div class="row mb-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Penentuan Otomatis</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">Aturan otomatis:</p>
                            <ul class="mb-3">
                                <li>Dahulukan guru produktif sesuai jurusan siswa.</li>
                                <li>Satu kelompok minimal 8 siswa dan maksimal 12 siswa.</li>
                            </ul>

                            <form action="{{ route('kelompok-bimbingan.generate-automatic') }}" method="POST"
                                class="d-inline" id="formGenerateOtomatis">
                                @csrf
                                <button type="submit" class="btn btn-primary" id="btnGenerateOtomatis">Generate
                                    Otomatis</button>
                            </form>

                            <form action="{{ route('kelompok-bimbingan.reset') }}" method="POST" class="d-inline ml-2"
                                id="formResetKelompok">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">Reset Semua Kelompok</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Penentuan Manual</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('kelompok-bimbingan.store-manual') }}" method="POST"
                                id="formManualKelompok">
                                @csrf
                                <div class="form-group">
                                    <label for="nama_kelompok">Nama Kelompok</label>
                                    <input type="text" name="nama_kelompok" id="nama_kelompok" class="form-control"
                                        value="{{ old('nama_kelompok') }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="pembimbing_id">Pilih Pembimbing</label>
                                    <select name="pembimbing_id" id="pembimbing_id" class="form-control" required>
                                        <option value="">Pilih Pembimbing</option>
                                        @foreach ($pembimbing as $item)
                                            <option value="{{ $item->id }}" @selected(old('pembimbing_id') == $item->id)>
                                                {{ $item->nama_pembimbing }}
                                                ({{ $item->jenis_guru === 'guru_produktif' ? 'Guru Produktif' : 'Adaptif Normatif' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="siswa_ids">Pilih Siswa (8-12 siswa)</label>
                                    <select name="siswa_ids[]" id="siswa_ids" class="form-control" size="8" multiple
                                        required>
                                        @foreach ($siswa as $item)
                                            <option value="{{ $item->id }}" @selected(collect(old('siswa_ids'))->contains($item->id))>
                                                {{ $item->nama_siswa }}
                                                @if ($item->kelas)
                                                    - {{ $item->kelas->nama_kelas }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Gunakan Ctrl atau Cmd untuk pilih lebih dari satu
                                        siswa.</small>
                                </div>

                                <button type="submit" class="btn btn-success">Simpan Kelompok Manual</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Daftar Kelompok Bimbingan</h5>
                </div>
                <div class="card-body table-responsive">
                    <table id="kelompokTable" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kelompok</th>
                                <th>Metode</th>
                                <th>Pembimbing</th>
                                <th>Jumlah Siswa</th>
                                <th>Daftar Anggota</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kelompok as $index => $item)
                                @php
                                    $kuotaPembimbing = $item->pembimbing
                                        ? (int) round((36 / 44) * (int) $item->pembimbing->jumlah_jam)
                                        : 0;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->nama_kelompok }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $item->metode === 'otomatis' ? 'bg-info' : 'bg-secondary' }}">
                                            {{ ucfirst($item->metode) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $item->pembimbing->nama_pembimbing ?? '-' }}
                                        @if ($item->pembimbing && $item->pembimbing->jenis_guru === 'guru_produktif')
                                            <br><small class="text-muted">Produktif -
                                                {{ $item->pembimbing->jurusan->nama_jurusan ?? '-' }}</small>
                                        @elseif($item->pembimbing)
                                            <br><small class="text-muted">Adaptif Normatif</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($kuotaPembimbing > 0)
                                            <span
                                                class="badge {{ $item->siswa_count > $kuotaPembimbing ? 'bg-danger' : 'bg-success' }}">
                                                {{ $item->siswa_count }} / {{ $kuotaPembimbing }}
                                            </span>
                                            <br><small class="text-muted">Terisi / Kuota Pembimbing</small>
                                        @else
                                            <span class="badge bg-secondary">{{ $item->siswa_count }}</span>
                                            <br><small class="text-muted">Kuota pembimbing belum diatur</small>
                                        @endif
                                    </td>
                                    <td>
                                        @foreach ($item->siswa as $anggota)
                                            <div>{{ $anggota->nama_siswa }} - {{ $anggota->kelas->nama_kelas ?? '-' }}
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>
                                        <form action="{{ route('kelompok-bimbingan.destroy', $item->id) }}" method="POST"
                                            class="formDeleteKelompok d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                {{-- Empty state handled by DataTables language.emptyTable. --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('kelompok-bimbingan.index') }}">
                        <div class="form-row align-items-end">
                            <div class="col-md-4 mb-2">
                                <label class="mb-1">Kelompok Bimbingan</label>
                                <select name="kelompok_id" class="form-control form-control-sm">
                                    <option value="">Semua Kelompok</option>
                                    @foreach ($kelompok as $item)
                                        <option value="{{ $item->id }}"
                                            {{ (string) $filters['kelompok_id'] === (string) $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_kelompok }}
                                            @if ($item->pembimbing)
                                                - {{ $item->pembimbing->nama_pembimbing }}
                                            @endif
                                            ({{ $item->siswa_count }} siswa)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-2">
                                <label class="mb-1">Guru Pembimbing</label>
                                <select name="pembimbing_id" class="form-control form-control-sm">
                                    <option value="">Semua Pembimbing</option>
                                    @foreach ($pembimbing as $item)
                                        <option value="{{ $item->id }}"
                                            {{ (string) $filters['pembimbing_id'] === (string) $item->id ? 'selected' : '' }}>
                                            {{ $item->nama_pembimbing }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-2 d-flex">
                                <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                                <a href="{{ route('kelompok-bimbingan.index') }}"
                                    class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                            </div>
                        </div>

                        <div class="form-row mt-1">
                            <div class="col-md-12">
                                <input type="text" name="keyword" class="form-control form-control-sm"
                                    placeholder="Cari kelompok, siswa, kelas, tempat PKL, atau nomor HP"
                                    value="{{ $filters['keyword'] }}">
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex align-items-center">
                    <div>
                        <h5 class="mb-0">Daftar Kelompok Bimbingan</h5>
                        <small class="text-muted">{{ $kelompok->sum('siswa_count') }} data</small>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="kelompokTablePembimbing" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="width: 70px;">No</th>
                                <th style="width: 180px;">Kelompok</th>
                                <th style="width: 100px;">Jumlah Siswa</th>
                                <th style="width: 220px;">Nama Siswa</th>
                                <th style="width: 120px;">Kelas</th>
                                <th style="width: 220px;">Tempat PKL</th>
                                <th style="width: 150px;">No HP Siswa</th>
                                <th style="width: 170px;">No HP Orang Tua</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowNumber = 1; @endphp
                            @forelse ($kelompok as $item)
                                @if ($item->siswa->isEmpty())
                                    <tr>
                                        <td>{{ $rowNumber++ }}</td>
                                        <td>{{ $item->nama_kelompok }}</td>
                                        <td>{{ $item->siswa_count }}</td>
                                        <td colspan="5" class="text-center">Belum ada anggota pada kelompok ini.</td>
                                    </tr>
                                @else
                                    @foreach ($item->siswa as $anggota)
                                        <tr>
                                            <td>{{ $rowNumber++ }}</td>
                                            <td>{{ $item->nama_kelompok }}</td>
                                            <td>{{ $item->siswa_count }}</td>
                                            <td>
                                                {{ $anggota->nama_siswa }}
                                                @if ($item->pembimbing)
                                                    <br><small
                                                        class="text-muted">{{ $item->pembimbing->nama_pembimbing }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $anggota->kelas->nama_kelas ?? '-' }}</td>
                                            <td>{{ $anggota->suratIzin->perusahaan->nama_perusahaan ?? '-' }}</td>
                                            <td>{{ $anggota->no_hp_siswa ?? '-' }}</td>
                                            <td>{{ $anggota->no_hp_ortu ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @empty
                                {{-- Empty state handled by DataTables language.emptyTable. --}}
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
            content: '+';
            background: linear-gradient(135deg, #0d6efd 0%, #3b82f6 100%);
            border: 0;
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.22);
            font-weight: 700;
            font-size: 12px;
            line-height: 18px;
            text-align: center;
            width: 20px;
            height: 20px;
            border-radius: 999px;
            color: #fff;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr.parent>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr.parent>th.dtr-control:before {
            content: '-';
            background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
            box-shadow: 0 6px 16px rgba(249, 115, 22, 0.22);
            transform: rotate(180deg);
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr:hover>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr:hover>th.dtr-control:before {
            transform: scale(1.06);
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr.parent:hover>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr.parent:hover>th.dtr-control:before {
            transform: rotate(180deg) scale(1.06);
        }
    </style>
@endsection

@section('plugins.Datatables', true)

@section('js')
    @include('sweetalert::alert')

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

    <script>
        const successMessage = @json(session('success'));
        const errorMessage = @json(session('error'));
        const warningMessage = @json(session('warning'));

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

        if (warningMessage) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: warningMessage,
            });
        }

        $(function() {
            if (!$.fn.DataTable) {
                console.error('DataTables library gagal dimuat.');
                return;
            }

            @if ($canManageKelompok)
                if ($.fn.DataTable.isDataTable('#kelompokTable')) {
                    $('#kelompokTable').DataTable().destroy();
                }

                $('#kelompokTable').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    autoWidth: false,
                    order: [
                        [0, 'asc']
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: 'Belum ada kelompok bimbingan.'
                    }
                });
            @else
                if ($.fn.DataTable.isDataTable('#kelompokTablePembimbing')) {
                    $('#kelompokTablePembimbing').DataTable().destroy();
                }

                $('#kelompokTablePembimbing').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    autoWidth: false,
                    order: [
                        [0, 'asc']
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: 'Belum ada kelompok bimbingan.'
                    }
                });
            @endif

            @if ($canManageKelompok)
                $('#btnGenerateOtomatis').on('click', function(e) {
                    e.preventDefault();
                    const form = $('#formGenerateOtomatis');

                    Swal.fire({
                        title: 'Generate kelompok otomatis?',
                        text: 'Data kelompok lama akan diganti dengan hasil generate baru.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, generate',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.trigger('submit');
                        }
                    });
                });

                $('#formResetKelompok').on('submit', function(e) {
                    e.preventDefault();
                    const form = this;

                    Swal.fire({
                        title: 'Reset semua kelompok?',
                        text: 'Semua data kelompok dan anggotanya akan dihapus.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, reset',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });

                $('.formDeleteKelompok').on('submit', function(e) {
                    e.preventDefault();
                    const form = this;

                    Swal.fire({
                        title: 'Hapus kelompok ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            @endif
        });
    </script>
@endsection

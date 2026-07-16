@extends('adminlte::page')

@section('title', 'Kelompok Bimbingan')

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
                                <li>Satu kelompok hanya memiliki satu pembimbing.</li>
                                <li>Jumlah siswa per kelompok mengikuti kuota pembimbing.</li>
                            </ul>

                            <div class="auto-action-grid">
                                <form action="{{ route('kelompok-bimbingan.generate-automatic') }}" method="POST"
                                    class="auto-action-form" id="formGenerateOtomatis">
                                    @csrf
                                    <button type="submit" class="btn btn-primary auto-action-btn"
                                        id="btnGenerateOtomatis">Generate Otomatis</button>
                                </form>

                                <form action="{{ route('kelompok-bimbingan.generate-kelompok') }}" method="POST"
                                    class="auto-action-form" id="formGenerateKelompokKosong">
                                    @csrf
                                    <button type="submit" class="btn btn-info auto-action-btn"
                                        id="btnGenerateKelompokKosong">Generate Kelompok</button>
                                </form>

                                <form action="{{ route('kelompok-bimbingan.reset') }}" method="POST"
                                    class="auto-action-form" id="formResetKelompok">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger auto-action-btn">Reset Semua
                                        Kelompok</button>
                                </form>
                            </div>
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
                                    <label for="kelompok_id">Nama Kelompok</label>
                                    <select name="kelompok_id" id="kelompok_id" class="form-control">
                                        <option value="">Buat Kelompok Baru Otomatis</option>
                                        @foreach ($kelompokBelumKuotaOptions as $opsiKelompok)
                                            <option value="{{ $opsiKelompok->id }}" @selected((string) old('kelompok_id') === (string) $opsiKelompok->id)>
                                                {{ $opsiKelompok->nama_kelompok }}
                                                ({{ (int) $opsiKelompok->siswa_count }}/8)
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Kosongkan pilihan jika ingin langsung buat kelompok
                                        baru saat simpan. Daftar di bawah menampilkan kelompok dengan jumlah siswa di
                                        bawah 8.</small>
                                </div>

                                <div class="form-group">
                                    @php
                                        $oldPembimbingId = old('pembimbing_id');
                                        if (empty($oldPembimbingId)) {
                                            $oldPembimbingId = collect(old('pembimbing_ids', []))->first();
                                        }
                                    @endphp
                                    <label for="pembimbing_id">Pilih Pembimbing</label>
                                    <select name="pembimbing_id" id="pembimbing_id" class="form-control" required>
                                        <option value="">Pilih Pembimbing</option>
                                        @foreach ($pembimbingAssignable as $item)
                                            <option value="{{ $item->id }}" @selected((string) $oldPembimbingId === (string) $item->id)>
                                                {{ $item->nama_pembimbing }}
                                                ({{ $item->jenis_guru === 'guru_produktif' ? 'Guru Produktif' : 'Adaptif Normatif' }})
                                                -
                                                @if (!is_null($item->sisa_kuota))
                                                    sisa {{ (int) $item->sisa_kuota }}
                                                @else
                                                    kuota belum diatur
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Satu kelompok menggunakan satu pembimbing sesuai kuota
                                        pembimbing.</small>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="info_jurusan_pembimbing">Jurusan Pembimbing</label>
                                        <input type="text" id="info_jurusan_pembimbing" class="form-control"
                                            value="-" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="info_kelas_pembimbing">Kelas Diampu</label>
                                        <input type="text" id="info_kelas_pembimbing" class="form-control" value="-"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="info_kuota_pembimbing">Kuota Pembimbing</label>
                                        <input type="text" id="info_kuota_pembimbing" class="form-control" value="-"
                                            readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="info_terpilih_siswa">Siswa Terpilih</label>
                                        <input type="text" id="info_terpilih_siswa" class="form-control" value="0 siswa"
                                            readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="filter_siswa_manual">Cari Siswa</label>
                                    <input type="text" id="filter_siswa_manual" class="form-control"
                                        placeholder="Ketik nama siswa atau kelas...">
                                </div>

                                <div class="form-group">
                                    <label for="filter_kelas_manual">Filter Kelas</label>
                                    <select id="filter_kelas_manual" class="form-control" disabled>
                                        <option value="">Pilih pembimbing dulu</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <label for="siswa_ids" id="label_siswa_ids" class="mb-1 mr-2">Pilih Siswa
                                            (sesuai kuota pembimbing)</label>
                                        <small id="label_siswa_status"
                                            class="manual-quota-status manual-quota-status-neutral">Pilih
                                            pembimbing</small>
                                    </div>
                                    <select name="siswa_ids[]" id="siswa_ids" class="form-control" size="8"
                                        multiple required>
                                        @foreach ($siswa as $item)
                                            <option value="{{ $item->id }}" data-kelas-id="{{ $item->kelas_id }}"
                                                data-kelas-nama="{{ optional($item->kelas)->nama_kelas }}"
                                                data-jurusan-id="{{ optional($item->kelas)->jurusan_id }}"
                                                @selected(collect(old('siswa_ids'))->contains($item->id))>
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

            <div class="row mb-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-danger">Pembimbing Kelebihan Kuota</h5>
                            <span class="badge bg-danger">{{ $pembimbingKelebihanKuota->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse ($pembimbingKelebihanKuota as $mentor)
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $mentor->nama_pembimbing }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                Terpakai {{ (int) $mentor->assigned_count }} / Kuota
                                                {{ (int) $mentor->kuota_total }}
                                            </small>
                                        </div>
                                        <span class="badge bg-danger">+{{ (int) $mentor->selisih_kuota }}</span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">Tidak ada pembimbing yang kelebihan kuota.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-warning">Pembimbing Kekurangan Kuota</h5>
                            <span class="badge bg-warning">{{ $pembimbingKekuranganKuota->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse ($pembimbingKekuranganKuota as $mentor)
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $mentor->nama_pembimbing }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                Terpakai {{ (int) $mentor->assigned_count }} / Kuota
                                                {{ (int) $mentor->kuota_total }}
                                            </small>
                                        </div>
                                        <span
                                            class="badge bg-warning">{{ (int) (($mentor->kuota_total ?? 0) - ($mentor->assigned_count ?? 0)) }}</span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-muted">Tidak ada pembimbing yang kekurangan kuota.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white kelompok-grid-cardheader">
                    <div>
                        <h5 class="mb-0">Daftar Kelompok Bimbingan</h5>
                        <small id="kelompokTableInfo" class="text-muted">Menampilkan
                            {{ count($kelompokTableRows) }} kelompok.</small>
                    </div>
                    <div class="d-flex align-items-center flex-wrap justify-content-end kelompok-grid-header-actions">
                        <div class="kelompok-grid-sortbar mb-2 mb-sm-0">
                            <select id="kelompokTableSort" class="form-control form-control-sm">
                                <option value="0-asc">Urutkan: Nomor Terlama</option>
                                <option value="0-desc">Urutkan: Nomor Terbaru</option>
                                <option value="1-asc">Nama Kelompok A-Z</option>
                                <option value="1-desc">Nama Kelompok Z-A</option>
                                <option value="4-desc">Jumlah Siswa Terbanyak</option>
                                <option value="4-asc">Jumlah Siswa Tersedikit</option>
                            </select>
                        </div>
                        <a href="{{ route('kelompok-bimbingan.export-excel', request()->only(['kelompok_id', 'pembimbing_id', 'keyword'])) }}"
                            class="btn btn-sm btn-success mr-1">Export Excel</a>
                        <a href="{{ route('kelompok-bimbingan.export-pdf', request()->only(['kelompok_id', 'pembimbing_id', 'keyword'])) }}"
                            class="btn btn-sm btn-danger">Export PDF</a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <div class="d-flex justify-content-end align-items-center flex-wrap mb-2 kelompok-grid-statusbar">
                        <small class="text-muted">Filter form di atas tetap berlaku untuk export.</small>
                    </div>
                    <table id="kelompokTable" class="table table-bordered table-sm kelompok-grid-table w-100"
                        style="width: 100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kelompok</th>
                                <th>Metode</th>
                                <th>Pembimbing</th>
                                <th>Jumlah Siswa</th>
                                <th>Siswa per Kelas</th>
                                <th>Daftar Anggota</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($kelompokTableRows as $index => $group)
                                @php
                                    $item = $group['model'];
                                @endphp
                                @foreach ($group['kelas_rows'] as $kelasIndex => $kelasRow)
                                    <tr class="kelompok-grid-row" data-group-index="{{ $index }}">
                                        <td data-order="{{ $index + 1 }}"
                                            class="align-middle text-center kelompok-grid-maincell kelompok-grid-number kelompok-grid-repeatable-cell">
                                            {{ $index + 1 }}
                                        </td>
                                        <td data-order="{{ strtolower($group['nama_kelompok']) }}"
                                            class="align-middle text-center kelompok-grid-maincell kelompok-grid-groupname kelompok-grid-repeatable-cell">
                                            {{ $group['nama_kelompok'] }}
                                        </td>
                                        <td data-order="{{ strtolower($group['metode']) }}"
                                            class="align-middle text-center kelompok-grid-maincell kelompok-grid-repeatable-cell">
                                            {{ $group['metode'] }}
                                        </td>
                                        <td data-order="{{ strtolower($group['pembimbing']) }}"
                                            class="align-middle text-center kelompok-grid-maincell kelompok-grid-mentor kelompok-grid-repeatable-cell">
                                            {{ $group['pembimbing'] }}
                                        </td>
                                        <td data-order="{{ $group['jumlah_siswa'] }}"
                                            class="align-middle text-center kelompok-grid-maincell kelompok-grid-total kelompok-grid-repeatable-cell">
                                            {{ $group['jumlah_siswa'] }}
                                        </td>
                                        <td class="align-middle text-center kelompok-grid-maincell kelompok-grid-class">
                                            {{ $kelasRow['siswa_per_kelas'] }}</td>
                                        <td class="kelompok-grid-members">
                                            @foreach ($kelasRow['daftar_anggota'] as $anggota)
                                                <div class="kelompok-grid-member-item">{{ $anggota }}</div>
                                            @endforeach
                                        </td>
                                        <td
                                            class="align-middle kelompok-grid-maincell kelompok-grid-actions {{ $kelasIndex > 0 ? 'kelompok-grid-actions-placeholder' : '' }}">
                                            @if ($kelasIndex === 0)
                                                <button type="button" class="btn btn-sm btn-warning mb-1"
                                                    data-toggle="modal"
                                                    data-target="#modalTambahAnggota{{ $item->id }}">Tambah Siswa /
                                                    Ganti Pembimbing</button>
                                                <button type="button" class="btn btn-sm btn-outline-warning mb-1"
                                                    data-toggle="modal"
                                                    data-target="#modalKeluarkanSiswa{{ $item->id }}">Keluarkan
                                                    Siswa</button>

                                                <form action="{{ route('kelompok-bimbingan.destroy', $item->id) }}"
                                                    method="POST" class="formDeleteKelompok d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>

                                                <div class="modal fade" id="modalTambahAnggota{{ $item->id }}"
                                                    tabindex="-1" role="dialog"
                                                    aria-labelledby="modalTambahAnggotaLabel{{ $item->id }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="modalTambahAnggotaLabel{{ $item->id }}">
                                                                    Tambah Siswa / Ganti Pembimbing -
                                                                    {{ $item->nama_kelompok }}
                                                                </h5>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form
                                                                action="{{ route('kelompok-bimbingan.add-anggota', $item->id) }}"
                                                                method="POST" class="formTambahAnggota"
                                                                data-group-id="{{ $item->id }}"
                                                                data-current-count="{{ (int) $item->siswa_count }}"
                                                                data-group-max="{{ $group['kuota_pembimbing'] > 0 ? $group['kuota_pembimbing'] : '' }}">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    @php
                                                                        $currentCount = (int) $item->siswa_count;
                                                                        $groupQuota = (int) $group['kuota_pembimbing'];
                                                                        $remainingSlots =
                                                                            $groupQuota > 0
                                                                                ? max(0, $groupQuota - $currentCount)
                                                                                : null;
                                                                    @endphp
                                                                    <div class="alert alert-light border mb-3 py-2"
                                                                        role="alert">
                                                                        <strong>Kapasitas Kelompok:</strong>
                                                                        <span id="slot_info_{{ $item->id }}"
                                                                            class="badge {{ is_null($remainingSlots) ? 'bg-secondary' : ($remainingSlots > 0 ? 'bg-success' : 'bg-danger') }} ml-1">
                                                                            {{ $currentCount }}/{{ $groupQuota > 0 ? $groupQuota : '?' }}
                                                                            @if (!is_null($remainingSlots))
                                                                                (sisa {{ $remainingSlots }})
                                                                            @endif
                                                                        </span>
                                                                    </div>

                                                                    <div class="form-group">
                                                                        <label>Pilih Pembimbing Tambahan</label>
                                                                        <select name="pembimbing_id" class="form-control">
                                                                            <option value="">Tetap gunakan pembimbing
                                                                                saat
                                                                                ini</option>
                                                                            @foreach ($pembimbingAssignable as $opsiPembimbing)
                                                                                <option value="{{ $opsiPembimbing->id }}">
                                                                                    {{ $opsiPembimbing->nama_pembimbing }}
                                                                                    ({{ $opsiPembimbing->jenis_guru === 'guru_produktif' ? 'Guru Produktif' : 'Adaptif Normatif' }})
                                                                                    -
                                                                                    @if (!is_null($opsiPembimbing->sisa_kuota))
                                                                                        sisa
                                                                                        {{ (int) $opsiPembimbing->sisa_kuota }}
                                                                                    @else
                                                                                        kuota belum diatur
                                                                                    @endif
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <small class="text-muted">Satu kelompok hanya satu
                                                                            pembimbing.</small>
                                                                    </div>

                                                                    <div class="form-group mb-0">
                                                                        <label>Pilih Siswa Tambahan (sesuai kuota pembimbing
                                                                            kelompok)</label>
                                                                        <select name="siswa_ids[]"
                                                                            class="form-control select-siswa-tambahan"
                                                                            size="8" multiple
                                                                            data-group-id="{{ $item->id }}"
                                                                            data-current-count="{{ $currentCount }}">
                                                                            @foreach ($siswa as $opsiSiswa)
                                                                                <option value="{{ $opsiSiswa->id }}">
                                                                                    {{ $opsiSiswa->nama_siswa }}
                                                                                    @if ($opsiSiswa->kelas)
                                                                                        -
                                                                                        {{ $opsiSiswa->kelas->nama_kelas }}
                                                                                    @endif
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        <small class="text-muted">Daftar siswa ini hanya
                                                                            siswa
                                                                            yang belum punya kelompok.</small>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-warning">Simpan
                                                                        Tambahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal fade" id="modalKeluarkanSiswa{{ $item->id }}"
                                                    tabindex="-1" role="dialog"
                                                    aria-labelledby="modalKeluarkanSiswaLabel{{ $item->id }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-lg" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="modalKeluarkanSiswaLabel{{ $item->id }}">
                                                                    Keluarkan Siswa - {{ $item->nama_kelompok }}
                                                                </h5>
                                                                <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <form
                                                                action="{{ route('kelompok-bimbingan.remove-anggota', $item->id) }}"
                                                                method="POST" class="formRemoveAnggota">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="form-group mb-0">
                                                                        <label>Pilih Siswa yang Akan Dikeluarkan</label>
                                                                        <select name="siswa_ids[]" class="form-control"
                                                                            size="10" multiple required>
                                                                            @forelse ($item->siswa as $anggota)
                                                                                <option value="{{ $anggota->id }}">
                                                                                    {{ $anggota->nama_siswa }}
                                                                                    @if ($anggota->kelas)
                                                                                        - {{ $anggota->kelas->nama_kelas }}
                                                                                    @endif
                                                                                </option>
                                                                            @empty
                                                                                <option value="" disabled>Tidak ada
                                                                                    siswa di
                                                                                    kelompok ini.</option>
                                                                            @endforelse
                                                                        </select>
                                                                        <small class="text-muted">Gunakan Ctrl/Cmd untuk
                                                                            pilih
                                                                            lebih dari satu siswa.</small>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-dismiss="modal">Batal</button>
                                                                    <button type="submit"
                                                                        class="btn btn-outline-danger">Keluarkan
                                                                        Siswa</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="kelompok-grid-actions-placeholder-text">Aksi kelompok</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Belum ada kelompok
                                            bimbingan.</td>
                                    </tr>
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
                                                @if ($item->pembimbings->isNotEmpty())
                                                    - {{ $item->pembimbings->pluck('nama_pembimbing')->join(', ') }}
                                                @elseif($item->pembimbing)
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
                        <div class="ml-auto">
                            <a href="{{ route('kelompok-bimbingan.export-excel', request()->only(['kelompok_id', 'pembimbing_id', 'keyword'])) }}"
                                class="btn btn-sm btn-success">Export Excel</a>
                            <a href="{{ route('kelompok-bimbingan.export-pdf', request()->only(['kelompok_id', 'pembimbing_id', 'keyword'])) }}"
                                class="btn btn-sm btn-danger">Export PDF</a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="kelompokTablePembimbing" class="table table-bordered table-striped table-sm w-100"
                            style="width: 100%;">
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
                                                    @if ($item->pembimbings->isNotEmpty())
                                                        <br><small
                                                            class="text-muted">{{ $item->pembimbings->pluck('nama_pembimbing')->join(', ') }}</small>
                                                    @elseif($item->pembimbing)
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

            .manual-quota-status {
                display: inline-block;
                margin-left: 8px;
                padding: 3px 10px;
                border-radius: 999px;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: 0.2px;
                line-height: 1.4;
            }

            .manual-quota-status-good {
                background-color: #d1fae5;
                color: #065f46;
            }

            .manual-quota-status-warn {
                background-color: #fef3c7;
                color: #92400e;
            }

            .manual-quota-status-bad {
                background-color: #fee2e2;
                color: #991b1b;
            }

            .manual-quota-status-neutral {
                background-color: #e5e7eb;
                color: #374151;
            }

            .auto-action-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: stretch;
            }

            .auto-action-form {
                flex: 1 1 180px;
                margin: 0;
            }

            .auto-action-btn {
                width: 100%;
                min-height: 42px;
                font-weight: 600;
                white-space: normal;
            }

            .kelompok-grid-table thead th {
                background: #eef2f7;
                color: #111827;
                font-weight: 700;
                text-align: center;
                vertical-align: middle;
                border-color: #9ca3af;
                white-space: nowrap;
                font-size: 12px;
                padding: 8px 6px;
            }

            .kelompok-grid-table td {
                border-color: #9ca3af;
                vertical-align: top;
                font-size: 12px;
                line-height: 1.3;
                padding: 6px;
            }

            .kelompok-grid-table,
            #kelompokTablePembimbing,
            .dataTables_wrapper,
            .dataTables_scroll,
            .dataTables_scrollHead,
            .dataTables_scrollHeadInner,
            .dataTables_scrollBody {
                width: 100% !important;
            }

            .kelompok-grid-table .kelompok-grid-maincell {
                background: #fbfdff;
            }

            .kelompok-grid-table .kelompok-grid-row--start-visible td {
                border-top: 2px solid #94a3b8;
            }

            .kelompok-grid-table .kelompok-grid-row--continued-visible .kelompok-grid-repeatable-cell {
                border-top-color: transparent;
                padding-top: 0;
                padding-bottom: 0;
            }

            .kelompok-grid-table .kelompok-grid-row--continued-visible .kelompok-grid-repeatable-cell {
                color: transparent;
                text-shadow: none;
            }

            .kelompok-grid-table .kelompok-grid-number,
            .kelompok-grid-table .kelompok-grid-total {
                font-weight: 700;
                font-size: 12px;
            }

            .kelompok-grid-table .kelompok-grid-groupname,
            .kelompok-grid-table .kelompok-grid-mentor,
            .kelompok-grid-table .kelompok-grid-class {
                font-size: 12px;
                line-height: 1.3;
            }

            .kelompok-grid-table .kelompok-grid-members {
                min-width: 220px;
                padding-top: 0;
                padding-bottom: 0;
            }

            .kelompok-grid-table .kelompok-grid-member-item {
                padding: 6px 5px;
                border-bottom: 1px solid #d1d5db;
                font-size: 12px;
                line-height: 1.3;
            }

            .kelompok-grid-table .kelompok-grid-member-item:last-child {
                border-bottom: 0;
            }

            .kelompok-grid-table .kelompok-grid-actions {
                min-width: 145px;
                width: 145px;
            }

            .kelompok-grid-table .kelompok-grid-actions .btn {
                width: 100%;
                font-size: 11px;
                padding: 0.3rem 0.45rem;
                line-height: 1.25;
            }

            .kelompok-grid-table .kelompok-grid-actions-placeholder {
                border-top-color: transparent;
            }

            .kelompok-grid-table .kelompok-grid-actions--merged {
                vertical-align: middle;
                background: #fbfdff;
            }

            .kelompok-grid-table .kelompok-grid-actions--hidden {
                display: none;
            }

            .kelompok-grid-table .kelompok-grid-actions-placeholder-text {
                visibility: hidden;
                display: inline-block;
                line-height: 0;
            }

            .kelompok-grid-table .kelompok-grid-row--continued-visible .kelompok-grid-actions-placeholder {
                padding-top: 0;
                padding-bottom: 0;
            }

            .kelompok-grid-table tbody tr:hover td {
                background: #f8fbff;
            }

            .kelompok-grid-table tbody tr.kelompok-grid-row--continued-visible:hover .kelompok-grid-repeatable-cell {
                color: transparent;
            }

            .kelompok-grid-cardheader {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }

            .kelompok-grid-header-actions {
                gap: 8px;
            }

            .kelompok-grid-sortbar {
                width: 220px;
                max-width: 100%;
            }

            .kelompok-grid-statusbar {
                gap: 8px;
            }

            @media (max-width: 767.98px) {
                .auto-action-form {
                    flex-basis: 100%;
                }

                .kelompok-grid-cardheader {
                    align-items: stretch;
                }

                .kelompok-grid-header-actions {
                    width: 100%;
                }

                .kelompok-grid-sortbar {
                    width: 100%;
                    margin-bottom: 8px;
                }
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

        function showGenerateLoading(titleText) {
            Swal.fire({
                title: titleText,
                text: 'Mohon tunggu, proses sedang berjalan...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        $(function() {
            if (!$.fn.DataTable) {
                console.error('DataTables library gagal dimuat.');
                return;
            }

            $('[data-toggle="tooltip"]').tooltip();

            @if ($canManageKelompok)
                if ($.fn.DataTable.isDataTable('#kelompokTable')) {
                    $('#kelompokTable').DataTable().destroy();
                }

                const $kelompokSort = $('#kelompokTableSort');
                const $kelompokTableInfo = $('#kelompokTableInfo');
                const kelompokTable = $('#kelompokTable').DataTable({
                    pageLength: 10,
                    lengthChange: true,
                    ordering: true,
                    searching: true,
                    responsive: true,
                    autoWidth: false,
                    order: [
                        [0, 'asc'],
                        [1, 'asc'],
                        [5, 'asc']
                    ],
                    columnDefs: [{
                            targets: 7,
                            searchable: false,
                            orderable: false
                        },
                        {
                            targets: [0, 1, 2, 3, 4],
                            responsivePriority: 1
                        },
                        {
                            targets: [5, 6],
                            responsivePriority: 2
                        }
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: 'Belum ada kelompok bimbingan.'
                    },
                    drawCallback: function() {
                        const api = this.api();
                        const info = api.page.info();
                        const searchValue = String(api.search() || '').trim();
                        let previousGroupIndex = null;
                        const visibleGroupIndexes = new Set();
                        const $currentRows = $(api.rows({
                            page: 'current'
                        }).nodes());

                        $currentRows.each(function() {
                            const $row = $(this);
                            const groupIndex = String($row.data('group-index') ?? '');
                            const isContinued = previousGroupIndex !== null &&
                                previousGroupIndex === groupIndex;

                            $row.removeClass(
                                'kelompok-grid-row--start-visible kelompok-grid-row--continued-visible'
                            );
                            $row.addClass(isContinued ? 'kelompok-grid-row--continued-visible' :
                                'kelompok-grid-row--start-visible');

                            previousGroupIndex = groupIndex;
                            if (groupIndex !== '') {
                                visibleGroupIndexes.add(groupIndex);
                            }
                        });

                        $currentRows.each(function() {
                            $(this).find('td.kelompok-grid-actions')
                                .removeClass(
                                    'kelompok-grid-actions--hidden kelompok-grid-actions--merged'
                                )
                                .removeAttr('rowspan');
                        });

                        let groupStartRow = null;
                        let currentGroupIndex = null;
                        let currentGroupLength = 0;

                        function mergeActionColumnForGroup() {
                            if (!groupStartRow || currentGroupLength <= 0) {
                                return;
                            }

                            const $startActionCell = $(groupStartRow).find('td.kelompok-grid-actions')
                                .first();
                            if (!$startActionCell.length) {
                                return;
                            }

                            $startActionCell.attr('rowspan', currentGroupLength).addClass(
                                'kelompok-grid-actions--merged');

                            if (currentGroupLength <= 1) {
                                return;
                            }

                            let seen = 0;
                            $currentRows.each(function() {
                                const $row = $(this);
                                const rowGroupIndex = String($row.data('group-index') ?? '');

                                if (rowGroupIndex !== currentGroupIndex) {
                                    return;
                                }

                                seen++;
                                if (seen === 1) {
                                    return;
                                }

                                $row.find('td.kelompok-grid-actions').first().addClass(
                                    'kelompok-grid-actions--hidden');
                            });
                        }

                        $currentRows.each(function() {
                            const rowGroupIndex = String($(this).data('group-index') ?? '');

                            if (currentGroupIndex === null || rowGroupIndex !==
                                currentGroupIndex) {
                                mergeActionColumnForGroup();
                                groupStartRow = this;
                                currentGroupIndex = rowGroupIndex;
                                currentGroupLength = 1;
                            } else {
                                currentGroupLength++;
                            }
                        });

                        mergeActionColumnForGroup();

                        if (info.recordsDisplay === 0) {
                            $kelompokTableInfo.text(
                                'Tidak ada kelompok yang cocok dengan filter saat ini.');
                        } else if (searchValue === '') {
                            $kelompokTableInfo.text('Menampilkan ' + visibleGroupIndexes.size +
                                ' kelompok (' + info.recordsDisplay + ' baris).');
                        } else {
                            $kelompokTableInfo.text('Ditemukan ' + visibleGroupIndexes.size +
                                ' kelompok (' + info.recordsDisplay +
                                ' baris) untuk pencarian DataTables.');
                        }
                    }
                });

                $kelompokSort.on('change', function() {
                    const sortValue = String($(this).val() || '0-asc');
                    const sortParts = sortValue.split('-');
                    const columnIndex = Number(sortParts[0] || 0);
                    const direction = sortParts[1] === 'desc' ? 'desc' : 'asc';

                    kelompokTable.order([
                        [columnIndex, direction],
                        [1, 'asc'],
                        [5, 'asc']
                    ]).draw();
                });

                kelompokTable.columns.adjust();
                if (kelompokTable.responsive) {
                    kelompokTable.responsive.recalc();
                }
                kelompokTable.draw();
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

                const kelompokTablePembimbing = $('#kelompokTablePembimbing').DataTable();
                kelompokTablePembimbing.columns.adjust();
                if (kelompokTablePembimbing.responsive) {
                    kelompokTablePembimbing.responsive.recalc();
                }
            @endif

            @if ($canManageKelompok)
                const pembimbingMeta = @json($pembimbingMeta ?? []);
                const siswaOptionSource = $('#siswa_ids option').map(function() {
                    const $opt = $(this);

                    return {
                        value: String($opt.val()),
                        text: $opt.text(),
                        kelasId: String($opt.data('kelas-id') || ''),
                        kelasNama: String($opt.data('kelas-nama') || ''),
                        jurusanId: String($opt.data('jurusan-id') || '')
                    };
                }).get();

                const kelasOptionSource = Array.from(new Map(
                    siswaOptionSource
                    .filter(function(item) {
                        return String(item.kelasId || '') !== '';
                    })
                    .map(function(item) {
                        const nama = String(item.kelasNama || '').trim();
                        return [String(item.kelasId), {
                            id: String(item.kelasId),
                            nama: nama || ('Kelas ' + String(item.kelasId))
                        }];
                    })
                ).values()).sort(function(a, b) {
                    return a.nama.localeCompare(b.nama, 'id');
                });

                function getSelectedMentorMeta() {
                    const mentorId = String($('#pembimbing_id').val() || '');
                    if (!mentorId) {
                        return {
                            mentorId: '',
                            meta: null
                        };
                    }

                    return {
                        mentorId,
                        meta: pembimbingMeta[mentorId] || null
                    };
                }

                function renderKelasFilterOptions(meta, hasMentorSelected) {
                    const $kelasFilter = $('#filter_kelas_manual');
                    const currentValue = String($kelasFilter.val() || '');

                    if (!hasMentorSelected) {
                        $kelasFilter.prop('disabled', true).html('<option value="">Pilih pembimbing dulu</option>');
                        return;
                    }

                    const allowAll = !!(meta && meta.all);

                    let kelasOptions = [];
                    if (allowAll) {
                        kelasOptions = kelasOptionSource;
                    } else {
                        const allowedIds = new Set((meta?.kelas_ids || []).map(String));

                        kelasOptions = kelasOptionSource.filter(function(item) {
                            return allowedIds.has(item.id);
                        });
                    }

                    const htmlOptions = ['<option value="">Semua kelas sesuai pembimbing</option>']
                        .concat(kelasOptions.map(function(item) {
                            const selected = currentValue === item.id ? ' selected' : '';
                            return '<option value="' + item.id + '"' + selected + '>' + item.nama +
                                '</option>';
                        }))
                        .join('');

                    $kelasFilter.prop('disabled', false).html(htmlOptions);

                    if (currentValue && !kelasOptions.some(function(item) {
                            return item.id === currentValue;
                        })) {
                        $kelasFilter.val('');
                    }
                }

                function renderSiswaOptionsByPembimbing() {
                    const mentorSelection = getSelectedMentorMeta();
                    const selectedMentorId = mentorSelection.mentorId;
                    const selectedMeta = mentorSelection.meta;
                    const keyword = String($('#filter_siswa_manual').val() || '').trim().toLowerCase();
                    const selectedKelasId = String($('#filter_kelas_manual').val() || '');
                    const selectedValues = new Set($('#siswa_ids').val() || []);
                    const hasMentorSelected = !!selectedMentorId;

                    renderKelasFilterOptions(selectedMeta, hasMentorSelected);

                    if (!hasMentorSelected) {
                        $('#info_jurusan_pembimbing').val('-');
                        $('#info_kelas_pembimbing').val('-');
                        $('#info_kuota_pembimbing').val('-');
                    } else {
                        $('#info_jurusan_pembimbing').val(String(selectedMeta?.jurusan_nama || '-'));

                        const allowAllByMentor = !!(selectedMeta && selectedMeta.all);
                        if (allowAllByMentor) {
                            $('#info_kelas_pembimbing').val('Semua Kelas');
                        } else {
                            const kelasNames = Array.from(new Set((selectedMeta?.kelas_names || []).map(String)));
                            const kelasLabel = kelasNames.length ? kelasNames.join(', ') : '-';
                            $('#info_kelas_pembimbing').val(kelasLabel);
                        }

                        const kuotaSiswa = Number(selectedMeta?.kuota_siswa || 0);
                        if (kuotaSiswa > 0) {
                            $('#info_kuota_pembimbing').val(String(kuotaSiswa) + ' siswa');
                        } else {
                            $('#info_kuota_pembimbing').val('Belum diatur');
                        }
                    }

                    const allowAll = !hasMentorSelected || !!(selectedMeta && selectedMeta.all);
                    const allowedKelas = new Set((selectedMeta?.kelas_ids || []).map(String));

                    const filtered = siswaOptionSource.filter(function(item) {
                        const inAllowedClass = allowAll || allowedKelas.has(String(item.kelasId || ''));
                        if (!inAllowedClass) {
                            return false;
                        }

                        if (selectedKelasId && String(item.kelasId || '') !== selectedKelasId) {
                            return false;
                        }

                        if (!keyword) {
                            return true;
                        }

                        if (selectedValues.has(item.value)) {
                            // Keep selected options visible even when search text changes.
                            return true;
                        }

                        const haystack = String(item.text || '').toLowerCase();
                        return haystack.includes(keyword);
                    });

                    const html = filtered.map(function(item) {
                        const selected = selectedValues.has(item.value) ? ' selected' : '';
                        return '<option value="' + item.value + '" data-kelas-id="' + item.kelasId +
                            '" data-jurusan-id="' + item.jurusanId + '"' + selected + '>' + item.text +
                            '</option>';
                    }).join('');

                    $('#siswa_ids').html(html);
                    updateSiswaTerpilihInfo();
                }

                function updateSiswaTerpilihInfo() {
                    const mentorSelection = getSelectedMentorMeta();
                    const selectedMentorId = mentorSelection.mentorId;
                    const selectedMeta = mentorSelection.meta;
                    const jumlahTerpilih = ($('#siswa_ids').val() || []).length;
                    const kuotaSiswa = Number(selectedMeta?.kuota_siswa || 0);
                    const batasAtas = kuotaSiswa > 0 ? kuotaSiswa : 0;

                    let info = String(jumlahTerpilih) + ' siswa';
                    if (batasAtas > 0) {
                        info += ' / maks ' + String(batasAtas);
                    } else {
                        info += ' / kuota belum diatur';
                    }

                    $('#info_terpilih_siswa').val(info);
                    updateSiswaRuleLabel(kuotaSiswa, !!selectedMentorId);
                }

                function updateSiswaRuleLabel(kuotaSiswa, hasMentorSelected) {
                    if (!hasMentorSelected) {
                        $('#label_siswa_ids').text('Pilih Siswa (sesuai kuota pembimbing)');
                        setSiswaRuleStatus('neutral', 'Pilih pembimbing');
                        return;
                    }

                    if (kuotaSiswa > 0) {
                        $('#label_siswa_ids').text('Pilih Siswa (maks ' + String(kuotaSiswa) + ' siswa)');
                        setSiswaRuleStatus('good', 'Kuota tersedia');
                        return;
                    }

                    $('#label_siswa_ids').text('Pilih Siswa (kuota pembimbing belum diatur)');
                    setSiswaRuleStatus('warn', 'Kuota pembimbing belum diatur');
                }

                function setSiswaRuleStatus(type, text) {
                    const $status = $('#label_siswa_status');
                    $status.removeClass(
                        'manual-quota-status-good manual-quota-status-warn manual-quota-status-bad manual-quota-status-neutral'
                    );

                    if (type === 'good') {
                        $status.addClass('manual-quota-status-good');
                    } else if (type === 'warn') {
                        $status.addClass('manual-quota-status-warn');
                    } else if (type === 'bad') {
                        $status.addClass('manual-quota-status-bad');
                    } else {
                        $status.addClass('manual-quota-status-neutral');
                    }

                    $status.text(text);
                }

                $('#pembimbing_id').on('change', function() {
                    renderSiswaOptionsByPembimbing();
                });

                $('#filter_siswa_manual').on('input', function() {
                    renderSiswaOptionsByPembimbing();
                });

                $('#filter_kelas_manual').on('change', function() {
                    renderSiswaOptionsByPembimbing();
                });

                $('#siswa_ids').on('change', function() {
                    // Re-render to keep filtering consistent while preserving current selections.
                    renderSiswaOptionsByPembimbing();
                });

                function updateTambahAnggotaSlots($select) {
                    const groupId = String($select.data('group-id') || '');
                    const currentCount = Number($select.data('current-count') || 0);
                    const groupMax = Number($select.closest('form').data('group-max') || 0);
                    const selectedCount = ($select.val() || []).length;
                    const totalAfter = currentCount + selectedCount;
                    const remaining = groupMax > 0 ? Math.max(0, groupMax - totalAfter) : 0;
                    const $info = $('#slot_info_' + groupId);

                    if ($info.length) {
                        $info
                            .removeClass('bg-success bg-warning bg-danger bg-secondary')
                            .text(groupMax > 0 ? (String(totalAfter) + '/' + String(groupMax) + ' (sisa ' +
                                String(remaining) + ')') : (String(totalAfter) + '/?'));

                        if (groupMax <= 0) {
                            $info.addClass('bg-secondary');
                        } else if (totalAfter > groupMax) {
                            $info.addClass('bg-danger');
                        } else if (remaining <= 2) {
                            $info.addClass('bg-warning');
                        } else {
                            $info.addClass('bg-success');
                        }
                    }
                }

                $('.select-siswa-tambahan').on('change', function() {
                    updateTambahAnggotaSlots($(this));
                });

                $('.formTambahAnggota').on('submit', function(e) {
                    const $form = $(this);
                    const currentCount = Number($form.data('current-count') || 0);
                    const groupMax = Number($form.data('group-max') || 0);
                    const selectedCount = ($form.find('.select-siswa-tambahan').val() || []).length;
                    const totalAfter = currentCount + selectedCount;

                    if (groupMax > 0 && totalAfter > groupMax) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Melebihi kapasitas kelompok',
                            text: 'Total siswa setelah ditambah menjadi ' + String(totalAfter) +
                                '. Maksimal ' + String(groupMax) + ' siswa sesuai kuota pembimbing.'
                        });
                    }
                });

                $('#formManualKelompok').on('submit', function(e) {
                    const mentorSelection = getSelectedMentorMeta();
                    const selectedMentorId = mentorSelection.mentorId;
                    const selectedMeta = mentorSelection.meta;
                    const jumlahTerpilih = ($('#siswa_ids').val() || []).length;
                    const kuotaSiswa = Number(selectedMeta?.kuota_siswa || 0);

                    if (!selectedMentorId) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pembimbing belum dipilih',
                            text: 'Pilih satu pembimbing.'
                        });
                        return;
                    }

                    if (kuotaSiswa > 0 && kuotaSiswa < 8) {
                        // Informational only: allow saving manual even when mentor quota is still below 8.
                    }

                    if (kuotaSiswa <= 0) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Kuota pembimbing belum diatur',
                            text: 'Isi kuota siswa pada data pembimbing terlebih dahulu sebelum menyimpan kelompok.'
                        });
                        return;
                    }

                    const batasAtas = kuotaSiswa;
                    if (jumlahTerpilih > batasAtas) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Jumlah siswa melebihi kuota pembimbing',
                            text: 'Maksimal siswa untuk pembimbing terpilih adalah ' + String(
                                batasAtas) + ' siswa.'
                        });
                        return;
                    }

                    // Tidak ada validasi minimum 8 pada simpan manual.
                });

                renderSiswaOptionsByPembimbing();

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
                            showGenerateLoading('Sedang generate kelompok otomatis');
                            form.trigger('submit');
                        }
                    });
                });

                $('#btnGenerateKelompokKosong').on('click', function(e) {
                    e.preventDefault();
                    const form = $('#formGenerateKelompokKosong');

                    Swal.fire({
                        title: 'Generate kelompok kosong?',
                        text: 'Kelompok lama akan diganti, lalu dibuat kelompok kosong berdasarkan total siswa (8-13 siswa per kelompok).',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, generate',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            showGenerateLoading('Sedang generate kelompok kosong');
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

                $('.formRemoveAnggota').on('submit', function(e) {
                    const $form = $(this);
                    const selectedCount = ($form.find('select[name="siswa_ids[]"]').val() || []).length;

                    if (selectedCount <= 0) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Siswa belum dipilih',
                            text: 'Pilih minimal satu siswa yang akan dikeluarkan.'
                        });
                        return;
                    }

                    e.preventDefault();
                    const form = this;
                    Swal.fire({
                        title: 'Keluarkan siswa terpilih?',
                        text: 'Siswa akan dikeluarkan dari kelompok ini.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, keluarkan',
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

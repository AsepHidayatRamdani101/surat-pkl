@extends('adminlte::page')

@section('title', 'Kelompok Bimbingan')

@section('content')
    <div class="container pt-4">
        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Penentuan Otomatis</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">Aturan otomatis:</p>
                        <ul class="mb-3">
                            <li>Dahulukan guru produktif sesuai jurusan siswa.</li>
                            <li>Satu kelompok minimal 8 siswa dan maksimal 12 siswa.</li>
                        </ul>

                        <form action="{{ route('kelompok-bimbingan.generate-automatic') }}" method="POST" class="d-inline"
                            id="formGenerateOtomatis">
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
                <div class="card h-100">
                    <div class="card-header">
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
                                <small class="text-muted">Gunakan Ctrl atau Cmd untuk pilih lebih dari satu siswa.</small>
                            </div>

                            <button type="submit" class="btn btn-success">Simpan Kelompok Manual</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Kelompok Bimbingan</h5>
            </div>
            <div class="card-body table-responsive">
                <table id="kelompokTable" class="table table-bordered table-striped">
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
                                    <span class="badge {{ $item->metode === 'otomatis' ? 'bg-info' : 'bg-secondary' }}">
                                        {{ ucfirst($item->metode) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $item->pembimbing->nama_pembimbing ?? '-' }}
                                    @if ($item->pembimbing && $item->pembimbing->jenis_guru === 'guru_produktif')
                                        <br>
                                        <small class="text-muted">Produktif -
                                            {{ $item->pembimbing->jurusan->nama_jurusan ?? '-' }}</small>
                                    @elseif($item->pembimbing)
                                        <br>
                                        <small class="text-muted">Adaptif Normatif</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($kuotaPembimbing > 0)
                                        <span
                                            class="badge {{ $item->siswa_count > $kuotaPembimbing ? 'bg-danger' : 'bg-success' }}">
                                            {{ $item->siswa_count }} / {{ $kuotaPembimbing }}
                                        </span>
                                        <br>
                                        <small class="text-muted">Terisi / Kuota Pembimbing</small>
                                    @else
                                        <span class="badge bg-secondary">{{ $item->siswa_count }}</span>
                                        <br>
                                        <small class="text-muted">Kuota pembimbing belum diatur</small>
                                    @endif
                                </td>
                                <td>
                                    @foreach ($item->siswa as $anggota)
                                        <div>{{ $anggota->nama_siswa }} - {{ $anggota->kelas->nama_kelas ?? '-' }}</div>
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
                            <tr>
                                <td colspan="7" class="text-center">Belum ada kelompok bimbingan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('js')
    @include('sweetalert::alert')

    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
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

        $(document).ready(function() {
            $('#kelompokTable').DataTable({
                pageLength: 10,
                order: [
                    [0, 'asc']
                ]
            });

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
        });
    </script>
@endsection

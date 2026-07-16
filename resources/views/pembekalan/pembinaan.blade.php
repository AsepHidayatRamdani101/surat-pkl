@extends('adminlte::page')

@section('title', 'Pembinaan Peserta Pembekalan PKL')

@section('content-header')
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0">Pembinaan Peserta Pembekalan PKL</h1>
            <button type="button" class="btn btn-sm btn-primary" id="btnAddPembinaan" data-toggle="modal"
                data-target="#pembinaanModal">
                <i class="fas fa-plus mr-1"></i> Tambah Pembinaan
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid pt-2">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('pembekalan.pembinaan') }}">
                    <div class="form-row align-items-end">
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Dari</label>
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                                value="{{ $filters['tanggal_awal'] }}">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="mb-1">Sampai</label>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                                value="{{ $filters['tanggal_akhir'] }}">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="mb-1">Pembimbing</label>
                            <select name="pembimbing_id" class="form-control form-control-sm"
                                {{ $isPembimbingOnly ? 'disabled' : '' }}>
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
                            <label class="mb-1">Peserta</label>
                            <select name="siswa_id" class="form-control form-control-sm">
                                <option value="">Semua Peserta</option>
                                @foreach ($siswaOptions as $siswa)
                                    <option value="{{ $siswa->id }}"
                                        {{ (string) $filters['siswa_id'] === (string) $siswa->id ? 'selected' : '' }}>
                                        {{ $siswa->nama_siswa }} ({{ $siswa->nis }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 d-flex">
                            <button type="submit" class="btn btn-sm btn-primary mr-1 w-100">Filter</button>
                            <a href="{{ route('pembekalan.pembinaan') }}"
                                class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                        </div>
                    </div>
                    <div class="form-row mt-1">
                        <div class="col-md-12">
                            <input type="text" name="keyword" class="form-control form-control-sm"
                                placeholder="Cari peserta, pembimbing, kronologi, atau catatan"
                                value="{{ $filters['keyword'] }}">
                        </div>
                    </div>
                    @if ($isPembimbingOnly)
                        <small class="text-muted">Pembimbing terkunci sesuai akun login.</small>
                    @endif
                </form>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#pembinaanModal">
                        <i class="fas fa-plus mr-1"></i> Tambah Pembinaan
                    </button>
                    <a href="{{ route('pembekalan.pembinaan.export-pdf', array_merge(request()->query(), ['stream' => 1])) }}"
                        class="btn btn-sm btn-primary" target="_blank">
                        <i class="fas fa-print mr-1"></i> Print Rekap
                    </a>
                    <a href="{{ route('pembekalan.pembinaan.export-excel', request()->query()) }}"
                        class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </a>
                    <a href="{{ route('pembekalan.pembinaan.export-pdf', request()->query()) }}"
                        class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Pembinaan ({{ $records->count() }} data)</h5>
                {{-- <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#pembinaanModal">
                    <i class="fas fa-plus mr-1"></i> Tambah Pembinaan
                </button> --}}
            </div>
            <div class="card-body table-responsive">
                <table id="pembinaanTable" class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width: 95px;">Tanggal</th>
                            <th style="width: 220px;">Peserta</th>
                            <th style="width: 200px;">Pembimbing</th>
                            <th style="width: 150px;">Tempat</th>
                            <th style="width: 160px;">Tingkat</th>
                            <th>Kronologi</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $item)
                            @php
                                $selectedJenis = collect($item->jenis_pembinaan ?? []);
                                $selectedTindakan = collect($item->tindakan_pembinaan ?? []);
                            @endphp
                            <tr>
                                <td>{{ optional($item->tanggal_formulir)->format('d-m-Y') }}</td>
                                <td>
                                    {{ $item->siswa->nama_siswa ?? '-' }}<br>
                                    <small class="text-muted">NIS: {{ $item->siswa->nis ?? '-' }}</small>
                                </td>
                                <td>{{ $item->pembimbing->nama_pembimbing ?? '-' }}</td>
                                <td>{{ $item->tempat ?? '-' }}</td>
                                <td>{{ $tingkatPembinaanOptions[$item->tingkat_pembinaan]['label'] ?? '-' }}</td>
                                <td>
                                    <div>{{ \Illuminate\Support\Str::limit($item->kronologi ?? '-', 90) }}</div>
                                    <small class="text-muted">Pelanggaran: {{ $selectedJenis->count() }} | Tindakan:
                                        {{ $selectedTindakan->count() }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('pembekalan.pembinaan.print', $item->id) }}" target="_blank"
                                        class="btn btn-xs btn-outline-primary mb-1">Cetak</a>
                                    <a href="{{ route('pembekalan.pembinaan.pdf', $item->id) }}" target="_blank"
                                        class="btn btn-xs btn-outline-danger mb-1">PDF</a>
                                    <button type="button" class="btn btn-xs btn-warning mb-1 btn-edit-pembinaan"
                                        data-id="{{ $item->id }}" data-siswa_id="{{ $item->siswa_id }}"
                                        data-pembimbing_id="{{ $item->pembimbing_id }}"
                                        data-tanggal_formulir="{{ optional($item->tanggal_formulir)->format('Y-m-d') }}"
                                        data-waktu_formulir="{{ $item->waktu_formulir }}"
                                        data-tempat="{{ e($item->tempat ?? '') }}"
                                        data-kronologi="{{ e($item->kronologi ?? '') }}"
                                        data-komitmen_peserta="{{ e($item->komitmen_peserta ?? '') }}"
                                        data-catatan_guru="{{ e($item->catatan_guru ?? '') }}"
                                        data-jenis_pembinaan='@json($item->jenis_pembinaan ?? [])'
                                        data-jenis_pembinaan_lainnya="{{ e($item->jenis_pembinaan_lainnya ?? '') }}"
                                        data-tindakan_pembinaan='@json($item->tindakan_pembinaan ?? [])'
                                        data-tindakan_pembinaan_lainnya="{{ e($item->tindakan_pembinaan_lainnya ?? '') }}"
                                        data-hasil_pembinaan='@json($item->hasil_pembinaan ?? [])'
                                        data-tingkat_pembinaan="{{ $item->tingkat_pembinaan }}">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('pembekalan.pembinaan.destroy', $item->id) }}"
                                        class="d-inline form-delete-pembinaan">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger mb-1">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="mb-2">Belum ada data pembinaan.</div>
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal"
                                        data-target="#pembinaanModal">
                                        <i class="fas fa-plus mr-1"></i> Tambah Pembinaan
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pembinaanModal" tabindex="-1" role="dialog" aria-labelledby="pembinaanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pembinaanModalLabel">Tambah Pembinaan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('pembekalan.pembinaan.store') }}" id="pembinaanForm">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="_method" id="pembinaanMethod" value="POST">
                        <div class="form-row">
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Tanggal</label>
                                <input type="date" name="tanggal_formulir" class="form-control form-control-sm"
                                    value="{{ old('tanggal_formulir', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="mb-1">Waktu</label>
                                <input type="time" name="waktu_formulir" class="form-control form-control-sm"
                                    value="{{ old('waktu_formulir') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="mb-1">Tempat</label>
                                <input type="text" name="tempat" class="form-control form-control-sm"
                                    value="{{ old('tempat') }}" placeholder="Tempat kegiatan">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="mb-1">Guru Pembimbing</label>
                                @if ($isPembimbingOnly)
                                    <input type="hidden" name="pembimbing_id"
                                        value="{{ old('pembimbing_id', $filters['pembimbing_id']) }}">
                                @endif
                                <select name="pembimbing_id" class="form-control form-control-sm"
                                    {{ $isPembimbingOnly ? 'disabled' : 'required' }}>
                                    <option value="">Pilih Pembimbing</option>
                                    @foreach ($pembimbingOptions as $pembimbing)
                                        <option value="{{ $pembimbing->id }}"
                                            {{ (string) old('pembimbing_id', $filters['pembimbing_id']) === (string) $pembimbing->id ? 'selected' : '' }}>
                                            {{ $pembimbing->nama_pembimbing }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8 mb-2">
                                <label class="mb-1">Peserta</label>
                                <select name="siswa_id" class="form-control form-control-sm" required>
                                    <option value="">Pilih Peserta</option>
                                    @foreach ($siswaOptions as $siswa)
                                        @php
                                            $kelompokUtama = $siswa->kelompokBimbingan->firstWhere(
                                                'pembimbing_id',
                                                '!=',
                                                null,
                                            );
                                            $autoPembimbingId = $kelompokUtama?->pembimbing_id;
                                        @endphp
                                        <option value="{{ $siswa->id }}"
                                            data-pembimbing-id="{{ $autoPembimbingId ?? '' }}"
                                            {{ (string) old('siswa_id') === (string) $siswa->id ? 'selected' : '' }}>
                                            {{ $siswa->nama_siswa }} ({{ $siswa->nis }})
                                            @if ($siswa->kelas)
                                                - {{ $siswa->kelas->nama_kelas }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-2">B. Jenis Pembinaan</h6>
                        <div class="form-row">
                            @foreach ($jenisPembinaanOptions as $key => $label)
                                <div class="col-md-6 mb-1">
                                    <label class="mb-0 font-weight-normal">
                                        <input type="checkbox" name="jenis_pembinaan[]" value="{{ $key }}"
                                            {{ in_array($key, old('jenis_pembinaan', []), true) ? 'checked' : '' }}>
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                            <div class="col-md-12 mt-1">
                                <input type="text" name="jenis_pembinaan_lainnya" class="form-control form-control-sm"
                                    placeholder="Isi jika memilih pelanggaran lainnya"
                                    value="{{ old('jenis_pembinaan_lainnya') }}">
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-2">C. Kronologi Kejadian</h6>
                        <textarea name="kronologi" rows="3" class="form-control form-control-sm"
                            placeholder="Uraikan secara singkat dan objektif">{{ old('kronologi') }}</textarea>

                        <hr>
                        <h6 class="mb-2">D. Tindakan Pembinaan</h6>
                        <div class="form-row">
                            @foreach ($tindakanPembinaanOptions as $key => $label)
                                <div class="col-md-6 mb-1">
                                    <label class="mb-0 font-weight-normal">
                                        <input type="checkbox" name="tindakan_pembinaan[]" value="{{ $key }}"
                                            {{ in_array($key, old('tindakan_pembinaan', []), true) ? 'checked' : '' }}>
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                            <div class="col-md-12 mt-1">
                                <input type="text" name="tindakan_pembinaan_lainnya"
                                    class="form-control form-control-sm" placeholder="Isi jika memilih tindakan lainnya"
                                    value="{{ old('tindakan_pembinaan_lainnya') }}">
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-2">E/F. Komitmen dan Hasil Pembinaan</h6>
                        <textarea name="komitmen_peserta" rows="2" class="form-control form-control-sm mb-2"
                            placeholder="Komitmen peserta">{{ old('komitmen_peserta') }}</textarea>

                        <div class="form-row">
                            @foreach ($hasilPembinaanOptions as $key => $label)
                                <div class="col-md-6 mb-1">
                                    <label class="mb-0 font-weight-normal">
                                        <input type="checkbox" name="hasil_pembinaan[]" value="{{ $key }}"
                                            {{ in_array($key, old('hasil_pembinaan', []), true) ? 'checked' : '' }}>
                                        {{ $label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <div class="form-row mt-2">
                            <div class="col-md-4">
                                <label class="mb-1">Tingkat Pembinaan</label>
                                <select name="tingkat_pembinaan" class="form-control form-control-sm">
                                    <option value="">Pilih Tahap</option>
                                    @foreach ($tingkatPembinaanOptions as $key => $item)
                                        <option value="{{ $key }}"
                                            {{ old('tingkat_pembinaan') === $key ? 'selected' : '' }}>
                                            {{ $item['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="mb-1">Catatan Guru Pembimbing</label>
                                <textarea name="catatan_guru" rows="2" class="form-control form-control-sm"
                                    placeholder="Catatan tambahan guru pembimbing">{{ old('catatan_guru') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="btnSubmitPembinaan">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('plugins.Datatables', true)

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            const modal = $('#pembinaanModal');
            const form = $('#pembinaanForm');
            const methodInput = $('#pembinaanMethod');
            const title = $('#pembinaanModalLabel');
            const submitBtn = $('#btnSubmitPembinaan');
            const siswaSelect = form.find('[name="siswa_id"]');
            const pembimbingSelect = form.find('[name="pembimbing_id"]');
            const updateUrlTemplate = @json(route('pembekalan.pembinaan.update', ['pembinaanPembekalan' => '__ID__']));
            const isPembimbingOnly = @json($isPembimbingOnly);
            const successMessage = @json(session('success'));
            const errorMessages = @json($errors->all());

            if (successMessage) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: successMessage,
                    timer: 1800,
                    showConfirmButton: false
                });
            }

            if (errorMessages.length > 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal menyimpan data',
                    html: errorMessages.join('<br>'),
                });
                modal.modal('show');
            }

            if ($.fn.DataTable && $('#pembinaanTable').length) {
                $('#pembinaanTable').DataTable({
                    pageLength: 10,
                    responsive: true,
                    autoWidth: false,
                    order: [
                        [0, 'desc']
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                        emptyTable: 'Belum ada data pembinaan.'
                    }
                });
            }

            const resetForm = () => {
                form[0].reset();
                form.attr('action', @json(route('pembekalan.pembinaan.store')));
                methodInput.val('POST');
                title.text('Tambah Pembinaan');
                submitBtn.text('Simpan');
                form.find('input[type="checkbox"]').prop('checked', false);
                if (isPembimbingOnly) {
                    const currentPembimbing = @json((string) ($filters['pembimbing_id'] ?? ''));
                    pembimbingSelect.val(currentPembimbing);
                }
            };

            const setValue = (name, value) => {
                form.find('[name="' + name + '"]').val(value || '');
            };

            const setCheckedByArray = (name, values) => {
                form.find('input[name="' + name + '[]"]').prop('checked', false);
                (values || []).forEach((val) => {
                    form.find('input[name="' + name + '[]"][value="' + val + '"]').prop('checked',
                        true);
                });
            };

            const syncPembimbingBySiswa = () => {
                if (isPembimbingOnly) {
                    return;
                }
                const selected = siswaSelect.find('option:selected');
                const pembimbingId = selected.attr('data-pembimbing-id');
                if (pembimbingId) {
                    pembimbingSelect.val(pembimbingId);
                }
            };

            siswaSelect.on('change', syncPembimbingBySiswa);

            $('#btnAddPembinaan').on('click', function() {
                resetForm();
            });

            $(document).on('click', '.btn-edit-pembinaan', function() {
                const btn = $(this);
                resetForm();

                title.text('Edit Pembinaan');
                submitBtn.text('Simpan Perubahan');
                form.attr('action', updateUrlTemplate.replace('__ID__', btn.data('id')));
                methodInput.val('PUT');

                setValue('siswa_id', btn.data('siswa_id'));
                setValue('pembimbing_id', btn.data('pembimbing_id'));
                setValue('tanggal_formulir', btn.data('tanggal_formulir'));
                setValue('waktu_formulir', btn.data('waktu_formulir'));
                setValue('tempat', btn.data('tempat'));
                setValue('kronologi', btn.data('kronologi'));
                setValue('komitmen_peserta', btn.data('komitmen_peserta'));
                setValue('catatan_guru', btn.data('catatan_guru'));
                setValue('jenis_pembinaan_lainnya', btn.data('jenis_pembinaan_lainnya'));
                setValue('tindakan_pembinaan_lainnya', btn.data('tindakan_pembinaan_lainnya'));
                setValue('tingkat_pembinaan', btn.data('tingkat_pembinaan'));

                setCheckedByArray('jenis_pembinaan', btn.data('jenis_pembinaan'));
                setCheckedByArray('tindakan_pembinaan', btn.data('tindakan_pembinaan'));
                setCheckedByArray('hasil_pembinaan', btn.data('hasil_pembinaan'));

                modal.modal('show');
            });

            $(document).on('submit', '.form-delete-pembinaan', function(e) {
                e.preventDefault();
                const currentForm = this;

                Swal.fire({
                    title: 'Hapus data pembinaan?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        currentForm.submit();
                    }
                });
            });

            @if ($errors->any())
                modal.modal('show');
            @endif
        });
    </script>
@endsection

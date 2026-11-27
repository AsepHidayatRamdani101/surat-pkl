@extends('adminlte::page')

@section('title', 'Jadwal Sidang PKL')

@section('content')
    <div class="container-fluid pt-4">
        <div class="card sidang-card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center my-1">
                    <span class="card-title-icon"><i class="fas fa-calendar-alt"></i></span>
                    <div>
                        <h4>
                            @if ($role === 'siswa')
                                Jadwal Bimbingan & Sidang Saya
                            @else
                                Jadwal Bimbingan & Sidang PKL
                            @endif
                        </h4>
                        <small>
                            @if ($role === 'siswa')
                                Jadwal bimbingan laporan dan sidang PKL Anda
                            @elseif ($role === 'pembimbing')
                                Jadwal siswa yang Anda bimbing / uji
                            @else
                            Atur tanggal, waktu, ruangan, dan jeda berdasarkan siswa bimbingan pembekalan
                            @endif
                        </small>
                    </div>
                </div>
                <div class="my-1 d-flex align-items-center">
                    <select class="form-control form-control-sm mr-2" id="filterJenis" style="width: auto;">
                        <option value="">Semua Jenis</option>
                        <option value="bimbingan">Bimbingan Sidang</option>
                        <option value="sidang">Sidang</option>
                    </select>
                    @if ($role === 'panitia')
                        <button class="btn btn-outline-warning btn-sm mr-2" id="btnBukaKunciTerpilih" disabled>
                            <i class="fas fa-unlock mr-1"></i> Buka Kunci Terpilih (<span id="jumlahTerkunci">0</span>)
                        </button>
                    @endif
                    @if (in_array($role, ['panitia', 'pembimbing']))
                        <button class="btn btn-outline-danger btn-sm mr-2" id="btnHapusTerpilih" disabled>
                            <i class="fas fa-trash mr-1"></i> Hapus Terpilih (<span id="jumlahTerpilih">0</span>)
                        </button>
                    @endif
                    @if ($role === 'pembimbing')
                        <button class="btn btn-outline-primary btn-sm mr-2" id="btnAturBimbinganMassal">
                            <i class="fas fa-calendar-plus mr-1"></i> Tambah Bimbingan Massal
                        </button>
                        <button class="btn btn-primary btn-sm" id="btnAturSidang">
                            <i class="fas fa-gavel mr-1"></i> Atur Sidang
                        </button>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="filter-jadwal mb-3">
                    <div class="form-row align-items-end">
                        <div class="col-6 col-md-2 form-group mb-2">
                            <label class="small mb-1" for="filterTanggalDari">Tanggal dari</label>
                            <input type="date" class="form-control form-control-sm" id="filterTanggalDari">
                        </div>
                        <div class="col-6 col-md-2 form-group mb-2">
                            <label class="small mb-1" for="filterTanggalSampai">Sampai</label>
                            <input type="date" class="form-control form-control-sm" id="filterTanggalSampai">
                        </div>
                        @if ($bisaFilterPembimbing)
                            <div class="col-12 col-md-3 form-group mb-2">
                                <label class="small mb-1" for="filterPembimbing">Pembimbing</label>
                                <select class="form-control form-control-sm" id="filterPembimbing">
                                    <option value="">Semua Pembimbing</option>
                                    @foreach ($pembimbingList as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama_pembimbing }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-12 col-md-3 form-group mb-2">
                            <label class="small mb-1" for="filterRuangan">Ruangan</label>
                            <select class="form-control form-control-sm" id="filterRuangan">
                                <option value="">Semua Ruangan</option>
                                @foreach ($ruangan as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_ruangan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-auto form-group mb-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetFilter">
                                <i class="fas fa-undo mr-1"></i> Reset
                            </button>
                        </div>
                    </div>
                    <small class="text-muted" id="infoFilter" style="display: none;">
                        <i class="fas fa-filter mr-1"></i>Filter tanggal, pembimbing, atau ruangan hanya menampilkan siswa yang sudah dijadwalkan.
                    </small>
                </div>
                <table id="jadwalTable" class="table table-striped table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            @if (in_array($role, ['panitia', 'pembimbing']))
                                <th style="width: 30px;" class="text-center"><input type="checkbox" id="checkAll"></th>
                            @endif
                            <th style="width: 50px;">No</th>
                            <th style="width: 110px;">Jenis</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pembimbing</th>
                            <th>Ruangan</th>
                            <th>Tanggal</th>
                            <th style="width: 120px;">Jam</th>
                            <th>Status</th>
                            @if (in_array($role, ['panitia', 'pembimbing']))
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        @if ($role === 'panitia')
            {{-- Modal jadwal manual --}}
            <div class="modal fade" id="modalForm" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <form id="formJadwal">
                        @csrf
                        <input type="hidden" name="id" id="id">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTitle">Tambah Jadwal</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Siswa <span class="text-danger">*</span></label>
                                        <select class="form-control" name="siswa_id" id="siswa_id" required>
                                            <option value="">-- Pilih Siswa --</option>
                                            @foreach ($siswaList as $siswa)
                                                <option value="{{ $siswa->id }}">
                                                    {{ $siswa->nama_siswa }} ({{ $siswa->kelas->nama_kelas ?? '-' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Pembimbing / Penguji</label>
                                        <select class="form-control" name="pembimbing_id" id="pembimbing_id">
                                            <option value="">-- Pilih Pembimbing --</option>
                                            @foreach ($pembimbingList as $pembimbing)
                                                <option value="{{ $pembimbing->id }}">{{ $pembimbing->nama_pembimbing }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label>Jenis <span class="text-danger">*</span></label>
                                        <select class="form-control" name="jenis" id="jenis" required>
                                            <option value="bimbingan">Bimbingan Sidang</option>
                                            <option value="sidang">Sidang</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Ruangan</label>
                                        <select class="form-control" name="ruangan_sidang_id" id="ruangan_sidang_id">
                                            <option value="">-- Pilih Ruangan --</option>
                                            @foreach ($ruangan as $item)
                                                <option value="{{ $item->id }}">{{ $item->nama_ruangan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal" id="tanggal" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label>Jam Mulai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_mulai" id="jam_mulai" required>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Jam Selesai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_selesai" id="jam_selesai"
                                            required>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Catatan</label>
                                        <input type="text" class="form-control" name="catatan" id="catatan">
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal generate otomatis --}}
            <div class="modal fade" id="modalGenerate" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form id="formGenerate">
                        @csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Generate Jadwal Otomatis</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted">
                                    Jadwal dibuat berurutan untuk semua siswa yang belum terjadwal, dibagi ke
                                    ruangan terpilih. Pembimbing diambil dari kelompok bimbingan masing-masing siswa.
                                </p>
                                <div class="form-group" id="generateRuanganGroup">
                                    <label>Jenis Jadwal <span class="text-danger">*</span></label>
                                    <select class="form-control" name="jenis" id="gen_jenis" required>
                                        <option value="bimbingan">Bimbingan Sidang</option>
                                        <option value="sidang">Sidang</option>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_mulai" id="gen_tanggal"
                                            required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Durasi per Siswa (menit) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" name="durasi_menit" id="gen_durasi"
                                            value="30" min="10" max="240" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Jam Mulai Harian <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_mulai" id="gen_jam_mulai"
                                            value="08:00" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Jam Selesai Harian <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_selesai" id="gen_jam_selesai"
                                            value="15:00" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Ruangan yang Dipakai <span class="text-danger">*</span></label>
                                    <div>
                                        @forelse ($ruangan as $item)
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input gen-ruangan"
                                                    id="ruangan{{ $item->id }}" value="{{ $item->id }}">
                                                <label class="custom-control-label" for="ruangan{{ $item->id }}">
                                                    {{ $item->nama_ruangan }}
                                                    {{ $item->lokasi ? '(' . $item->lokasi . ')' : '' }}
                                                </label>
                                            </div>
                                        @empty
                                            <em class="text-danger">Belum ada ruangan — input ruangan sidang dahulu.</em>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="gen_lewati_weekend" checked>
                                    <label class="custom-control-label" for="gen_lewati_weekend">Lewati Sabtu &
                                        Minggu</label>
                                </div>
                                <div class="form-group">
                                    <label>Jeda Antar Sesi (menit)</label>
                                    <input type="number" class="form-control" id="gen_jeda" value="5" min="0" max="120">
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="gen_timpa">
                                    <label class="custom-control-label" for="gen_timpa">
                                        Hapus jadwal lama jenis ini & buat ulang semua
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">
                            <i class="fas fa-magic mr-1"></i> Generate
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if ($role === 'pembimbing')
            {{-- Modal bimbingan massal: semua siswa, bisa rentang beberapa hari --}}
            <div class="modal fade" id="modalBimbinganMassal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form id="formBimbinganMassal">
                        @csrf
                        <input type="hidden" name="bulk" value="1">
                        <input type="hidden" name="jenis" value="bimbingan">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Atur Bimbingan untuk Semua Siswa</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info small" id="infoMassal">
                                    Jadwal ini diterapkan ke <strong>seluruh siswa bimbingan Anda</strong>.
                                    Bisa satu hari atau rentang beberapa hari dengan waktu mulai dan selesai
                                    yang sama setiap harinya.
                                </div>
                                <div id="siswaTerpilihInputs"></div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_mulai" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Tanggal Selesai <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_selesai" required>
                                        <small class="text-muted">Samakan dengan tanggal mulai jika satu hari.</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Waktu Mulai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_mulai" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Waktu Selesai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_selesai" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Tempat / Ruangan</label>
                                    <select class="form-control" name="ruangan_sidang_id">
                                        <option value="">-- Tempat bebas (opsional) --</option>
                                        @foreach ($ruangan as $item)
                                            <option value="{{ $item->id }}">{{ $item->nama_ruangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label>Catatan</label>
                                    <input type="text" class="form-control" name="catatan" maxlength="1000"
                                        placeholder="Misal: bawa draf laporan bab 1-3">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Terapkan ke Semua Siswa
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal atur sidang: satu ruangan, satu waktu mulai, durasi mengikuti jumlah siswa --}}
            <div class="modal fade" id="modalAturSidang" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form id="formAturSidang">
                        @csrf
                        <div id="aturSidangSiswaInputs"></div>
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-gavel mr-1"></i> Atur Sidang</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info small mb-3" id="infoAturSidang"></div>
                                <div class="form-group">
                                    <label>Ruangan <span class="text-danger">*</span></label>
                                    <select class="form-control" name="ruangan_sidang_id" id="aturSidangRuangan" required>
                                        <option value="">-- Pilih Ruangan --</option>
                                        @foreach ($ruangan as $item)
                                            <option value="{{ $item->id }}">{{ $item->nama_ruangan }}{{ $item->lokasi ? ' (' . $item->lokasi . ')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal" id="aturSidangTanggal"
                                            min="{{ now()->toDateString() }}" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Waktu Mulai <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="jam_mulai" id="aturSidangMulai" required>
                                    </div>
                                </div>
                                <div class="small text-muted mb-2">
                                    <i class="far fa-clock mr-1"></i>Jam kerja {{ $jamKerja['mulai'] }}–{{ $jamKerja['selesai'] }}.
                                    Tidak ada sidang pada
                                    @foreach ($jamKerja['istirahat'] as $item)
                                        {{ strtolower($item['nama']) }} {{ $item['mulai'] }}–{{ $item['selesai'] }}{{ $loop->last ? '.' : ' dan' }}
                                    @endforeach
                                    Slot yang menabrak jam tersebut otomatis dimajukan.
                                </div>
                                <div class="sidang-ringkasan" id="aturSidangRingkasan">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Jumlah siswa</span><strong id="ringkasJumlah">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Durasi per siswa</span><strong>{{ $durasiSidang }} menit</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Waktu sidang</span><strong id="ringkasWaktu">-</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Istirahat ({{ $istirahatSidang }} menit)</span><strong id="ringkasIstirahat">-</strong>
                                    </div>
                                </div>
                                <div class="alert alert-warning small mt-3 mb-0" id="peringatanSidang" style="display: none;"></div>
                                <div class="form-group mt-3 mb-0">
                                    <label>Catatan</label>
                                    <input type="text" class="form-control" name="catatan" maxlength="1000">
                                </div>
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-lock mr-1"></i>Setelah disimpan, tanggal, waktu, dan ruangan
                                    terkunci (termasuk waktu istirahat) dan tidak dapat dipakai jadwal lain.
                                </small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-lock mr-1"></i> Simpan &amp; Kunci</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="modalPembimbing" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <form id="formPembimbing">
                        @csrf
                        <input type="hidden" name="siswa_id" id="pembimbingSiswaId">
                        <input type="hidden" name="jadwal_id" id="pembimbingJadwalId">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Atur Jadwal Bimbingan / Sidang</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info small">
                                    Jadwal dibuat untuk siswa yang dipilih. Jadwal sidang otomatis memakai
                                    durasi <strong>{{ $durasiSidang }} menit per siswa</strong> dan langsung dikunci.
                                </div>
                                <div class="form-group">
                                    <label>Jenis Jadwal</label>
                                    <select class="form-control" name="jenis" id="pembimbingJenis" required>
                                        <option value="bimbingan">Bimbingan Sebelum Sidang</option>
                                        <option value="sidang">Sidang</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Ruangan <span class="text-danger room-required">*</span></label>
                                    <select class="form-control" name="ruangan_sidang_id" id="pembimbingRuangan">
                                        <option value="">-- Pilih Ruangan (opsional untuk bimbingan) --</option>
                                        @foreach ($ruangan as $item)
                                            <option value="{{ $item->id }}">{{ $item->nama_ruangan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Jam Mulai</label>
                                        <input type="time" class="form-control" name="jam_mulai" required>
                                    </div>
                                    <div class="col-md-6 form-group bimbingan-duration">
                                        <label>Durasi Bimbingan (menit)</label>
                                        <input type="number" class="form-control" name="durasi_menit" value="30" min="1" max="480">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Jeda Antar Sesi (menit)</label>
                                    <input type="number" class="form-control" name="jeda_menit" value="5" min="0" max="120">
                                    <small class="text-muted">Berlaku untuk bimbingan. Sidang otomatis istirahat {{ $istirahatSidang }} menit.</small>
                                </div>
                                <div class="form-group">
                                    <label>Catatan</label>
                                    <input type="text" class="form-control" name="catatan" maxlength="1000">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-lock mr-1"></i> Simpan Jadwal</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('css')
    @include('sidang.partials.table_styles')
    <style>
        .filter-jadwal {
            background: #f8f9fa;
            border: 1px solid #e3e6ea;
            border-radius: .4rem;
            padding: .75rem .9rem .35rem;
        }

        .sidang-ringkasan {
            background: #f8f9fa;
            border: 1px solid #e3e6ea;
            border-radius: .4rem;
            padding: .65rem .9rem;
            line-height: 1.9;
        }
    </style>
@endsection

@section('js')
    @include('sidang.partials.table_scripts')
    <script>
        $(document).ready(function() {
            const isPanitia = @json($role === 'panitia');
            const isPembimbing = @json($role === 'pembimbing');
            const initialJenis = @json($initialJenis ?? '');
            $('#filterJenis').val(initialJenis);

            const columns = [
                ...((isPanitia || isPembimbing) ? [{ data: 'pilih', orderable: false, searchable: false, className: 'text-center' }] : []),
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                {
                    data: 'jenis',
                    render: jenis => jenis === 'sidang'
                        ? '<span class="badge badge-soft-primary px-2 py-1">Sidang</span>'
                        : '<span class="badge badge-soft-info px-2 py-1">Bimbingan</span>'
                },
                { data: 'nama_siswa' },
                { data: 'kelas' },
                { data: 'nama_pembimbing' },
                { data: 'nama_ruangan' },
                { data: 'tanggal' },
                { data: 'jam', orderable: false },
                { data: 'status_jadwal', orderable: false, searchable: false },
            ];
            if (isPanitia || isPembimbing) {
                columns.push({ data: 'aksi', orderable: false, searchable: false, className: 'text-center' });
            }

            const table = $('#jadwalTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('sidang.jadwal.data') }}',
                    data: d => {
                        d.jenis = $('#filterJenis').val();
                        d.tanggal_dari = $('#filterTanggalDari').val();
                        d.tanggal_sampai = $('#filterTanggalSampai').val();
                        d.pembimbing_id = $('#filterPembimbing').val() || '';
                        d.ruangan_sidang_id = $('#filterRuangan').val();
                    }
                },
                columns: columns,
            });

            const filterAktif = () => ['#filterTanggalDari', '#filterTanggalSampai', '#filterPembimbing', '#filterRuangan']
                .some(selector => $(selector).val());

            $('#filterJenis, #filterTanggalDari, #filterTanggalSampai, #filterPembimbing, #filterRuangan').on('change', function() {
                $('#infoFilter').toggle(filterAktif());
                table.ajax.reload();
            });

            $('#btnResetFilter').on('click', function() {
                $('#filterTanggalDari, #filterTanggalSampai, #filterPembimbing, #filterRuangan').val('');
                $('#infoFilter').hide();
                table.ajax.reload();
            });

            $(document).on('click', '.btnAturPerOrang', function() {
                const button = $(this);
                const jenis = button.data('jenis');
                if (isPembimbing) {
                    const form = $('#formPembimbing');
                    form[0].reset();
                    $('#pembimbingSiswaId').val(button.data('siswa_id'));
                    $('#pembimbingJadwalId').val(button.data('id') || '');
                    $('#pembimbingJenis').val(jenis).trigger('change');
                    $('#pembimbingRuangan').val(button.data('ruangan_sidang_id') || '');
                    form.find('[name="tanggal"]').val(button.data('tanggal') || '');
                    form.find('[name="jam_mulai"]').val(button.data('jam_mulai') || '');
                    form.find('[name="catatan"]').val(button.data('catatan') || '');
                    $('#modalPembimbing').modal('show');
                    return;
                }
                $('#formJadwal')[0].reset();
                $('#id').val(button.data('id') || '');
                $('#siswa_id').val(button.data('siswa_id'));
                $('#jenis').val(jenis);
                $('#pembimbing_id').val(button.data('pembimbing_id') || '');
                $('#ruangan_sidang_id').val(button.data('ruangan_sidang_id') || '');
                $('#tanggal').val(button.data('tanggal') || '');
                $('#jam_mulai').val(button.data('jam_mulai') || '');
                $('#jam_selesai').val(button.data('jam_selesai') || '');
                $('#modalTitle').text('Atur Jadwal Per Siswa');
                $('#modalForm').modal('show');
            });

            $(document).on('click', '.btnHapus', function() {
                const id = $(this).data('id');
                konfirmasiHapus({ title: 'Hapus jadwal ini?' }, () => {
                    $.ajax({ url: `{{ url('sidang/jadwal') }}/${id}`, type: 'DELETE' })
                        .done(() => {
                            table.ajax.reload(null, false);
                            Swal.fire('Terhapus', 'Jadwal berhasil dihapus.', 'success');
                        })
                        .fail(xhr => Swal.fire('Gagal',
                            xhr.responseJSON?.message || 'Jadwal tidak dapat dihapus.', 'error'));
                });
            });

            const selectedRows = () => $('.row-check:checked');
            const selectedJadwalIds = () => selectedRows().filter(function() { return Number($(this).data('terkunci')) !== 1; })
                .map(function() { return $(this).data('id'); }).get().filter(Boolean);
            const selectedTerkunciIds = () => selectedRows().filter(function() { return Number($(this).data('terkunci')) === 1; })
                .map(function() { return $(this).data('id'); }).get().filter(Boolean);
            const refreshSelection = () => {
                const total = selectedRows().length;
                const hapusCount = selectedJadwalIds().length;
                $('#jumlahTerpilih').text(hapusCount);
                $('#btnHapusTerpilih').prop('disabled', hapusCount === 0);
                $('#jumlahTerkunci').text(selectedTerkunciIds().length);
                $('#btnBukaKunciTerpilih').prop('disabled', selectedTerkunciIds().length === 0);
                $('#checkAll').prop('checked', total > 0 && total === $('.row-check:not(:disabled)').length);
            };

            table.on('draw', () => { $('#checkAll').prop('checked', false); refreshSelection(); });
            $(document).on('change', '.row-check', refreshSelection);
            $('#checkAll').on('change', function() {
                $('.row-check:not(:disabled)').prop('checked', this.checked);
                refreshSelection();
            });

            const bukaKunci = ids => {
                Swal.fire({
                    icon: 'warning',
                    title: `Buka kunci ${ids.length} jadwal?`,
                    text: 'Waktu dan ruangan jadwal ini bisa diubah atau dihapus. Jadwal akan terkunci lagi saat sidang disimpan ulang.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, buka kunci',
                    cancelButtonText: 'Batal',
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $.post('{{ route('sidang.jadwal.buka-kunci') }}', { ids: ids })
                        .done(response => {
                            table.ajax.reload(null, false);
                            Swal.fire('Berhasil', response.message, 'success');
                        })
                        .fail(xhr => Swal.fire('Gagal',
                            xhr.responseJSON?.message || 'Kunci jadwal tidak dapat dibuka.', 'error'));
                });
            };

            $(document).on('click', '.btnBukaKunci', function() { bukaKunci([$(this).data('id')]); });
            $('#btnBukaKunciTerpilih').on('click', function() {
                const ids = selectedTerkunciIds();
                if (ids.length) bukaKunci(ids);
            });

            $('#btnHapusTerpilih').on('click', function() {
                const ids = selectedJadwalIds();
                if (!ids.length) return;
                konfirmasiHapus({ title: `Hapus ${ids.length} jadwal terpilih?` }, () => {
                    $.post('{{ route('sidang.jadwal.bulk-delete') }}', { ids: ids })
                        .done(response => {
                            table.ajax.reload(null, false);
                            Swal.fire('Terhapus', response.message, 'success');
                        })
                        .fail(xhr => Swal.fire('Gagal',
                            xhr.responseJSON?.message || 'Jadwal tidak dapat dihapus.', 'error'));
                });
            });

            if (isPembimbing) {
                $('#btnAturBimbinganMassal').on('click', function() {
                    $('#formBimbinganMassal')[0].reset();
                    const siswaIds = [...new Set(selectedRows().map(function() { return $(this).data('siswa_id'); }).get())];
                    $('#siswaTerpilihInputs').html(siswaIds.map(id => `<input type="hidden" name="siswa_ids[]" value="${id}">`).join(''));
                    $('#infoMassal').html(siswaIds.length
                        ? `Jadwal ini diterapkan ke <strong>${siswaIds.length} siswa terpilih</strong>. Bisa satu hari atau rentang beberapa hari.`
                        : 'Jadwal ini diterapkan ke <strong>seluruh siswa bimbingan Anda</strong>. Centang baris siswa terlebih dahulu untuk memilih sebagian saja. Bisa satu hari atau rentang beberapa hari.');
                    $('#modalBimbinganMassal').modal('show');
                });

                $('#formBimbinganMassal').on('submit', function(e) {
                    e.preventDefault();
                    $.post('{{ route('sidang.jadwal.store') }}', $(this).serialize())
                        .done(response => {
                            $('#modalBimbinganMassal').modal('hide');
                            table.ajax.reload();
                            Swal.fire('Berhasil', response.message, 'success');
                        })
                        .fail(xhr => Swal.fire('Gagal',
                            xhr.responseJSON?.message || 'Jadwal tidak dapat disimpan.', 'error'));
                });

                const DURASI_SIDANG = @json($durasiSidang);
                const ISTIRAHAT_SIDANG = @json($istirahatSidang);
                const siswaBelumSidang = @json($siswaBelumSidangIds).map(Number);
                const fmtJam = menit => String(Math.floor(menit / 60) % 24).padStart(2, '0') + ':' + String(menit % 60).padStart(2, '0');

                const siswaSidangTerpilih = () => {
                    const dipilih = [...new Set(selectedRows().map(function() { return Number($(this).data('siswa_id')); }).get())]
                        .filter(id => siswaBelumSidang.includes(id));
                    return dipilih.length ? dipilih : siswaBelumSidang;
                };

                const JAM_KERJA = @json($jamKerja);
                const keMenit = jam => { const [h, m] = jam.split(':').map(Number); return h * 60 + m; };
                const kerjaMulai = keMenit(JAM_KERJA.mulai);
                const kerjaSelesai = keMenit(JAM_KERJA.selesai);
                const istirahat = JAM_KERJA.istirahat.map(i => ({ ...i, awal: keMenit(i.mulai), akhir: keMenit(i.selesai) }));

                // Sama dengan JamKerjaSidang::susun di server: slot yang menabrak istirahat dimajukan.
                function susunSlot(awal, jumlah) {
                    const slots = [];
                    let waktu = awal;
                    for (let i = 0; i < jumlah; i++) {
                        istirahat.forEach(r => {
                            if (waktu < r.akhir && waktu + DURASI_SIDANG > r.awal) waktu = r.akhir;
                        });
                        slots.push([waktu, waktu + DURASI_SIDANG]);
                        waktu += DURASI_SIDANG;
                    }
                    return slots;
                }

                function hitungRingkasanSidang() {
                    const jumlah = siswaSidangTerpilih().length;
                    const mulai = $('#aturSidangMulai').val();
                    const peringatan = $('#peringatanSidang');
                    const tombol = $('#formAturSidang [type="submit"]');
                    $('#ringkasJumlah').text(jumlah);
                    peringatan.hide().removeClass('alert-info').addClass('alert-warning');
                    tombol.prop('disabled', false);
                    if (!mulai || !jumlah) {
                        $('#ringkasWaktu, #ringkasIstirahat').text('-');
                        return;
                    }
                    const awal = keMenit(mulai);
                    if (awal < kerjaMulai || awal >= kerjaSelesai) {
                        $('#ringkasWaktu, #ringkasIstirahat').text('-');
                        peringatan.html(`<i class="fas fa-exclamation-triangle mr-1"></i>Waktu mulai harus di dalam jam kerja ${JAM_KERJA.mulai}–${JAM_KERJA.selesai}.`).show();
                        tombol.prop('disabled', true);
                        return;
                    }
                    const slots = susunSlot(awal, jumlah);
                    const selesai = slots[jumlah - 1][1];
                    $('#ringkasWaktu').text(fmtJam(slots[0][0]) + ' – ' + fmtJam(selesai));
                    $('#ringkasIstirahat').text(fmtJam(selesai) + ' – ' + fmtJam(selesai + ISTIRAHAT_SIDANG));
                    if (selesai > kerjaSelesai) {
                        const muat = slots.filter(s => s[1] <= kerjaSelesai).length;
                        peringatan.html(`<i class="fas fa-exclamation-triangle mr-1"></i>Sidang berakhir pukul ${fmtJam(selesai)}, melewati jam kerja sekolah (${JAM_KERJA.selesai}). `
                            + `Pada waktu mulai ini hanya muat <strong>${muat} siswa</strong>. `
                            + 'Mulai lebih awal, atau <strong>tambah hari</strong>: centang sebagian siswa di tabel, simpan, lalu atur lagi sisanya di hari berikutnya.').show();
                        tombol.prop('disabled', true);
                    } else if (slots.some((s, i) => s[0] !== awal + i * DURASI_SIDANG)) {
                        peringatan.removeClass('alert-warning').addClass('alert-info')
                            .html('<i class="fas fa-info-circle mr-1"></i>Sebagian siswa dimajukan karena menabrak jam istirahat atau shalat dzuhur.').show();
                    }
                }

                $('#btnAturSidang').on('click', function() {
                    const ids = siswaSidangTerpilih();
                    if (!siswaBelumSidang.length) {
                        Swal.fire('Perhatian', 'Semua siswa bimbingan Anda sudah memiliki jadwal sidang.', 'info');
                        return;
                    }
                    $('#formAturSidang')[0].reset();
                    $('#aturSidangSiswaInputs').html(ids.map(id => `<input type="hidden" name="siswa_ids[]" value="${id}">`).join(''));
                    const dipilihManual = selectedRows().length > 0 && ids.length !== siswaBelumSidang.length;
                    $('#infoAturSidang').html(`Sidang diatur untuk <strong>${ids.length} siswa ${dipilihManual ? 'terpilih' : 'bimbingan Anda yang belum disidang'}</strong>. `
                        + 'Centang baris siswa terlebih dahulu untuk memilih sebagian saja. '
                        + `Waktu selesai dihitung otomatis (${DURASI_SIDANG} menit per siswa).`);
                    hitungRingkasanSidang();
                    $('#modalAturSidang').modal('show');
                });

                $('#aturSidangMulai').on('input change', hitungRingkasanSidang);

                $('#formAturSidang').on('submit', function(e) {
                    e.preventDefault();
                    const form = $(this);
                    const tombol = form.find('[type="submit"]').prop('disabled', true);
                    $.post('{{ route('sidang.jadwal.atur-sidang') }}', form.serialize())
                        .done(response => {
                            $('#modalAturSidang').modal('hide');
                            table.ajax.reload();
                            Swal.fire('Berhasil', response.message, 'success');
                        })
                        .fail(xhr => Swal.fire('Gagal',
                            xhr.responseJSON?.message || 'Jadwal sidang tidak dapat disimpan.', 'error'))
                        .always(hitungRingkasanSidang);
                });

                $('#pembimbingJenis').on('change', function() {
                    const sidang = $(this).val() === 'sidang';
                    $('#pembimbingRuangan').prop('required', sidang);
                    $('.room-required').toggle(sidang);
                    $('.bimbingan-duration').toggle(!sidang);
                }).trigger('change');

                $('#formPembimbing').on('submit', function(e) {
                    e.preventDefault();
                    $.post('{{ route('sidang.jadwal.store') }}', $(this).serialize())
                        .done(response => {
                            $('#modalPembimbing').modal('hide');
                            table.ajax.reload();
                            Swal.fire('Berhasil', response.message, 'success');
                        })
                        .fail(xhr => Swal.fire('Gagal', xhr.responseJSON?.message || 'Jadwal tidak dapat disimpan.', 'error'));
                });
                return;
            }

            if (!isPanitia) return;

            $('#gen_jenis').on('change', function() {
                const sidang = $(this).val() === 'sidang';
                $('#gen_durasi').val(sidang ? @json($durasiSidang ?? 20) : 30).prop('readonly', sidang);
                $('#generateRuanganGroup').toggle(sidang);
            }).trigger('change');

            $('#btnTambah').on('click', function() {
                $('#modalGenerate').modal('show');
            });

            $(document).on('click', '.btnEdit', function() {
                $('#id').val($(this).data('id'));
                $('#siswa_id').val($(this).data('siswa_id'));
                $('#pembimbing_id').val($(this).data('pembimbing_id'));
                $('#ruangan_sidang_id').val($(this).data('ruangan_sidang_id'));
                $('#jenis').val($(this).data('jenis'));
                $('#tanggal').val($(this).data('tanggal'));
                $('#jam_mulai').val($(this).data('jam_mulai'));
                $('#jam_selesai').val($(this).data('jam_selesai'));
                $('#catatan').val($(this).data('catatan'));
                $('#modalTitle').text('Edit Jadwal');
                $('#modalForm').modal('show');
            });

            $('#formJadwal').on('submit', function(e) {
                e.preventDefault();
                const id = $('#id').val();
                const data = $(this).serializeArray();
                if (id) data.push({ name: '_method', value: 'PUT' });

                $.post(id ? `{{ url('sidang/jadwal') }}/${id}` : '{{ route('sidang.jadwal.store') }}',
                        $.param(data))
                    .done(() => {
                        $('#modalForm').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire('Berhasil', 'Jadwal tersimpan.', 'success');
                    })
                    .fail(xhr => Swal.fire('Gagal',
                        xhr.responseJSON?.message || 'Jadwal tidak dapat disimpan.', 'error'));
            });

            $('#btnGenerate').on('click', () => $('#modalGenerate').modal('show'));

            $('#formGenerate').on('submit', function(e) {
                e.preventDefault();
                const ruanganIds = $('.gen-ruangan:checked').map(function() {
                    return $(this).val();
                }).get();

                if ($('#gen_jenis').val() === 'sidang' && !ruanganIds.length) {
                    Swal.fire('Perhatian', 'Pilih minimal satu ruangan.', 'warning');
                    return;
                }

                $.post('{{ route('sidang.jadwal.generate') }}', {
                        jenis: $('#gen_jenis').val(),
                        tanggal_mulai: $('#gen_tanggal').val(),
                        jam_mulai: $('#gen_jam_mulai').val(),
                        jam_selesai: $('#gen_jam_selesai').val(),
                        durasi_menit: $('#gen_durasi').val(),
                        jeda_menit: $('#gen_jeda').val(),
                        ruangan_ids: ruanganIds,
                        lewati_akhir_pekan: $('#gen_lewati_weekend').is(':checked') ? 1 : 0,
                        timpa_jadwal: $('#gen_timpa').is(':checked') ? 1 : 0,
                    })
                    .done(response => {
                        $('#modalGenerate').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Berhasil', response.message, 'success');
                    })
                    .fail(xhr => Swal.fire('Gagal',
                        xhr.responseJSON?.message || 'Jadwal tidak dapat digenerate.', 'error'));
            });
        });
    </script>
@endsection

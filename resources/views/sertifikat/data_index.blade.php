@extends('adminlte::page')

@section('title', 'Data Sertifikat PKL')

@section('content')
    <div class="container-fluid pt-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        {{-- Card ringkasan pengisian data sertifikat --}}
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $siswaSudah }}</h3>
                        <p class="mb-0">Siswa Sudah Mengisi</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-check"></i></div>
                    <a href="{{ route('sertifikat.data.index', ['status' => 'sudah']) }}" class="small-box-footer">
                        Lihat detail <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $siswaBelum }}</h3>
                        <p class="mb-0">Siswa Belum Mengisi</p>
                    </div>
                    <div class="icon"><i class="fas fa-user-times"></i></div>
                    <a href="{{ route('sertifikat.data.index', ['status' => 'belum']) }}" class="small-box-footer">
                        Lihat detail <i class="fas fa-arrow-circle-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $perusahaanTerisi }}</h3>
                        <p class="mb-0">Perusahaan Sudah Terisi</p>
                    </div>
                    <div class="icon"><i class="fas fa-building"></i></div>
                    <span class="small-box-footer">Pemilik &amp; pembimbing lengkap</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $perusahaanBelum }}</h3>
                        <p class="mb-0">Perusahaan Belum Terisi</p>
                    </div>
                    <div class="icon"><i class="fas fa-building"></i></div>
                    <span class="small-box-footer">Masih ada data kosong</span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card app-card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                        <div class="d-flex align-items-center my-1">
                            <span class="card-title-icon"><i class="fas fa-certificate"></i></span>
                            <div>
                                <h4>Data Sertifikat PKL</h4>
                                <small>Pengisian data pemilik &amp; pembimbing perusahaan oleh siswa (satu perusahaan boleh berbeda pembimbing per siswa)</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-wrap my-1">
                            <form method="GET" action="{{ route('sertifikat.data.index') }}" class="form-inline mr-2">
                                <select name="status" class="form-control form-control-sm mr-2">
                                    <option value="">Semua Status</option>
                                    <option value="sudah" {{ $statusFilter === 'sudah' ? 'selected' : '' }}>Sudah Mengisi</option>
                                    <option value="belum" {{ $statusFilter === 'belum' ? 'selected' : '' }}>Belum Mengisi</option>
                                </select>
                                <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm mr-2"
                                    placeholder="Cari nama / NIS / perusahaan...">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-search mr-1"></i> Cari
                                </button>
                            </form>
                            @if ($siswaBelum > 0)
                                <form method="POST" action="{{ route('sertifikat.data.kirim-wa-massal') }}"
                                    class="d-inline" id="formKirimWaMassal">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"
                                        title="Kirim WA pengingat ke semua siswa yang belum mengisi">
                                        <i class="fab fa-whatsapp mr-1"></i> Kirim WA ke Semua yang Belum Mengisi
                                        <span class="badge badge-light ml-1">{{ $siswaBelum }}</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Perusahaan</th>
                                        <th>Pemilik / Pimpinan Perusahaan</th>
                                        <th>Pembimbing Perusahaan</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($tempatList as $tempat)
                                        @php
                                            $pemilikTerisi = filled($tempat->nama_pemilik_instansi);
                                            $pembimbingTerisi = filled($tempat->nama_pembimbing_instansi);
                                            $lengkap = $pemilikTerisi && $pembimbingTerisi;
                                        @endphp
                                        <tr>
                                            <td>{{ $tempatList->firstItem() + $loop->index }}</td>
                                            <td>{{ $tempat->siswa->nis ?? '-' }}</td>
                                            <td>{{ $tempat->siswa->nama_siswa ?? '-' }}</td>
                                            <td>{{ $tempat->siswa->kelas->nama_kelas ?? '-' }}</td>
                                            <td>{{ $tempat->perusahaan->nama_perusahaan ?? '-' }}</td>
                                            <td>
                                                @if ($pemilikTerisi)
                                                    {{ $tempat->nama_pemilik_instansi }}
                                                    @if ($tempat->nip_pemilik_instansi)
                                                        <br><small class="text-muted">NIP: {{ $tempat->nip_pemilik_instansi }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge badge-danger">Belum diisi</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($pembimbingTerisi)
                                                    {{ $tempat->nama_pembimbing_instansi }}
                                                    @if ($tempat->nip_pembimbing_instansi)
                                                        <br><small class="text-muted">NIP: {{ $tempat->nip_pembimbing_instansi }}</small>
                                                    @endif
                                                @else
                                                    <span class="badge badge-danger">Belum diisi</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($lengkap)
                                                    <span class="badge badge-success">Sudah Mengisi</span>
                                                @else
                                                    <span class="badge badge-warning">Belum Lengkap</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if (!$lengkap && $tempat->siswa)
                                                    @if ($tempat->siswa->no_hp_siswa)
                                                        <form method="POST"
                                                            action="{{ route('sertifikat.data.kirim-wa', $tempat->siswa->id) }}"
                                                            class="d-inline form-kirim-wa">
                                                            @csrf
                                                            <button type="submit" class="btn btn-xs btn-success"
                                                                title="Kirim WA pengingat ke {{ $tempat->siswa->nama_siswa }}">
                                                                <i class="fab fa-whatsapp mr-1"></i> Kirim WA
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="badge badge-secondary" title="Nomor HP siswa tidak tersedia">
                                                            <i class="fas fa-phone-slash mr-1"></i> No HP kosong
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Tidak ada data tempat PKL.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end">
                            {{ $tempatList->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.querySelectorAll('.form-kirim-wa').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (!confirm('Kirim pesan WhatsApp pengingat pengisian data ke siswa ini?')) {
                    e.preventDefault();
                }
            });
        });

        var formMassal = document.getElementById('formKirimWaMassal');
        if (formMassal) {
            formMassal.addEventListener('submit', function (e) {
                if (!confirm('Kirim pesan WhatsApp pengingat ke SEMUA siswa yang belum mengisi ({{ $siswaBelum }} siswa)?')) {
                    e.preventDefault();
                    return;
                }
                var btn = formMassal.querySelector('button[type="submit"]');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Mengirim...';
            });
        }
    </script>
@endsection

@extends('adminlte::page')

@section('title', 'Nilai Sidang Saya')

@section('content')
    <div class="container pt-4">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card sidang-card">
                    <div class="card-header d-flex align-items-center">
                        <span class="card-title-icon"><i class="fas fa-star"></i></span>
                        <div>
                            <h4>Nilai Sidang PKL</h4>
                            <small>{{ $siswa->nama_siswa }}</small>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($kriteriaList->isEmpty())
                            <div class="alert alert-warning mb-0">Kriteria penilaian sidang belum tersedia.</div>
                        @else
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Kriteria</th>
                                        <th style="width: 10%;" class="text-center">Bobot</th>
                                        <th style="width: 12%;" class="text-center">Nilai</th>
                                        <th>Catatan Penguji</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($kriteriaList as $kriteria)
                                        @php $item = $nilai->get($kriteria->id); @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $kriteria->nama_kriteria }}</strong>
                                                @if ($kriteria->deskripsi)
                                                    <br><small class="text-muted">{{ $kriteria->deskripsi }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-soft-primary px-2 py-1">{{ $kriteria->bobot }}%</span>
                                            </td>
                                            <td class="text-center">
                                                @if ($item)
                                                    <strong>{{ rtrim(rtrim(number_format($item->nilai, 2), '0'), '.') }}</strong>
                                                @else
                                                    <span class="text-muted">belum dinilai</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->catatan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <th colspan="2" class="text-right">Nilai Akhir (tertimbang)</th>
                                        <th class="text-center">{{ $nilaiAkhir ?? '-' }}</th>
                                        <th>{{ $predikat ?? '-' }}</th>
                                    </tr>
                                </tfoot>
                            </table>

                            @if ($sertifikat)
                                <div class="alert alert-success d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-certificate mr-1"></i>
                                        Sertifikat hasil sidang Anda sudah terbit
                                        ({{ $sertifikat->generated_at->locale('id')->translatedFormat('d F Y') }}) —
                                        Nilai <strong>{{ rtrim(rtrim(number_format($sertifikat->nilai_akhir, 2), '0'), '.') }}</strong>,
                                        predikat <strong>{{ $sertifikat->predikat }}</strong>.
                                    </div>
                                    <div class="text-nowrap ml-2">
                                        <a href="{{ route('sidang.sertifikat.cetak') }}" target="_blank"
                                            class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-eye mr-1"></i> Lihat
                                        </a>
                                        <a href="{{ route('sidang.sertifikat.download') }}"
                                            class="btn btn-sm btn-success">
                                            <i class="fas fa-download mr-1"></i> Download PDF
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-light border mb-0">
                                    <i class="fas fa-info-circle text-info mr-1"></i>
                                    Sertifikat hasil sidang akan tersedia setelah semua kriteria dinilai dan
                                    sertifikat digenerate oleh panitia.
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    @include('sidang.partials.table_styles')
@endsection

@section('js')
    @include('sweetalert::alert')
@endsection

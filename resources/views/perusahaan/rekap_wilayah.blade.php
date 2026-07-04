@extends('adminlte::page')

@section('title', 'Rekap Wilayah Perusahaan')

@section('content')
    <div class="container pt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Rekap Wilayah Perusahaan</h4>
                        <div>
                            <a href="{{ route('perusahaan.index') }}" class="btn btn-sm btn-outline-secondary mr-2">Kembali ke
                                Data Perusahaan</a>
                            <span class="badge badge-primary mr-1">Total Wilayah: {{ $rows->count() }}</span>
                            <span class="badge badge-success">Total Perusahaan: {{ $rows->sum('jumlah') }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="rekapWilayahTable" class="table table-bordered table-striped">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 70px;">No</th>
                                        <th>Wilayah</th>
                                        <th>Kecamatan / Kabupaten Kota Terkait</th>
                                        <th style="width: 180px;">Jumlah Perusahaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $row)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $row['wilayah'] }}</td>
                                            <td>{{ $row['kecamatan'] }}</td>
                                            <td class="text-center font-weight-bold">{{ $row['jumlah'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada data perusahaan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endsection

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#rekapWilayahTable').DataTable({
                pageLength: 10,
                order: [
                    [3, 'desc']
                ]
            });
        });
    </script>
@endsection

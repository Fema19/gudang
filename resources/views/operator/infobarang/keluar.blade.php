@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold">📦 Info Barang Keluar</span>
                    <div>
                        <a href="{{ route('infobarang.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="bi bi-box-arrow-in-right"></i> Barang Masuk
                        </a>
                        <a href="{{ route('infobarang.keluar') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-box-arrow-left"></i> Barang Keluar
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Tabel Info Barang Keluar -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="bg-dark text-white text-center">
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th>Jenis Barang</th>
                                    <th style="width: 10%">Keluar</th>
                                    <th style="width: 10%">Sisa Stok</th>
                                    <th style="width: 20%">Tanggal Keluar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($barangKeluars as $index => $keluar)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $keluar->barang->nama_barang ?? '-' }}</td>
                                        <td class="text-center">{{ $keluar->jumlah_keluar ?? '-' }}</td>
                                        <td class="text-center">{{ $keluar->barang->stok ?? '0' }}</td>
                                        <td class="text-center">
                                            {{ \Carbon\Carbon::parse($keluar->tanggal_keluar)->format('d M Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Belum ada data barang keluar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Akhir Tabel -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

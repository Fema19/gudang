@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Info Barang Masuk</span>
                    <div>
                        <a href="{{ route('infobarang.index') }}" class="btn btn-primary btn-sm me-2">
                            <i class="bi bi-box-arrow-in-right"></i> Barang Masuk
                        </a>
                        <a href="{{ route('infobarang.keluar') }}" class="btn btn-outline-secondary btn-sm">
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

                    <!-- Content goes here -->
                    <div class="mb-3">
                        <p class="text-muted small">Riwayat pembuatan dan perubahan stok (urut terbaru di atas).</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="bg-dark text-white text-center">
                                <tr>
                                    <th style="width:5%">No</th>
                                    <th>Jenis Barang</th>
                                    <th style="width:12%">Tipe Event</th>
                                    <th style="width:10%">Stok Masuk</th>
                                    <th style="width:10%">Sebelum</th>
                                    <th style="width:10%">Sesudah</th>
                                    <th style="width:20%">Tanggal / Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // flatten semua histories dari barangs menjadi satu collection
                                    $allHistories = collect();
                                    foreach($barangs as $b) {
                                            foreach($b->histories as $h) {
                                                // exclude keluar events from the 'masuk' page
                                                if ($h->type === 'keluar') continue;
                                                $h->barang = $b; // attach barang untuk akses nama
                                                $allHistories->push($h);
                                            }
                                    }
                                    // urutkan berdasarkan waktu (desc)
                                    $allHistories = $allHistories->sortByDesc('created_at')->values();
                                @endphp

                                @forelse($allHistories as $index => $h)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $h->barang->nama_barang ?? '-' }}</td>
                                        <td class="text-center">
                                            @if($h->type === 'created')
                                                Dibuat
                                            @elseif($h->type === 'stock_changed')
                                                Perubahan Stok
                                            @else
                                                {{ ucfirst($h->type) }}
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $h->qty ?? '-' }}</td>
                                        <td class="text-center">{{ $h->stok_before ?? '-' }}</td>
                                        <td class="text-center">{{ $h->stok_after ?? '-' }}</td>
                                        <td class="text-center">
                                            {{ $h->created_at->format('d M Y') }}<br>
                                            <small class="text-muted">{{ $h->created_at->format('H:i:s') }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat barang masuk atau perubahan stok.</td>
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

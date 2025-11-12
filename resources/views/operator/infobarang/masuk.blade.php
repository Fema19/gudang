@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <span>Info Barang Masuk</span>
                    <div>
                        <a href="{{ route('infobarang.masuk.export') }}" class="btn btn-success btn-sm me-2">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </a>
                        <form action="{{ route('infobarang.masuk.clearAll') }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus semua riwayat masuk/perubahan stok?');">
                            @csrf
                            <button class="btn btn-danger btn-sm me-2" type="submit">Clear All</button>
                        </form>
                        <a href="{{ route('infobarang.index') }}" class="btn btn-primary btn-sm me-2">
                            <i class="bi bi-box-arrow-in-right"></i> Barang Masuk
                        </a>
                        <a href="{{ route('infobarang.keluar') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-box-arrow-left"></i> Barang Keluar
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Alert --}}
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{-- 🔽 Filter Bulan & Tahun --}}
                    <form action="{{ route('infobarang.masuk') }}" method="GET" class="d-flex align-items-center gap-2 mb-3">
                        <select name="month" class="form-select form-select-sm" style="max-width: 160px;">
                            <option value="">Semua Bulan</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>

                        @php $currentYear = now()->year; @endphp
                        <select name="year" class="form-select form-select-sm" style="max-width: 120px;">
                            <option value="">Semua Tahun</option>
                            @for ($y = $currentYear; $y >= $currentYear - 5; $y--)
                                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                        <a href="{{ route('infobarang.masuk') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </form>

                    {{-- 🔍 Kolom Pencarian --}}
                    <form action="{{ route('infobarang.masuk') }}" method="GET" class="mb-3">
                        <div class="input-group">
                            <input 
                                type="text" 
                                name="search" 
                                class="form-control" 
                                placeholder="Cari nama barang..." 
                                value="{{ request('search') }}"
                            >
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                    </form>

                    {{-- 📋 Tabel Data --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Barang</th>
                                    <th>Penambahan Stok</th>
                                    <th>Stok Sebelum</th>
                                    <th>Jumlah Stok</th>
                                    <th>Keterangan</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 1; @endphp
                                @foreach($barangs as $barang)
                                    @foreach($barang->histories as $history)
                                        @if($history->type !== 'keluar')
                                        <tr>
                                            <td>{{ $no++ }}</td>
                                            <td>{{ $barang->nama_barang }}</td>
                                            <td>{{ $history->qty ?? '-' }}</td>
                                            <td>{{ $history->stok_before ?? '-' }}</td>
                                            <td>{{ $history->stok_after ?? '-' }}</td>
                                            <td>{{ $history->type === 'created' ? 'Dibuat' : 'Perubahan Stok' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($history->created_at)->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                                        </tr>
                                        @endif
                                    @endforeach
                                @endforeach

                                @if($barangs->isEmpty() || $barangs->pluck('histories')->flatten()->where('type', '!=', 'keluar')->isEmpty())
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <span>Info Barang Keluar</span>
                    <div>
                        <a href="{{ route('infobarang.keluar.export') }}" class="btn btn-success btn-sm me-2">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </a>
                        <form action="{{ route('infobarang.keluar.clearAll') }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus semua riwayat keluar?');">
                            @csrf
                            <button class="btn btn-danger btn-sm me-2" type="submit">Clear All</button>
                        </form>
                        <a href="{{ route('infobarang.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                            <i class="bi bi-box-arrow-in-right"></i> Barang Masuk
                        </a>
                        <a href="{{ route('infobarang.keluar') }}" class="btn btn-primary btn-sm">
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
                    <form action="{{ route('infobarang.keluar') }}" method="GET" class="d-flex align-items-center gap-2 mb-3">
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
                        <a href="{{ route('infobarang.keluar') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    </form>

                    {{-- 🔍 Kolom Pencarian (tidak diubah) --}}
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

                    {{-- Guard --}}
                    @php
                        $keluarHistories = $keluarHistories ?? collect();
                        $startIndex = (method_exists($keluarHistories, 'firstItem') && $keluarHistories->firstItem()) ? $keluarHistories->firstItem() : 1;
                    @endphp

                    {{-- 📊 Tabel Data --}}
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:6%">No</th>
                                    <th>Nama Barang</th>
                                    <th style="width:12%">Total Keluar</th>
                                    <th style="width:14%">Stok Terakhir</th>
                                    <th style="width:18%">Terakhir Keluar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($keluarHistories as $i => $row)
                                    <tr>
                                        <td>{{ $startIndex + $loop->index }}</td>
                                        <td>{{ $row->barang->nama_barang ?? '-' }}</td>
                                        <td>{{ $row->qty }}</td>
                                        <td>{{ $row->stok_after }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->created_at)->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if(method_exists($keluarHistories, 'links'))
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted small">
                                Showing {{ $keluarHistories->firstItem() ?? 0 }} to {{ $keluarHistories->lastItem() ?? 0 }} of {{ $keluarHistories->total() }} results
                            </div>
                            <div>
                                {{ $keluarHistories->appends([
                                    'search' => request('search'),
                                    'month' => request('month'),
                                    'year' => request('year')
                                ])->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

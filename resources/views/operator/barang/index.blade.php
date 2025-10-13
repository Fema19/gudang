@extends('layouts.app')

@section('content')

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="page-title">Daftar Barang</h3>
        <a href="{{ route('barang.create') }}" class="btn btn-tambah">
            <i class="fas fa-plus"></i> Tambah Barang
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success-custom">
            {{ session('success') }}
        </div>
    @endif

    <div class="data-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                    <th>Foto</th>
                    <th>Nama Barang</th>
                    <th>Stok</th>
                    <th>Satuan</th>
                    <th>Kategori</th>
                    <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($barangs as $barang)
                    <tr>
                        <td>{{ $barang->kode_barang }}</td>
                        <td>
                            @if(!empty($barang->foto))
                                <img src="{{ asset('storage/' . $barang->foto) }}" alt="foto" class="table-img">
                            @endif
                        </td>
                        <td>{{ $barang->nama_barang }}</td>
                        <td>{{ $barang->stok }}</td>
                        <td>{{ $barang->satuan ?? '-' }}</td>
                        <td>{{ $barang->kategori ?? '-' }}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('barang.edit', $barang) }}" class="btn btn-edit btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('barang.destroy', $barang) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Yakin hapus barang ini?')" class="btn btn-hapus btn-sm">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data barang.</td></tr>
            @endforelse
        </tbody>
    </table>
        </div>
    </div>
</div>
@endsection

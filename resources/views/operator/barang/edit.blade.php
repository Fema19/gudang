@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-3">Edit Barang: {{ $barang->kode_barang }}</h3>

    <form action="{{ route('barang.update', $barang) }}" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="nama_barang" class="form-label">Nama Barang</label>
            <input type="text" id="nama_barang" name="nama_barang" class="form-control" value="{{ $barang->nama_barang }}" required>
        </div>

        <div class="mb-3">
            <label for="stok" class="form-label">Stok</label>
            <input type="number" id="stok" name="stok" class="form-control" value="{{ $barang->stok }}" required>
        </div>

        <div class="mb-3">
            <label for="satuan" class="form-label">Satuan</label>
            <input type="text" id="satuan" name="satuan" class="form-control" value="{{ $barang->satuan }}">
        </div>

        <div class="mb-3">
            <label for="kategori" class="form-label">Kategori</label>
            <input type="text" id="kategori" name="kategori" class="form-control" value="{{ $barang->kategori }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Foto Saat Ini</label>
            @if(!empty($barang->foto))
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $barang->foto) }}" alt="Foto {{ $barang->nama_barang }}" style="max-height:120px;">
                </div>
            @else
                <p class="text-muted">Belum ada foto.</p>
            @endif
        </div>

        <div class="mb-3">
            <label for="foto" class="form-label">Ganti Foto (opsional)</label>
            <input type="file" id="foto" name="foto" class="form-control" accept="image/*">
        </div>
        <button type="submit" class="btn btn-warning">Update</button>
        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Tambah Permintaan Barang</h4>

    <form action="{{ route('permintaan.store') }}" method="POST" class="mt-3">
        @csrf
        <div class="mb-3">
            <label>Barang</label>
            <select name="barang_id" class="form-select" required>
                <option value="">-- Pilih Barang --</option>
                @foreach ($barangs as $b)
                    <option value="{{ $b->id }}">{{ $b->nama_barang }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Nama Peminta</label>
            <input type="text" name="nama_peminta" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Nama Ruangan</label>
            <input type="text" name="nama_ruangan" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Jumlah</label>
            <input type="number" name="jumlah" class="form-control" required min="1">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('permintaan.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection

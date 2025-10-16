@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h3 class="page-heading">Tambah Barang</h3>

            <form action="{{ route('barang.store') }}" method="POST" enctype="multipart/form-data" class="card p-3 shadow-sm">
                @csrf

                <div class="mb-3">
                    <label for="nama_barang" class="form-label small">Nama Barang</label>
                    <input type="text" id="nama_barang" name="nama_barang" class="form-control form-control-sm" required>
                </div>

                <div class="mb-3">
                    <label for="stok" class="form-label small">Stok</label>
                    <input type="number" id="stok" name="stok" class="form-control form-control-sm" required>
                </div>

                <div class="mb-3">
                    <label for="satuan" class="form-label small">Satuan</label>
                    <input type="text" id="satuan" name="satuan" class="form-control form-control-sm">
                </div>

                <div class="mb-3">
                    <label for="kategori" class="form-label small">Kategori</label>
                    <input type="text" id="kategori" name="kategori" class="form-control form-control-sm">
                </div>

                <div class="mb-3">
                    <label for="foto" class="form-label small">Foto Barang (opsional)</label>
                    <input type="file" id="foto" name="foto" class="form-control form-control-sm" accept="image/*">
                </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('barang.index') }}" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button type="submit" class="btn btn-save">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
            </form>
        </div>
    </div>
</div>
@endsection

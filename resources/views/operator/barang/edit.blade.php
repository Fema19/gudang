@extends('layouts.app')

@section('content')
<div class="container pt-2">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <h3 class="page-heading">Edit Barang: {{ $barang->kode_barang }}</h3>

            <form action="{{ route('barang.update', $barang) }}" method="POST" enctype="multipart/form-data" class="card p-3 shadow-sm">
                @csrf
                @method('PUT')

                <div class="mb-2">
                    <label for="nama_barang" class="form-label small">Nama Barang</label>
                    <input type="text" id="nama_barang" name="nama_barang" class="form-control form-control-sm" value="{{ $barang->nama_barang }}" required>
                </div>

                <div class="mb-2">
                    <label for="stok" class="form-label small">Stok</label>
                    <input type="number" id="stok" name="stok" class="form-control form-control-sm" value="{{ $barang->stok }}" required>
                </div>

                <div class="mb-2">
                    <label for="satuan" class="form-label small">Satuan</label>
                    <input type="text" id="satuan" name="satuan" class="form-control form-control-sm" value="{{ $barang->satuan }}">
                </div>

                <div class="mb-2">
                    <label for="kategori" class="form-label small">Kategori</label>
                    <input type="text" id="kategori" name="kategori" class="form-control form-control-sm" value="{{ $barang->kategori }}">
                </div>

                <div class="mb-2">
                    <label class="form-label small">Foto Saat Ini</label>
                    @if(!empty($barang->foto))
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $barang->foto) }}" alt="Foto {{ $barang->nama_barang }}" class="preview-img">
                        </div>
                    @else
                        <p class="text-muted small">Belum ada foto.</p>
                    @endif
                </div>

                <div class="mb-2">
                    <label for="foto" class="form-label small">Ganti Foto (opsional)</label>
                    <input type="file" id="foto" name="foto" class="form-control form-control-sm" accept="image/*">
        </div>

        <div class="d-flex justify-content-center gap-3 mt-4">
            <button type="submit" class="btn btn-update btn-sm">
                <i class="fas fa-save"></i> Update
            </button>
            <a href="{{ route('barang.index') }}" class="btn btn-back btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
            </form>
        </div>
    </div>
</div>
@endsection

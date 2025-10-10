<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Permintaan Barang</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS Eksternal -->
    <link rel="stylesheet" href="{{ asset('css/request.css') }}">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container">
          <span class="solar--box-bold"></span>
           <a class="navbar-brand fw-semibold" href="{{ url('/') }}">
          <span style="color: #0d6efd;">Gudang</span><span style="color: #000;">Kantor</span>
            </a>

        </div>
    </nav>

    <!-- Konten Tengah -->
    <div class="container d-flex justify-content-center mt-5">
        <div class="request-box">
            <div class="text-center mb-4">
                <h4 class="fw-semibold" style="display: inline-block; position: relative; padding-bottom: 10px;">
                  Request Barang
                  <span style="
                    display: block;
                    height: 5px;          
                    width: 120px;         
                    background-color: #0d6efd;
                    border-radius: 10px;  
                    margin: 5px auto 0;" 
                   ></span>
                </h4>
            </div>




            <!-- Tombol Lihat Stok -->
            <div class="text-center mb-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#stokModal">
                    Lihat Stok
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('permintaan.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Barang</label>
                    <select name="barang_id" class="form-select custom-select" required>
                        <option value=""> Pilih Barang </option>
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

                <div class="mb-4">
                    <label>Jumlah</label>
                    <input type="number" name="jumlah" class="form-control" required min="1">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-4">Kirim</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Stok -->
    <div class="modal fade" id="stokModal" tabindex="-1" aria-labelledby="stokModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="stokModalLabel">Daftar Stok Barang</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if(count($barangs) > 0)
                        <div class="barang-grid">
                            @foreach ($barangs as $barang)
                                <div class="barang-card">
                                    <h6 class="barang-nama">{{ $barang->nama_barang }}</h6>
                                    <p><strong>Stok:</strong> {{ $barang->stok }}</p>
                                    <p><strong>Satuan:</strong> {{ $barang->satuan ?? '-' }}</p>
                                    <p><strong>Kategori:</strong> {{ $barang->kategori ?? '-' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted m-0">Belum ada data barang tersedia.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @if(session('success'))
    <script>alert("{{ session('success') }}");</script>
    @endif
</body>
</html>

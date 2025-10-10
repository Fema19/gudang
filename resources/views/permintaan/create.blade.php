<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Permintaan Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar sederhana -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">Gudang Kantor</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h4>Tambah Permintaan Barang</h4>

        <!-- Tombol Lihat Stok -->
        <button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#stokModal">
            Lihat Stok
        </button>

        <!-- Form Permintaan -->
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
        </form>

        <!-- Modal Pop-up Stok Barang -->
        <div class="modal fade" id="stokModal" tabindex="-1" aria-labelledby="stokModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="stokModalLabel">Daftar Stok Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <table class="table table-bordered">
                  <thead class="table-dark">
                    <tr>
                      <th>No</th>
                      <th>Nama Barang</th>
                      <th>Stok</th>
                      <th>Satuan</th>
                      <th>Kategori</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($barangs as $index => $barang)
                      <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $barang->nama_barang }}</td>
                        <td>{{ $barang->stok }}</td>
                        <td>{{ $barang->satuan ?? '-' }}</td>
                        <td>{{ $barang->kategori ?? '-' }}</td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="5" class="text-center">Belum ada data barang tersedia.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Notifikasi Success -->
    @if(session('success'))
    <script>
        alert("{{ session('success') }}");
    </script>
    @endif

</body>
</html>

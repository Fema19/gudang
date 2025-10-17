<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Permintaan Barang</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="{{ asset('css/barang.css') }}" rel="stylesheet">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
      <a class="navbar-brand" href="{{ url('/') }}">
        <span class="solar--box-bold"></span>
        <span class="brand-text">
          <span class="first-half">GUDANG</span><span class="second-half">KANTOR</span>
        </span>
      </a>
    </div>
  </nav>

  <div class="container d-flex justify-content-center mt-5">
    <div class="request-box shadow-sm p-4 rounded bg-white w-100" style="max-width: 600px;">
      <div class="text-center mb-4">
        <h4 class="fw-semibold">Permintaan Barang</h4>
      </div>

      @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
      @elseif ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="text-center mb-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#stokModal">
          Lihat Stok
        </button>
      </div>

      <form action="{{ route('permintaan.store') }}" method="POST" id="multiForm">
        @csrf

        <div class="mb-3">
          <label>Barang</label>
          <select id="barangSelect" class="form-select">
            <option value="">Pilih Barang</option>
            @foreach ($barangs as $b)
              <option value="{{ $b->id }}" data-nama="{{ $b->nama_barang }}" data-stok="{{ $b->stok }}">
                {{ $b->nama_barang }}
              </option>
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

        <div id="barangList" class="mt-4">
          <h6 class="mb-3 fw-semibold text-secondary">Daftar Barang Dipilih</h6>
          <p class="text-muted small" id="emptyText">Belum ada barang dipilih.</p>
        </div>

        <div class="text-center mt-4">
          <button type="submit" class="btn btn-primary px-4">Kirim Permintaan</button>
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
                  @if(!empty($barang->foto))
                    <div class="mb-2 text-center">
                      <img src="{{ asset('storage/' . $barang->foto) }}" alt="Foto {{ $barang->nama_barang }}" class="preview-img-modal">
                    </div>
                  @endif
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const select = document.getElementById("barangSelect");
      const list = document.getElementById("barangList");
      const emptyText = document.getElementById("emptyText");

      select.addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];
        const id = selectedOption.value;
        const nama = selectedOption.dataset.nama;
        const stok = selectedOption.dataset.stok;

        if (!id) return;

        // Cegah duplikasi barang
        if (document.getElementById(`barang-item-${id}`)) return;

        emptyText.style.display = "none";

        const div = document.createElement("div");
        div.classList.add("border", "p-3", "mb-3", "rounded");
        div.id = `barang-item-${id}`;
        div.innerHTML = `
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <input type="hidden" name="barangs[${id}][barang_id]" value="${id}">
              <strong>${nama}</strong><br>
              <small class="text-muted">Stok: ${stok}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button type="button" class="btn btn-outline-secondary btn-sm minus-btn">−</button>
              <input type="number" name="barangs[${id}][jumlah]" class="form-control text-center" value="1" min="1" max="${stok}" style="width:70px">
              <button type="button" class="btn btn-outline-success btn-sm plus-btn">+</button>
              <button type="button" class="btn btn-outline-danger btn-sm remove-btn">×</button>
            </div>
          </div>
          <div class="mt-2">
            <textarea name="barangs[${id}][catatan]" class="form-control form-control-sm" placeholder="Tambahkan catatan untuk ${nama}" rows="2"></textarea>
          </div>
        `;
        list.appendChild(div);

        // Reset dropdown
        this.value = "";
      });

      // Delegasi event untuk + - dan hapus
      list.addEventListener("click", function (e) {
        if (e.target.classList.contains("plus-btn")) {
          const input = e.target.parentElement.querySelector("input[type='number']");
          if (parseInt(input.value) < parseInt(input.max)) input.value++;
        } else if (e.target.classList.contains("minus-btn")) {
          const input = e.target.parentElement.querySelector("input[type='number']");
          if (parseInt(input.value) > 1) input.value--;
        } else if (e.target.classList.contains("remove-btn")) {
          e.target.closest(".border").remove();
          if (list.querySelectorAll(".border").length === 0) emptyText.style.display = "block";
        }
      });
    });
  </script>
</body>
</html>

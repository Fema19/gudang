@extends('layouts.app')

@section('content')
<div class="container mt-4">

  {{-- ✅ INTERNAL CSS --}}
  <style>
    /* === Tampilan umum === */
    h3 {
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    h3::before {
      content: "📊";
      font-size: 1.3rem;
    }

    .text-muted.small {
      font-size: 0.9rem;
    }

    /* === Tabel Statistik === */
    .custom-table {
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid #e6e6e6;
    }

    .custom-table th {
      background: #f9fafb;
      font-weight: 600;
      color: #333;
      border-bottom: 2px solid #eaeaea;
      vertical-align: middle;
    }

    .custom-table td {
      vertical-align: middle;
      color: #444;
      border-top: 1px solid #f0f0f0;
    }

    .custom-table tr:hover {
      background-color: #f8f9fa;
      transition: background 0.15s ease-in-out;
    }

    /* Kolom tanda tangan */
    .signature-img {
      width: 70px;
      height: 35px;
      object-fit: contain;
      border: 1px solid #ddd;
      border-radius: 4px;
      background: #fff;
      padding: 2px;
    }

    /* Tombol Export */
    #exportPdfBtn {
      display: flex;
      align-items: center;
      gap: 6px;
      font-weight: 500;
    }

    /* Form Filter */
    #filterForm select,
    #filterForm button {
      height: 40px;
    }

    /* Card section */
    .card {
      border-radius: 10px;
      border: 1px solid #ececec;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
  </style>

  {{-- === Header Section === --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="mb-0">Statistik Permintaan</h3>
      <div class="text-muted small">Lihat ringkasan permintaan per bulan</div>
    </div>

    <div class="d-flex gap-2">
      <button id="exportPdfBtn" type="button" class="btn btn-outline-primary">
        <i class="bi bi-file-earmark-pdf-fill"></i> Export to PDF
      </button>
    </div>
  </div>

  {{-- === Filter Form === --}}
  <div class="card mb-4">
    <div class="card-body">
      <form id="filterForm" method="GET" action="{{ route('permintaan.stats') }}" class="row g-2 align-items-center">
        <div class="col-auto">
          <select name="month" id="month" class="form-select">
            @foreach([1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"] as $m => $name)
              <option value="{{ $m }}" {{ (isset($selectedMonth) && $selectedMonth == $m) || (!isset($selectedMonth) && now()->month == $m) ? 'selected' : '' }}>
                {{ $name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-auto">
          @php
              $currentYear = now()->year;
              $start = $currentYear - 5;
              $end = $currentYear + 1;
          @endphp
          <select name="year" id="year" class="form-select">
            @for($y = $start; $y <= $end; $y++)
              <option value="{{ $y }}" {{ (isset($selectedYear) && $selectedYear == $y) || (!isset($selectedYear) && $currentYear == $y) ? 'selected' : '' }}>
                {{ $y }}
              </option>
            @endfor
          </select>
        </div>

        <div class="col-auto">
          <button type="submit" class="btn btn-primary">Tampilkan</button>
        </div>
      </form>
    </div>
  </div>

  {{-- === Statistik Table === --}}
  <div class="card" id="statsCard">
    <div class="card-body">
      <h5 class="card-title mb-3">
        Stats Gudang Kantor: 
        <span class="text-primary">
          {{ $selectedMonthName ?? \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM') }} 
          {{ $selectedYear ?? now()->year }}
        </span>
      </h5>

      @if(empty($stats) || count($stats) == 0)
        <div class="alert alert-info mb-0">Tidak ada data statistik untuk bulan ini.</div>
      @else
        <div class="table-responsive">
          <table class="table custom-table table-hover align-middle">
            <thead>
              <tr>
                <th>No.</th>
                <th>Barang</th>
                <th>Nama Peminta</th>
                <th>Ruangan</th>
                <th>Total Barang Diminta</th>
                <th>Status</th>
                <th>Tanda Tangan</th>
                <th>Tanggal</th>
              </tr>
            </thead>
            <tbody>
              @foreach($stats as $i => $row)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td style="min-width:200px">{{ $row['barang'] ?? '-' }}</td>
                <td>{{ $row['nama_peminta'] ?? '-' }}</td>
                <td>{{ $row['ruangan'] ?? '-' }}</td>
                <td><strong>{{ $row['total'] ?? 0 }}</strong></td>
                <td>
                  <span class="badge bg-warning text-dark">{{ ucfirst($row['status'] ?? '-') }}</span>
                </td>
                <td>
                  @if(!empty($row['tanda_tangan']))
                    <img src="{{ asset('storage/' . $row['tanda_tangan']) }}" alt="Tanda Tangan" class="signature-img">
                  @else
                    <span class="text-muted">—</span>
                  @endif
                </td>
                <td>
                  {{ $row['tanggal'] ?? '-' }}
                  @if(!empty($row['jam']))
                    <br><small class="text-muted">{{ $row['jam'] }} WIB</small>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- === PDF Export Script === --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    (function(){
      const exportBtn = document.getElementById('exportPdfBtn');
      const statsCard = document.getElementById('statsCard');
      const monthSelect = document.getElementById('month');
      const yearSelect = document.getElementById('year');

      exportBtn && exportBtn.addEventListener('click', async function(){
        if(!statsCard) return alert('Tidak ada data untuk diexport.');

        exportBtn.disabled = true;
        exportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Membuat PDF...';

        try {
          const canvas = await html2canvas(statsCard, { scale: 2 });
          const imgData = canvas.toDataURL('image/png');
          const { jsPDF } = window.jspdf;
          const pdf = new jsPDF('p', 'mm', 'a4');

          const pdfWidth = pdf.internal.pageSize.getWidth();
          const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
          pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);

          const fileName = `statistik-${yearSelect.value}-${monthSelect.value}.pdf`;
          pdf.save(fileName);
        } catch (err) {
          console.error(err);
          alert('Gagal membuat PDF.');
        } finally {
          exportBtn.disabled = false;
          exportBtn.innerHTML = '<i class="bi bi-file-earmark-pdf-fill"></i> Export to PDF';
        }
      });
    })();
  </script>

</div>
@endsection

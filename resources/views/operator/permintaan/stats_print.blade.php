@extends('layouts.app')

@section('content')
<div class="container mt-4">

  <style>
    /* === PRINT STYLES === */
    body {
      background: #fff;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #333;
    }

    h3 {
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 4px;
    }

    h3::before {
      content: "📄";
      font-size: 1.3rem;
    }

    .sub-title {
      color: #777;
      font-size: 0.9rem;
      margin-bottom: 16px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 8px 10px;
      vertical-align: middle;
    }

    th {
      background-color: #f9fafb;
      font-weight: 600;
      text-align: center;
    }

    td {
      color: #444;
    }

    tr:nth-child(even) {
      background-color: #fcfcfc;
    }

    .text-center {
      text-align: center;
    }

    .signature-img {
      width: 70px;
      height: 35px;
      object-fit: contain;
      border: 1px solid #ddd;
      border-radius: 4px;
      background: #fff;
      padding: 2px;
    }

    .footer {
      margin-top: 40px;
      font-size: 0.85rem;
      text-align: right;
      color: #666;
    }

    @media print {
      body {
        background: white;
        color: black;
      }
      .footer {
        position: fixed;
        bottom: 0;
        right: 0;
      }
    }
  </style>

  {{-- === Header === --}}
  <h3>Statistik Permintaan</h3>
  <div class="sub-title">
    Periode: {{ $selectedMonthName }} {{ $selectedYear }}
  </div>

  {{-- === Tabel Statistik === --}}
  <table>
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
      @forelse($stats as $i => $row)
        <tr>
          <td class="text-center">{{ $i + 1 }}</td>
          <td>{{ $row['barang'] ?? '-' }}</td>
          <td>{{ $row['nama_peminta'] ?? '-' }}</td>
          <td>{{ $row['ruangan'] ?? '-' }}</td>
          <td class="text-center"><strong>{{ $row['total'] ?? 0 }}</strong></td>
          <td class="text-center">{{ ucfirst($row['status'] ?? '-') }}</td>
          <td class="text-center">
            @if(!empty($row['tanda_tangan']))
              <img src="{{ public_path('storage/' . $row['tanda_tangan']) }}" alt="Tanda Tangan" class="signature-img">
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
      @empty
        <tr>
          <td colspan="8" class="text-center text-muted py-3">Tidak ada data.</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- === Footer Info === --}}
  <div class="footer">
    Dicetak pada {{ now()->timezone('Asia/Jakarta')->locale('id')->isoFormat('D MMMM YYYY [pukul] HH.mm [WIB]') }}
  </div>

</div>
@endsection

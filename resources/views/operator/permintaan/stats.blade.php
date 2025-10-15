@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Statistik Permintaan</h3>
            <div class="text-muted small">Lihat ringkasan permintaan per bulan</div>
        </div>

        <div class="d-flex gap-2">
            {{-- Export PDF: generate client-side PDF (html2canvas + jsPDF) --}}
            <button id="exportPdfBtn" type="button" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('permintaan.stats') }}" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label for="month" class="form-label visually-hidden">Bulan</label>
                    <select name="month" id="month" class="form-select">
                        @foreach([1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"] as $m => $name)
                            <option value="{{ $m }}" {{ (isset($selectedMonth) && $selectedMonth == $m) || (!isset($selectedMonth) && now()->month == $m) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <label for="year" class="form-label visually-hidden">Tahun</label>
                    <select name="year" id="year" class="form-select">
                        @php
                            $currentYear = now()->year;
                            $start = $currentYear - 5;
                            $end = $currentYear + 1;
                        @endphp
                        @for($y = $start; $y <= $end; $y++)
                            <option value="{{ $y }}" {{ (isset($selectedYear) && $selectedYear == $y) || (!isset($selectedYear) && $currentYear == $y) ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>

               
            </form>
        </div>
    </div>

    <div class="card" id="statsCard">
        <div class="card-body">
            <h5 class="card-title">Stats Gudang Kantor: {{ $selectedMonthName ?? 
                \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM') }} {{ $selectedYear ?? now()->year }}</h5>

            @if(empty($stats) || count($stats) == 0)
                <div class="alert alert-info">Tidak ada data statistik untuk bulan ini.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Barang</th>
                                <th>Nama Peminta</th>
                                <th>Ruangan</th>
                                <th>Total Barang Diminta</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td style="min-width:240px">{{ $row['barang'] ?? '-' }}</td>
                                    <td>{{ $row['nama_peminta'] ?? '-' }}</td>
                                    <td>{{ $row['ruangan'] ?? '-' }}</td>
                                    <td>{{ $row['total'] ?? 0 }}</td>
                                    <td>{{ ucfirst($row['status'] ?? '-') }}</td>
                                    <td>{{ $row['tanggal'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Inline JS: prev/next navigation + client-side PDF export using html2canvas + jsPDF --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script>
        (function(){
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const form = document.getElementById('filterForm');
            const exportBtn = document.getElementById('exportPdfBtn');
            const statsCard = document.getElementById('statsCard');

            prevBtn && prevBtn.addEventListener('click', function(){
                let m = parseInt(monthSelect.value, 10);
                let y = parseInt(yearSelect.value, 10);
                m -= 1;
                if(m < 1){ m = 12; y -= 1; }
                monthSelect.value = m;
                yearSelect.value = y;
                form.submit();
            });

            nextBtn && nextBtn.addEventListener('click', function(){
                let m = parseInt(monthSelect.value, 10);
                let y = parseInt(yearSelect.value, 10);
                m += 1;
                if(m > 12){ m = 1; y += 1; }
                monthSelect.value = m;
                yearSelect.value = y;
                form.submit();
            });

            // Client-side PDF export
            exportBtn && exportBtn.addEventListener('click', async function(){
                if(!statsCard) return alert('Tidak ada data untuk diexport.');

                // show a simple loading state
                exportBtn.disabled = true;
                exportBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Membuat PDF...';

                try {
                    const canvas = await html2canvas(statsCard, { scale: 2 });
                    const imgData = canvas.toDataURL('image/png');

                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a4');

                    const imgProps = pdf.getImageProperties(imgData);
                    const pdfWidth = pdf.internal.pageSize.getWidth();
                    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                    // if content height higher than page, add pages
                    let position = 0;
                    pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, pdfHeight);

                    // multiple pages handling
                    const pageHeight = pdf.internal.pageSize.getHeight();
                    if (pdfHeight > pageHeight) {
                        let remainingHeight = pdfHeight - pageHeight;
                        while (remainingHeight > 0) {
                            position = -(pageHeight * (Math.ceil((pdfHeight - remainingHeight) / pageHeight)));
                            pdf.addPage();
                            pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, pdfHeight);
                            remainingHeight -= pageHeight;
                        }
                    }

                    const m = monthSelect.value || '{{ $selectedMonth ?? now()->month }}';
                    const y = yearSelect.value || '{{ $selectedYear ?? now()->year }}';
                    const fileName = `statistik-${y}-${m}.pdf`;
                    pdf.save(fileName);
                } catch (err) {
                    console.error(err);
                    alert('Gagal membuat PDF. Coba lagi atau gunakan fitur export server-side.');
                } finally {
                    exportBtn.disabled = false;
                    exportBtn.innerHTML = '<i class="bi bi-file-earmark-pdf-fill me-1"></i> Export to PDF';
                }
            });
        })();
    </script>

@endsection

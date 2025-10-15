@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Statistik Permintaan - {{ $selectedMonthName }} {{ $selectedYear }}</h3>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Barang</th>
                    <th>Nama Peminta</th>
                    <th>Ruangan</th>
                    <th>Total Barang Diminta</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['barang'] ?? '-' }}</td>
                        <td>{{ $row['nama_peminta'] ?? '-' }}</td>
                        <td>{{ $row['ruangan'] ?? '-' }}</td>
                        <td>{{ $row['total'] }}</td>
                        <td>{{ ucfirst($row['status'] ?? '-') }}</td>
                        <td>{{ $row['tanggal'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

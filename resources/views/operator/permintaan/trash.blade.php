@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Trash - Riwayat Permintaan (Deleted)</h4>
        <div>
            <a href="{{ route('permintaan.index') }}" class="btn btn-secondary">Kembali</a>
            <form action="{{ route('permintaan.restoreAll') }}" method="POST" style="display:inline-block">
                @csrf
                <button class="btn btn-success">Restore All</button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-secondary">
            <tr>
                <th>#</th>
                <th>Barang</th>
                <th>Nama Peminta</th>
                <th>Ruangan</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
                <th>Dihapus Pada</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permintaans as $p)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $p->barang->nama_barang ?? '-' }}</td>
                <td>{{ $p->nama_peminta }}</td>
                <td>{{ $p->nama_ruangan }}</td>
                <td>{{ $p->jumlah }}</td>
                <td>{{ $p->keterangan }}</td>
                <td>{{ $p->deleted_at?->format('Y-m-d H:i') }}</td>
                <td>
                    <form action="{{ route('permintaan.restore', $p->id) }}" method="POST" style="display:inline-block">
                        @csrf
                        <button class="btn btn-sm btn-primary">Restore</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

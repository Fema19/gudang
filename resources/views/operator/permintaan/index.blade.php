@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Daftar Permintaan Barang</h4>

        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Actions
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('permintaan.trash') }}">Trash</a>
                    <a class="dropdown-item" href="{{ route('permintaan.stats') }}">Statistik</a>
                </li>
                <li>
                    <button class="dropdown-item text-danger" type="button" data-bs-toggle="modal" data-bs-target="#clearModal">Clear (soft-delete)</button>
                </li>
            </ul>
        </div>

        <!-- Modal konfirmasi Clear -->
        <div class="modal fade" id="clearModal" tabindex="-1" aria-labelledby="clearModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clearModalLabel">Konfirmasi Hapus Riwayat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Yakin ingin menghapus semua riwayat permintaan yang sudah diproses (diterima/ditolak)?
                        Data akan dipindahkan ke trash (soft delete) dan masih bisa dipulihkan nanti.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form action="{{ route('permintaan.clear') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered">
        <thead class="table-secondary">
            <tr>
                <th>#</th>
                <th>Barang (Jumlah)</th>
                <th>Nama Peminta</th>
                <th>Ruangan</th>
                <th>Total Barang Diminta</th>
                <th>Stok Tersisa</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($permintaans as $p)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <ul class="mb-0">
                        @foreach ($p->items as $item)
                            <li>{{ $item->barang->nama_barang ?? '-' }} ({{ $item->jumlah }})</li>
                        @endforeach
                    </ul>
                </td>
                <td>{{ $p->nama_peminta }}</td>
                <td>{{ $p->nama_ruangan }}</td>
                <td>{{ $p->items->sum('jumlah') }}</td>
                <td>
                    @foreach ($p->items as $item)
                        <div>{{ $item->barang->stok ?? '-' }}</div>
                    @endforeach
                </td>
                <td>
                    @if ($p->status == 'pending')
                        <span class="badge bg-warning text-dark">Pending</span>
                    @elseif ($p->status == 'selesai')
                        <span class="badge bg-success">Selesai</span>
                    @else
                        <span class="badge bg-danger">Ditolak</span>
                    @endif
                </td>
                <td>
                    @if ($p->status == 'pending')
                        <!-- Tombol terima -->
                        <form action="{{ route('permintaan.selesai', $p->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-success btn-sm">Terima</button>
                        </form>

                        <!-- Tombol tolak -->
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $p->id }}">
                            Tolak
                        </button>

                        <!-- Modal Tolak -->
                        <div class="modal fade" id="rejectModal{{ $p->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $p->id }}" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('permintaan.reject', $p->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="rejectModalLabel{{ $p->id }}">Tolak Permintaan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <textarea name="keterangan" class="form-control" placeholder="Alasan penolakan..." required></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Tolak</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted">Belum ada permintaan</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

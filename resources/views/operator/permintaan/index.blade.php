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
                        Tindakan ini akan memindahkan data ke trash (soft delete) dan dapat dipulihkan jika perlu.
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

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered align-middle text-center">
        <thead class="table-secondary">
            <tr>
                <th>No</th>
                <th>Barang</th>
                <th>Nama Peminta</th>
                <th>Ruangan</th>
                <th>Jumlah</th>
                <th>Tanda Tangan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permintaans as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    {{-- Barang yang diminta --}}
                    <td class="text-start">
                        <ul class="mb-0">
                            @foreach ($p->items as $item)
                                <li>
                                    {{ $item->barang->nama_barang ?? '-' }} 
                                    (<strong>{{ $item->jumlah }}</strong>)
                                    @if ($item->catatan)
                                        <br><small class="text-muted">Catatan: {{ $item->catatan }}</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </td>

                    {{-- Nama peminta dan waktu --}}
                    <td>
                        {{ $p->nama_peminta }}<br>
                        <small class="text-muted">{{ $p->created_at->format('d M Y, H:i') }} WIB</small>
                    </td>

                    <td>{{ $p->nama_ruangan }}</td>
                    <td>{{ $p->items->sum('jumlah') }}</td>

                    {{-- Kolom tanda tangan --}}
                    <td class="text-center">
                        @if($p->tanda_tangan)
                            <img src="{{ asset('storage/' . $p->tanda_tangan) }}" alt="Tanda Tangan" style="width: 80px; height: auto;">
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td>
                        @if ($p->status == 'pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif ($p->status == 'selesai')
                            <span class="badge bg-success">Selesai</span>
                        @else
                            <span class="badge bg-danger">Ditolak</span>
                        @endif
                    </td>

                    {{-- Aksi --}}
                    <td>
                        @if ($p->status == 'pending')
                            <form action="{{ route('permintaan.selesai', $p->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-success btn-sm">Selesai</button>
                            </form>

                            <!-- Tombol tolak -->
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $p->id }}">
                                Tolak
                            </button>

                            <!-- Modal tolak -->
                            <div class="modal fade" id="rejectModal{{ $p->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('permintaan.reject', $p->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tolak Permintaan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
            @endforeach
        </tbody>
    </table>
</div>
@endsection

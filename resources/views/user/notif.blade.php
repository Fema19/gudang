@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Notifikasi Anda</h4>

    @if (session('notif'))
        <div class="alert alert-warning mt-3">
            {{ session('notif') }}
        </div>
    @else
        <p class="text-muted mt-3">Belum ada notifikasi.</p>
    @endif
</div>
@endsection

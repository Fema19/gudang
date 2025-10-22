@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Info Barang Masuk</span>
                    <div>
                        <a href="{{ route('infobarang.index') }}" class="btn btn-primary btn-sm me-2">
                            <i class="bi bi-box-arrow-in-right"></i> Barang Masuk
                        </a>
                        <a href="{{ route('infobarang.keluar') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-box-arrow-left"></i> Barang Keluar
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Content goes here -->
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

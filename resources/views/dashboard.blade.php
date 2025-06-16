@extends('layouts.app')
@section('title', 'Dashboard')
@section('css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />
@endsection
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row g-6">
        <!-- Ventas del mes -->
        <div class="col-md-12 col-xxl-8">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-md-6 order-2 order-md-1">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Bienvenido ! <span class="fw-bold">{{ Auth::user()->name }}!</span> 🎉</h4>
                        </div>
                    </div>
                    <div class="col-md-6 text-center text-md-end order-1 order-md-2">
                        <div class="card-body pb-0 px-0 pt-2">
                        <img src="../../assets/img/illustrations/illustration-john-light.png"
                            height="186" class="scaleX-n1-rtl" alt="View Profile"
                            data-app-light-img="illustrations/illustration-john-light.png"
                            data-app-dark-img="illustrations/illustration-john-dark.png">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ventas del mes -->
    </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('assets/vendor/libs/moment/moment.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>
<script src="{{ asset('assets/js/app-ecommerce-dashboard.js') }}"></script>
<!-- <script src="{{ asset('pagesjs/home.js') }}"></script> -->
@endsection

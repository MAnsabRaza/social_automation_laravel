{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="card-hover bg-white p-6 rounded-2xl shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Total Users</h3>
                <p class="text-3xl font-bold text-indigo-600 mt-2">1,234</p>
            </div>
            <div class="p-3 rounded-full bg-indigo-100">
                <i class="fa-solid fa-users text-xl text-indigo-600"></i>
            </div>
        </div>
    </div>

    <div class="card-hover bg-white p-6 rounded-2xl shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Revenue</h3>
                <p class="text-3xl font-bold text-green-600 mt-2">$45,678</p>
            </div>
            <div class="p-3 rounded-full bg-green-100">
                <i class="fa-solid fa-dollar-sign text-xl text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="card-hover bg-white p-6 rounded-2xl shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-700">Active Sessions</h3>
                <p class="text-3xl font-bold text-purple-600 mt-2">892</p>
            </div>
            <div class="p-3 rounded-full bg-purple-100">
                <i class="fa-solid fa-wifi text-xl text-purple-600"></i>
            </div>
        </div>
    </div>
</div>
@endsection
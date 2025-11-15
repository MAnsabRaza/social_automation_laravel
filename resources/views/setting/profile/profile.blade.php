@extends('layouts.app')

@section('page-title', 'Profile')
@section('breadcrumb', 'Profile')

@section('content')
    <div class="space-y-8">

        <!-- FORM TAB -->
        <div id="content-form">

            <form action="{{ route('saveUser') }}" method="POST">
                @csrf
                <!-- Header Card -->
                <div class="shadow-lg rounded-2xl bg-gradient-to-r from-blue-50 to-blue-100 p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-blue-800 flex items-center gap-2">
                                <i class="fas fa-users text-blue-900"></i> Profile Management
                            </h2>
                            <p class="text-gray-600 mt-1 text-sm">Add and manage profile</p>
                        </div>

                        <div class="space-x-2">
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-white bg-green-600 hover:bg-green-700 shadow-md">
                                <i class="fa-solid fa-save mr-2"></i> Save
                            </button>

                        </div>
                    </div>
                </div>

                <!-- Sections 1-4 (example placeholders) -->
                <div class="shadow-md rounded-2xl bg-white p-6 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <input type="hidden" id="user_id" name="id" value="{{ $user->id }}">

                        <div>
                            <label class="block font-semibold mb-1">Name</label>
                            <input type="text" name="name" id="name" required value="{{ $user->name }}"
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Email</label>
                            <input type="email" name="email" id="email" required value="{{ $user->email }}"
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Password</label>
                            <input type="password" name="password" id="password"
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg">
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
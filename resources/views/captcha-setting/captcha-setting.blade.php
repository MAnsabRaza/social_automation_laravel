@extends('layouts.app')

@section('page-title', 'Captcha Setting')
@section('breadcrumb', 'Captcha Setting')

@section('content')
    <div class="space-y-8">

        <!-- FORM TAB -->
        <div>

            <form action="{{ route('createCaptcha') }}" method="POST">
                @csrf
                <input type="hidden" id="captcha_setting_id" name="id" value="">
                <!-- Header Card -->
                <div class="shadow-lg rounded-2xl bg-gradient-to-r from-blue-50 to-blue-100 p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-blue-800 flex items-center gap-2">
                                <i class="fas fa-robot text-blue-900"></i> Captcha Management
                            </h2>
                            <p class="text-gray-600 mt-1 text-sm">Add and manage captcha</p>
                        </div>

                        <div class="space-x-2">
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-white bg-green-600 hover:bg-green-700 shadow-md">
                                <i class="fa-solid fa-save mr-2"></i> Save
                            </button>

                            <button type="reset"
                                class="px-5 py-2.5 rounded-xl text-gray-700 bg-yellow-300 hover:bg-yellow-400 shadow-md">
                                <i class="fa-solid fa-rotate-right mr-2"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sections 1-4 (example placeholders) -->
                <div class="shadow-md rounded-2xl bg-white p-6 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block font-semibold mb-1">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="current_date" id="current_date" readonly
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg ts_datepicker">
                        </div>
                        <div>
                            
                            <label class="block font-semibold mb-1">Service Name</label>
                             <select name="service_name" required id="service_name"
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg">
                                <option value="">-- Select Service Name --</option>
                                <option value="2captcha">2Captcha</option>
                                <option value="anticaptcha">AntiCaptcha</option>
                                <option value="deathbycaptcha">DeathByCaptcha</option>
                            </select>
                        </div>
                         <div>
                            <label class="block font-semibold mb-1">Api Key</label>
                            <input type="text" name="api_key" id="api_key" required
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Status</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="checkbox" name="status" class="w-5 h-5" id="status">
                                <span class="text-gray-600">Active</span>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

             <div class="flex justify-between items-center mt-5">
                <h2 class="text-2xl font-bold text-blue-800">Captcha Setting</h2>
            </div>
            <div class="overflow-auto mt-6 border rounded-lg">
                <table class="table-auto w-full text-sm" id="captcha_setting_table">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Service Name</th>
                            <th class="p-3">Api Key</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DataTables will populate rows here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>


@endsection
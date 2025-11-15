@extends('layouts.app')

@section('page-title', 'Proxy')
@section('breadcrumb', 'Proxy')

@section('content')
    <div class="space-y-8">

        <!-- Tabs -->
        <div class="border-b border-gray-300 bg-white shadow-sm rounded-xl p-2">
            <nav class="flex space-x-6">

                <button id="tab-form"
                    class="px-4 py-2 text-sm font-semibold text-blue-900 border-b-2 border-blue-900 transition">
                    <i class="fa-solid fa-plus-circle mr-2"></i> Proxy Entry
                </button>

                <button id="tab-list"
                    class="px-4 py-2 text-sm font-semibold text-gray-500 hover:text-blue-900 border-b-2 border-transparent">
                    <i class="fa-solid fa-list mr-2"></i> Proxy List
                </button>

            </nav>
        </div>

        <!-- FORM TAB -->
        <div id="content-form">

            <form action="{{ route('createProxy') }}" method="POST">
                @csrf
                <input type="hidden" id="proxy_id" name="id" value="">
                <!-- Header Card -->
                <div class="shadow-lg rounded-2xl bg-gradient-to-r from-blue-50 to-blue-100 p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-blue-800 flex items-center gap-2">
                                <i class="fas fa-server text-blue-900"></i> Proxy Management
                            </h2>
                            <p class="text-gray-600 mt-1 text-sm">Add and manage proxy</p>
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

                            <button type="button" id="refresh-btn"
                                class="px-5 py-2.5 rounded-xl text-white bg-purple-500 hover:bg-purple-600 shadow-md">
                                <i class="fa-solid fa-arrows-rotate mr-2"></i> Refresh
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
                            <label class="block font-semibold mb-1">Proxy Type <span class="text-red-500">*</span></label>
                            <select name="proxy_type" required id="proxy_type"
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg">
                                <option value="">-- Select Proxy Type --</option>
                                <option value="http">HTTP</option>
                                <option value="https">HTTPS</option>
                                <option value="socks4">SOCKS4</option>
                                <option value="socks5">SOCKS5</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Proxy Host</label>
                            <input type="text" name="proxy_host" id="proxy_host" required
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Status</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="checkbox" name="is_active" class="w-5 h-5" id="is_active">
                                <span class="text-gray-600">Active</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="shadow-md rounded-2xl bg-white p-6 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block font-semibold mb-1">Proxy Port <span class="text-red-500">*</span></label>
                            <input type="text" name="proxy_port" required id="proxy_port"
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg"
                                placeholder="e.g. 192.168.1.1">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Username</label>
                            <input type="text" name="proxy_username" required id="proxy_username"
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Password</label>
                            <input type="password" name="proxy_password" required id="proxy_password"
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Last Used</label>
                            <input type="date" name="last_used" required id="last_used"
                                class="w-full p-2 border border-gray-400 focus:border-gray-600 rounded-lg">
                        </div>
                    </div>
                </div>



            </form>
        </div>

        <!-- LIST TAB -->
        <div id="content-list" class="hidden p-6">

            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-blue-800">Proxies</h2>

                <button id="btn-add-new" class="px-5 py-2 rounded-xl bg-green-600 text-white shadow-md">
                    <i class="fa-solid fa-plus mr-1"></i>Add New
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-auto mt-6 border rounded-lg">
                <table class="table-auto w-full text-sm" id="proxy_table">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">User Name</th>
                            <th class="p-3">Password</th>
                            <th class="p-3">Proxy Type</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Proxy Port</th>
                            <th class="p-3">Proxy Host</th>
                            <th class="p-3">Last Used</th>
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
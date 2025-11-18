@extends('layouts.app')

@section('page-title', 'social-account')
@section('breadcrumb', 'social-account')

@section('content')
    <div class="space-y-8">

        <!-- Tabs -->
        <div class="border-b border-gray-300 bg-white shadow-sm rounded-xl p-2">
            <nav class="flex space-x-6">

                <button id="tab-form"
                    class="px-4 py-2 text-sm font-semibold text-blue-900 border-b-2 border-blue-900 transition">
                    <i class="fa-solid fa-plus-circle mr-2"></i> Social Account Entry
                </button>

                <button id="tab-list"
                    class="px-4 py-2 text-sm font-semibold text-gray-500 hover:text-blue-900 border-b-2 border-transparent">
                    <i class="fa-solid fa-list mr-2"></i> Social Account List
                </button>

            </nav>
        </div>

        <!-- FORM TAB -->
        <div id="content-form">

            <form action="{{ route('createSocialAccounts') }}" method="POST">
                @csrf
                <input type="hidden" id="social_account_id" name="id" value="">

                <!-- Header Card -->
                <div class="shadow-lg rounded-2xl bg-gradient-to-r from-blue-50 to-blue-100 p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-blue-800 flex items-center gap-2">
                                <i class="fas fa-user-friends text-blue-900"></i> Social Account Management
                            </h2>
                            <p class="text-gray-600 mt-1 text-sm">Add and manage social account</p>
                        </div>

                        <div class="space-x-2">

                            <!-- Save -->
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl text-white bg-green-600 hover:bg-green-700 shadow-md">
                                <i class="fa-solid fa-save mr-2"></i> Save
                            </button>

                            <!-- Reset -->
                            <button type="reset"
                                class="px-5 py-2.5 rounded-xl text-gray-700 bg-yellow-300 hover:bg-yellow-400 shadow-md">
                                <i class="fa-solid fa-rotate-right mr-2"></i> Reset
                            </button>

                            <!-- Refresh -->
                            <button type="button" id="refresh-btn"
                                class="px-5 py-2.5 rounded-xl text-white bg-purple-500 hover:bg-purple-600 shadow-md">
                                <i class="fa-solid fa-arrows-rotate mr-2"></i> Refresh
                            </button>

                            <!-- Import CSV Button -->
                            <button type="button" id="importCsvBtn"
                                class="px-5 py-2.5 rounded-xl text-white bg-blue-700 hover:bg-blue-800 shadow-md">
                                <i class="fa-solid fa-file-csv mr-2"></i> Import CSV
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Section 1 -->
                <div class="shadow-md rounded-2xl bg-white p-6 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                        <div>
                            <label class="block font-semibold mb-1">Date <span class="text-red-500">*</span></label>
                            <input type="date" name="current_date" id="current_date" readonly
                                class="w-full p-2 border border-gray-400 rounded-lg ts_datepicker">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Platform <span class="text-red-500">*</span></label>
                            <select name="platform" required id="platform"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                                <option value="">-- Select Platform --</option>
                                <option value="facebook">Facebook</option>
                                <option value="instagram">Instagram</option>
                                <option value="youtube">Youtube</option>
                                <option value="linkedin">linkedin</option>
                                <option value="google_business">google_business</option>
                                <option value="tripadvisor">tripadvisor</option>
                                <option value="trustpilot">trustpilot</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Proxy</label>
                            <select name="proxy_id" id="proxy_id" class="w-full p-2 border border-gray-400 rounded-lg">
                                <option value="">-- Select Proxy --</option>
                                @foreach($proxy as $proxies)
                                    <option value="{{ $proxies->id }}">
                                        {{ $proxies->proxy_username }} - {{ $proxies->proxy_port }}:{{ $proxies->proxy_host }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Last Login</label>
                            <input type="date" name="last_login" required id="last_login"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>


                        <!-- <div>
                            <label class="block font-semibold mb-1">Status</label>
                            <select name="status" required id="status"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                                <option value="">-- Select Status --</option>
                                <option value="active">Active</option>
                                <option value="inactive">InActive</option>
                                <option value="banned">Banned</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div> -->

                    </div>
                </div>

                <!-- Section 2 -->
                <div class="shadow-md rounded-2xl bg-white p-6 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                        <div>
                            <label class="block font-semibold mb-1">Account UserName</label>
                            <input type="text" name="account_username" required id="account_username"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Account Email</label>
                            <input type="email" name="account_email" required id="account_email"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Account Password </label>
                            <input type="password" name="account_password" required id="account_password"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Account Phone </label>
                            <input type="text" name="account_phone" required id="account_phone"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>

                    </div>
                </div>

                <!-- Section 3 -->
                <!-- <div class="shadow-md rounded-2xl bg-white p-6 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <div>
                            <label class="block font-semibold mb-1">Auth Token</label>
                            <input type="text" name="auth_token" required id="auth_token"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Cookies</label>
                            <input type="text" name="cookies" required id="cookies"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Session Data</label>
                            <input type="text" name="session_data" required id="session_data"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>

                    </div>
                </div> -->

                <!-- Section 4 -->
                <!-- <div class="shadow-md rounded-2xl bg-white p-6 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <div>
                            <label class="block font-semibold mb-1">Last Login</label>
                            <input type="date" name="last_login" required id="last_login"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Warmup Level</label>
                            <input type="text" name="warmup_level" required id="warmup_level"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Daily Actions Count</label>
                            <input type="text" name="daily_actions_count" required id="daily_actions_count"
                                class="w-full p-2 border border-gray-400 rounded-lg">
                        </div>

                    </div>
                </div> -->

                <!-- resources/views/social-account/social-account.blade.php mein form mein -->
                <input type="hidden" name="cookies" value="">
                <input type="hidden" name="auth_token" value="">
                <input type="hidden" name="session_data" value="">

                <input type="hidden" name="warmup_level" value="0">
                <input type="hidden" name="daily_actions_count" value="0">
                <input type="hidden" name="status" value="pending">
            </form>

            <!-- HIDDEN CSV UPLOAD FORM -->
            <form id="csvImportForm" action="{{ route('importCSV') }}" method="POST" enctype="multipart/form-data"
                class="hidden">
                @csrf
                <input type="file" id="csvFileInput" name="csv_file" accept=".csv">
            </form>

        </div>

        <!-- LIST TAB -->
        <div id="content-list" class="hidden p-6">

            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-blue-800">Social Accounts</h2>

                <button id="btn-add-new" class="px-5 py-2 rounded-xl bg-green-600 text-white shadow-md">
                    <i class="fa-solid fa-plus mr-1"></i>Add New
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-auto mt-6 border rounded-lg">
                <table class="table-auto w-full text-sm" id="social_account_table">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">User Name</th>
                            <th class="p-3">Email</th>
                            <th class="p-3">Password</th>
                            <th class="p-3">Proxy</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Platform</th>
                            <th class="p-3">Auth Token</th>
                            <th class="p-3">Cookies</th>
                            <th class="p-3">Session Data</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>

@endsection
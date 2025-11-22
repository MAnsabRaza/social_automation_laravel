@extends('layouts.app')

@section('page-title', 'post-content')
@section('breadcrumb', 'post-content')

@section('content')
    <div class="space-y-8">

        <!-- Tabs -->
        <div class="border-b border-gray-300 bg-white shadow-sm rounded-xl p-2">
            <nav class="flex space-x-6">
                <button id="tab-form"
                    class="px-4 py-2 text-sm font-semibold text-blue-900 border-b-2 border-blue-900 transition">
                    <i class="fa-solid fa-plus-circle mr-2"></i> Post Content Entry
                </button>

                <button id="tab-list"
                    class="px-4 py-2 text-sm font-semibold text-gray-500 hover:text-blue-900 border-b-2 border-transparent">
                    <i class="fa-solid fa-list mr-2"></i> Post Content List
                </button>
            </nav>
        </div>

        <!-- FORM TAB -->
     <div id="content-form">

    <form action="{{ route('createPostContent') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" id="post_content_id">

        <!-- Header Section -->
        <div class="shadow-lg rounded-2xl bg-gradient-to-r from-blue-50 to-blue-100 p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-blue-800 flex items-center gap-2">
                        <i class="fas fa-pen-fancy text-blue-900"></i> Post Content Management
                    </h2>
                    <p class="text-gray-600 mt-1 text-sm">Add and manage post content account</p>
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

                 
                </div>
            </div>
        </div>

        <!-- Section 1 -->
        <div class="shadow-md rounded-2xl bg-white p-6 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div>
                    <label class="font-semibold">Date</label>
                    <input type="date" name="current_date" id="current_date"
                        class="w-full p-2 border rounded-lg ts_datepicker">
                </div>

                <div>
                    <label class="font-semibold">Title</label>
                    <input type="text" name="title" id="title" required
                        class="w-full p-2 border rounded-lg">
                </div>

                <div>
                    <label class="font-semibold">Account</label>
                    <select name="account_id" id="account_id" class="w-full p-2 border rounded-lg" required>
                        <option value="" disabled selected>Select Account</option>
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">
                                {{ $account->account_username }} - {{ $account->platform }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        <!-- Section 2 -->
        <div class="shadow-md rounded-2xl bg-white p-6 mt-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div>
                    <label class="font-semibold">Content</label>
                    <textarea name="content" id="content" class="w-full p-2 border rounded-lg" required></textarea>
                </div>

                <div>
                    <label class="font-semibold">Media Url</label>
                    <input type="file" name="media_urls" id="media_urls" class="w-full p-2 border rounded-lg" required>
                    <img id="previewImage" src="" class="w-32 h-32 mt-2 border hidden" />
                </div>

                <div>
                    <label class="font-semibold">Hashtags</label>
                    <input type="text" name="hashtags" id="hashtags" required
                        class="w-full p-2 border rounded-lg">
                </div>

            </div>
        </div>

    </form>
</div>

        <!-- LIST TAB -->
        <div id="content-list" class="hidden p-6">

            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-blue-800">Post Content List</h2>

                <button id="btn-add-new" class="px-5 py-2 rounded-xl bg-green-600 text-white shadow-md">
                    <i class="fa-solid fa-plus mr-1"></i> Add New
                </button>
            </div>

            <div class="overflow-auto mt-6 border rounded-lg">
                <table class="table-auto w-full text-sm" id="post_content_table">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Current Date</th>
                            <th class="p-3">Title</th>
                            <th class="p-3">Content</th>
                            <th class="p-3">Media Url</th>
                            <th class="p-3">Hashtags</th>
                            <th class="p-3">Account</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
@endsection
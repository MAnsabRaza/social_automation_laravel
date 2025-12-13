@extends('layouts.app')

@section('page-title', 'task')
@section('breadcrumb', 'task')

@section('content')
    <div class="space-y-8">

        <!-- Tabs -->
        <div class="border-b border-gray-300 bg-white shadow-sm rounded-xl p-2">
            <nav class="flex space-x-6">
                <button id="tab-form"
                    class="px-4 py-2 text-sm font-semibold text-blue-900 border-b-2 border-blue-900 transition">
                    <i class="fa-solid fa-plus-circle mr-2"></i> Task Entry
                </button>

                <button id="tab-list"
                    class="px-4 py-2 text-sm font-semibold text-gray-500 hover:text-blue-900 border-b-2 border-transparent">
                    <i class="fa-solid fa-list mr-2"></i> Task List
                </button>
            </nav>
        </div>

        <!-- FORM TAB -->
        <div id="content-form">

            <form action="{{ route('createTask') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="task_id">

                <div class="shadow-lg rounded-2xl bg-gradient-to-r from-blue-50 to-blue-100 p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-blue-800 flex items-center gap-2">
                                <i class="fas fa-tasks text-blue-900"></i> Task Management
                            </h2>
                            <p class="text-gray-600 mt-1 text-sm">Add and manage task account</p>
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
                            <label class="font-semibold">Task Type</label>
                            <select name="task_type" id="task_type" class="w-full p-2 border rounded-lg">
                                <option value="">-- Select Task Type --</option>
                                <option value="post">Post</option>
                                <option value="comment">Comment</option>
                                <option value="like">Like</option>
                                <option value="follow">Follow</option>
                                <option value="unfollow">Unfollow</option>
                                <option value="share">Share</option>
                                <option value="review">Review</option>
                            </select>
                        </div>

                        <div>
                            <label class="font-semibold">Select Account</label>
                            <select name="account_id" id="account_id" class="w-full p-2 border rounded-lg">
                                <option value="">-- Select Account --</option>
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_username }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                <!-- Section 2 -->
                <div class="shadow-md rounded-2xl bg-white p-6 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                        <div class="mt-4">
                            <label class="font-semibold">Target URL</label>
                            <input type="text" name="target_url" id="target_url" class="w-full p-2 border rounded-lg">
                        </div>
                        <div class="mt-4">
                            <label class="font-semibold">Schedule Time</label>
                            <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                                class="w-full p-2 border rounded-lg">
                        </div>
                        <div class="mt-4">
                            <label class="font-semibold">Executed At</label>
                            <input type="datetime-local" name="executed_at" id="executed_at"
                                class="w-full p-2 border rounded-lg">
                        </div>
                    </div>
                </div>
                <div id="post-fields" class="shadow-md rounded-2xl bg-white p-6 mt-4 hidden">
                    <h3 class="text-lg font-bold text-blue-800 mb-4">Post Details</h3>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="font-semibold">Post Content</label>
                            <textarea name="content" id="content" class="w-full p-2 border rounded-lg"
                                placeholder="Write caption..."></textarea>
                        </div>

                        <div>
                            <label class="font-semibold">Hashtags</label>
                            <input type="text" name="hashtags" id="hashtags" class="w-full p-2 border rounded-lg"
                                placeholder="#instagram #automation">
                        </div>

                        <div>
                            <label class="font-semibold">Media URLs (Base64 / URL JSON)</label>
                            <textarea name="media_urls" id="media_urls" class="w-full p-2 border rounded-lg"
                                placeholder='["data:image/jpeg;base64,..."]'></textarea>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <!-- LIST TAB -->
        <div id="content-list" class="hidden p-6">

            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-blue-800">Task List</h2>

                <button id="btn-add-new" class="px-5 py-2 rounded-xl bg-green-600 text-white shadow-md">
                    <i class="fa-solid fa-plus mr-1"></i> Add New
                </button>
            </div>

            <div class="overflow-auto mt-6 border rounded-lg">
                <table class="table-auto w-full text-sm" id="task_table">
                    <thead class="bg-blue-900 text-white">
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Current Date</th>
                            <th class="p-3">Task Type</th>
                            <th class="p-3">Target Url</th>
                            <th class="p-3">Scheduled At</th>
                            <th class="p-3">Executed At</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
@endsection
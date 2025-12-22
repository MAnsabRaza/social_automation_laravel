{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('page-title', 'Admin Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Stats Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Total Accounts --}}
        <div class="card-hover bg-white p-6 rounded-2xl shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-600">Total Accounts</h3>
                    <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $totalAccounts }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-xs text-green-600">{{ $activeAccounts }} Active</span>
                        <span class="text-xs text-red-600">{{ $failedAccounts }} Failed</span>
                    </div>
                </div>
                <div class="p-3 rounded-full bg-indigo-100">
                    <i class="fa-solid fa-users text-2xl text-indigo-600"></i>
                </div>
            </div>
        </div>

        {{-- Logged In Accounts --}}
        <div class="card-hover bg-white p-6 rounded-2xl shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-600">Logged In</h3>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ $loggedInAccounts }}</p>
                    <p class="text-xs text-gray-500 mt-2">Out of {{ $totalAccounts }} accounts</p>
                </div>
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fa-solid fa-check-circle text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        {{-- Running Tasks --}}
        <div class="card-hover bg-white p-6 rounded-2xl shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-600">Running Tasks</h3>
                    <p class="text-3xl font-bold text-purple-600 mt-2">{{ $runningTasks }}</p>
                    <p class="text-xs text-gray-500 mt-2">{{ $scheduledTasks }} Scheduled</p>
                </div>
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fa-solid fa-spinner text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        {{-- Completed Tasks --}}
        <div class="card-hover bg-white p-6 rounded-2xl shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-600">Completed Tasks</h3>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $completedTasks }}</p>
                    <p class="text-xs text-red-600 mt-2">{{ $failedTasks }} Failed</p>
                </div>
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fa-solid fa-check-double text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Account Monitoring Section --}}
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fa-solid fa-shield-halved mr-2"></i>Account Monitoring
            </h2>
            <div class="flex items-center gap-3">
                <select id="platformFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="all">All Platforms</option>
                    <option value="facebook">Facebook</option>
                    <option value="twitter">Twitter</option>
                    <option value="instagram">Instagram</option>
                    <option value="linkedin">LinkedIn</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Platform</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Username</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Warmup Level</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Last Login</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="accountsTableBody">
                    @forelse($accounts as $account)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @php
                                    $platformIcons = [
                                        'facebook' => ['icon' => 'fa-facebook', 'color' => 'text-blue-600'],
                                        'twitter' => ['icon' => 'fa-twitter', 'color' => 'text-sky-500'],
                                        'instagram' => ['icon' => 'fa-instagram', 'color' => 'text-pink-600'],
                                        'linkedin' => ['icon' => 'fa-linkedin', 'color' => 'text-blue-700'],
                                    ];
                                    $platform = $platformIcons[$account->platform] ?? ['icon' => 'fa-globe', 'color' => 'text-gray-600'];
                                @endphp
                                <i class="fa-brands {{ $platform['icon'] }} {{ $platform['color'] }} text-lg"></i>
                                <span class="font-medium capitalize">{{ $account->platform }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $account->account_username ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $account->account_email ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusClasses = [
                                    'logged_in' => 'bg-green-100 text-green-800',
                                    'login_failed' => 'bg-red-100 text-red-800',
                                    'active' => 'bg-blue-100 text-blue-800',
                                    'inactive' => 'bg-gray-100 text-gray-800',
                                ];
                                $statusClass = $statusClasses[$account->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $account->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($account->warmup_level ?? 0) * 10 }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600">{{ $account->warmup_level ?? 0 }}/10</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $account->last_login ? $account->last_login->diffForHumans() : 'Never' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <i class="fa-solid fa-inbox text-4xl mb-2"></i>
                            <p>No accounts found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Task Status Monitoring --}}
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fa-solid fa-tasks mr-2"></i>Task Status Monitoring
            </h2>
            <div class="flex items-center gap-3">
                <select id="taskStatusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="all">All Tasks</option>
                    <option value="running">Running</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Task ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Account</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Task Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Scheduled At</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Executed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="tasksTableBody">
                    @forelse($recentTasks as $task)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-mono text-sm text-gray-700">#{{ $task->id }}</td>
                        <td class="px-4 py-3">
                            @if($task->socialAccount)
                            <div class="flex items-center gap-2">
                                @php
                                    $platformIcons = [
                                        'facebook' => ['icon' => 'fa-facebook', 'color' => 'text-blue-600'],
                                        'twitter' => ['icon' => 'fa-twitter', 'color' => 'text-sky-500'],
                                        'instagram' => ['icon' => 'fa-instagram', 'color' => 'text-pink-600'],
                                        'linkedin' => ['icon' => 'fa-linkedin', 'color' => 'text-blue-700'],
                                    ];
                                    $platform = $platformIcons[$task->socialAccount->platform] ?? ['icon' => 'fa-globe', 'color' => 'text-gray-600'];
                                @endphp
                                <i class="fa-brands {{ $platform['icon'] }} {{ $platform['color'] }}"></i>
                                <span class="text-sm">{{ $task->socialAccount->account_username ?? 'N/A' }}</span>
                            </div>
                            @else
                            <span class="text-sm text-gray-500">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-medium">
                                {{ ucfirst($task->task_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $taskStatusClasses = [
                                    'running' => 'bg-yellow-100 text-yellow-800',
                                    'scheduled' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                ];
                                $taskStatusClass = $taskStatusClasses[$task->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $taskStatusClass }}">
                                {{ ucfirst($task->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $task->scheduled_at ? \Carbon\Carbon::parse($task->scheduled_at)->format('M d, Y H:i') : 'N/A' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $task->executed_at ? \Carbon\Carbon::parse($task->executed_at)->format('M d, Y H:i') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <i class="fa-solid fa-tasks text-4xl mb-2"></i>
                            <p>No tasks found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Platform filter
    const platformFilter = document.getElementById('platformFilter');
    if (platformFilter) {
        platformFilter.addEventListener('change', function() {
            const platform = this.value;
            // You can implement AJAX filtering here if needed
            console.log('Filter by platform:', platform);
        });
    }

    // Task status filter
    const taskStatusFilter = document.getElementById('taskStatusFilter');
    if (taskStatusFilter) {
        taskStatusFilter.addEventListener('change', function() {
            const status = this.value;
            // You can implement AJAX filtering here if needed
            console.log('Filter by status:', status);
        });
    }
});
</script>
@endpush
@endsection
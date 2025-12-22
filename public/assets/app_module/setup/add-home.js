let HomeController = function () {
    return {
        init: function () {
            const platformIcons = {
                facebook: { icon: 'fa-facebook', color: 'text-blue-600' },
                twitter: { icon: 'fa-twitter', color: 'text-sky-500' },
                instagram: { icon: 'fa-instagram', color: 'text-pink-600' },
                linkedin: { icon: 'fa-linkedin', color: 'text-blue-700' }
            };

            const accountStatusClasses = {
                logged_in: 'bg-green-100 text-green-800',
                login_failed: 'bg-red-100 text-red-800',
                active: 'bg-blue-100 text-blue-800',
                inactive: 'bg-gray-100 text-gray-800'
            };

            const taskStatusClasses = {
                running: 'bg-yellow-100 text-yellow-800',
                scheduled: 'bg-blue-100 text-blue-800',
                completed: 'bg-green-100 text-green-800',
                failed: 'bg-red-100 text-red-800'
            };

            // Platform filter
            $('#platformFilter').on('change', function () {
                const platform = $(this).val();
                $.ajax({
                    url: "/getAccountsByPlatform",
                    data: { platform: platform },
                    dataType: 'json',
                    success: function (data) {
                        updateAccountsTable(data.accounts);
                    },
                    error: function (err) {
                        console.error('Error:', err);
                    }
                });
            });

            // Task status filter
            $('#taskStatusFilter').on('change', function () {
                const status = $(this).val();
                $.ajax({
                    url: "/getTasksByStatus",
                    data: { status: status },
                    dataType: 'json',
                    success: function (data) {
                        updateTasksTable(data.tasks);
                    },
                    error: function (err) {
                        console.error('Error:', err);
                    }
                });
            });

            // Update accounts table
            function updateAccountsTable(accounts) {
                const $tbody = $('#accountsTableBody');
                if (!accounts || accounts.length === 0) {
                    $tbody.html(`
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <i class="fa-solid fa-inbox text-4xl mb-2"></i>
                                <p>No accounts found</p>
                            </td>
                        </tr>
                    `);
                    return;
                }

                let html = '';
                $.each(accounts, function (i, account) {
                    const platform = platformIcons[account.platform] || { icon: 'fa-globe', color: 'text-gray-600' };
                    const statusClass = accountStatusClasses[account.status] || 'bg-gray-100 text-gray-800';
                    const warmupLevel = account.warmup_level || 0;
                    const lastLogin = account.last_login ? formatDate(account.last_login) : 'Never';

                    html += `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <i class="fa-brands ${platform.icon} ${platform.color} text-lg"></i>
                                    <span class="font-medium capitalize">${account.platform}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">${account.account_username || 'N/A'}</td>
                            <td class="px-4 py-3 text-gray-700">${account.account_email || 'N/A'}</td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusClass}">
                                    ${account.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: ${warmupLevel * 10}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600">${warmupLevel}/10</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">${lastLogin}</td>
                        </tr>
                    `;
                });

                $tbody.html(html);
            }

            // Update tasks table
            function updateTasksTable(tasks) {
                const $tbody = $('#tasksTableBody');
                if (!tasks || tasks.length === 0) {
                    $tbody.html(`
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <i class="fa-solid fa-tasks text-4xl mb-2"></i>
                                <p>No tasks found</p>
                            </td>
                        </tr>
                    `);
                    return;
                }

                let html = '';
                $.each(tasks, function (i, task) {
                    const platform = task.socialAccount ? (platformIcons[task.socialAccount.platform] || { icon: 'fa-globe', color: 'text-gray-600' }) : { icon: 'fa-globe', color: 'text-gray-600' };
                    const statusClass = taskStatusClasses[task.status] || 'bg-gray-100 text-gray-800';
                    const scheduledAt = task.scheduled_at ? formatDateTime(task.scheduled_at) : 'N/A';
                    const executedAt = task.executed_at ? formatDateTime(task.executed_at) : '-';

                    html += `
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-mono text-sm text-gray-700">#${task.id}</td>
                            <td class="px-4 py-3">
                                ${task.socialAccount ? `
                                    <div class="flex items-center gap-2">
                                        <i class="fa-brands ${platform.icon} ${platform.color}"></i>
                                        <span class="text-sm">${task.socialAccount.account_username || 'N/A'}</span>
                                    </div>
                                ` : '<span class="text-sm text-gray-500">N/A</span>'}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-medium">
                                    ${task.task_type.charAt(0).toUpperCase() + task.task_type.slice(1)}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusClass}">
                                    ${task.status.charAt(0).toUpperCase() + task.status.slice(1)}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">${scheduledAt}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">${executedAt}</td>
                        </tr>
                    `;
                });

                $tbody.html(html);
            }

            // Helpers
            function formatDate(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diffTime = now - date;
                const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

                if (diffDays < 1) return 'Today';
                if (diffDays === 1) return 'Yesterday';
                if (diffDays < 7) return `${diffDays} days ago`;

                return date.toLocaleDateString();
            }

            function formatDateTime(dateString) {
                const date = new Date(dateString);
                return date.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
        }
    }
}();

$(document).ready(function () {
    HomeController.init();
});

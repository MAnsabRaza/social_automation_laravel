let TaskController = function () {
    const $tabForm = $("#tab-form");
    const $tabList = $("#tab-list");
    const $contentForm = $("#content-form");
    const $contentList = $("#content-list");
    const $btnAddNew = $("#btn-add-new");
    const $taskType = $("#task_type");
    const $postFields = $("#post-fields");
    const $commentFields = $("#comment-fields");
    const $executedAt = $("#executed_at");

    // Track active scroll bots
    const activeScrollBots = new Set();

    function showFormTab() {
        $tabForm
            .addClass("text-blue-900 border-blue-900")
            .removeClass("text-gray-500");
        $contentForm.removeClass("hidden");
        $tabList
            .addClass("text-gray-500")
            .removeClass("text-blue-900 border-blue-900");
        $contentList.addClass("hidden");
    }

    function showListTab() {
        $tabList
            .addClass("text-blue-900 border-blue-900")
            .removeClass("text-gray-500");
        $contentList.removeClass("hidden");
        $tabForm
            .addClass("text-gray-500")
            .removeClass("text-blue-900 border-blue-900");
        $contentForm.addClass("hidden");
    }

    $tabForm.on("click", showFormTab);
    $tabList.on("click", showListTab);

    $btnAddNew.on("click", () => {
        showFormTab();
        $("#taskForm")[0].reset();
        $("#task_id").val("");
        $("#previewImage").addClass("hidden");
        toggleTaskFields(null);
        setMinimumExecutionTime();
    });

    function toggleTaskFields(type) {
        $postFields.addClass("hidden");
        $commentFields.addClass("hidden");
        $("#target_url").prop("readonly", false);

        if (type === "post") {
            $postFields.removeClass("hidden");
            $("#target_url").prop("readonly", true).val("");
        }

        if (type === "comment") {
            $commentFields.removeClass("hidden");
        }
    }

    $taskType.on("change", function () {
        toggleTaskFields($(this).val());
    });

    // Set minimum execution time to current time
    function setMinimumExecutionTime() {
        const now = new Date();
        // Add 1 minute buffer to ensure it's in the future
        now.setMinutes(now.getMinutes() + 1);
        
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        $executedAt.attr('min', minDateTime);
        
        // If field is empty, set it to minimum time
        if (!$executedAt.val()) {
            $executedAt.val(minDateTime);
        }
    }

    // Validate execution time before submission
    function validateExecutionTime() {
        const selectedTime = new Date($executedAt.val());
        const now = new Date();
        
        if (selectedTime < now) {
            Toastify({
                text: "⚠️ Execution time cannot be in the past. Please select current or future time.",
                duration: 4000,
                close: true,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
                backgroundColor: "#f44336",
            }).showToast();
            return false;
        }
        
        return true;
    }

    const populateData = function (elem) {
        $("#current_date").val(elem.current_date);
        $("#task_id").val(elem.id);
        $("#account_id").val(elem.account_id);
        $("#task_type").val(elem.task_type);
        $("#target_url").val(elem.target_url);
        $("#scheduled_at").val(elem.scheduled_at);
        
        // Format executed_at for datetime-local input
        if (elem.executed_at) {
            const date = new Date(elem.executed_at);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            $("#executed_at").val(`${year}-${month}-${day}T${hours}:${minutes}`);
        }
        
        $("#content").val(elem.content);
        $("#hashtags").val(elem.hashtags);
        $("#comment").val(elem.comment);
        
        if (elem.media_urls_full) {
            $("#previewImage").attr("src", elem.media_urls_full).removeClass("hidden");
        }
        
        toggleTaskFields(elem.task_type);
    };

    const fetchTaskData = async (id) => {
        $.ajax({
            type: "GET",
            url: "/fetchTaskData/" + id,
            dataType: "JSON",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.data) {
                    populateData(response.data);
                } else {
                    Toastify({
                        text: "Data not found for this task",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                        backgroundColor: "#f44336",
                    }).showToast();
                }
            },
            error: function (error) {
                Toastify({
                    text: error.responseJSON?.message || "Error fetching task",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                    backgroundColor: "#f44336",
                }).showToast();
            },
        });
    };

    const deleteTaskData = function (id) {
        if (!confirm("Are you sure you want to delete this task?")) return;

        $.ajax({
            type: "DELETE",
            url: "/deleteTask/" + id,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                if (res.success) {
                    Toastify({
                        text: res.message,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                        backgroundColor: "#4BB543",
                    }).showToast();
                    table.ajax.reload();
                }
            },
            error: function (err) {
                Toastify({
                    text: err.responseJSON?.message || "Error deleting task",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                    backgroundColor: "#f44336",
                }).showToast();
            },
        });
    };

    const stopScrollBot = function (accountId, $button) {
        $.ajax({
            type: "POST",
            url: "/stop-scroll",
            dataType: "JSON",
            data: { account_id: accountId },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success) {
                    Toastify({
                        text: "🛑 Scroll bot stopped successfully!",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                        backgroundColor: "#4BB543",
                    }).showToast();

                    activeScrollBots.delete(accountId);
                    $button.removeClass("bg-red-600 hover:bg-red-700");
                    $button.addClass("bg-gray-400 cursor-not-allowed");
                    $button.prop("disabled", true);
                    $button.html('<i class="fa-solid fa-stop-circle"></i> Stopped');

                    setTimeout(() => {
                        table.ajax.reload();
                    }, 2000);
                } else {
                    Toastify({
                        text: response.message || "Failed to stop scroll bot",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                        backgroundColor: "#f44336",
                    }).showToast();
                }
            },
            error: function (error) {
                Toastify({
                    text: error.responseJSON?.message || "Error stopping scroll bot",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                    backgroundColor: "#f44336",
                }).showToast();
            },
        });
    };

    const getScrollStatus = function (accountId, $statusElement) {
        $.ajax({
            type: "POST",
            url: "/scroll-status",
            dataType: "JSON",
            data: { account_id: accountId },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if (response.success && response.isRunning) {
                    const stats = response.stats;
                    $statusElement.html(`
                        <div class="text-xs text-green-600">
                            ▶ Scrolls: ${stats.scrolls} | ❤️ Likes: ${stats.likes} | 💬 Comments: ${stats.comments}
                        </div>
                    `);
                } else {
                    $statusElement.html('<div class="text-xs text-gray-400">Not running</div>');
                }
            },
        });
    };

    let table;

    return {
        init: function () {
            // Set minimum execution time on page load
            setMinimumExecutionTime();
            
            // Update minimum time every minute
            setInterval(setMinimumExecutionTime, 60000);

            table = $("#task_table").DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: "/getTaskData",
                columns: [
                    { data: "id", name: "id" },
                    { data: "current_date", name: "current_date" },
                    { data: "task_type", name: "task_type" },
                    { 
                        data: "status", 
                        name: "status",
                        render: function(data, type, row) {
                            let badgeClass = "bg-gray-500";
                            let icon = "";
                            
                            if (data === "completed") {
                                badgeClass = "bg-green-500";
                                icon = "✓";
                            }
                            if (data === "running") {
                                badgeClass = "bg-blue-500";
                                icon = "▶";
                            }
                            if (data === "failed") {
                                badgeClass = "bg-red-500";
                                icon = "✗";
                            }
                            if (data === "pending") {
                                badgeClass = "bg-yellow-500";
                                icon = "⏳";
                            }
                            
                            return `<span class="px-2 py-1 rounded text-white text-xs ${badgeClass}">${icon} ${data}</span>`;
                        }
                    },
                    { data: "scheduled_at", name: "scheduled_at" },
                    { 
                        data: "executed_at", 
                        name: "executed_at",
                        render: function(data, type, row) {
                            if (!data || data === 'N/A') return 'N/A';
                            
                            const executeTime = new Date(data);
                            const now = new Date();
                            const isPast = executeTime < now;
                            const isFuture = executeTime > now;
                            
                            let colorClass = '';
                            if (isPast) colorClass = 'text-red-600';
                            else if (isFuture) colorClass = 'text-blue-600';
                            else colorClass = 'text-green-600';
                            
                            return `<span class="${colorClass}">${data}</span>`;
                        }
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            let buttons = `
                                <button class="edit-btn px-2 py-1 border border-blue-600 rounded text-blue-600 hover:bg-blue-600 hover:text-white" data-id="${row.id}">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <button class="delete-btn px-2 py-1 border border-red-600 rounded text-red-600 hover:bg-red-600 hover:text-white" data-id="${row.id}">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            `;

                            if ((row.task_type === "scroll" || row.task_type === "share") && row.status === "running") {
                                buttons += `
                                    <button class="stop-scroll-btn px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 ml-1" 
                                            data-account-id="${row.account_id}">
                                        <i class="fa-solid fa-stop-circle"></i> Stop
                                    </button>
                                    <div class="scroll-status-${row.account_id} mt-1"></div>
                                `;
                            }

                            return buttons;
                        },
                        orderable: false,
                        searchable: false,
                    },
                ],
                drawCallback: function() {
                    $(".stop-scroll-btn").each(function() {
                        const accountId = $(this).data("account-id");
                        const $statusElement = $(`.scroll-status-${accountId}`);
                        
                        if ($statusElement.length) {
                            getScrollStatus(accountId, $statusElement);
                            
                            const intervalId = setInterval(() => {
                                if ($(this).prop("disabled")) {
                                    clearInterval(intervalId);
                                    return;
                                }
                                getScrollStatus(accountId, $statusElement);
                            }, 5000);
                        }
                    });
                }
            });

            // Form submission with validation
            $("#taskForm").submit(function (e) {
                // Validate execution time
                if (!validateExecutionTime()) {
                    e.preventDefault();
                    return false;
                }
            });

            // Edit button
            $("#task_table").on("click", ".edit-btn", function () {
                const id = $(this).data("id");
                fetchTaskData(id);
                showFormTab();
            });

            // Delete button
            $("#task_table").on("click", ".delete-btn", function () {
                const id = $(this).data("id");
                deleteTaskData(id);
            });

            // Stop scroll button
            $("#task_table").on("click", ".stop-scroll-btn", function () {
                const accountId = $(this).data("account-id");
                const $button = $(this);
                
                if (confirm("Are you sure you want to stop the scroll bot?")) {
                    stopScrollBot(accountId, $button);
                }
            });

            // Refresh button
            $("#refresh-btn").on("click", function () {
                table.ajax.reload();
                Toastify({
                    text: "🔄 Table refreshed!",
                    duration: 2000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    stopOnFocus: true,
                    backgroundColor: "#4BB543",
                }).showToast();
            });
        },
    };
};

const task = new TaskController();
task.init();
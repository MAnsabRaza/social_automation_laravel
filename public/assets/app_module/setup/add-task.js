let TaskController = function () {
    const $tabForm = $("#tab-form");
    const $tabList = $("#tab-list");
    const $contentForm = $("#content-form");
    const $contentList = $("#content-list");
    const $btnAddNew = $("#btn-add-new");
    const $taskType = $("#task_type");
    const $postFields = $("#post-fields");
    const $commentFields = $("#comment-fields");

    // 🔥 Track active scroll bots
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
        $("#proxyForm")[0].reset();
        $("#proxy_id").val("");
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

    const populateData = function (elem) {
        $("#current_date").val(elem.current_date);
        $("#task_id").val(elem.id);
        $("#account_id").val(elem.account_id);
        $("#task_type").val(elem.task_type);
        $("#target_url").val(elem.target_url);
        $("#scheduled_at").val(elem.scheduled_at);
        $("#executed_at").val(elem.executed_at);
        $("#content").val(elem.content);
        $("#hashtags").val(elem.hashtags);
        $("#comment").val(elem.comment);
        if (elem.media_urls) {
            $("#previewImage").attr("src", elem.media_urls).removeClass("hidden");
        }
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
                    console.log(response.data);
                    populateData(response.data);
                } else {
                    Toastify({
                        text: "Data not found for this Gallery ID",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                        backgroundColor: "#f44336",
                    }).showToast();
                    resetField();
                }
            },
            error: function (error) {
                Toastify({
                    text: error.responseJSON.message,
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
                    window.location.reload();
                }
            },
            error: function (err) {
                Toastify({
                    text: err.responseJSON.message,
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

    // 🔥 NEW: Stop Scroll Bot Function
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

                    // Remove from tracking
                    activeScrollBots.delete(accountId);

                    // Update button UI
                    $button.removeClass("bg-red-600 hover:bg-red-700");
                    $button.addClass("bg-gray-400 cursor-not-allowed");
                    $button.prop("disabled", true);
                    $button.html('<i class="fa-solid fa-stop-circle"></i> Stopped');

                    // Reload table after 2 seconds
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

    // 🔥 NEW: Get Scroll Bot Status
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

    $btnAddNew.on("click", () => {
        showFormTab();
        $("#taskForm")[0].reset();
        $("#task_id").val("");
        toggleTaskFields(null);
    });

    let table;

    return {
        init: function () {
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
                            if (data === "completed") badgeClass = "bg-green-500";
                            if (data === "running") badgeClass = "bg-blue-500";
                            if (data === "failed") badgeClass = "bg-red-500";
                            
                            return `<span class="px-2 py-1 rounded text-white text-xs ${badgeClass}">${data}</span>`;
                        }
                    },
                    { data: "scheduled_at", name: "scheduled_at" },
                    { data: "executed_at", name: "executed_at" },
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

                            // 🔥 Add STOP button for scroll/share tasks
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
                    // 🔥 Update status for running scroll bots every 5 seconds
                    $(".stop-scroll-btn").each(function() {
                        const accountId = $(this).data("account-id");
                        const $statusElement = $(`.scroll-status-${accountId}`);
                        
                        if ($statusElement.length) {
                            // Initial status check
                            getScrollStatus(accountId, $statusElement);
                            
                            // Update every 5 seconds
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

            // Submit form via AJAX
            $("#task_table").submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr("action"),
                    method: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            table.ajax.reload();
                            $("#task_table")[0].reset();
                            $("#task_id").val("");
                            showListTab();
                        }
                    },
                    error: function (err) {
                        alert(err.responseJSON?.message || "Error occurred");
                    },
                });
            });

            // Edit
            $("#task_table").on("click", ".edit-btn", function () {
                const id = $(this).data("id");
                fetchTaskData(id);
                showFormTab();
            });

            // Delete
            $("#task_table").on("click", ".delete-btn", function () {
                const id = $(this).data("id");
                deleteTaskData(id);
            });

            // 🔥 NEW: Stop Scroll Bot
            $("#task_table").on("click", ".stop-scroll-btn", function () {
                const accountId = $(this).data("account-id");
                const $button = $(this);
                
                if (confirm("Are you sure you want to stop the scroll bot?")) {
                    stopScrollBot(accountId, $button);
                }
            });

            $("#refresh-btn").on("click", function () {
                window.location.reload();
            });
        },
    };
};

const task = new TaskController();
task.init();
let TaskController = function () {
   const $tabForm = $("#tab-form");
    const $tabList = $("#tab-list");
    const $contentForm = $("#content-form");
    const $contentList = $("#content-list");
    const $btnAddNew = $("#btn-add-new");
    const $taskType = $("#task_type");
    const $postFields = $("#post-fields");

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
    function togglePostFields(type) {
    if (type === "post") {
        $postFields.removeClass("hidden");
        $("#target_url").prop("readonly", true).val("");
    } else {
        $postFields.addClass("hidden");
        $("#target_url").prop("readonly", false);
    }
}


    $taskType.on("change", function () {
        togglePostFields($(this).val());
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
          if (elem.media_urls) {
            $("#previewImage")
                .attr("src", elem.media_urls)
                .removeClass("hidden");
        }
    };

    // const fetchProxyData = async (id) => {
    //     $.getJSON(`/fetchProxyData/${id}`, function (res) {
    //         if (res.success) {
    //             populateData(res.data);
    //         } else {
    //             alert(res.message);
    //         }
    //     });
    // };
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

    
    $btnAddNew.on("click", () => {
        showFormTab();
        $("#taskForm")[0].reset();
        $("#task_id").val("");
        togglePostFields(null);
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
                    { data: "target_url", name: "target_url" },
                    { data: "scheduled_at", name: "scheduled_at" },
                    { data: "executed_at", name: "executed_at" },
                    {
                        data: null,
                        render: function (data, type, row) {
                            return `
                                <button class="edit-btn px-2 py-1 border border-blue-600 rounded text-blue-600 hover:bg-blue-600 hover:text-white" data-id="${row.id}"><i class="fa-solid fa-edit"></i></button>
                                <button class="delete-btn px-2 py-1 border border-red-600 rounded text-red-600 hover:bg-red-600 hover:text-white" data-id="${row.id}"><i class="fa-solid fa-trash"></i></button>
                            `;
                        },
                        orderable: false,
                        searchable: false,
                    },
                ],
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
            $("#refresh-btn").on("click", function () {
                window.location.reload();
            });
        },
    };
};

const task = new TaskController();
task.init();

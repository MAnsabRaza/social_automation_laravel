let PostContentController = function () {
    const $tabForm = $("#tab-form");
    const $tabList = $("#tab-list");
    const $contentForm = $("#content-form");
    const $contentList = $("#content-list");
    const $btnAddNew = $("#btn-add-new");

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

    const populateData = function (elem) {
        $("#current_date").val(elem.current_date);
        $("#post_content_id").val(elem.id);
        $("#content").val(elem.content);
        $("#title").val(elem.title);
        $("#hashtags").val(elem.hashtags);
        $("#category").val(elem.category);
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
    const fetchPostContentData = async (id) => {
        $.ajax({
            type: "GET",
            url: "/fetchPostContentData/" + id,
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

    const deletePostContentData = function (id) {
        if (!confirm("Are you sure you want to delete this task?")) return;

        $.ajax({
            type: "DELETE",
            url: "/deletePostContentData/" + id,
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

    let table;

    return {
        init: function () {
            table = $("#post_content_table").DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: "/getPostContentData",
                columns: [
                    { data: "id", name: "id" },
                    { data: "current_date", name: "current_date" },
                    { data: "title", name: "title" },
                    { data: "content", name: "content" },
                    {
                        data: "media_urls",
                        render: function (data) {
                            return data
                                ? `<img src="${data}" class="w-16 h-16 rounded"/>`
                                : "";
                        },
                    },

                    { data: "hashtags", name: "hashtags" },

                    { data: "category", name: "category" },
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
            $("#post_content_table").submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr("action"),
                    method: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            table.ajax.reload();
                            $("#post_content_table")[0].reset();
                            $("#post_content_id").val("");
                            showListTab();
                        }
                    },
                    error: function (err) {
                        alert(err.responseJSON?.message || "Error occurred");
                    },
                });
            });

            // Edit
            $("#post_content_table").on("click", ".edit-btn", function () {
                const id = $(this).data("id");
                fetchPostContentData(id);
                showFormTab();
            });

            // Delete
            $("#post_content_table").on("click", ".delete-btn", function () {
                const id = $(this).data("id");
                deletePostContentData(id);
            });
            $("#refresh-btn").on("click", function () {
                window.location.reload();
            });
        },
    };
};

const postContent = new PostContentController();
postContent.init();

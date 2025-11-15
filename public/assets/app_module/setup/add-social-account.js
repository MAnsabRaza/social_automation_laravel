let SocialAccountController = function () {
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
        $("#proxy_username").val(elem.proxy_username);
        $("#proxy_password").val(elem.proxy_password);
        $("#proxy_type").val(elem.proxy_type);
        $("#proxy_host").val(elem.proxy_host);
        $("#proxy_port").val(elem.proxy_port);
        $("#is_active").prop("checked", elem.is_active == 1);
        $("#last_used").val(elem.last_used);
        $("#current_date").val(elem.current_date);
        $("#proxy_id").val(elem.id);
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
    const fetchSocialAccountData = async (id) => {
        $.ajax({
            type: "GET",
            url: "/fetchSocialAccountData/" + id,
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

    const deleteSocialAccount = function (id) {
        if (!confirm("Are you sure you want to delete this proxy?")) return;

        $.ajax({
            type: "DELETE",
            url: "/deleteSocialAccount/" + id,
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
            table = $("#social_account_table").DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: "/getSocialAccountData",
                columns: [
                    { data: "id", name: "id" },
                    { data: "current_date", name: "current_date" },
                    { data: "account_username", name: "account_username" },
                    { data: "account_email", name: "account_email" },
                    { data: "account_password", name: "account_password" },
                    { data: "proxy_id", name: "proxy_id" },
                    {
                        data: "status",
                        name: "status",
                        render: function (data) {
                            return data == 'active'
                                ? `<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-semibold">Active</span>`
                                : `<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm font-semibold">Inactive</span>`;
                        },
                    },
                    { data: "daily_actions_count", name: "daily_actions_count" },
                    { data: "auth_token", name: "auth_token" },
                    { data: "session_data", name: "session_data" },
                       { data: "cookies", name: "cookies" },
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
            $("#proxyForm").submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr("action"),
                    method: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            table.ajax.reload();
                            $("#proxyForm")[0].reset();
                            $("#proxy_id").val("");
                            showListTab();
                        }
                    },
                    error: function (err) {
                        alert(err.responseJSON?.message || "Error occurred");
                    },
                });
            });

            // Edit
            $("#social_account_table").on("click", ".edit-btn", function () {
                const id = $(this).data("id");
                fetchSocialAccountData(id);
                showFormTab();
            });

            // Delete
            $("#social_account_table").on("click", ".delete-btn", function () {
                const id = $(this).data("id");
                deleteSocialAccount(id);
            });
            $("#refresh-btn").on("click", function () {
                window.location.reload();
            });
        },
    };
};

const socialAccount = new SocialAccountController();
socialAccount.init();

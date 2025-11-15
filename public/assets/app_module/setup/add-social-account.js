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
        $("#socialAccountForm")[0].reset();
        $("#proxy_id").val("");
    });

    const populateData = function (elem) {
        $("#account_username").val(elem.account_username);
        $("#account_password").val(elem.account_password);
        $("#platform").val(elem.platform);
        $("#current_date").val(elem.current_date);
        $("#social_account_id").val(elem.id);
        $("#proxy_id").val(elem.proxy_id);
        $("#daily_actions_count").val(elem.daily_actions_count);
        $("#account_email").val(elem.account_email);
        $("#status").val(elem.status);
        $("#auth_token").val(elem.auth_token);
        $("#session_data").val(elem.session_data);
        $("#cookies").val(elem.cookies);
        $("#account_phone").val(elem.account_phone);
        $("#warmup_level").val(elem.warmup_level);
        $("#last_login").val(elem.last_login);
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
                            let label = "";
                            let bg = "";
                            let text = "";

                            switch (data) {
                                case "active":
                                    bg = "bg-green-100";
                                    text = "text-green-800";
                                    label = "Active";
                                    break;

                                case "inactive":
                                    bg = "bg-gray-100";
                                    text = "text-blue-800";
                                    label = "Inactive";
                                    break;

                                case "banned":
                                    bg = "bg-red-100";
                                    text = "text-red-800";
                                    label = "Banned";
                                    break;

                                case "suspended":
                                    bg = "bg-yellow-100";
                                    text = "text-yellow-800";
                                    label = "Suspended";
                                    break;

                                default:
                                    bg = "bg-gray-100";
                                    text = "text-gray-800";
                                    label = data;
                            }

                            return `<span class="${bg} ${text} px-2 py-1 rounded text-sm font-semibold">${label}</span>`;
                        },
                    },

                    { data: "platform", name: "platform" },
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
            $("#socialAccountForm").submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr("action"),
                    method: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            table.ajax.reload();
                            $("#socialAccountForm")[0].reset();
                            $("#social_account_id").val("");
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
            // Import CSV button click
            $("#importCsvBtn").on("click", function () {
                $("#csvFileInput").click();
            });

            // Auto-submit when file selected
            $("#csvFileInput").on("change", function () {
                if (this.files.length > 0) {
                    $("#csvImportForm").submit();
                }
            });
        },
    };
};

const socialAccount = new SocialAccountController();
socialAccount.init();

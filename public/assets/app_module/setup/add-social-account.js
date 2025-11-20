let SocialAccountController = function () {
    // Elements
    const $tabForm = $("#tab-form");
    const $tabList = $("#tab-list");
    const $contentForm = $("#content-form");
    const $contentList = $("#content-list");
    const $btnAddNew = $("#btn-add-new");

    // Show/Hide Tabs
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
        $("#social_account_id").val("");
        const today = new Date().toISOString().split("T")[0];
        $("#current_date").val(today);
    });

    // Populate form for edit
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
        $("#last_login").val(elem.last_login.replace(" ", "T"));

    };

    // Fetch account for edit
    const fetchSocialAccountData = function (id) {
        $.ajax({
            type: "GET",
            url: "/fetchSocialAccountData/" + id,
            dataType: "JSON",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                if (res.success && res.data) {
                    populateData(res.data);
                    showFormTab();
                } else {
                    Toastify({
                        text: res.message || "Not found",
                        backgroundColor: "#f44336",
                    }).showToast();
                }
            },
            error: function () {
                Toastify({
                    text: "Error loading data",
                    backgroundColor: "#f44336",
                }).showToast();
            },
        });
    };

    const startAccount=function(id){
        $.ajax({
        url: "/startAccount/" + id,
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        success: function (res) {
            if (res.success) {
                Toastify({
                    text: "Starting... Opening new tab",
                    backgroundColor: "#4BB543"
                }).showToast();
                window.open(`/runAccount/${id}`, "_blank");

                table.ajax.reload();
            }
        },
        error: function () {
            Toastify({ text: "Failed to start", backgroundColor: "#f44336" }).showToast();
        }
        });
    }
    const stopAccount=function(id){
        $.ajax({  url: "/stopAccount/" + id,
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        },
        success: function (res) {
            if (res.success) {
                Toastify({
                    text: "Account stopped successfully",
                    backgroundColor: "#4BB543"
                }).showToast();

                table.ajax.reload();
            }
        },
        error: function () {
            Toastify({
                text: "Failed to stop account",
                backgroundColor: "#f44336"
            }).showToast();
        }
        });
    }

    // Delete account
    const deleteSocialAccount = function (id) {
        if (!confirm("Delete this account?")) return;
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
                        backgroundColor: "#4BB543",
                    }).showToast();
                    table.ajax.reload();
                }
            },
            error: function () {
                Toastify({
                    text: "Delete failed",
                    backgroundColor: "#f44336",
                }).showToast();
            },
        });
    };

    let table;
    let platformFilter = "";
    const today = new Date().toISOString().split("T")[0];
    $("#current_date").val(today);

    // Initialize DataTable
    table = $("#social_account_table").DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: "/getSocialAccountData",
            data: function (d) {
                d.platform = platformFilter; // send current filter to backend
            },
        },
        columns: [
            { data: "id" },
            { data: "current_date" },
            { data: "account_username" },
            { data: "account_email" },
            { data: "account_password" },
            { data: "last_login" },
            { data: "proxy_id" },
            {
                data: "status",
                render: function (data) {
                    const map = {
                        inprogress: {
                            bg: "bg-yellow-100",
                            text: "text-orange-800",
                            label: "In Progress",
                        },
                        complete: {
                            bg: "bg-green-100",
                            text: "text-green-800",
                            label: "Complete",
                        },
                        error: {
                            bg: "bg-red-100",
                            text: "text-red-800",
                            label: "Error",
                        },
                        pending: {
                            bg: "bg-yellow-100",
                            text: "text-yellow-800",
                            label: "Pending",
                        },
                    };
                    const s = map[data] || {
                        bg: "bg-gray-100",
                        text: "text-gray-800",
                        label: data || "Unknown",
                    };
                    return `<span class="${s.bg} ${s.text} px-2 py-1 rounded text-sm font-semibold">${s.label}</span>`;
                },
            },
            { data: "platform" },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <div class="flex gap-1 justify-center">
                            <button class="start-btn px-2 py-1 border border-green-600 rounded text-green-600 hover:bg-green-600 hover:text-white text-xs" data-id="${row.id}">
                                <i class="fa-solid fa-play"></i>
                            </button>
                            <button class="stop-btn px-2 py-1 border border-red-600 rounded text-red-600 hover:bg-red-600 hover:text-white text-xs" data-id="${row.id}">
                                <i class="fa-solid fa-stop"></i>
                            </button>
                            <button class="edit-btn px-2 py-1 border border-blue-600 rounded text-blue-600 hover:bg-blue-600 hover:text-white text-xs" data-id="${row.id}">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <button class="delete-btn px-2 py-1 border border-red-600 rounded text-red-600 hover:bg-red-600 hover:text-white text-xs" data-id="${row.id}">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>`;
                },
            },
        ],
    });

    // ==================== PLATFORM CARD FILTERING ====================
    $(".platform-card").on("click", function () {
        const platform = $(this).data("platform");

        // Remove active style from all cards
        $(".platform-card").removeClass("ring-4 ring-blue-500 bg-blue-50");

        if (platformFilter === platform) {
            // Clicking the same platform again → show all
            platformFilter = "";
        } else {
            // Apply new filter
            platformFilter = platform;
            $(this).addClass("ring-4 ring-blue-500 bg-blue-50");
        }

        // Reload table with new filter
        table.ajax.reload();
    });

    // Other event listeners
    $("#social_account_table").on("click", ".start-btn", function () {
        startAccount($(this).data("id"));
    });

    $("#social_account_table").on("click", ".stop-btn", function () {
        stopAccount($(this).data("id"));
    });

    $("#social_account_table").on("click", ".edit-btn", function () {
        fetchSocialAccountData($(this).data("id"));
    });

    $("#social_account_table").on("click", ".delete-btn", function () {
        deleteSocialAccount($(this).data("id"));
    });

    $("#refresh-btn").on("click", () => location.reload());
    $("#importCsvBtn").on("click", () => $("#csvFileInput").click());
    $("#csvFileInput").on("change", function () {
        if (this.files.length > 0) $("#csvImportForm").submit();
    });

    $("#socialAccountForm").on("submit", function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr("action"),
            method: "POST",
            data: $(this).serialize(),
            success: function () {
                Toastify({
                    text: "Saved successfully!",
                    backgroundColor: "#4BB543",
                }).showToast();

                table.ajax.reload();
                $("#socialAccountForm")[0].reset();
                $("#social_account_id").val("");
                $("#current_date").val(today);
                showListTab(); // your function to switch tab
            },
            error: function (err) {
                Toastify({
                    text: err.responseJSON?.message || "An error occurred",
                    backgroundColor: "#f44336",
                }).showToast();
            },
        });
    });
};

const socialAccount = new SocialAccountController();
socialAccount.init();

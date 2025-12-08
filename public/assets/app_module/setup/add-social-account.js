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
        if (elem.last_login) {
            $("#last_login").val(elem.last_login.replace(" ", "T"));
        }
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
                        duration: 3000
                    }).showToast();
                }
            },
            error: function () {
                Toastify({
                    text: "Error loading data",
                    backgroundColor: "#f44336",
                    duration: 3000
                }).showToast();
            },
        });
    };

    // ========== ENHANCED START ACCOUNT WITH SESSION SAVING ==========
    const startAccount = function (id, $button) {
        // Disable button and show loading
        if ($button) {
            $button.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
        }

        $.ajax({
            url: "/startAccount/" + id,
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                if (res.success) {
                    Toastify({
                        text: "✅ Login successful! Session saved.",
                        backgroundColor: "#4BB543",
                        duration: 3000
                    }).showToast();

                    // Reload table to show updated status
                    table.ajax.reload(null, false);
                } else {
                    Toastify({
                        text: "❌ Login failed: " + (res.message || 'Unknown error'),
                        backgroundColor: "#f44336",
                        duration: 4000
                    }).showToast();
                }
                
                // Re-enable button
                if ($button) {
                    $button.prop('disabled', false).html('<i class="fa-solid fa-play"></i>');
                }
            },
            error: function (xhr) {
                Toastify({
                    text: "❌ Request failed. Please try again.",
                    backgroundColor: "#f44336",
                    duration: 3000
                }).showToast();
                
                // Re-enable button
                if ($button) {
                    $button.prop('disabled', false).html('<i class="fa-solid fa-play"></i>');
                }
            },
        });
    };

    // ========== CHECK ACCOUNT STATUS ==========
    const checkAccountStatus = function (id) {
        $.ajax({
            url: "/checkAccountStatus/" + id,
            method: "GET",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                if (res.success) {
                    const statusText = res.is_logged_in 
                        ? "✅ Account is logged in (Session active)" 
                        : "⚠️ Account session expired";
                    
                    const lastLogin = res.last_login 
                        ? new Date(res.last_login).toLocaleString() 
                        : "Never";
                    
                    Toastify({
                        text: `${statusText}\nLast login: ${lastLogin}`,
                        backgroundColor: res.is_logged_in ? "#4BB543" : "#FFA500",
                        duration: 5000
                    }).showToast();
                }
            },
            error: function () {
                Toastify({
                    text: "Failed to check status",
                    backgroundColor: "#f44336",
                    duration: 3000
                }).showToast();
            },
        });
    };

    const stopAccount = function (id) {
        $.ajax({
            url: "/stopAccount/" + id,
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (res) {
                if (res.success) {
                    Toastify({
                        text: "Account stopped successfully",
                        backgroundColor: "#4BB543",
                        duration: 3000
                    }).showToast();

                    table.ajax.reload();
                }
            },
            error: function () {
                Toastify({
                    text: "Failed to stop account",
                    backgroundColor: "#f44336",
                    duration: 3000
                }).showToast();
            },
        });
    };

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
                        duration: 3000
                    }).showToast();
                    table.ajax.reload();
                }
            },
            error: function () {
                Toastify({
                    text: "Delete failed",
                    backgroundColor: "#f44336",
                    duration: 3000
                }).showToast();
            },
        });
    };

    let table;
    let platformFilter = "";
    const today = new Date().toISOString().split("T")[0];
    $("#current_date").val(today);

    // ========== ENHANCED DATATABLE WITH SESSION INDICATORS ==========
    table = $("#social_account_table").DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        ajax: {
            url: "/getSocialAccountData",
            data: function (d) {
                d.platform = platformFilter;
            },
        },
        columns: [
            { data: "id" },
            { data: "current_date" },
            { data: "account_username" },
            { data: "account_email" },
            {
                data: "last_login",
                render: function (data, type, row) {
                    if (!data) return '<span class="text-gray-400 text-xs">Never</span>';
                    
                    const loginDate = new Date(data);
                    const now = new Date();
                    const hoursDiff = Math.floor((now - loginDate) / (1000 * 60 * 60));
                    
                    let statusIcon = '';
                    let statusClass = '';
                    
                    if (hoursDiff < 24) {
                        statusIcon = '🟢';
                        statusClass = 'text-green-600';
                    } else {
                        statusIcon = '🔴';
                        statusClass = 'text-red-600';
                    }
                    
                    return `<span class="${statusClass} text-xs">${statusIcon} ${loginDate.toLocaleString()}</span>`;
                }
            },
            { data: "proxy_id" },
            {
                data: "status",
                render: function (data) {
                    const map = {
                        active: {
                            bg: "bg-green-100",
                            text: "text-green-800",
                            label: "Active",
                        },
                        inactive: {
                            bg: "bg-gray-100",
                            text: "text-gray-800",
                            label: "Inactive",
                        },
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
                        failed: {
                            bg: "bg-red-100",
                            text: "text-red-800",
                            label: "Failed",
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
                    return `<span class="${s.bg} ${s.text} px-2 py-1 rounded text-xs font-semibold">${s.label}</span>`;
                },
            },
            { data: "platform" },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <div class="flex gap-1 justify-center">
                            <button class="start-btn px-2 py-1 border border-green-600 rounded text-green-600 hover:bg-green-600 hover:text-white text-xs" 
                                    data-id="${row.id}" 
                                    title="Login & Save Session">
                                <i class="fa-solid fa-play"></i>
                            </button>
                            <button class="check-status-btn px-2 py-1 border border-blue-600 rounded text-blue-600 hover:bg-blue-600 hover:text-white text-xs" 
                                    data-id="${row.id}"
                                    title="Check Login Status">
                                <i class="fa-solid fa-circle-info"></i>
                            </button>
                            <button class="stop-btn px-2 py-1 border border-red-600 rounded text-red-600 hover:bg-red-600 hover:text-white text-xs" 
                                    data-id="${row.id}"
                                    title="Stop Account">
                                <i class="fa-solid fa-stop"></i>
                            </button>
                            <button class="edit-btn px-2 py-1 border border-yellow-600 rounded text-yellow-600 hover:bg-yellow-600 hover:text-white text-xs" 
                                    data-id="${row.id}"
                                    title="Edit Account">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <button class="delete-btn px-2 py-1 border border-red-600 rounded text-red-600 hover:bg-red-600 hover:text-white text-xs" 
                                    data-id="${row.id}"
                                    title="Delete Account">
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

    // ========== EVENT LISTENERS ==========
    
    // Start button with enhanced session saving
    $("#social_account_table").on("click", ".start-btn", function () {
        const accountId = $(this).data("id");
        const $button = $(this);
        startAccount(accountId, $button);
    });

    // Check status button
    $("#social_account_table").on("click", ".check-status-btn", function () {
        const accountId = $(this).data("id");
        checkAccountStatus(accountId);
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
                    duration: 3000
                }).showToast();

                table.ajax.reload();
                $("#socialAccountForm")[0].reset();
                $("#social_account_id").val("");
                $("#current_date").val(today);
                showListTab();
            },
            error: function (err) {
                Toastify({
                    text: err.responseJSON?.message || "An error occurred",
                    backgroundColor: "#f44336",
                    duration: 3000
                }).showToast();
            },
        });
    });

    // ========== POST CONTENT FORM WITH SESSION CHECK ==========
    $("#postContentForm").on("submit", function(e) {
        const accountId = $("#account_id").val();
        
        if (!accountId) {
            e.preventDefault();
            Toastify({
                text: "⚠️ Please select an account",
                backgroundColor: "#FFA500",
                duration: 3000
            }).showToast();
            return false;
        }
        
        // Show loading indicator
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Publishing...');
        
        // Re-enable after 2 seconds (will be handled by page reload/redirect)
        setTimeout(() => {
            $submitBtn.prop('disabled', false).html(originalText);
        }, 120000); // 2 minutes timeout
    });
};

// Initialize the controller
$(document).ready(function() {
    const socialAccount = new SocialAccountController();
});
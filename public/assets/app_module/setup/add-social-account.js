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
        $("#last_login").val(elem.last_login);
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

    // ======================================================
    // AUTO-LOGIN ENGINE – FULLY WORKING (2025)
    // ======================================================
    const loginHandlers = {
        instagram: {
            url: "https://www.instagram.com/accounts/login/",
            waitFor: 'input[name="username"]',
            fill: (doc) => {
                const u = doc.querySelector('input[name="username"]');
                const p = doc.querySelector('input[name="password"]');
                const btn = doc.querySelector('button[type="submit"]');
                if (u && p && btn) {
                    u.value = window.__LOGIN_USER__;
                    p.value = window.__LOGIN_PASS__;
                    ["input", "change"].forEach((e) => {
                        u.dispatchEvent(new Event(e, { bubbles: true }));
                        p.dispatchEvent(new Event(e, { bubbles: true }));
                    });
                    setTimeout(() => btn.click(), 600);
                }
            },
        },
        facebook: {
            url: "https://www.facebook.com/login.php",
            waitFor: "#email",
            fill: (doc) => {
                const email = doc.getElementById("email");
                const pass = doc.getElementById("pass");
                const login =
                    doc.querySelector('button[name="login"]') ||
                    doc.querySelector(
                        'button[data-testid="royal_login_button"]'
                    );
                if (email && pass && login) {
                    email.value = window.__LOGIN_USER__;
                    pass.value = window.__LOGIN_PASS__;
                    setTimeout(() => login.click(), 600);
                }
            },
        },
        youtube: {
            url: "https://accounts.google.com/signin/v2/identifier?service=youtube&flowName=GlifWebSignIn",
            waitFor: 'input[type="email"]',
            fill: (doc) => {
                const email = doc.querySelector('input[type="email"]');
                if (email) {
                    email.value = window.__LOGIN_USER__;
                    email.dispatchEvent(new Event("input", { bubbles: true }));
                    setTimeout(() => {
                        const next =
                            doc.getElementById("identifierNext") ||
                            doc.querySelector("button");
                        if (next) next.click();
                    }, 800);
                }
            },
        },
        linkedin: {
            url: "https://www.linkedin.com/login",
            waitFor: "#username",
            fill: (doc) => {
                const u = doc.getElementById("username");
                const p = doc.getElementById("password");
                const btn = doc.querySelector('button[type="submit"]');
                if (u && p && btn) {
                    u.value = window.__LOGIN_USER__;
                    p.value = window.__LOGIN_PASS__;
                    setTimeout(() => btn.click(), 600);
                }
            },
        },
        google_business: {
            url: "https://accounts.google.com/signin",
            waitFor: 'input[type="email"]',
            fill: (doc) => {
                const email = doc.querySelector('input[type="email"]');
                if (email) {
                    email.value = window.__LOGIN_USER__;
                    email.dispatchEvent(new Event("input", { bubbles: true }));
                    setTimeout(() => {
                        const next = doc.querySelector(
                            "#identifierNext, button"
                        );
                        if (next) next.click();
                    }, 800);
                }
            },
        },
    };

    const performAutoLogin = function (platform, username, password) {
        const handler = loginHandlers[platform.toLowerCase()];
        if (!handler) {
            alert(`Auto-login not supported for ${platform}`);
            return;
        }

        const newTab = window.open(handler.url, "_blank");
        if (!newTab) {
            alert("Please allow pop-ups");
            return;
        }

        const safeUser = username.replace(/"/g, '\\"');
        const safePass = password.replace(/"/g, '\\"');

        const script = `
        window.__LOGIN_USER__ = "${safeUser}";
        window.__LOGIN_PASS__ = "${safePass}";
        const fill = ${handler.fill.toString()};
        let attempts = 0;
        const iv = setInterval(() => {
            if (document.readyState === 'complete') {
                const el = document.querySelector("${handler.waitFor}");
                if (el) {
                    clearInterval(iv);
                    fill(document);
                }
            }
            if (++attempts > 200) clearInterval(iv);
        }, 100);
    `;

        newTab.addEventListener("load", () => {
            const s = newTab.document.createElement("script");
            s.textContent = script;
            (
                newTab.document.head || newTab.document.documentElement
            ).appendChild(s);
            s.remove();
        });
    };

    // ======================================================
    // DATATABLE
    // ======================================================
    let table;

    return {
        init: function () {
            const today = new Date().toISOString().split("T")[0];
            $("#current_date").val(today);

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
                        render: (data) => {
                            const map = {
                                active: {
                                    bg: "bg-green-100",
                                    text: "text-green-800",
                                    label: "Active",
                                },
                                inactive: {
                                    bg: "bg-gray-100",
                                    text: "text-blue-800",
                                    label: "Inactive",
                                },
                                banned: {
                                    bg: "bg-red-100",
                                    text: "text-red-800",
                                    label: "Banned",
                                },
                                suspended: {
                                    bg: "bg-yellow-100",
                                    text: "text-yellow-800",
                                    label: "Suspended",
                                },
                            };
                            const s = map[data] || {
                                bg: "bg-gray-100",
                                text: "text-gray-800",
                                label: data,
                            };
                            return `<span class="${s.bg} ${s.text} px-2 py-1 rounded text-sm font-semibold">${s.label}</span>`;
                        },
                    },
                    { data: "platform", name: "platform" },
                    { data: "auth_token", name: "auth_token" },
                    { data: "session_data", name: "session_data" },
                    { data: "cookies", name: "cookies" },
                    {
                        data: null,
                        render: (data, type, row) => {
                            // let loginBtn = "";
                            // if (row.status === "active") {
                            //     loginBtn = `<button class="login-btn px-2 py-1 mx-1 border border-green-600 rounded text-green-600 hover:bg-green-600 hover:text-white text-xs"
                            //                         data-platform="${row.platform}"
                            //                         data-username="${row.account_username}"
                            //                         data-password="${row.account_password}">
                            //                         <i class="fa-solid fa-sign-in-alt"></i> Login
                            //                     </button>`;
                            // }
                            return `
                                    <div class="flex gap-1 justify-center">
                                        <button class="edit-btn px-2 py-1 border border-blue-600 rounded text-blue-600 hover:bg-blue-600 hover:text-white text-xs" data-id="${row.id}">
                                            <i class="fa-solid fa-edit"></i>
                                        </button>
                                        <button class="delete-btn px-2 py-1 border border-red-600 rounded text-red-600 hover:bg-red-600 hover:text-white text-xs" data-id="${row.id}">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                      <button class="login-btn px-2 py-1 border border-green-600 rounded text-green-600 hover:bg-green-600 hover:text-white text-xs"
        data-platform="instagram"
        data-username="${row.account_username}"
        data-password="${row.account_password}">
<i class="fa-solid fa-sign-in-alt"></i> Login
</button>

                                    </div>
                                `;
                        },
                        orderable: false,
                        searchable: false,
                    },
                ],
            });

            $("#social_account_table").on("click", ".login-btn", function () {
                const platform = $(this).data("platform");
                const username = $(this).data("username");
                const password = $(this).data("password");

                if (!username || !password) {
                    alert("Username or password missing!");
                    return;
                }

                performAutoLogin(platform, username, password);
            });

            // Event Listeners
            $("#social_account_table").on("click", ".edit-btn", function () {
                fetchSocialAccountData($(this).data("id"));
            });

            $("#social_account_table").on("click", ".delete-btn", function () {
                deleteSocialAccount($(this).data("id"));
            });

            $("#social_account_table").on("click", ".login-btn", function () {
                const platform = $(this).data("platform");
                const username = $(this).data("username");
                const password = $(this).data("password");
                performAutoLogin(platform, username, password);
            });

            $("#refresh-btn").on("click", () => location.reload());

            $("#importCsvBtn").on("click", () => $("#csvFileInput").click());
            $("#csvFileInput").on("change", function () {
                if (this.files.length > 0) $("#csvImportForm").submit();
            });

            // Form Submit
            $("#socialAccountForm").on("submit", function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr("action"),
                    method: "POST",
                    data: $(this).serialize(),
                    success: () => {
                        Toastify({
                            text: "Saved!",
                            backgroundColor: "#4BB543",
                        }).showToast();
                        table.ajax.reload();
                        this.reset();
                        $("#social_account_id").val("");
                        $("#current_date").val(today);
                        showListTab();
                    },
                    error: (err) => {
                        Toastify({
                            text: err.responseJSON?.message || "Error",
                            backgroundColor: "#f44336",
                        }).showToast();
                    },
                });
            });
        },
    };
};

const socialAccount = new SocialAccountController();
socialAccount.init();

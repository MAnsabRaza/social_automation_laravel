let CaptchaSettingController = function () {
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
        $("#captcha_setting_id").val("");
    });

    const populateData = function (elem) {
        $("#service_name").val(elem.service_name);
        $("#api_key").val(elem.api_key);
        $("#status").prop("checked", elem.status == 1);
        $("#current_date").val(elem.current_date);
        $("#captcha_setting_id").val(elem.id);
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
    const fetchCaptchaSettingData = async (id) => {
        $.ajax({
            type: "GET",
            url: "/fetchCaptchaSettingData/" + id,
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

    const deleteCaptchaSettingData = function (id) {
        if (!confirm("Are you sure you want to delete this proxy?")) return;

        $.ajax({
            type: "DELETE",
            url: "/deleteCaptchaSettingData/" + id,
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
            table = $("#captcha_setting_table").DataTable({
                autoWidth: false,
                processing: true,
                serverSide: true,
                ajax: "/getCaptchaSettingData",
                columns: [
                    { data: "id", name: "id" },
                    { data: "current_date", name: "current_date" },
                    { data: "service_name", name: "service_name" },
                    { data: "api_key", name: "api_key" },
                    {
                        data: "status",
                        name: "status",
                        render: function (data) {
                            return data == 1
                                ? `<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm font-semibold">Active</span>`
                                : `<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm font-semibold">Inactive</span>`;
                        },
                    },
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
            $("#captchaSettingForm").submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr("action"),
                    method: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        if (res.success) {
                            alert(res.message);
                            table.ajax.reload();
                            $("#captchaSettingForm")[0].reset();
                            $("#captch_setting_id").val("");
                            showListTab();
                        }
                    },
                    error: function (err) {
                        alert(err.responseJSON?.message || "Error occurred");
                    },
                });
            });

            // Edit
            $("#captcha_setting_table").on("click", ".edit-btn", function () {
                const id = $(this).data("id");
                fetchCaptchaSettingData(id);
                showFormTab();
            });

            // Delete
            $("#captcha_setting_table").on("click", ".delete-btn", function () {
                const id = $(this).data("id");
                deleteCaptchaSettingData(id);
            });
            $("#refresh-btn").on("click", function () {
                window.location.reload();
            });
        },
    };
};

const captchaSetting = new CaptchaSettingController();
captchaSetting.init();

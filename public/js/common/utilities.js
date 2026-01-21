var isLoading = false;
export function handleDeleteAction(options) {
    const { url, tableId, successMessage, errorMessage, isDelete = true } = options;

    const message = isDelete
        ? "You'll not be able to recover this record again!"
        : "Are you sure you want to proceed with this action?";
    const confirmColor = isDelete ? 'red' : 'green';
    const buttonText = isDelete ? "Yes, Delete it!" : "Yes, Confirm it!";
    const spinner = '<i class="fas fa-spinner fa-spin"></i>';

    swal({
        title: "Are you sure?",
        text: message,
        type: "warning",
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonClass: isDelete ? "btn-danger" : "btn-success",
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#d33',
        confirmButtonText: buttonText
    }, function (isConfirm) {
        if (isConfirm) {
            // Show spinner on confirmation
            swal({
                title: isDelete ? "Deleting..." : "Processing...",
                text: "Please wait while we process your request.",
                buttons: false,
                closeOnClickOutside: false,
                closeOnEsc: false,
                icon: "info",
                content: {
                    element: "div",
                    attributes: {
                        innerHTML: spinner + " Processing...",
                        style: "display: flex; align-items: center; justify-content: center; font-size: 16px;"
                    }
                }
            });

            // Perform AJAX request
            $.ajax({
                url: url,
                type: "DELETE",
                success: function (response) {
                    if (response.success) {
                        swal({
                            title: "Success!",
                            text: response.message ?? successMessage ?? "Action completed successfully.",
                            type: "success",
                            confirmButtonColor: "#28a745",
                        }, function () {
                            // Reload DataTable or page
                            if (tableId && typeof $(`#${tableId}`).DataTable === "function") {
                                $(`#${tableId}`).DataTable().ajax.reload(null, false); // Reload DataTable
                            } else {
                                location.reload(); // Fallback to page reload
                            }
                        });
                    } else {
                        swal({
                            title: "Error!",
                            text: errorMessage || response.message || "An error occurred. Please try again.",
                            type: "error",
                            confirmButtonColor: "#dc3545",
                        });
                    }
                },
                error: function (xhr, status, error) {
                    // console.log(xhr.responseJSON.error)
                    const showError = xhr.responseJSON.error ?? `An unexpected error occurred: ${error}`;
                    swal({
                        title: "Opps!",
                        text: showError,
                        type: "error",
                        confirmButtonColor: "#dc3545",
                    });
                }
            });
        }
    });
}


/************************************************************************************/
export function initializeAddCustomerButton(selector) {
    $(selector).on('click', function (e) {
        e.preventDefault();
        const url = $(this).attr('data-url');
        const saveCustomerUrl = $(this).attr('data-saveCustomerUrl');
        const title = "New Customer";
        loadCustomerForm(url, title, saveCustomerUrl);
    });
}

/************************************************************************************/
export function initializeCustomerAutocomplete(selector) {
    $(selector).autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "/customer-auto-complete",
                type: "GET",
                data: { term: request.term },
                success: function (data) {
                    if (!Array.isArray(data)) {
                        console.error("Invalid data format received:", data);
                        response([]);
                        return;
                    }
                    if (data.length === 0) {
                        const url = $("#addCustomer").attr("data-url");
                        const saveCustomerUrl = $("#addCustomer").attr("data-saveCustomerUrl");
                        const title = "New Customer";
                        loadCustomerForm(url, title, saveCustomerUrl);
                    } else {
                        response(
                            $.map(data, (item) => ({
                                label: `${item.customerName} (${item.mobile})`,
                                value: item.customerName,
                                customerID: item.customerID,
                                areaID: item.areaID,
                                customerBalance: item.customerBalance || 0,
                            }))
                        );
                    }
                },
                error: function (xhr) {
                    console.error("AJAX Error:", xhr.responseText);
                    response([]);
                },
            });
        },
        minLength: 1,
        select: function (event, ui) {
            const item = ui.item;
            if (item) {
                $("#customer_id").val(item.customerID);
                $("#area_id").val(item.areaID);
                $("#customerBalance").val(item.customerBalance.toFixed(2));
                $(".customerBalance").text(item.customerBalance.toFixed(2));
            }
        },
    });
}
/************************************************************************************/
export function loadCustomerForm(url, title, saveCustomerUrl) {
    // console.log(saveCustomerUrl)
    if (!isLoading) {
        isLoading = true;
        $.ajax({
            url,
            type: "GET",
            success: function (response) {
                if (response.success) {
                    $("#customerModalContainer").html(response.html);
                    const modal = $("#addCustomerModal");
                    modal.find("form").attr("action", saveCustomerUrl);
                    const modalTitle = modal.find(".modal-title");
                    modalTitle.contents().filter((_, node) => node.nodeType === 3).remove();
                    modalTitle.append(` ${title}`);
                    modal.modal("show");
                    $(modal).on('hidden.bs.modal', function () {
                        // Move focus back to the button that triggered the modal
                        $('#triggerButton').trigger('focus');
                    });
                    $(".select2").select2({
                        placeholder: "Select Area",
                        allowClear: true,
                    });
                }

                if (!response.success) {
                    showErrorToast(response.message, "Opps!");
                }
            },
            error: function () {
                showErrorToast("Failed to load customer form.");
            },
            complete: function () {
                isLoading = false;
            }
        });
    }
}
/************************************************************************************/

export function submitCustomerForm(selector) {
    $(document).on("submit", selector, function (e) {
        e.preventDefault();
        const submitButton = $(this).find("button[type='submit']");
        const spinnerText = "Processing...";
        const originalText = addSpinner(submitButton, spinnerText);
        const formData = serializeFormToObject(this);
        const url = $(this).attr("action");

        ajaxRequest(
            url,
            "POST",
            formData,
            // Success callback
            function (response) {
                if (response.success) {
                    // Reload the customers DataTable
                    $('#customersTable').DataTable().ajax.reload();

                    // Hide the modal and update related fields
                    $("#addCustomerModal").modal("hide");
                    $("#searchCustomer").val(response.customerName);
                    $("#customer_id").val(response.customerID);
                    $("#area_id").val(response.areaID);
                    $("#customerBalance").val(response.customerBalance);
                    // Show success toast
                    showSuccessToast(response.message || "Customer added successfully.");
                }

                // Handle validation errors if any
                if (response.errors) {
                    $.each(response.errors, function (field, messages) {
                        messages.forEach(function (message) {
                            showWarningToast(message);
                        });
                    });
                }

                // Remove spinner
                removeSpinner(submitButton, originalText);
            },
            // Error callback
            function (error) {
                showErrorToast("Failed to add customer. Please try again.");
                removeSpinner(submitButton, originalText); // Remove spinner even on error
            },
            // Custom options
            {
                timeout: 180000, // Set a timeout for the request
                beforeSendCallback: function () {
                    console.log("Custom: Showing loader...");
                    // Show a global loader if required
                },
                completeCallback: function () {
                    console.log("Custom: Hiding loader...");
                    // Hide the global loader
                },
            }
        );
    });
}
/************************************************************************************/
export function initializeDataTable({
    tableSelector,
    ajaxUrl,
    columns,
    additionalOptions = {},
    defaultOrder = [[1, 'desc']], // Default sorting
}) {
    // Ensure the selector exists
    if (!$(tableSelector).length) {
        console.error(`Table with selector "${tableSelector}" does not exist.`);
        return;
    }

    // Initialize the DataTable
    $(tableSelector).DataTable({
        processing: true,
        serverSide: true,
        destroy: true, // Allow reinitialization
        ajax: {
            url: ajaxUrl,
            type: 'GET',
            error: function (xhr, error, thrown) {
                console.error('Error loading data:', error); // Log errors
            },
        },
        autoWidth: false,
        responsive: true,
        order: defaultOrder,
        pageLength: 10, // Default records per page
        lengthMenu: [10, 25, 50, 100, 500],
        deferRender: true,
        dom: "<'row'<'col-md-6'l><'col-md-6'f>>" + // Length menu and search
            "<'row'<'col-md-12 text-center mb-3'B>>" + // Buttons
            "<'row'<'col-md-12'tr>>" + // Table
            "<'row'<'col-md-5'i><'col-md-7'p>>", // Info and pagination
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-primary btn-sm rounded-pill',
                exportOptions: {
                    columns: ':not(:last-child)', // Exclude the last column (Action)
                },
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm rounded-pill',
                exportOptions: {
                    columns: ':not(:last-child)', // Exclude the last column (Action)
                },
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-info btn-sm rounded-pill',
                exportOptions: {
                    columns: ':not(:last-child)', // Exclude the last column (Action)
                },
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm rounded-pill',
                exportOptions: {
                    columns: ':not(:last-child)', // Exclude the last column (Action)
                },
                orientation: 'landscape',
                pageSize: 'A4',
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-secondary btn-sm rounded-pill',
                exportOptions: {
                    columns: ':not(:last-child)', // Exclude the last column (Action)
                },
            },
        ],
        columns: columns,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
        },
        ...additionalOptions, // Merge additional options
    });
}

/************************************************************************************/
export function initializeAddSupplierButton(selector) {
    $(selector).on('click', function (e) {
        e.preventDefault();
        const url = $(this).attr('data-url');
        const saveSupplierUrl = $(this).attr('data-saveSupplierUrl');
        const title = "New Supplier";
        loadSupplierForm(url, title, saveSupplierUrl);
    });
}

/************************************************************************************/
export function initializeSupplierAutocomplete(selector) {
    $(selector).autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "/supplier-auto-complete",
                type: "GET",
                data: { term: request.term },
                success: function (data) {
                    if (!Array.isArray(data)) {
                        showWarningToast(data.message);
                        // console.error("Invalid data format received:", data);
                        response([]);
                        return;
                    }
                    if (data.length === 0) {
                        const url = $("#addSupplier").attr("data-url");
                        const saveSupplierUrl = $("#addSupplier").attr("data-saveSupplierUrl");
                        const title = "New Supplier";
                        loadSupplierForm(url, title, saveSupplierUrl);
                    } else {
                        response(
                            $.map(data, (item) => ({
                                label: `${item.supplierName} (${item.mobile})`,
                                value: item.supplierName,
                                supplierID: item.supplierID,
                                supplierBalance: item.supplierBalance || 0,
                            }))
                        );
                    }
                },
                error: function (xhr) {
                    console.error("AJAX Error:", xhr.responseText);
                    response([]);
                },
            });
        },
        minLength: 1,
        select: function (event, ui) {
            const item = ui.item;
            if (item) {
                $("#supplier_id").val(item.supplierID);
                $("#supplierBalance").val(item.supplierBalance.toFixed(2));
                $(".supplierBalance").text(item.supplierBalance.toFixed(2));
            }
        },
    });
}
/************************************************************************************/
export function loadSupplierForm(url, title, saveSupplierUrl) {
    // console.log(saveSupplierUrl)
    if (!isLoading) {
        isLoading = true;
        $.ajax({
            url,
            type: "GET",
            success: function (response) {
                if (response.success) {
                    $("#supplierModalContainer").html(response.html);
                    const modal = $("#addSupplierModal");
                    modal.find("form").attr("action", saveSupplierUrl);
                    const modalTitle = modal.find(".modal-title");
                    modalTitle.contents().filter((_, node) => node.nodeType === 3).remove();
                    modalTitle.append(` ${title}`);
                    modal.modal("show");
                    $(modal).on('hidden.bs.modal', function () {
                        // Move focus back to the button that triggered the modal
                        $('#triggerButton').trigger('focus');
                    });
                    $(".select2").select2({
                        placeholder: "Select Area",
                        allowClear: true,
                    });
                }

                if (!response.success) {
                    showErrorToast(response.message, "Opps!");
                }
            },
            error: function () {
                showErrorToast("Failed to load supplier form.");
            },
            complete: function () {
                isLoading = false;
            }
        });
    }
}
/************************************************************************************/

export function submitSupplierForm(selector) {
    $(document).on("submit", selector, function (e) {
        e.preventDefault();
        const submitButton = $(this).find("button[type='submit']");
        const spinnerText = "Processing...";
        const originalText = addSpinner(submitButton, spinnerText);
        const formData = serializeFormToObject(this);
        const url = $(this).attr("action");

        ajaxRequest(
            url,
            "POST",
            formData,
            // Success callback
            function (response) {
                if (response.success) {
                    // Reload the customers DataTable
                    $('#suppliersTable').DataTable().ajax.reload();

                    // Hide the modal and update related fields
                    $("#addSupplierModal").modal("hide");
                    $("#supplierName").val(response.supplierName);
                    $("#supplier_id").val(response.supplierID);
                    $("#supplierBalance").val(response.supplierBalance);
                    // Show success toast
                    showSuccessToast(response.message || "Supplier added successfully.");
                }

                // Handle validation errors if any
                if (response.errors) {
                    $.each(response.errors, function (field, messages) {
                        messages.forEach(function (message) {
                            showWarningToast(message);
                        });
                    });
                }

                // Remove spinner
                removeSpinner(submitButton, originalText);
            },
            // Error callback
            function (error) {
                showErrorToast("Failed to add supplier. Please try again.");
                removeSpinner(submitButton, originalText); // Remove spinner even on error
            },
            // Custom options
            {
                timeout: 180000, // Set a timeout for the request
                beforeSendCallback: function () {
                    console.log("Custom: Showing loader...");
                    // Show a global loader if required
                },
                completeCallback: function () {
                    console.log("Custom: Hiding loader...");
                    // Hide the global loader
                },
            }
        );
    });
}





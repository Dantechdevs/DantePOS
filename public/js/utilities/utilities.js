export function initializeDatepickerSelect2() {
    // Initialize the datepicker
    $('#datepicker').datepicker({
        uiLibrary: 'bootstrap4',
        format: 'dd-mm-yyyy',
    });

    // Initialize Select2 elements
    $('.select2').select2({
        placeholder: 'Select an option', // Placeholder text
        allowClear: true, // Allow clearing the selection
    });

    $('.select2bs4').select2({
        theme: 'bootstrap4', // Use the Bootstrap 4 theme
        placeholder: 'Select an option', // Placeholder text
        allowClear: true, // Allow clearing the selection
    });
}
/************************************************************************************/

export function dynamicFormData(url, title, saveUrl, container, modalId) {
    return new Promise((resolve, reject) => {
        loadForm({
            url: url,
            title: title,
            saveUrl: saveUrl,
            modalContainerId: container,
            modalId: modalId,
            triggerButtonId: "#triggerButton",
            select2Options: {
                placeholder: "-select-",
                allowClear: true,
            },
            onSuccessCallback: function (modal, response) {
                if (!response.id && response.page && response.page =='customer_payments') {
                    $('.select2').val('credit').trigger('change');
                }
                // console.log("Form loaded successfully!", response);
                resolve(response); // Resolve the Promise when form loads successfully
            },
            onErrorCallback: function (response) {
                // console.error("Failed to load form.", response);
                reject(response); // Reject the Promise if form loading fails
            },
        });
    });
}


export function loadForm({
    url,
    title = "",
    saveUrl = "",
    modalContainerId = "#modalContainer",
    modalId = "#dynamicModal",
    triggerButtonId = "#triggerButton",
    select2Options = { placeholder: "Select an option", allowClear: true },
    onSuccessCallback = null,
    onErrorCallback = null,
}) {
    if (typeof isLoading === "undefined") {
        window.isLoading = false;
    }

    if (!isLoading) {
        isLoading = true;
        $.ajax({
            url,
            type: "GET",
            success: function (response) {
                if (response.success) {
                    $(modalContainerId).html(response.html);

                    const modal = $(modalId);
                    const form = modal.find("form");

                    if (saveUrl) {
                        form.attr("action", saveUrl);
                    }

                    const modalTitle = modal.find(".modal-title");
                    modalTitle.contents().filter((_, node) => node.nodeType === 3).remove();
                    modalTitle.append(` ${title}`);

                    modal.modal("show");

                    modal.on("hidden.bs.modal", function () {
                        $(triggerButtonId).trigger("focus");
                    });

                    $(".select2").select2(select2Options);

                    if (typeof onSuccessCallback === "function") {
                        initializeDatepickerSelect2();
                        onSuccessCallback(modal, response);
                    }
                } else {
                    showErrorToast(response.message || "An error occurred.", "Oops!");

                    if (typeof onErrorCallback === "function") {
                        onErrorCallback(response);
                    }
                }
            },
            error: function () {
                showErrorToast("Failed to load form.", "Error!");

                if (typeof onErrorCallback === "function") {
                    onErrorCallback({ success: false });
                }
            },
            complete: function () {
                isLoading = false;
            },
        });
    }
}
/************************* Form Submit ***********************************/

export function submitFormxxxxxx({
    formSelector,
    method = "POST",
    reloadTableSelector = null,
    modalSelector = null,
    successToastMessage = "Operation completed successfully.",
    onSuccessCallback = null,
    onErrorCallback = null,
    beforeSendCallback = null,
    completeCallback = null,
    extraFieldUpdates = null,
    spinnerText = " Processing...",
    requestOptions = {},
}) {
    // Unbind any previous submit event handlers to avoid duplication
    $(document).off("submit", formSelector).on("submit", formSelector, function (e) {
        e.preventDefault(); // Prevent default form submission

        const form = $(this);
        const submitButton = form.find("button[type='submit']");
        const originalButtonText = addSpinner(submitButton, spinnerText); // Save original button text
        const formData = serializeFormToObject(form); // Serialize the form data for AJAX
        console.log("Form Data Submitted:", formData); // Debug: Log the form data
        const url = form.attr("action"); // Get the form action URL

        // Ensure the action URL is set
        if (!url) {
            console.error("Form action URL is missing.");
            showErrorToast("Form action URL is missing. Cannot submit the form.");
            return;
        }

        // Add spinner and disable the button to prevent multiple clicks
        submitButton.prop("disabled", true).html(
            `<span class="spinner-border spinner-border-sm"></span> ${spinnerText}`
        );

        // AJAX request
        $.ajax({
            url: url,
            type: method,
            data: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"), // Add CSRF token for Laravel
            },

            ...requestOptions,
            beforeSend: function () {
                if (typeof beforeSendCallback === "function") {
                    beforeSendCallback();
                }
            },
            success: function (response) {
                if (response.success) {
                    // Reload the table if specified
                    if (reloadTableSelector) {
                        $(reloadTableSelector).DataTable().ajax.reload();
                    }

                    // Hide the modal if specified
                    if (modalSelector) {
                        $(modalSelector).modal("hide");
                    }

                    // Update extra fields dynamically
                    if (typeof extraFieldUpdates === "function") {
                        extraFieldUpdates(response);
                    }

                    // Show success toast
                    showSuccessToast(response.message || successToastMessage);

                    // Execute custom success callback
                    if (typeof onSuccessCallback === "function") {
                        onSuccessCallback(response);
                    }
                } else {
                    // Handle validation or general errors
                    if (response.errors) {
                        $.each(response.errors, function (field, messages) {
                            messages.forEach(function (message) {
                                showWarningToast(message);
                            });
                        });
                    } else {
                        showErrorToast(response.message || "An error occurred.");
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", xhr, status, error);
                showErrorToast("An error occurred. Please try again.");

                // Execute custom error callback
                if (typeof onErrorCallback === "function") {
                    onErrorCallback(xhr, status, error);
                }
            },
            complete: function () {
                // Restore button state after the request completes
                // submitButton.prop("disabled", false).html(originalButtonText);
                removeSpinner(submitButton, originalButtonText);
                if (typeof completeCallback === "function") {
                    completeCallback();
                }
            },
        });
    });
}

export function submitForm({
    formSelector,
    method = "POST",
    reloadTableSelector = null,
    modalSelector = null,
    successToastMessage = "Operation completed successfully.",
    onSuccessCallback = null,
    onErrorCallback = null,
    beforeSendCallback = null,
    completeCallback = null,
    extraFieldUpdates = null,
    spinnerText = " Processing...",
    requestOptions = {},
    forceFormData = false // Optional: force FormData usage
}) {
    $(document).off("submit", formSelector).on("submit", formSelector, function (e) {
        e.preventDefault();

        const form = $(this);
        const submitButton = form.find("button[type='submit']");
        const originalButtonText = addSpinner(submitButton, spinnerText);

        // Detect if form has files or should use FormData
        const hasFileInputs = form.find('input[type="file"]').length > 0;
        const hasFileInFormData = form.attr('enctype') === 'multipart/form-data';
        const useFormData = forceFormData || hasFileInputs || hasFileInFormData;

        let formData;
        let ajaxConfig = {
            url: form.attr("action"),
            type: method,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            ...requestOptions
        };

        if (useFormData) {
            formData = new FormData(this);

            // Add any additional data from requestOptions
            if (requestOptions.data && typeof requestOptions.data === 'object') {
                Object.keys(requestOptions.data).forEach(key => {
                    formData.append(key, requestOptions.data[key]);
                });
            }

            ajaxConfig = {
                ...ajaxConfig,
                data: formData,
                processData: false,
                contentType: false
            };
            console.log("Form Data Submitted (FormData):", Object.fromEntries(formData));
        } else {
            formData = serializeFormToObject(form);
            ajaxConfig.data = formData;
            console.log("Form Data Submitted:", formData);
        }

        const url = form.attr("action");
        if (!url) {
            console.error("Form action URL is missing.");
            showErrorToast("Form action URL is missing. Cannot submit the form.");
            return;
        }

        submitButton.prop("disabled", true).html(
            `<span class="spinner-border spinner-border-sm"></span> ${spinnerText}`
        );

        $.ajax({
            ...ajaxConfig,
            beforeSend: function () {
                if (typeof beforeSendCallback === "function") {
                    beforeSendCallback();
                }
            },
            success: function (response) {
                if (response.success) {
                    if (reloadTableSelector) {
                        $(reloadTableSelector).DataTable().ajax.reload();
                    }
                    if (modalSelector) {
                        $(modalSelector).modal("hide");
                    }
                    if (typeof extraFieldUpdates === "function") {
                        extraFieldUpdates(response);
                    }
                    showSuccessToast(response.message || successToastMessage);
                    if (typeof onSuccessCallback === "function") {
                        onSuccessCallback(response);
                    }
                } else {
                    if (response.errors) {
                        $.each(response.errors, function (field, messages) {
                            messages.forEach(function (message) {
                                showWarningToast(message);
                            });
                        });
                    } else {
                        showErrorToast(response.message || "An error occurred.");
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", xhr, status, error);
                showErrorToast("An error occurred. Please try again.");
                if (typeof onErrorCallback === "function") {
                    onErrorCallback(xhr, status, error);
                }
            },
            complete: function () {
                removeSpinner(submitButton, originalButtonText);
                if (typeof completeCallback === "function") {
                    completeCallback();
                }
            },
        });
    });
}
/*********************************************************************/
/**
 * Validate that start_date is not greater than end_date.
 * @param {string} startDateSelector - The selector for the start date input.
 * @param {string} endDateSelector - The selector for the end date input.
 */
export function validateDates(startDateSelector, endDateSelector) {
    // Utility function to parse a date string in dd-mm-yyyy format
    function parseDate(dateStr) {
        const parts = dateStr.split("-");
        if (parts.length === 3) {
            // Rearrange to yyyy-mm-dd for JavaScript Date compatibility
            return new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
        }
        return new Date("Invalid Date"); // Return an invalid date if parsing fails
    }

    // Function to validate the dates
    function checkDates() {
        const startDate = parseDate($(startDateSelector).val());
        const endDate = parseDate($(endDateSelector).val());

        // console.log("Start Date:", startDate);
        // console.log("End Date:", endDate);

        if (
            (startDate && isNaN(new Date(startDate).getTime())) ||
            (endDate && isNaN(new Date(endDate).getTime()))
        ) {
            showErrorToast("Invalid date format");
            return;
        }


        if (startDate > endDate) {
            showWarningToast("The start date cannot be greater than the end date.");
            $(startDateSelector).val(""); // Clear invalid start date
        } else if (endDate < startDate) {
            showWarningToast("The end date cannot be earlier than the start date.");
            $(endDateSelector).val(""); // Clear invalid end date
        }
    }

    // Bind events to validate dates
    $(startDateSelector).on("change", function () {
        checkDates();
    });

    $(endDateSelector).on("change", function () {
        checkDates();
    });

    // For Bootstrap Datepicker compatibility
    $(startDateSelector).datepicker().on("changeDate", function () {
        checkDates();
    });

    $(endDateSelector).datepicker().on("changeDate", function () {
        checkDates();
    });
}



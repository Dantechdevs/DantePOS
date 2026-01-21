$(function () {
    var isLoading = false;

    window.addSpinner = function (element, spinnerText) {
        const originalHTML = $(element).html(); // Store the original HTML (icon + text)
        const iconHTML = $(element).find("i").prop("outerHTML") || ''; // Extract the icon HTML if it exists
        $(element).html(`${iconHTML} <i class="fa fa-spinner fa-spin"></i> ${spinnerText}`).attr("disabled", true); // Keep the icon, add spinner, and update text
        return originalHTML; // Return the original HTML for restoration
    };

    window.removeSpinner = function (element, originalHTML) {
        $(element).html(originalHTML); // Restore the original HTML (icon + text)
        $(element).removeAttr("disabled");
    };


    window.showSuccessToast = function (message) {
        toastr.success(message, "Success", { timeOut: 3000 });
    }

    window.showErrorToast = function (message, title = null) {
        toastr.error(message, title ?? "Error", { timeOut: 3000 });
    }

    window.showWarningToast = function (message) {
        toastr.warning(message, "Warning", { timeOut: 3000 });
    }

    window.formatCurrency = function (amount) {
        const num = parseFloat(amount) || 0;
        return 'PKR' + num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    window.formatDate = function (dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // Form Data Serializer (optional)
    // window.serializeFormToObject = function (formSelector) {
    //     const formArray = $(formSelector).serializeArray();
    //     const formData = {};
    //     formArray.forEach((item) => {
    //         formData[item.name] = item.value;
    //     });
    //     return formData;
    // }

    window.serializeFormToObject = function (formSelector) {
        const formArray = $(formSelector).serializeArray();
        const formData = {};

        formArray.forEach((item) => {
            // If the key already exists, push the new value to an array
            if (formData[item.name]) {
                if (!Array.isArray(formData[item.name])) {
                    // Convert existing value to an array if it's not already
                    formData[item.name] = [formData[item.name]];
                }
                formData[item.name].push(item.value);
            } else {
                // Otherwise, simply add the value
                formData[item.name] = item.value;
            }
        });

        return formData;
    };

    // Track ongoing requests
    const activeRequests = new Map();

    window.ajaxRequest = function (url, method, data = {}, successCallback, errorCallback, options = {}) {
        const requestKey = `${method}:${url}:${JSON.stringify(data)}`; // Create a unique key for the request

        // Check if a request with the same key is already active
        if (activeRequests.has(requestKey)) {
            // console.log("Request already in progress:", requestKey);
            return; // Prevent duplicate requests
        }

        // Mark the request as active
        activeRequests.set(requestKey, true);

        const defaultOptions = {
            url: url,
            method: method,
            data: data,
            beforeSend: function () {
                console.log("Request sent...");
            },
            success: function (response, status, xhr) {
                if (successCallback && typeof successCallback === "function") {
                    successCallback(response, status, xhr);
                }
            },
            error: function (error) {
                if (errorCallback && typeof errorCallback === "function") {
                    errorCallback(error);
                }
            },
            complete: function () {
                // Remove the request from active requests
                activeRequests.delete(requestKey);
            },
        };

        // Merge custom options with default options
        const ajaxSettings = $.extend(true, {}, defaultOptions, options);

        // Return the $.ajax promise to allow chaining
        return $.ajax(ajaxSettings);
    };


    // window.ajaxRequest = function (url, method, data = {}, successCallback, errorCallback, options = {}) {
    //     const defaultOptions = {
    //         url: url,
    //         method: method,
    //         data: data,
    //         beforeSend: function () {
    //             console.log("Request sent...");
    //         },
    //         success: function (response, status, xhr) {
    //             if (successCallback && typeof successCallback === "function") {
    //                 successCallback(response, status, xhr);
    //             }
    //         },
    //         error: function (error) {
    //             if (errorCallback && typeof errorCallback === "function") {
    //                 errorCallback(error);
    //             }
    //         },
    //     };

    //     // Merge custom options with default options
    //     const ajaxSettings = $.extend(true, {}, defaultOptions, options);

    //     // Return the $.ajax promise to allow chaining
    //     return $.ajax(ajaxSettings);
    // };




    // window.ajaxRequest = function (url, method, data = {}, successCallback, errorCallback) {
    //     $.ajax({
    //         url: url,
    //         method: method,
    //         data: data,
    //         beforeSend: function () {
    //             console.log("Request sent...");
    //         },
    //         success: function (response) {
    //             if (successCallback && typeof successCallback === "function") {
    //                 successCallback(response);
    //             }
    //         },
    //         error: function (error) {
    //             if (errorCallback && typeof errorCallback === "function") {
    //                 errorCallback(error);
    //             }
    //         }
    //     });
    // };
});

$(function () {
    // Ensure CSRF Token is set for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // loadSalesForm();

    /*******************load sale form****************/

    $(document).on("submit", "#saleForm", function (e) {
        e.preventDefault();

        // Get the submit button and add a spinner
        const submitButton = $(this).find("button[type='submit']");
        const spinnerText = "Processing...";
        const originalText = addSpinner(submitButton, spinnerText);

        // Serialize form data
        const formData = serializeFormToObject(this);
        let url = $("#saleForm").attr('action');

        // Send AJAX request
        ajaxRequest(
            url,
            "POST",
            formData,
            function (response) {
                if (response.success) {
                    if (response.sale_type === 'new') {
                        // Rerender the form after submission
                        loadSalesForm(); // Call function to reload the form
                        showSuccessToast("Sale added successfully!");
                        const windowFeatures = "scrollbars=yes,resizable=yes,height=500,width=500";
                        // Open the new window
                        window.open(response.url, "_blank", windowFeatures);
                    } else if (response.sale_type === 'update') {
                        showSuccessToast("Sale updated successfully!");
                        setTimeout(function () {
                            window.location.href = response.url;
                        }, 2000);
                    }
                }

                if (response.errors) {
                    // Loop through validation errors and display them in a toaster
                    $.each(response.errors, function (field, messages) {
                        messages.forEach(function (message) {
                            showWarningToast(message); // Display each error as a toast
                        });
                    });
                }

                removeSpinner(submitButton, originalText); // Remove spinner after success
            },
            function (error) {
                if (error.responseJSON && error.responseJSON.error) {
                    // Display multi-line errors in a toast
                    const errors = error.responseJSON.error.split('<br>');
                    errors.forEach(function (err) {
                        showErrorToast(err.trim());
                    });
                } else if (error.status === 422) {
                    const validationErrors = error.responseJSON.errors;
                    $.each(validationErrors, function (field, messages) {
                        messages.forEach(function (message) {
                            showWarningToast(message); // Display each error as a toast
                        });
                    });
                } else {
                    showErrorToast("Failed to add sale. Please try again.");
                }
                removeSpinner(submitButton, originalText);
            }

        );
    });

    function reloadFormComponent(url, renderCurrentForm){
        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                if (response.html) {
                    $(renderCurrentForm).html(response.html);

                    // Clear previous assets to avoid duplicates
                    $('link[href^="css/"]').remove();
                    $('script[src^="js/"]').remove();

                    // Load new assets
                    if (Array.isArray(response.styles)) {
                        response.styles.forEach(function (styleUrl) {
                            loadCSS(styleUrl);
                        });
                    }
                    if (Array.isArray(response.scripts)) {
                        let scriptsLoaded = 0;
                        response.scripts.forEach(function (scriptUrl) {
                            loadScript(scriptUrl, function () {
                                scriptsLoaded++;
                                if (scriptsLoaded === response.scripts.length) {
                                    // All scripts are loaded, perform additional initialization if needed
                                    // console.log('All scripts loaded');


                                    // Reinitialize Select2
                                    $('.disabling-options').select2();
                                    $('.datepicker-default').pickadate();
                                    // Initialize Select2 for countries
                                    initializeSelect2('#searchCountry', '/search/country', 'Search Country');

                                    // Initialize Select2 for states
                                    initializeSelect2('#searchState', '/search/state', 'Search State');

                                    initializeSelect2('#searchCity', '/search/city', 'Search City');
                                    initializeSelect2('#searchCourtCategory', '/search/court/category', 'Search Court Category');
                                }
                            });
                        });
                    } else {
                        // No scripts to load, perform additional initialization if needed
                        // Reinitialize Select2
                        $('.disabling-options').select2();
                        $('.datepicker-default').pickadate();
                        // Initialize Select2 for countries
                        initializeSelect2('#searchCountry', '/search/country', 'Search Country');

                        // Initialize Select2 for states
                        initializeSelect2('#searchState', '/search/state', 'Search State');
                        initializeSelect2('#searchCity', '/search/city', 'Search City');
                        initializeSelect2('#searchCourtCategory', '/search/court/category', 'Search Court Category');
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                alert('Failed to reload form view.');
            }
        });
    }

    // Function to reload the sales form
    function loadSalesForm() {
        var url = "/load-sale-form";
        ajaxRequest(
            url,
            "GET",
            {},
            function (response) {
                if (response.success) {
                    $(".saleFormContainer").html(response.html); // Replace the form content
                    initializeForm(); // Reinitialize any plugins/widgets
                }
            },
            function (error) {
                showErrorToast("Failed to reload the form.");
            }
        );
    }

    // Function to initialize form plugins/widgets
    function initializeForm() {
        // Example: Initialize Select2, datepicker, or other plugins
        $(".select2").select2();
        $('#datepicker').datepicker({
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy'
        });
        // Initialize any other assets or custom bindings
        bindCustomEvents();
    }

    function bindCustomEvents() {
        $(".custom-class").on("click", function () {
            alert("Button clicked!");
        });
    }


    // $(document).on("submit", "#saleForm", function (e) {
    //     e.preventDefault();

    //     // Get the submit button and add a spinner
    //     const submitButton = $(this).find("button[type='submit']");
    //     const spinnerText = "Processing...";
    //     const originalText = addSpinner(submitButton, spinnerText);

    //     // Serialize form data
    //     const formData = serializeFormToObject(this);
    //     let url = $("#saleForm").attr('action');

    //     // Send AJAX request
    //     ajaxRequest(
    //         url,
    //         "POST",
    //         formData,
    //         function (response) {
    //             if (response.success) {
    //                 if (response.sale_type === 'new') {
    //                     showSuccessToast("Sale added successfully!");
    //                     const windowFeatures = "scrollbars=yes,resizable=yes,height=500,width=500";
    //                     // Open the new window
    //                     window.open(response.url, "_blank", windowFeatures);
    //                 } else if (response.sale_type === 'update') {
    //                     showSuccessToast("Sale updated successfully!");
    //                     setTimeout(function () {
    //                         window.location.href = response.url;
    //                     }, 2000);
    //                 }

    //             }

    //             if (response.errors) {
    //                 // Loop through validation errors and display them in toaster
    //                 $.each(response.errors, function (field, messages) {
    //                     messages.forEach(function (message) {
    //                         showWarningToast(message); // Display each error as a toast
    //                     });
    //                 });
    //             }

    //             removeSpinner(submitButton, originalText); // Remove spinner after success
    //         },
    //         function (error) {
    //             // Extract validation errors from the error response
    //             if (error.status === 422) {
    //                 const validationErrors = error.responseJSON.errors;
    //                 $.each(validationErrors, function (field, messages) {
    //                     messages.forEach(function (message) {
    //                         showWarningToast(message); // Display each error as a toast
    //                     });
    //                 });
    //             } else {
    //                 showErrorToast("Failed to add sale. Please try again.");
    //             }

    //             removeSpinner(submitButton, originalText); // Remove spinner after error
    //         }
    //     );
    // });
});

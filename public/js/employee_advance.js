$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // Toastr configuration
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "5000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut",
    };

    function loadEmployeeAdvance_return(url) {
        $('#employeeTable').DataTable({
            processing: true,
            serverSide: true,
            destroy: true, // Allow reinitialization
            ajax: {
                url: url,
                type: 'GET',
            },
            autoWidth: false, // Ensure columns adjust automatically
            responsive: true, // Make the table responsive
            order: [[0, 'desc']],
            columns: [
                {
                    data: null,
                    name: 'id',
                    title: '#',
                    className: 'text-center',
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1; // Counter
                    }
                },
                { data: 'description', name: 'description', title: 'Description' },
                { data: 'amount', name: 'amount', title: 'Amount', className: 'text-right' },
                { data: 'date', name: 'date', title: 'Date' },
                { data: 'action', name: 'action', className: 'text-right', orderable: false, searchable: false, title: 'Action' },
            ],
        });
    }


    // function loadEmployeeReturnAdvances(url) {
    //     $('#employeeTable').DataTable({
    //         processing: true,
    //         serverSide: true,
    //         destroy: true, // Allow reinitialization
    //         ajax: {
    //             url: url,
    //             type: 'GET',
    //         },
    //         columns: [
    //             { data: 'id', name: 'id' },
    //             { data: 'description', name: 'description',title:'Description' },
    //             { data: 'amount', name: 'amount', title: 'Amount' },
    //             { data: 'date', name: 'date', title: 'Date' },
    //             { data: 'action', name: 'action', orderable: false, searchable: false, title: 'Action' },
    //         ],
    //     });
    // }


    // Add click handler for the "Add Advance" button
    $(document).on("click", ".addAdvance", function (e) {
        e.preventDefault();
        let url = $(this).attr("data-url");
        let advanceListUrl = $(this).attr("data-advanceListUrl");
        let modal = $("#advancePaymentModal");

        // Set the form action dynamically
        modal.find("form").attr("action", url);

        // Update the table heading dynamically

        $("#tableHeading").text("Employee Advance");
        $('#employeeTable').DataTable().destroy();
        loadEmployeeAdvance_return(advanceListUrl)

        // Show the modal
        modal.modal("show");
    });

    // Add click handler for the "Add Advance" button
    $(document).on("click", ".returnAdvance", function (e) {
        e.preventDefault();
        let url = $(this).attr("data-url");
        let advanceReturnListUrl = $(this).attr("data-advanceReturnListUrl");
        let modal = $("#advancePaymentModal");

        // Set the form action dynamically
        modal.find("form").attr("action", url);

        $("#tableHeading").text("Employee Return Advance");
        $('#employeeTable').DataTable().destroy();
        loadEmployeeAdvance_return(advanceReturnListUrl)

        // Show the modal
        modal.modal("show");
    });

    // Handle form submission with AJAX
    $(document).on("submit", "#employeePaymentForm", function (e) {
        e.preventDefault();
        let form = $(this);
        let url = form.attr("action"); // Get form action URL
        let submitButton = form.find("button[type='submit']");

        // Reset previous validation errors
        form.find(".is-invalid").removeClass("is-invalid");
        form.find(".invalid-feedback").remove();

        // Show loader on the submit button
        submitButton.prop("disabled", true).html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
        );

        // AJAX request
        $.ajax({
            url: url,
            method: "POST",
            data: form.serialize(), // Serialize form data
            success: function (response) {
                if (response.status === "success") {
                    // Show success notification
                    toastr.success(response.message);

                    // Close modal, reset form, and reset button
                    // $("#advancePaymentModal").modal("hide");
                    loadEmployeeAdvance_return(response.url);
                    form[0].reset();
                    submitButton.prop("disabled", false).html("Save");
                }
            },
            error: function (xhr) {
                // Reset button
                submitButton.prop("disabled", false).html("Save");

                if (xhr.status === 422) {
                    // Handle validation errors
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        let input = form.find(`[name="${field}"]`);
                        input.addClass("is-invalid");
                        input.after(
                            `<div class="invalid-feedback">${errors[field][0]}</div>`
                        );
                    }
                    toastr.warning("Please fix the highlighted errors and try again.", "Validation Error");
                } else {
                    // Handle general errors
                    toastr.error(
                        xhr.responseJSON?.message || "Oops, Something went wrong. Please try again.",
                        "Error"
                    );
                }
            },
        });
    });


    // $(document).on("click", ".delete", function () {
    //     let id = $(this).data("id");
    //     let row = $(this).closest("tr"); // Get the table row containing the delete button

    //     if (confirm("Are you sure you want to delete this record?")) {
    //       // Animate the trash icon and disable the button during deletion
    //       let trashIcon = $(this).find("i");
    //       trashIcon.removeClass("fa-trash").addClass("fa-spinner fa-spin");

    //       // Send AJAX request to delete the record
    //       $.ajax({
    //         url: `/delete-advance/${id}`,
    //         type: "DELETE",
    //         headers: {
    //           "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    //         },
    //         success: function (response) {
    //           if (response.status === "success") {
    //             // Add a fade-out animation to the table row
    //             row.fadeOut(500, function () {
    //               $('#employeeTable').DataTable().ajax.reload(); // Reload the DataTable
    //               alert(response.message); // Display success message (replace with Toastr if needed)
    //             });
    //           } else {
    //             alert("Failed to delete the record.");
    //           }
    //         },
    //         error: function () {
    //           alert("An error occurred. Please try again.");
    //         },
    //         complete: function () {
    //           // Reset the trash icon regardless of success or error
    //           trashIcon.removeClass("fa-spinner fa-spin").addClass("fa-trash");
    //         },
    //       });
    //     }
    //   });



    $(document).on("click", ".deleteRecord", function () {
        // let id = $(this).data("id");
        let url = $(this).data("url");
        let row = $(this).closest("tr"); // Get the table row containing the delete button
        let trashIcon = $(this).find("i"); // Get the trash icon

        if (confirm("Are you sure you want to delete this record?")) {
          // Add animation class
          $(this).addClass("trash-animation");

          // Send AJAX request to delete the record
          $.ajax({
            url: url,
            type: "DELETE",
            headers: {
              "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
              if (response.status === 'success') {
                // Add fade-out animation to the table row
                row.fadeOut(500, function () {
                  $('#employeeTable').DataTable().ajax.reload(); // Reload the DataTable
                });
              } else {
                toastr.error(
                    response.message || "Failed to delete the record.",
                    "Error"
                );
              }
            },
            error: function () {
                toastr.error(
                    "An error occurred. Please try again.",
                    "Error"
                );
            },
            complete: function () {
              // Remove the animation class
              trashIcon.removeClass("fa-spinner fa-spin");
              trashIcon.closest(".delete").removeClass("trash-animation");
            },
          });
        }
      });

});

$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // Preview image
    window.previewImage = function (previewId, input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#" + previewId).attr("src", e.target.result);
            };
            reader.readAsDataURL(file);
        }
    };

    // Update file input label with the chosen file name
    $(".custom-file-input").on("change", function () {
        const fileName = this.files[0]?.name || "No file chosen";
        $(this).next(".custom-file-label").text(fileName);
    });

    // AJAX form submission with spinner
    $("#settingsForm").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitButton = $form.find(".btn-submit");
        const originalText = $submitButton.text(); // Save the original button text
        $submitButton.addClass("loading").text("Updating");

        let dotCount = 0;
        const dotAnimation = setInterval(() => {
            dotCount = (dotCount + 1) % 4; // Cycle through 0, 1, 2, 3
            $submitButton.text(`Updating${'.'.repeat(dotCount)}`);
        }, 500); // Update every 500ms

        const formData = new FormData(this);

        $.ajax({
            url: $form.attr("action"),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            success: function (data) {
                if (data.success) {
                    showSuccessToast(data.message);
                    // alert("Settings updated successfully!");
                } else {
                    showErrorToast("Error updating settings.");
                    // alert("Error updating settings.");
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const validationErrors = xhr.responseJSON.errors;
                    $.each(validationErrors, function (field, messages) {
                        messages.forEach(function (message) {
                            showWarningToast(message); // Display each error as a toast
                        });
                    });
                }
                // console.error("Error:", xhr.responseText);
            },
            complete: function () {
                clearInterval(dotAnimation); // Stop the dot animation
                $submitButton.removeClass("loading").text(originalText); // Reset the button text
            },
        });
    });
});

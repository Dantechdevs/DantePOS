// Global Toastr Configuration
toastr.options = {
    closeButton: true, // Show close button
    progressBar: true, // Show progress bar
    preventDuplicates: true, // Prevent duplicate toasts
    newestOnTop: true, // Show newest toast on top
    timeOut: 3000, // Base timeout for a toast
    extendedTimeOut: 1000, // Additional timeout for hover
    positionClass: "toast-top-right", // Positioning
    showMethod: "fadeIn", // Animation for showing
    hideMethod: "fadeOut", // Animation for hiding
    showDuration: 300, // Show animation duration
    hideDuration: 300, // Hide animation duration
    onHidden: function () {
        // Trigger next toast fade when one hides
        if ($("#toast-container > .toast").length) {
            const nextToast = $("#toast-container > .toast").last();
            if (nextToast.length) {
                setTimeout(() => nextToast.fadeOut(300), 200);
            }
        }
    },
};

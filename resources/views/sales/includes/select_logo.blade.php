<style>
    /* Centering the modal content */
    .modal-content {
        border-radius: 10px;
        border: none;
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        border-bottom: none;
    }

    .modal-body {
        padding: 20px;
    }

    /* Styling for the logo selection */
    .logo-wrapper {
        cursor: pointer;
        transition: transform 0.3s ease-in-out;
        text-align: center;
        margin: 10px;
        position: relative;
    }

    .logo-wrapper:hover {
        transform: scale(1.05);
    }

    .logo-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        background: #fff;
        transition: border-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .logo-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    /* Selection effect */
    .logo-wrapper.selected .logo-circle {
        border-color: #007bff;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        transform: scale(1.1);
    }

    /* Checkmark icon */
    .checkmark {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #007bff;
        color: white;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transform: scale(0);
        transition: transform 0.2s ease-in-out;
    }

    .logo-wrapper.selected .checkmark {
        transform: scale(1);
    }

    /* Button styling */
    .btn-select-logo {
        background: #007bff;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.3s;
        margin-top: 20px;
    }

    .btn-select-logo:hover {
        background: #0056b3;
    }
</style>

<!-- Logo Selection Modal -->
<div class="modal fade" id="logoSelectionModal" tabindex="-1" role="dialog" aria-labelledby="logoSelectionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                {{-- <h5 class="modal-title">Select Your Invoice Logo</h5> --}}
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-3">Please select the logo to be used for the invoice:</p>
                <div class="d-flex justify-content-center">
                    <!-- Logo 1 -->
                    <div class="logo-wrapper">
                        <div class="logo-circle">
                            <img src="{{ optional($settings)['invoice_logo'] && file_exists(public_path(optional($settings)['invoice_logo']))
                                ? asset(optional($settings)['invoice_logo'])
                                : asset('images/default-image.png') }}"
                                alt="Invoice Logo 1" class="logo-option" data-logo="invoice_logo">
                        </div>
                        <div class="checkmark"><i class="fas fa-check"></i></div>
                        {{-- <p class="mt-2">Logo 1</p> --}}
                    </div>

                    <!-- Logo 2 -->
                    <div class="logo-wrapper">
                        <div class="logo-circle">
                            <img src="{{ optional($settings)['invoice_logo2'] && file_exists(public_path(optional($settings)['invoice_logo2']))
                                ? asset(optional($settings)['invoice_logo2'])
                                : asset('images/default-image.png') }}"
                                alt="Invoice Logo 2" class="logo-option" data-logo="invoice_logo2">
                        </div>
                        <div class="checkmark"><i class="fas fa-check"></i></div>
                        {{-- <p class="mt-2">Logo 2</p> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

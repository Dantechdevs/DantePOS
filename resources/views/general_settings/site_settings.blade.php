@extends('layouts.layout')
@section('title', '| General Settings')
@section('content')
@section('custom_styles')
    <link rel="stylesheet" href="{{ asset('css/custom_styles.css') }}">
    <style>
        /* --- Existing styles unchanged --- */
        .settings-card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); border: 1px solid #eaeaea; }
        .settings-card .card-header { background: linear-gradient(135deg, #174577 0%, #2c6aa0 100%); color: white; border-radius: 12px 12px 0 0 !important; padding: 1.25rem 1.5rem; }
        .settings-card .card-header h3 { font-size: 1.4rem; font-weight: 600; margin: 0; }
        .settings-section { margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f0f0f0; }
        .settings-section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .section-title { font-size: 1.2rem; font-weight: 600; color: #174577; margin-bottom: 1.25rem; padding-bottom: 0.5rem; border-bottom: 2px solid #f0f0f0; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { font-weight: 600; color: #444; margin-bottom: 0.5rem; }
        .form-control, .custom-file-input { border-radius: 8px; border: 1px solid #d1d5db; padding: 0.75rem 1rem; transition: all 0.2s ease; }
        .form-control:focus, .custom-file-input:focus { border-color: #174577; box-shadow: 0 0 0 3px rgba(23, 69, 119, 0.15); }
        .custom-file-label { border-radius: 8px; padding: 0.75rem 1rem; border: 1px solid #d1d5db; color: #6b7280; }
        .custom-file-label::after { background-color: #f8f9fa; border-left: 1px solid #d1d5db; color: #174577; font-weight: 500; border-radius: 0 8px 8px 0; }
        .preview-container { display: flex; align-items: center; gap: 15px; margin-top: 10px; }
        .preview-container img { max-height: 100px; max-width: 100px; border: 2px solid #e5e7eb; border-radius: 8px; padding: 5px; background-color: white; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05); }
        .image-preview-label { font-size: 0.875rem; color: #6b7280; margin-top: 5px; }
        .btn-submit { background: linear-gradient(135deg, #174577 0%, #2c6aa0 100%); color: white; font-size: 1rem; font-weight: 600; padding: 0.75rem 2rem; border: none; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(23, 69, 119, 0.2); display: flex; align-items: center; justify-content: center; min-width: 160px; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 8px rgba(23, 69, 119, 0.25); color: white; }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit.loading { cursor: not-allowed; opacity: 0.85; }
        .spinner { width: 18px; height: 18px; border: 2px solid rgba(255, 255, 255, 0.3); border-top: 2px solid white; border-radius: 50%; animation: spin 0.8s linear infinite; margin-left: 10px; display: none; }
        .btn-submit.loading .spinner { display: inline-block; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .help-text { font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem; }
        .form-text { font-size: 0.8rem; }
        @media (max-width: 768px) { .settings-card .card-header { padding: 1rem 1.25rem; } .section-title { font-size: 1.1rem; } .btn-submit { width: 100%; } }
        .select2-container--default .select2-selection--single { border-radius: 8px; border: 1px solid #d1d5db; height: auto; padding: 0.75rem 1rem; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 100%; }
    </style>
@endsection

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>General Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Site Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <form id="settingsForm" action="{{ route('update.site.settings') }}" enctype="multipart/form-data">
            @csrf
            <div class="card settings-card">
                <div class="card-header">
                    <h3>Site Configuration</h3>
                </div>
                <div class="card-body">
                    <div id="flash_messages"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Company Information Section -->
                            <div class="settings-section">
                                <h4 class="section-title">Company Information</h4>

                                <!-- Site Name -->
                                <div class="form-group">
                                    <label for="site_name">Company Name</label>
                                    <input type="text" class="form-control" name="site_name" id="site_name"
                                        value="{{ $settings['site_name'] ?? '' }}" placeholder="Enter company name">
                                </div>

                                <!-- Placeholder for uniformity -->
                                <div class="form-group">
                                    <label for="site_name_alt">Company Name (Optional)</label>
                                    <input type="text" class="form-control" name="site_name_alt" id="site_name_alt"
                                        value="{{ $settings['site_name_alt'] ?? '' }}" placeholder="Optional">
                                </div>

                                <!-- Site Address -->
                                <div class="form-group">
                                    <label for="site_address">Company Address</label>
                                    <textarea class="form-control" name="site_address" id="site_address" rows="3"
                                        placeholder="Enter company address">{{ $settings['site_address'] ?? '' }}</textarea>
                                </div>

                                <!-- Placeholder for uniformity -->
                                <div class="form-group">
                                    <label for="site_address_alt">Company Address (Optional)</label>
                                    <textarea class="form-control" name="site_address_alt" id="site_address_alt" rows="3"
                                        placeholder="Optional">{{ $settings['site_address_alt'] ?? '' }}</textarea>
                                </div>

                                <!-- Mobile Numbers -->
                                <div class="form-group">
                                    <label for="mobile_numbers">Mobile Numbers</label>
                                    <input type="text" class="form-control" name="mobile_numbers" id="mobile_numbers"
                                        value="{{ $settings['mobile_numbers'] ?? '' }}"
                                        placeholder="Enter mobile numbers (comma-separated)">
                                    <div class="help-text">Add multiple mobile numbers separated by commas</div>
                                </div>
                            </div>

                            <!-- System Configuration Section -->
                            <div class="settings-section">
                                <h4 class="section-title">System Configuration</h4>

                                <div class="form-group">
                                    <label for="timezone">Timezone</label>
                                    <select name="timezone" class="form-control select2" id="timezone">
                                        @foreach ($timezones as $timezone)
                                            <option value="{{ $timezone }}"
                                                {{ old('timezone', @$settings['timezone']) === $timezone ? 'selected' : '' }}>
                                                {{ $timezone }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="currency">Currency</label>
                                    <select name="currency" class="form-control select2" id="currency">
                                        @foreach ($currencies as $currency => $label)
                                            <option value="{{ $currency }}"
                                                {{ old('currency', @$settings['currency']) === $currency ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Billing Language -->
                                <div class="form-group">
                                    <label for="billing_language">Billing Language</label>
                                    <select name="billing_language" class="form-control select2" id="billing_language">
                                        <option value="english"
                                            {{ old('billing_language', @$settings['billing_language']) === 'english' ? 'selected' : '' }}>
                                            English</option>
                                    </select>
                                    <div class="help-text">Select the language for invoices and bills</div>
                                </div>

                                <div class="form-group">
                                    <label for="threshold_amount">Threshold Amount</label>
                                    <input type="number" class="form-control" name="threshold_amount" id="threshold_amount"
                                        value="{{ $settings['threshold_amount'] ?? '' }}" placeholder="Enter threshold amount">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Brand Assets Section -->
                            <div class="settings-section">
                                <h4 class="section-title">Brand Assets</h4>

                                <!-- Login Logo -->
                                <div class="form-group">
                                    <label for="login_logo">Login Logo</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="login_logo" id="login_logo"
                                            accept="image/*" onchange="previewImage('login_logo_preview', this)">
                                        <label class="custom-file-label" for="login_logo">Choose file</label>
                                    </div>
                                    <div class="preview-container">
                                        <img id="login_logo_preview"
                                            src="{{ asset($settings['login_logo'] ?? 'images/default-image.png') }}"
                                            alt="Login Logo">
                                        <div>
                                            <div class="image-preview-label">Current Login Logo</div>
                                            <div class="help-text">Recommended size: 150x50px</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Invoice Logo 1 -->
                                <div class="form-group">
                                    <label for="invoice_logo">Invoice Logo 1</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="invoice_logo"
                                            id="invoice_logo" accept="image/*"
                                            onchange="previewImage('invoice_logo_preview', this)">
                                        <label class="custom-file-label" for="invoice_logo">Choose file</label>
                                    </div>
                                    <div class="preview-container">
                                        <img id="invoice_logo_preview"
                                            src="{{ asset($settings['invoice_logo'] ?? 'images/default-image.png') }}"
                                            alt="Invoice Logo">
                                        <div>
                                            <div class="image-preview-label">Current Invoice Logo 1</div>
                                            <div class="help-text">Recommended size: 200x100px</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Invoice Logo 2 -->
                                <div class="form-group">
                                    <label for="invoice_logo2">Invoice Logo 2</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="invoice_logo2"
                                            id="invoice_logo2" accept="image/*"
                                            onchange="previewImage('invoice_logo2_preview', this)">
                                        <label class="custom-file-label" for="invoice_logo2">Choose file</label>
                                    </div>
                                    <div class="preview-container">
                                        <img id="invoice_logo2_preview"
                                            src="{{ asset($settings['invoice_logo2'] ?? 'images/default-image.png') }}"
                                            alt="Invoice Logo">
                                        <div>
                                            <div class="image-preview-label">Current Invoice Logo 2</div>
                                            <div class="help-text">Recommended size: 200x100px</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Default Image -->
                                <div class="form-group">
                                    <label for="default_image">Default Image</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="default_image"
                                            id="default_image" accept="image/*"
                                            onchange="previewImage('default_image_preview', this)">
                                        <label class="custom-file-label" for="default_image">Choose file</label>
                                    </div>
                                    <div class="preview-container">
                                        <img id="default_image_preview"
                                            src="{{ asset($settings['default_image'] ?? 'images/default-image.png') }}"
                                            alt="Default Image">
                                        <div>
                                            <div class="image-preview-label">Current Default Image</div>
                                            <div class="help-text">Upload a default image for use across the site</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Favicon Icon -->
                                <div class="form-group">
                                    <label for="favicon">Favicon</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="favicon" id="favicon"
                                            accept="image/*" onchange="previewImage('favicon_preview', this)">
                                        <label class="custom-file-label" for="favicon">Choose file</label>
                                    </div>
                                    <div class="preview-container">
                                        <img id="favicon_preview"
                                            src="{{ asset($settings['favicon'] ?? 'images/default-image.png') }}"
                                            alt="Favicon">
                                        <div>
                                            <div class="image-preview-label">Current Favicon</div>
                                            <div class="help-text">Upload a favicon for your website</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Section -->
                            <div class="settings-section">
                                <h4 class="section-title">Footer Settings</h4>

                                <!-- Footer Text -->
                                <div class="form-group">
                                    <label for="footer_text">Footer Text</label>
                                    <textarea class="form-control" name="footer_text" id="footer_text" rows="3" placeholder="Enter footer text">{{ $settings['footer_text'] ?? 'Developed By: Dantechdevs Developers | Contact: +254712328150' }}</textarea>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group text-right mt-4">
                                <button type="submit" class="btn btn-submit">
                                    Update Settings
                                    <div class="spinner"></div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>
@endsection

@push('custom-script')
<script src="{{ asset('js/common/global.js') }}"></script>
<script src="{{ asset('js/settings/site-settings.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInputs = document.querySelectorAll('.custom-file-input');
        fileInputs.forEach(input => {
            input.addEventListener('change', function() {
                let fileName = this.files[0] ? this.files[0].name : 'Choose file';
                this.nextElementSibling.textContent = fileName;
            });
        });

        if (jQuery && jQuery.fn.select2) {
            jQuery('.select2').select2({ theme: 'bootstrap4', width: '100%' });
        }
    });

    function previewImage(previewId, input) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) { preview.src = e.target.result; };
            reader.readAsDataURL(file);
        }
    }
</script>
@endpush

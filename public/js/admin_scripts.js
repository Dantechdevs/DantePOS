$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    // check Admin password is correct or not....
    $("#current_pwd").keyup(function () {
        var current_pwd = $("#current_pwd").val();
        // alert(current_pwd);
        $.ajax({
            type: 'post',
            url: '/check-current-pwd',
            data: { current_pwd: current_pwd },
            success: function (resp) {
                if (resp == "false") {
                    $("#chkCurrentPwd").html("<font color=red>Current Password is incorrect</font>");
                } else if (resp == "true") {
                    $("#chkCurrentPwd").html("<font color=green>Current Password is correct</font>");
                }
            }, error: function () {
                alert("Error")
            }
        });
    });

    // Add Customer with Ajax.....
    $("#saveCustomer").click(function (e) {
        e.preventDefault();

        // customerForm = $('#customerForm').serializeArray();
        var customerName = $('#customer_name').val();
        var mobile = $('#mobile').val();
        var cnic = $('#cnic').val();
        var address = $('#address').val();
        var area_id = $('#area_id').val();
        alert(area_id);


        $.ajax({
            url: "/ajax-add-customer",
            method: "POST",
            data: {
                customerName: customerName,
                mobile: mobile,
                cnic: cnic,
                address: address,
                area_id: area_id
            },
            success: function (response) {

                console.log(response);
                $("#customer_id").val(response.customerID);
                $("#customerName").val(response.customerName);
                $("#area_id").val(response.areaID);

                if (response.success == true) {
                    $.notify("New Customer Added Successfully!", { globalPosition: 'top right', className: 'success' });
                    $('input#customer_name').val('');
                    $('input#mobile').val('');
                    $('input#cnic').val('');
                    $('input#address').val('');
                } else if (response == "false") {
                    $.notify("Enter Customer Details!", { globalPosition: 'top right', className: 'error' });
                }
            }
        });
    });
    $("#customerName").on('keyup', function () {
        $("#customerBlnce").hide();
        $("#blnceTitle").hide();
    })

    // Customer Autocomplete......
    $("#customerName").autocomplete({

        source: "/customer-auto-complete",
        minLength: 1,
        select: function (event, ui) {
            var item = ui.item;
            if (item) {
                $("#customer_id").val(item.customerID);
                $("#autoCustomerName").val(item.customerName);
                $("#area_id").val(item.areaID);
                $("#chkCustomerBalance").val(item.customerBalance);
                $("#customerBlnce").text(item.customerBalance);
                $("#customerBlnce").show();
                $("#blnceTitle").show();
            }
        }
    });

    // Customer Autocomplete......
    $("#supplierName").autocomplete({

        source: "/supplier-auto-complete",
        minLength: 1,
        select: function (event, ui) {
            var item = ui.item;
            if (item) {
                $("#supplier_id").val(item.customerID);
                $("#autoSupplierName").val(item.supplierName);
                $("#chkSupplierBalance").val(item.supplierBalance);
            }
        }
    });

    // Invoice# Autocomplete......
    $("#invoice_no").autocomplete({

        source: "/invoiceNo-auto-complete",
        minLength: 1,
        select: function (event, ui) {
            var item = ui.item;
            if (item) {
                $("#invoice_no").val(item.invoiceNo);
                $("#customer_id").val(item.custmerID);
                $("#customerName").val(item.customerName);
            }
        }
    });

    // Delivery Man Autocomplete......
    $("#deliveryMan").autocomplete({

        source: "/delivery-man-auto-complete",
        minLength: 1,
        select: function (event, ui) {
            var item = ui.item;
            if (item) {
                $("#employee_id").val(item.employeeID);
                $("#deliveryMan").val(item.employeeName);
            }
        }
    });
    // Delivery Man Advance with Ajax.....
    $("#deliveryManAmount").click(function (e) {
        e.preventDefault();

        var date = $('#date').val();
        var invoice_no = $('#invoice_no').val();
        var employee_id = $('#employee_id').val();
        var deliveryAmount = $('#deliveryAmount').val();


        $.ajax({
            url: "/ajax-add-delivery-man-amount",
            method: "POST",
            data: {
                employee_id: employee_id,
                invoice_no: invoice_no,
                deliveryAmount: deliveryAmount,
                date: date
            },
            success: function (response) {

                console.log(response);

                if (response.success == true) {
                    $.notify("Delivery Payment Successfully Added!", { globalPosition: 'top right', className: 'success' });
                    $('input#deliveryAmount').val('');
                }
            }
        });
    });

    // Section Status code....

    $(document).on('click', '.updateSectionStatus', function (e) {
        // var status = $(this).text();
        var status = $(this).children("i").attr("status");
        var section_id = $(this).attr("section_id");
        $.ajax({
            type: 'post',
            url: '/admin/update-section-status',
            data: { status: status, section_id: section_id },
            success: function (resp) {
                // alert(resp['status']);
                // alert(resp['section_id']);
                if (resp['status'] == 0) {
                    $("#section-" + section_id).html("<i class='fas fa-toggle-off' status='InActive'></i>");
                } else if (resp['status'] == 1) {
                    $("#section-" + section_id).html("<i class='fas fa-toggle-on' status='Active'></i>");
                }
            }, error: function () {
                alert("Error");
            }
        });
    });
    // Section Status code End....

});

$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#show_report').on('click', function () {

        var startDate = $("#startDate").val();
        var endDate = $("#endDate").val();


        var all_customers = $("#all_customers").val();
        var user_id = $("#user_id").val();

        let btnVal = $(".showUserSaleBtn").text();

        let btn = $(".showUserSaleBtn");

        $.ajax({
            url: '/report-get-staff-sales',
            type: 'POST',
            data: {
                startDate: startDate,
                endDate: endDate,
                user_id: user_id,
                all_customers: all_customers
            },
            // async: false,
            beforeSend: function () {
                $(btn).text("processing...");
                $(btn).prepend('<i class="fa fa-spinner fa-spin"></i>');
                $(btn).attr("disabled", 'disabled');
            },
            success: function (data) {

                $(btn).text(btnVal);
                $(btn).find(".fa-spinner").remove();
                $(btn).removeAttr("disabled");
                if (data == 'false') {
                    $("#DocumentResults").html("");
                    $.notify("there is no data found!", { globalPosition: 'top right', className: 'error' });
                    return false;
                }
                var source = $("#document-template").html();
                var template = Handlebars.compile(source);
                var html = template(data);
                $('#DocumentResults').html(html);
                $('[data-toggle="tooltip"]').tooltip();

            }, errro: function () {
                $(btn).text(btnVal);
                $(btn).find(".fa-spinner").remove();
                $(btn).removeAttr("disabled");
                $.notify("Unauthorized Error", { globalPosition: 'top right', className: 'error' });
                return false;
            }
        });
    });

});

$(document).ready(function() {
    $('#pingForm').validate({
        rules: {
            firstName: "required",            
            email: {
                required: true,
                email: true
            },
            tel: {
                required: true,
                number: true
            },
            company: "required"
        },
        errorElement: "span" ,                            
        messages: {
            firstName: "Please enter your sweet name",
            email: "Please enter valid email address",
            tel: "Please enter your mobile number",
            company: "Please choose category"
        },
        submitHandler: function(form) {
            var dataparam = $('#pingForm').serialize();

            $.ajax({
                type: 'POST',
                async: true,
                url: 'mailer.php',
                data: dataparam,
                datatype: 'json',
                cache: true,
                global: false,
                beforeSend: function() { 
                    $('#loader').show();
                },
                success: function(data) {
                    if(data == 'success'){
                        console.log(data);
                    } else {
                        $('.no-config').show();
                        console.log(data);
                    }

                },
                complete: function() { 
                    $('#loader').hide();
                }
            });
        }                
    });
});
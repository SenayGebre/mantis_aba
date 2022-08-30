
        $(document).ready( function() {
            $('.some_class').hide();
                $('#terminal_id').on('change', function() {
                    //    console.log('senay');
                        var terminal_id = this.value;
                        // // console.log(country_id);
                        $.ajax({
                            url: './plugins/ATM_Monitoring/files/branch.php',
                            // url: 'branch.php',
                            type: "POST",
                            data: {
                                terminal_data: terminal_id
                            },
                            success: function(result) {
                                // $("select").removeClass("senselectpicker");
                                // $('.some_class').hide();
                                console.log(result);
                            }
                        })
                    });

        });
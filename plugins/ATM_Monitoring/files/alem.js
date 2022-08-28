
        $(document).ready( function() {
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
                                $('#branch_id').html(result);
                                console.log(result);
                            }
                        })
                    });
        });
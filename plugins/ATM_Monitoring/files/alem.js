
        $(document).ready( function() {
            // $('.some_class').hide();

            let selected_atms =  [];

           
            
            $(document).on('click','.atm_chip',function(){
            console.log($(this).attr('id'));
            selected_atms.append();

            // code here
        });

                $('#terminal_id').on('change', function() {
                    //    console.log('senay');
                        var terminal_id = this.value;
                        // // console.log(country_id);
                        $.ajax({
                            url: './plugins/ATM_Monitoring/branch.php',
                            // url: 'branch.php',
                            type: "POST",
                            data: {
                                terminal_data: terminal_id
                            },
                            success: function(result) {
                                // $("select").removeClass("senselectpicker");
                                $('.some_class').hide();
                                console.log(result);
                            }
                        })
                    });

                    $('#branch_idd').on('change', function() {
                  
                            console.log('senay');
                            var branch_id = $(this).val();

                            $.ajax({
                                url: './plugins/ATM_Monitoring/branch.php',
                                // url: 'branch.php',
                                type: "POST",
                                data: {
                                    branch_data: branch_id
                                },
                                success: function(result) {
                                    console.log(result);
                                    $('.terminal_list').html(result);
                                }
                            })
                        });

                    $('.wrapper_atm label').click(function() {
                        if($(this).prev().attr('id') === "terminal_select") {
                            $('.branch_selection').hide();
                            $('.terminal_selection').show();

                        } else {
                            $('.branch_selection').show();

                            $('.terminal_selection').hide();
                        }
                        // console.log('Value of Radion: '.concat($(this).prev().val(), 'Name of radio: ', ));
                      });

                    //   var selected = [];
// $('#terminals_checkbox input:checked').each(function() {
//     // console.log($(this).attr('name'));
//     console.log('sfdsf');
//     // selected.push($(this).attr('name'));
// });
                    

        });
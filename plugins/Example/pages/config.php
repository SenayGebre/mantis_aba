<?php
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin();

$t_foo_or_bar = plugin_config_get( 'foo_or_bar' );
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>

<div class="form-container">
    <form action="<?php echo plugin_page( 'config_update' ) ?>" method="post">
        <?php echo form_security_field( 'plugin_Example_config_update' ) ?>
        <div class="widget-box widget-color-blue2">
            <div class="widget-header widget-header-small">
                <h4 class="widget-title lighter">
                    <?php
                    print_icon( 'fa-text-width', 'ace-icon' );
                    echo "&nbsp;";
                    echo plugin_lang_get( 'title') . ': '
                        . plugin_lang_get( 'configuration' );
                    ?>
                </h4>
            </div>

            <div class="widget-body">
                <div class="widget-main no-padding table-responsive">
                    <table class="table table-bordered table-condensed table-striped">
                        <tr>
                            <th class="category">
                                <label for="foo_or_bar">
                                    <?php echo plugin_lang_get( 'foo_or_bar' ) ?>
                                </label>
                            </th>
                            <td>
                                <input type="text" id="foo_or_bar" name="foo_or_bar"
                                       value="<?php echo string_attribute( $t_foo_or_bar ) ?>"
                                />
                            </td>
                        </tr>
                        <tr>
                            <th class="category">
                                <label for="reset">
                                    <?php echo plugin_lang_get( 'reset' ) ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" id="reset" name="reset" class="ace" />
                                <span class="lbl"> </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="widget-toolbox padding-8 clearfix">
                    <button class="btn btn-primary btn-white btn-round">
                        <?php echo lang_get( 'change_configuration' )?>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

</div>

<?php
layout_page_end();

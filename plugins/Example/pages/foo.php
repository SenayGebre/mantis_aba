<?php
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin();
?>

<p>
    Here is a link to <a href="<?php echo plugin_page( 'foo' ) ?>">page foo</a>.
</p>

<link rel="stylesheet" type="text/css" href="<?php echo plugin_file( 'foo.css' ) ?>" />
<p class="foo">
    Our custom stylesheet paints this text red.
</p>

<p>
    Custom event hooks:
<?php
event_signal( 'EVENT_EXAMPLE_FOO' );

$t_string = 'A sentence with the word "foo" in it.';
$t_new_string = event_signal( 'EVENT_EXAMPLE_BAR', array( $t_string ) );
echo $t_new_string;
?>
</p>

<?php
layout_page_end();

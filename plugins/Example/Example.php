<?php
class ExamplePlugin extends MantisPlugin {
    function register() {
        $this->name = plugin_lang_get( 'title' );
                                             # Proper name of plugin
        $this->description = plugin_lang_get( 'description' );
                                             # Short description of the plugin
        $this->page = 'config';              # Default plugin page

        $this->version = '2.0.1';            # Plugin version string
        $this->requires = array(             # Plugin dependencies
            'MantisCore' => '2.0',           # Should always depend on an appropriate
                                             # version of MantisBT
        );

        $this->author = 'MantisBT Team';     # Author/team name
        $this->contact = 'mantisbt-dev@lists.sourceforge.net';
                                             # Author/team e-mail address
        $this->url = 'https://mantisbt.org'; # Support webpage
    }

    function events() {
        return array(
            'EVENT_EXAMPLE_FOO' => EVENT_TYPE_EXECUTE,
            'EVENT_EXAMPLE_BAR' => EVENT_TYPE_CHAIN,
        );
    }

    function hooks() {
        return array(
            'EVENT_MENU_MAIN' => 'menu',

            'EVENT_EXAMPLE_FOO' => 'foo',
            'EVENT_EXAMPLE_BAR' => 'bar',
        );
    }

    function menu() {
        $t_menu[] = array(
            'title' => $this->name,
            'url' => plugin_page( 'foo' ),
            'access_level' => ANYBODY,
            'icon' => 'fa-smile-o'
        );
        return $t_menu;
    }

    function foo( $p_event ) {
        echo 'In method foo(). ';
    }

    function bar( $p_event, $p_chained_param ) {
        return str_replace( 'foo', 'bar', $p_chained_param );
    }

    function config() {
        return array(
            'foo_or_bar' => 'foo',
        );
    }

}

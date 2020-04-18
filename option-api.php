<?php
/*
Plugin Name: WPDB Demo
Plugin URI:
Description: Demonstration of WPDB Methods
Version: 1.0.0
Author: Tarikul Islam
Author URI:
License: GPLv2 or later
Text Domain: wpdb-demo
 */
// use function GuzzleHttp\json_decode;

function wpdbdemo_init() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'persons';
    $sql = "CREATE TABLE {$table_name} (
			id INT NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(250),
			email VARCHAR(250),
            age INT,
			PRIMARY KEY (id)
	);";
    require_once ABSPATH . "wp-admin/includes/upgrade.php";
    dbDelta( $sql );
}

register_activation_hook( __FILE__, "wpdbdemo_init" );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( 'toplevel_page_wpdb-demo' == $hook ) {
        wp_enqueue_style( 'pure-grid-css', '//unpkg.com/purecss@1.0.1/build/grids-min.css' );
        wp_enqueue_style( 'wpdb-demo-css', plugin_dir_url( __FILE__ ) . "assets/css/style.css", null, time() );
        wp_enqueue_script( 'wpdb-demo-js', plugin_dir_url( __FILE__ ) . "assets/js/main.js", array( 'jquery' ), time(), true );
        $nonce = wp_create_nonce( 'display_result' );
        wp_localize_script(
            'wpdb-demo-js',
            'plugindata',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => $nonce )
        );
    }
} );

function wpdemo_ajax_display_result() {
    if ( wp_verify_nonce( $_POST['nonce'], $_POST['action'] ) ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'persons';
        $task = $_POST['task'];
        if ( 'add-option' == $task ) {
            $key = 'od_country';
            $value = "Bangladesh";
            echo "New Record Added =" . add_option( $key, $value );

        } elseif ( 'add-array-option' == $task ) {
            $key = 'array_add';
            $value = array(
                'country' => 'Bangladesh',
                'state'   => 'Gandaria',
            );
            echo "New Record Added = " . add_option( $key, $value );

            $key = 'array_add_jsonn1';
            $value = json_encode( array(
                'country' => 'Bangladesh',
                'state'   => 'Gandaria',
            ) );
            echo "New Record Added = " . add_option( $key, $value );

        } elseif ( 'get-option' == $task ) {
            $key = 'od_country';
            $result = get_option( $key );
            echo "Result = " . $result;

        } elseif ( 'get-array-option' == $task ) {

            // Array data with json encode Add by update option
            // And I make sure that data sucessfully saved in option table
            $key = 'raju-json';
            $value = json_encode( array( 'country' => 'Bangladesh', 'capital' => 'dhaka', 'name' => 'tarikul' ) );
            echo "Result = " . update_option( $key, $value ) . '<br/>';

            // try to pick the array data with json decode but no result
            // But if don't use here json_decode() that time get data
            // Like that data = {"country":"Bangladesh","capital":"dhaka"}

            $jsonkey = 'raju-json';
            $result = get_option( $jsonkey );
            $decode_data = json_decode( $result );
            print_r( $decode_data );

        } elseif ( 'option-filter-hook' == $task ) {

            $key = 'od_country';
            $result = get_option( $key );
            echo "Result = " . $result;

        } elseif ( 'update-option' == $task ) {

            $key = 'od_capital';
            $value = "Dhaka";
            $result = update_option( $key, $value );
            echo "Result = " . $result;

        } elseif ( 'update-array-option' == $task ) {

            $key = 'raju-json';
            $value = json_encode( array( 'country' => 'Bangladesh' ) );
            $result = update_option( $key, $value );
            echo "Result = " . $result;

        } elseif ( 'delete-option' == $task ) {

            $key = 'info3';
            $result = delete_option( $key );
            echo "Result = " . $result;

        } elseif ( 'export-option' == $task ) {

            // $key = 'info-array';
            // $value = array('name'=>'Tarikul Islam','email'=>'tarikul47@gmail.com');
            // $result = update_option( $key, $value);
            // echo "Result = " . $result;

            //Export data

            $key_normal = ['country', 'satate'];
            $key_array = ['info-array'];
            $key_json = ['student'];
            $export_data = [];

            foreach ( $key_normal as $key ) {
                $value = get_option( $key );
                $export_data[$key] = $value;
            }
            foreach ( $key_array as $key ) {
                $value = get_option( $key );
                $export_data[$key] = $value;
            }
            foreach ( $key_json as $key ) {
                $value = json_decode( get_option( $key ), true );
                $export_data[$key] = $value;
            }
            //  print_r($export_data);
            echo json_encode( $export_data ); // Json encode
            // $data_json =  json_encode($export_data); // Json encode
            // echo base64_encode(json_encode($export_data));

        } elseif ( 'import-option' == $task ) {

            $import_data = '{"country":"Bangladesh","satate":"Dhaka","info-array":{"name":"Tarikul Islam","email":"tarikul47@gmail.com"},"student":{"name":"Tarikul Islam","email":"tarikul47@gmail.com"}}';
            $array_data = json_decode( $import_data, true );
           // print_r( $array_data );

            foreach($array_data as $key => $value){
                update_option($key,$value);
            }

        }
    }
    die( 0 );
}
add_action( 'wp_ajax_display_result', 'wpdemo_ajax_display_result' );

/**
 * Option Filter Hook
 */
add_filter( 'option_od_country', function ( $value ) {
    return strtoupper( $value ) . " MY LOVE";
} );

add_filter( 'option_od-array-json', function ( $value ) {
    return json_decode( $value, true );
} );

add_action( 'admin_menu', function () {
    add_menu_page( 'WPDB Demo', 'WPDB Demo', 'manage_options', 'wpdb-demo', 'wpdbdemo_admin_page' );
} );

function wpdbdemo_admin_page() {
    ?>
        <div class="container" style="padding-top:20px;">
            <h1>WPDB Demo</h1>
            <div class="pure-g">
                <div class="pure-u-1-4" style='height:100vh;'>
                    <div class="plugin-side-options">
                        <button class="action-button" data-task='add-option'>Add New Option</button>
                        <button class="action-button" data-task='add-array-option'>Add Array Option</button>
                        <button class="action-button" data-task='get-option'>Get Option</button>
                        <button class="action-button" data-task='get-array-option'>Get Option Array</button>
                        <button class="action-button" data-task='option-filter-hook'>Option Filter Hook</button>
                        <button class="action-button" data-task='update-option'>Update option</button>
                        <button class="action-button" data-task='update-array-option'>Update Array Option</button>
                        <button class="action-button" data-task='delete-option'>Delete Option</button>
                        <button class="action-button" data-task='export-option'>Export Option</button>
                        <button class="action-button" data-task='import-option'>Import Option</button>
                    </div>
                </div>
                <div class="pure-u-3-4">
                    <div class="plugin-demo-content">
                        <h3 class="plugin-result-title">Result</h3>
                        <div id="plugin-demo-result" class="plugin-result"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}

<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('otp_service')) {

    class otp_service {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('product-info', __CLASS__ . '::registration');
            add_shortcode('serial-number-list', __CLASS__ . '::list_serial_number');
            add_shortcode('curtain-product-list', __CLASS__ . '::list_curtain_products');
            add_shortcode('curtain-user-list', __CLASS__ . '::list_curtain_users');
            self::create_tables();
            //self::delete_records();
        }

        function push_text_message($text_message='', $line_user_id='') {
            $client = line_bot_sdk();
            $client->pushMessage([
                'to' => $line_user_id,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $text_message
                    ]
                ]
            ]);
        }

        function push_OTP_to($line_user_id='') {
            $six_digit_random_number = random_int(100000, 999999);
            $text_message = 'OTP code : '.$six_digit_random_number;
            self::push_text_message($text_message, $line_user_id);
        }

        function registration() {

            if( isset($_POST['submit_action']) ) {

                $line_user_id = $_POST['line_user_id'];

                if( $_POST['submit_action']=='Code' ) {
                    //return do_shortcode( '[dqr_code]' );
                    $serial_no = $_POST['serial_no'];
                    global $wp;
                    //echo home_url( $wp->request );
                    $output = '<div id="qrcode"></div>';
                    $output .= '<p>This is the default dialog which is useful for displaying information. The dialog window can be moved, resized and closed with the icon.</p>';
                    //return $output;
?><?php
                    //return do_shortcode( '[dqr_code url="'.home_url( $wp->request ).'?serial_no='.$serial_no.'"]' );
                }

                if( $_POST['submit_action']=='Confirm' ) {
                    // check the $_POST['otp_input'] to match the last_otp field in curtain_users table
                    if ( $last_otp==$_POST['otp_input'] ) {

                    } else {
                        $text_message = 'The '.$_POST['otp_input'].' is a wrong OTP code.';
                        self::push_text_message($text_message, $line_user_id);
                    }
                }

                if( $_POST['submit_action']=='Resend' ) {

                    self::push_OTP_to($line_user_id);

                    global $wpdb;
                    $table = $wpdb->prefix.'curtain_users';
                    $data = array(
                        'last_otp' => $six_digit_random_number,
                    );
                    $where = array(
                        'curtain_user_id' => $curtain_user_id,
                    );
                    $wpdb->update( $table, $data, $where );                
                }
                unset($_POST['submit_action']);
            }

            $qr_code_serial_no = $_GET['serial_no'];
            
            $output = '<div>';
            global $wpdb;
            //$row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = {$qr_code_serial_no}", OBJECT );
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $qr_code_serial_no ), OBJECT );            
            if (count($row) > 0) {

                $curtain_user_id=$row->curtain_user_id;
                $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$curtain_user_id}", OBJECT );
                if (count($user) > 0) {
                    $output .= 'Hi, '.$user->display_name.'<br>';
                }
                $output .= '感謝您選購我們的電動窗簾<br>';
                $curtain_product_id=$row->curtain_product_id;
                $product = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = {$curtain_product_id}", OBJECT );
                if (count($product) > 0) {
                    $output .= '型號:'.$product->model_number.' 規格: '.$product->specification.' '.$product->product_name.'<br>';
                }

                if (count($user) > 0) {
                    $output .= '請輸入我們送到您Line帳號的OTP(一次性密碼):';
                    $output .= '<form method="post">';
                    $output .= '<input type="text" name="otp_input">';
                    $output .= '<div class="wp-block-button">';
                    $output .= '<input type="hidden" value="'.$user->line_user_id.'" name="line_user_id">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Confirm" name="submit_action">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Resend" name="submit_action">';
                    $output .= '</div>';
                    $output .= '</form>';
                } else {
                    // send invitation link by URL for the Line@ account
                    $six_digit_random_number = random_int(100000, 999999);
                    $output .= '請利用手機按 '.'<a href="https://line.me/ti/p/@490tjxdt">';
                    $output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="36" border="0"></a>';
                    $output .= ' 加入我們的官方帳號, 讓我們成為您的好友,<br> 並在Line聊天室中輸入六位數字註冊密碼: <p style="color:blue">'.$six_digit_random_number.' </p>完成註冊程序<br>';
                    global $wpdb;
                    $table = $wpdb->prefix.'serial_number';
                    $data = array(
                        'curtain_user_id' => intval($six_digit_random_number),
                    );
                    $where = array(
                        'qr_code_serial_no' => $qr_code_serial_no,
                    );
                    $wpdb->update( $table, $data, $where );                
                }

            } else {
                // send invitation link by URL for the Line@ account
                // https://line.me/ti/p/@490tjxdt
                // <a href="https://lin.ee/LPnyoeD">
                $output .= '請利用手機按 '.'<a href="https://line.me/ti/p/@490tjxdt">';
                $output .= '<img src="https://scdn.line-apps.com/n/line_add_friends/btn/zh-Hant.png" alt="加入好友" height="36" border="0"></a>';
                $output .= ' 加入我們的官方帳號, 讓我們成為您的好友,<br>';

                if( isset($_GET['serial_no']) ) {
                    $output .= 'qr_code_serial_no='.$_GET['serial_no'].'<br>';
                }

                if( isset($_GET['action']) ) {

                    if( ($_GET['action']=='insert-curtain-product') && (isset($_GET['model_number'])) && (isset($_GET['specification'])) ) {
                        $data=array();
                        $data['model_number']=$_GET['model_number'];
                        $data['specification']=$_GET['specification'];
                        $data['product_name']=$_GET['product_name'];
                        $result = self::insert_curtain_products($data);
                        $output .= $result.'<br>';
                    }

                    if( ($_GET['action']=='update-curtain-product') && (isset($_GET['curtain_product_id'])) ) {
                        $data=array();
                        if( isset($_GET['model_number']) ) {
                            $data['model_number']=$_GET['model_number'];
                        }
                        if( isset($_GET['specification']) ) {
                            $data['specification']=$_GET['specification'];
                        }
                        if( isset($_GET['product_name']) ) {
                            $data['product_name']=$_GET['product_name'];
                        }
                        $where=array();
                        $where['curtain_product_id']=$_GET['curtain_product_id'];
                        $result = self::update_curtain_products($data, $where);
                        $output .= $result.'<br>';
                    }

                    if( ($_GET['action']=='insert-serial-number') && (isset($_GET['curtain_product_id'])) ) {
                        $data=array();
                        $data['curtain_product_id']=$_GET['curtain_product_id'];
                        $result = self::insert_serial_number($data);
                        $output .= $result.'<br>';
                    }

                    if( ($_GET['action']=='update-serial-number') && (isset($_GET['serial_number_id'])) ) {
                        $data=array();
                        if( isset($_GET['curtain_product_id']) ) {
                            $data['curtain_product_id']=$_GET['curtain_product_id'];
                        }
                        if( isset($_GET['curtain_user_id']) ) {
                            $data['curtain_user_id']=$_GET['curtain_user_id'];
                        }
                        $where=array();
                        $where['serial_number_id']=$_GET['serial_number_id'];
                        $result = self::update_serial_number($data, $where);
                        $output .= $result.'<br>';
                    }

                    if( ($_GET['action']=='insert-curtain-user') && (isset($_GET['line_user_id'])) && (isset($_GET['display_name'])) ) {
                        $data=array();
                        $data['line_user_id']=$_GET['line_user_id'];
                        $data['display_name']=$_GET['display_name'];
                        $result = self::insert_curtain_users($data);
                        $output .= $result.'<br>';
                    }
                }
            }
            $output .= '</div>';
            return $output;
        }

        function list_curtain_products() {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_products", OBJECT );
            $output  = '<h2>Curtain Products</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>id</td>';
            $output .= '<td>model</td>';
            $output .= '<td>spec</td>';
            $output .= '<td>product_name</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_product_id.'</td>';
                $output .= '<td>'.$result->model_number.'</td>';
                $output .= '<td>'.$result->specification.'</td>';
                $output .= '<td>'.$result->product_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
            return $output;
        }

        function list_serial_number() {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number", OBJECT );
            $output  = '<h2>Serial Number</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>QR</td>';
            $output .= '<td>serial_no</td>';
            $output .= '<td>model</td>';
            $output .= '<td>spec</td>';
            $output .= '<td>user</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td><form method="post">';
                $output .= '<input type="submit" value="Code" name="submit_action">';
                $output .= '<input type="hidden" value="'.$result->qr_code_serial_no.'" name="serial_no">';
                //$output .= '<a href="#" rel="INSERT_POST_ID" class="popup">here</a>';
                $output .= '</form></td>';
                $output .= '<td>'.$result->qr_code_serial_no.'</td>';
                $product = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = {$result->curtain_product_id}", OBJECT );
                $output .= '<td>'.$product->model_number.'</td>';
                $output .= '<td>'.$product->specification.'</td>';
                $user = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = {$result->curtain_user_id}", OBJECT );
                $output .= '<td>'.$user->display_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
            return $output;
        }

        function list_curtain_users() {
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users", OBJECT );
            $output  = '<h2>Curtain Users</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr style="background-color:yellow">';
            $output .= '<td>id</td>';
            $output .= '<td>line_user_id</td>';
            $output .= '<td>display_name</td>';
            $output .= '<td>update_time</td>';
            $output .= '</tr>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_user_id.'</td>';
                $output .= '<td>'.$result->line_user_id.'</td>';
                $output .= '<td>'.$result->display_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
            return $output;
        }

        function insert_curtain_products($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_products';
            $data = array(
                'model_number' => $data['model_number'],
                'specification' => $data['specification'],
                'product_name' => $data['product_name'],
                'create_timestamp' => time(),
                'update_timestamp' => time(),
            );
            $wpdb->insert($table, $data);        
            return $wpdb->insert_id;
        }

        function update_curtain_products($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_products';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        function insert_serial_number($data=[]) {
            global $wpdb;
            $curtain_product_id = intval($data['curtain_product_id']);
            $product = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = {$curtain_product_id}", OBJECT );
            if (count($product) > 0) {
                $qr_code_serial_no = $product->model_number . $product->specification . time();
                $table = $wpdb->prefix.'serial_number';
                $data = array(
                    'qr_code_serial_no' => $qr_code_serial_no,
                    'curtain_product_id' => $data['curtain_product_id'],
                    'curtain_user_id' => $data['curtain_user_id'],
                    'create_timestamp' => time(),
                    'update_timestamp' => time(),
                );
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            }
        }

        function update_serial_number($data=[], $where=[]) {
            global $wpdb;
            $curtain_product_id = intval($data['curtain_product_id']);
            $product = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_products WHERE curtain_product_id = {$curtain_product_id}", OBJECT );
            if (count($product) > 0) {
                $qr_code_serial_no = $product->model_number . $product->specification . time();
                $data['qr_code_serial_no'] = $qr_code_serial_no;
            }
            $data['update_timestamp'] = time();
            $table = $wpdb->prefix.'serial_number';
            $wpdb->update($table, $data, $where);
        }

        public function insert_curtain_users($data=[]) {
            global $wpdb;
            $line_user_id = $data['line_user_id'];
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = {$line_user_id}", OBJECT );
            if (count($row) > 0) {
                return $row->curtain_user_id;
            } else {
                $table = $wpdb->prefix.'curtain_users';
                $data = array(
                    'line_user_id' => $data['line_user_id'],
                    'display_name' => $data['display_name'],
                    'last_otp' => $data['last_otp'],
                    'create_timestamp' => time(),
                    'update_timestamp' => time(),
                );
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            }
        }

        function delete_records() {

            global $wpdb;
            $table = $wpdb->prefix.'eventLogs';
            $where = array('event_timestamp' => 2147483647);
            $deleted = $wpdb->delete( $table, $where );
        }

        function create_tables() {

            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}serial_number` (
                serial_number_id int NOT NULL AUTO_INCREMENT,
                curtain_product_id int(10),
                curtain_user_id int(10),
                qr_code_serial_no varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (serial_number_id)
            ) $charset_collate;";
            dbDelta($sql);
            
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_products` (
                curtain_product_id int NOT NULL AUTO_INCREMENT,
                model_number varchar(5),
                specification varchar(5),
                product_name varchar(50),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_product_id)
            ) $charset_collate;";
            dbDelta($sql);
            
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_users` (
                curtain_user_id int NOT NULL AUTO_INCREMENT,
                line_user_id varchar(50),
                display_name varchar(50),
                last_otp varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_user_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }

    new otp_service();

}
?>



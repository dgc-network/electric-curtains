<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('curtain_users')) {

    class curtain_users {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('curtain-user-list', __CLASS__ . '::list_curtain_users');
            self::create_tables();
        }

        function list_curtain_users() {
/*            
            if( isset($_POST['_mode']) || isset($_POST['_id']) ) {
                return self::edit_curtain_user($_POST['_id'], $_POST['_mode']);
            }

            if( ($_GET['action']=='insert-curtain-user') && (isset($_GET['line_user_id'])) ) {
                $data=array();
                $data['line_user_id']=$_GET['line_user_id'];
                $data['display_name']=$_GET['display_name'];
                $data['mobile_phone']=$_GET['mobile_phone'];
                $data['user_role']=$_GET['user_role'];
                $result = self::insert_curtain_user($data);
                $output .= $result.'<br>';
            }
                        
            if( isset($_POST['_create_user']) ) {
                $data=array();
                $data['line_user_id']=$_POST['_line_user_id'];
                $data['display_name']=$_POST['_display_name'];
                $data['mobile_phone']=$_POST['_mobile_phone'];
                $data['user_role']=$_POST['_user_role'];
                $result = self::insert_curtain_user($data);
            }
*/        
            if( isset($_POST['_update']) ) {
                $data=array();
                $data['display_name']=$_POST['_display_name'];
                $data['mobile_phone']=$_POST['_mobile_phone'];
                $data['user_role']=$_POST['_user_role'];
                $where=array();
                $where['curtain_user_id']=$_POST['_curtain_user_id'];
                $result = self::update_curtain_users($data, $where);
            }
        
            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE display_name LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users", OBJECT );
            }
            $output  = '<h2>Curtain Users</h2>';
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div class="ui-widget">';
            $output .= '<table id="users" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th>id</th>';
            $output .= '<th>line_user_id</th>';
            $output .= '<th>name</th>';
            $output .= '<th>mobile</th>';
            $output .= '<th>update_time</th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                $output .= '<td>'.$result->curtain_user_id.'</a></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_user_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->line_user_id.'" name="_update_user">';
                $output .= '</form></td>';
                $output .= '<td><form method="post">';
                $output .= '<input type="hidden" value="'.$result->curtain_user_id.'" name="_id">';
                $output .= '<input type="submit" value="'.$result->display_name.'" name="_chat_user">';
                $output .= '</form></td>';
                //$output .= '<td>'.$result->display_name.'</td>';
                $output .= '<td>'.$result->mobile_phone.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';

            if( isset($_POST['_update_user']) && isset($_POST['_id']) ) {
                $_id = $_POST['_id'];
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id={$_id}", OBJECT );
                if (count($row) > 0) {
                    $output .= '<div id="dialog" title="Curtain user update">';
                    $output .= '<form method="post">';
                    $output .= '<fieldset>';
                    $output .= '<input type="hidden" value="'.$row->curtain_user_id.'" name="_curtain_user_id">';
                    $output .= '<label for="_display_name">Display Name</label>';
                    $output .= '<input type="text" name="_display_name" id="display_name" class="text ui-widget-content ui-corner-all" value="'.$row->display_name.'">';
                    $output .= '<label for="_mobile_phone">Mobile Phone</label>';
                    $output .= '<input type="text" name="_mobile_phone" id="mobile_phone" class="text ui-widget-content ui-corner-all" value="'.$row->mobile_phone.'">';
                    $output .= '<label for="_user_role">User Role</label>';
                    $output .= '<input type="text" name="_user_role" id="user_role" class="text ui-widget-content ui-corner-all" value="'.$row->user_role.'">';
                    $output .= '</fieldset>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update">';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="_delete">';
                    $output .= '</form>';
                    $output .= '</div>';

                }
            }

            if( isset($_POST['_chat_user']) && isset($_POST['_id']) ) {
                $_id = $_POST['_id'];
                global $wpdb;
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id={$_id}", OBJECT );
                if (count($row) > 0) {
                    $output .= '<div id="dialog" title="Chat to '.$row->display_name.'">';
                    $output .= '<div class="chatboxcontent"></div>';
                    $output .= '<div class="chatboxinput"><textarea class="chatboxtextarea"></textarea></div>';
                    $output .= '</div>';
                }
            }

            return $output;
        }

        public function insert_curtain_user($data=[]) {
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
                    'user_role' => $data['user_role'],
                    'create_timestamp' => time(),
                    'update_timestamp' => time(),
                );
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            }
        }

        function update_curtain_users($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'curtain_users';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}curtain_users` (
                curtain_user_id int NOT NULL AUTO_INCREMENT,
                line_user_id varchar(50),
                display_name varchar(50),
                mobile_phone varchar(20),
                user_role varchar(20),
                last_otp varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                UNIQUE (line_user_id),
                PRIMARY KEY (curtain_user_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    new curtain_users();
}
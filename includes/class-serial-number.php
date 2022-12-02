<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('serial_number')) {
    class serial_number {
        /**
         * Class constructor
         */
        public function __construct() {
            self::create_tables();
        }

        function list_serial_number() {

            global $wpdb;
            if( isset($_SESSION['username']) ) {
                $option = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}service_options WHERE service_option_page = %s", '_serials_page' ), OBJECT );
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $_SESSION['username'] ), OBJECT );
                $permission = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_permissions WHERE curtain_user_id = %d AND service_option_id= %d", $user->curtain_user_id, $option->service_option_id ), OBJECT );            
                if (is_null($permission) || !empty($wpdb->last_error)) {
                    if ( $_GET['_check_permission'] != 'false' ) {
                        return 'You have not permission to access this page. Please check to the administrators.';
                    }
                }
            } else {
                if ( $_GET['_check_permission'] != 'false' ) {
                    return 'You have not permission to access this page. Please check to the administrators.';
                }
            }

            if( isset($_POST['_create']) ) {
                $data=array();
                $data['curtain_model_id']=$_POST['_curtain_model_id'];
                $data['specification']=$_POST['_specification'];
                $data['curtain_agent_id']=$_POST['_curtain_agent_id'];
                $result = self::insert_serial_number($data);
            }

            global $wpdb;
            if( isset($_POST['_where']) ) {
                $where='"%'.$_POST['_where'].'%"';
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no LIKE {$where}", OBJECT );
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}serial_number", OBJECT );
            }
            $output  = '<h2>Serial Number</h2>';
            $output .= '<div style="text-align: right">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input style="display:inline" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div class="ui-widget">';
            $output .= '<table id="users" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th>serial_no</th>';
            $output .= '<th>model</th>';
            $output .= '<th>spec</th>';
            $output .= '<th>agent</th>';
            $output .= '<th>user</th>';
            $output .= '<th>update_time</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
/*                
                $output .= '<td>'.$result->serial_number_id.'</td>';
                $output .= '<td style="display: flex;"><form method="post">';
                $output .= '<input type="submit" value="'.$result->qr_code_serial_no.'" name="_serial_no">';
                $output .= '</form>';
                $output .= '</td>';
*/                
                $output .= '<td>'.$result->qr_code_serial_no.'</td>';
                $model = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = %d", $result->curtain_model_id ), OBJECT );            
                $output .= '<td>'.$model->curtain_model_name.'</td>';
                $output .= '<td>'.$result->specification.'</td>';
                $agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $result->curtain_agent_id ), OBJECT );            
                $output .= '<td>'.$agent->agent_name.'</td>';
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE curtain_user_id = %d", $result->curtain_user_id ), OBJECT );            
                $output .= '<td>'.$user->display_name.'</td>';
                $output .= '<td>'.wp_date( get_option('date_format'), $result->update_timestamp ).' '.wp_date( get_option('time_format'), $result->update_timestamp ).'</td>';
                $output .= '<td style="text-align: center;">';
                $output .= '<span id="edit-btn-'.$result->serial_number_id.'"><i class="fa-regular fa-qrcode"></i></span>';
                $output .= '<span>  </span>';
                $output .= '<span id="del-btn-'.$result->serial_number_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                $output .= '</td>';
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            $output .= '<form method="post">';
            $output .= '<input id="create-model" class="wp-block-button__link" type="submit" value="Create" name="_add">';
            $output .= '</form>';
            echo do_shortcode('[print-me target="body"/]');

            if( isset($_POST['_add']) ) {
                $curtain_models = new curtain_models();
                $curtain_agents = new curtain_agents();
                $output .= '<div id="dialog" title="Create new serial_no">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="curtain_model_id">Model</label>';                    
                $output .= '<select name="_curtain_model_id" id="curtain_model_id">'.$curtain_models->select_options().'</select>';
                $output .= '<label for="specification">Specification</label>';
                $output .= '<input type="text" name="_specification" id="specification" class="text ui-widget-content ui-corner-all">';
                $output .= '<label for="curtain_agent_id">Agent</label>';
                $output .= '<select name="_curtain_agent_id" id="curtain_agent_id">'.$curtain_agents->select_options().'</select>';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $output .= '<div id="dialog" title="QR Code">';
                $output .= '<div id="qrcode">';
                $output .= '<div id="qrcode_content">';
                $output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $print_me = do_shortcode('[print-me target=".print-me-'.$_id.'"/]');
                $output .= $print_me;
                $output .= '</div>';
                $output .= '<br><br><br><br><br>';
                
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $_id ), OBJECT );            
                $output .= '<div class="print-me-'.$_id.'">';
                //$output .= '<div id="qrcode1" style="display: inline-block; margin-left: 100px;">';
                $output .= '<div id="qrcode1">';
                $output .= '<div id="qrcode_content">';
                $output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<p><h1 style="margin-left: 25px;">'.wp_date( get_option('date_format'), $row->create_timestamp ).'</h1></p><br><br><br>';
                //$output .= '<div id="qrcode2" style="display: inline-block;; margin-left: 200px;">';
                $output .= '<div id="qrcode2" style="margin-top: 100px;">';
                $output .= '<div id="qrcode_content">';
                $output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_id;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<p><h1 style="margin-left: 25px;">'.wp_date( get_option('date_format'), $row->create_timestamp ).'</h1></p>';
                $output .= '</div>';                
            }
/*
            if( isset($_POST['_serial_no']) ) {
                
                $output .= '<div id="dialog" title="QR Code">';
                $output .= '<div id="qrcode">';
                $output .= '<div id="qrcode_content">';
                $output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_POST['_serial_no'];
                $output .= '</div>';
                $output .= '</div>';
                $print_me = do_shortcode('[print-me target=".print-me-'.$_POST['_serial_no'].'"/]');
                $output .= $print_me;
                $output .= '</div>';
                $output .= '<br><br><br><br><br>';
                
                $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}serial_number WHERE qr_code_serial_no = %s", $_POST['_serial_no'] ), OBJECT );            
                $output .= '<div class="print-me-'.$_POST['_serial_no'].'">';
                //$output .= '<div id="qrcode1" style="display: inline-block; margin-left: 100px;">';
                $output .= '<div id="qrcode1">';
                $output .= '<div id="qrcode_content">';
                $output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_POST['_serial_no'];
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<p><h1 style="margin-left: 25px;">'.wp_date( get_option('date_format'), $row->create_timestamp ).'</h1></p><br><br><br>';
                //$output .= '<div id="qrcode2" style="display: inline-block;; margin-left: 200px;">';
                $output .= '<div id="qrcode2" style="margin-top: 100px;">';
                $output .= '<div id="qrcode_content">';
                $output .= get_site_url().'/'.get_option('_service_page').'/?serial_no='.$_POST['_serial_no'];
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<p><h1 style="margin-left: 25px;">'.wp_date( get_option('date_format'), $row->create_timestamp ).'</h1></p>';
                $output .= '</div>';                
            }
*/            
            return $output;
        }

        function insert_serial_number($data=[], $_x='') {
            global $wpdb;
            $curtain_model_id = $data['curtain_model_id'];
            $model = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_model_id = {$curtain_model_id}", OBJECT );
            if (!(is_null($model) || !empty($wpdb->last_error))) {
                //$qr_code_serial_no = $model->curtain_model_name . $data['specification'] . time();
                //$qr_code_serial_no = $model->curtain_model_name . $data['specification'] . time() . $data['x'];
                $qr_code_serial_no = $model->curtain_model_name . $data['specification'] . time() . $_x;
                $data['qr_code_serial_no'] = $qr_code_serial_no;
                $data['create_timestamp'] = time();
                $data['update_timestamp'] = time();
                $table = $wpdb->prefix.'serial_number';
                $wpdb->insert($table, $data);
                return $wpdb->insert_id;
            }
        }

        public function update_serial_number($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'serial_number';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_serial_number($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'serial_number';
            $wpdb->delete($table, $where);
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}serial_number` (
                serial_number_id int NOT NULL AUTO_INCREMENT,
                qr_code_serial_no varchar(50) UNIQUE,
                curtain_model_id int(10),
                specification varchar(10),
                curtain_agent_id int(10),
                curtain_user_id int(10),
                one_time_password int(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (serial_number_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
    }
    $serial_number = new serial_number();
    add_shortcode( 'serial-number-list', array( $serial_number, 'list_serial_number' ) );
}
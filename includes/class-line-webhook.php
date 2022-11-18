<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('line_webhook')) {
    class line_webhook {
        /**
         * Class constructor
         */
        public function __construct() {
            self::create_tables();
        }

        function line_rich_menu( $rich_menu_content='' ) {
            $client = new LINEBotTiny();
            $client->richMenu($rich_menu_content);
        }

        function line_push_message( $line_user_id='', $rich_message='' ) {
            $client = new LINEBotTiny();
            $client->pushMessage([
                'to' => $line_user_id,
                'messages' => $rich_message
            ]);
        }

        function forward_text_message( $flex_contents=array() ) {
            $client = new LINEBotTiny();
            $client->pushMessage([
                'to' => $flex_contents['line_user_id'],
                'messages' => [
                    [
                        "type" => "flex",
                        "altText" => "this is a flex message",
                        "contents" => [
                            "type" => "bubble",
                            "hero" => [
                                "type" => "box",
                                "layout" => "horizontal",
                                "contents" => [
/*                                    [
                                        "type" => "separator",
                                        "margin" => "15px"
                                    ], */
                                    [
                                        "type" => "text",
                                        "text" => $flex_contents['forward_title'],
                                        "margin" => "20px",
                                        "action" => [
                                            "type" => "uri",
                                            "label" => "action",
                                            "uri" => $flex_contents['forward_to_uri']
                                        ]
                                    ]
                                ],
                                "backgroundColor" => "#00b900"
                            ],
                            "body" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => $flex_contents['message'],
                                        "wrap" => true
                                    ]
                                ]
/*                            ],
                            "styles" => [
                                "hero" => [
                                    "backgroundColor" => "#00b900"
                                ] */
                            ]
                        ]    
                    ]
                ]
            ]);
        }

        function reply_text_messages( $flex_contents=array() ) {
            $hero_contents = array();
            $body_contents = array();
            foreach ( $flex_contents['messages'] as $message ) {
                $body_content = array();
                $body_content['type'] = 'text';
                $body_content['text'] = $message;
                $body_content['wrap'] = true;
                $body_content['action']['type'] = 'uri';
                $body_content['action']['label'] = 'action';
                $body_content['action']['uri'] = $flex_contents['forward_to_uri'];
                $body_contents[] = $body_content;
            }

            $client = new LINEBotTiny();
            $client->pushMessage([
                'to' => $flex_contents['line_user_id'],
                'messages' => [
                    [
                        "type" => "flex",
                        "altText" => "this is a flex message",
                        "contents" => [
                            "type" => "bubble",
                            "hero" => [
                                "type" => "box",
                                "layout" => "horizontal",
                                "contents" => $hero_contents
                            ],
                            "body" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => $body_contents
                            ]
                        ]    
                    ]
                ]
            ]);
        }

        function push_text_message( $line_user_id='', $text_message='' ) {
            $client = new LINEBotTiny();
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

        public function init() {
            $serial_number = new serial_number();
            $curtain_users = new curtain_users();
            $client = new LINEBotTiny();
            foreach ((array)$client->parseEvents() as $event) {
                //self::insert_event_log($event);

                $profile = $client->getProfile($event['source']['userId']);
                $line_user_id = $profile['userId'];
                $display_name = $profile['displayName'];
            
                global $wpdb;
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $line_user_id ), OBJECT );            
                if (is_null($row) || !empty($wpdb->last_error)) {
                    $data=array();
                    $data['line_user_id']=$profile['userId'];
                    $data['display_name']=$profile['displayName'];
                    $return_id = $curtain_users->insert_curtain_user($data);
                }

                switch ($event['type']) {
                    case 'message':
                        $message = $event['message'];
                        switch ($message['type']) {
                            case 'text':
                                $six_digit_random_number = $message['text'];
                                if( strlen( $six_digit_random_number ) == 6 ) {
                                    global $wpdb;
                                    $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}serial_number WHERE one_time_password = {$six_digit_random_number}", OBJECT );
                                    if (!(is_null($row) || !empty($wpdb->last_error))) {
                                        // continue the process if the 6 digit number is correct, register the qr code
                                        $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $line_user_id ), OBJECT );            
                                        if (!(is_null($user) || !empty($wpdb->last_error))) {
                                            $data=array();
                                            $data['curtain_user_id']=$user->curtain_user_id;
                                            $where=array();
                                            $where['one_time_password']=$six_digit_random_number;
                                            $result = $serial_number->update_serial_number($data, $where);
    
                                            $messages = array();
                                            $messages[] = 'Hi, '.$profile['displayName'];
                                            $messages[] = 'QR Code 已經完成註冊';
                                            $messages[] = '請點擊連結進入售後服務區:';
                                            $flex_contents = array();
                                            $flex_contents['line_user_id'] = $line_user_id;
                                            $flex_contents['forward_to_uri'] = get_site_url().'/'.get_option('_service_page');
                                            $flex_contents['messages'] = $messages;
                                            self::reply_text_messages( $flex_contents );
                                        }
                                    } else {
                                        // continue the process if the 6 digit number is incorrect
                                        $messages = array();
                                        $messages[] = 'Hi, '.$profile['displayName'];
                                        $messages[] = '您輸入的六位數字'.$message['text'].'有錯誤';
                                        $messages[] = '請重新輸入正確數字已完成 QR Code 註冊';
                                        $flex_contents = array();
                                        $flex_contents['line_user_id'] = $line_user_id;
                                        $flex_contents['forward_to_uri'] = get_site_url().'/'.get_option('_service_page').'/?serial_no=';
                                        $flex_contents['messages'] = $messages;
                                        self::reply_text_messages( $flex_contents );
                                    }
                                } else {
                                    //send message to line_bot if the message is not six digit 
                                    $data=array();
                                    $data['chat_from']=$line_user_id;
                                    $data['chat_to']='line_bot';
                                    $data['chat_message']=$message['text'];
                                    $result = self::insert_chat_message($data);
                                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE user_role = 'admin'", OBJECT );
                                    foreach ( $results as $index=>$result ) {
                                        $flex_contents = array();
                                        $flex_contents['line_user_id'] = $result->line_user_id;
                                        $flex_contents['forward_title'] = $display_name;
                                        $flex_contents['forward_to_uri'] = get_site_url().'/'.get_option('_service_page');
                                        $flex_contents['message'] = $message['text'];
                                        self::forward_text_message( $flex_contents );
                                    }
                                }
                                break;
                            default:
                                //send notification to administrators
                                error_log('Unsupported message type: ' . $message['type']);
                                break;
                        }
                        break;
                    default:
                        error_log('Unsupported event type: ' . $event['type']);
                        break;
                }    
            }            
        }

        function insert_event_log($event) {

            switch ($event['type']) {
                case 'message':
                    $event_object = $event['message'];
                    break;
                case 'unsend':
                    $event_object = $event['unsend'];
                    break;
                case 'memberJoined':
                    $event_object = $event['joined'];
                    break;
                case 'memberLeft':
                    $event_object = $event['left'];
                    break;
                case 'postback':
                    $event_object = $event['postback'];
                    break;
                case 'videoPlayComplete':
                    $event_object = $event['videoPlayComplete'];
                    break;
                case 'beacon':
                    $event_object = $event['beacon'];
                    break;
                case 'accountLink':
                    $event_object = $event['link'];
                    break;
                case 'things':
                    $event_object = $event['things'];
                    break;
            }

            switch ($event['source']['type']) {
                case 'user':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    $group_id = $event['source']['userId'];
                    break;
                case 'group':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    $group_id = $event['source']['groupId'];
                    break;
                case 'room':
                    $source_type = $event['source']['type'];
                    $user_id = $event['source']['userId'];
                    $group_id = $event['source']['roomId'];
                    break;
            }

            global $wpdb;
            $table = $wpdb->prefix.'eventLogs';
            $data = array(
                'event_type' => $event['type'],
                'event_timestamp' => time(),
                'source_type' => $source_type,
                'source_user_id' => $user_id,
                'source_group_id' => $group_id,
                'event_replyToken' => $event['replyToken'],
                'event_object' => json_encode($event_object),
            );
            $insert_id = $wpdb->insert($table, $data);        
        }
    
        public function insert_chat_message($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'chat_messages';
            $data['create_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "CREATE TABLE {$wpdb->prefix}chat_messages (
                message_id int NOT NULL AUTO_INCREMENT,
                chat_from varchar(255) NOT NULL DEFAULT '',
                chat_to varchar(255) NOT NULL DEFAULT '',
                chat_message TEXT NOT NULL,
                create_timestamp int(10),
                PRIMARY KEY (message_id)
            ) $charset_collate;";
            dbDelta($sql);
        
            $sql = "CREATE TABLE `{$wpdb->prefix}eventLogs` (
                event_id int NOT NULL AUTO_INCREMENT,
                event_type varchar(20),
                event_timestamp int(10),
                source_type varchar(10),
                source_user_id varchar(50),
                source_group_id varchar(50),
                event_replyToken varchar(50),
                event_object varchar(1000),
                PRIMARY KEY  (event_id)
            ) $charset_collate;";
            dbDelta($sql);
        }        
    }
}
?>
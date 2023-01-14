<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('curtain_orders')) {
    class curtain_orders {
        private $_wp_page_title;
        private $_wp_page_postid;
        /**
         * Class constructor
         */
        public function __construct() {
            $this->_wp_page_title = 'Orders';
            $this->_wp_page_postid = get_page_by_title($this->_wp_page_title)->ID;
            $wp_pages = new wp_pages();
            $wp_pages->create_page($this->_wp_page_title, '[shopping-item-list]', 'system');
            add_action( 'wp_ajax_select_order_status', array( $this, 'select_order_status' ) );
            add_action( 'wp_ajax_nopriv_select_order_status', array( $this, 'select_order_status' ) );
            add_action( 'wp_ajax_select_category_id', array( $this, 'select_category_id' ) );
            add_action( 'wp_ajax_nopriv_select_category_id', array( $this, 'select_category_id' ) );
            add_shortcode( 'shopping-item-list', array( $this, 'list_shopping_items' ) );
            $this->create_tables();
        }

        public function order_status_notice($customer_order_number, $customer_order_status) {
            global $wpdb;
            $system_status = new system_status();
            $curtain_service = new curtain_service();
            $json_templates = new json_templates();
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE is_admin = %d", 1 ), OBJECT );
            foreach ( $results as $index=>$result ) {

                $see_more = '    {
                    "type": "bubble",
                    "body": {
                      "type": "box",
                      "layout": "vertical",
                      "spacing": "sm",
                      "contents": [
                        {
                          "type": "button",
                          "flex": 1,
                          "gravity": "center",
                          "action": {
                            "type": "uri",
                            "label": "See more",
                            "uri": "https://linecorp.com"
                          }
                        }
                      ]
                    }
                  }
                ';


                $contents = array(
                    "type"=>  "carousel",
                    "contents"=> array(
                        0 => array(
                            "type"=> "bubble",
                            "body"=> array(
                                "type"=> "box",
                                "layout"=> "vertical",
                                "contents"=> array(

                                    0=> array(
                                        "type"=> "image",
                                        //"url"=> "https://scdn.line-apps.com/n/channel_devcenter/img/flexsnapshot/clip/clip1.jpg",
                                        "url"=> "https://lh3.googleusercontent.com/m3y0WMGmo6HbkBO_GrgNyVE1jgkQwu1r5qdWV1hoq5dMy8S82j7TLW6CPQlx83xYX0AFzW1A6cIkrV7AL8iyUDaBC-OrFMXjSdScM5HnF4Jxs8NA5IYBIjcNGC2N8GnmnY9nTD-XhRimNbwjrWdtZlBwHzAx8IvuYhWhllglb3ato0JqNT-lMd6Am8N1fcHlNQw7qJp0hIteqmkryy-PzZ9jDV5_hMH51Ck5I-u5v_cqFryEPi6glCpWek7REHOYBoKnaIH7KfJ5zrwy2otAlLuL9pbkL73nDW2O1cIPniUoy_Fzq3i1ve4LW4xWsz0DL_uQuZ5hm2UqyQM-RrvTJSlkPmuBGxuqRiPG99zihgFav6Oo6osO9oASXUU85WjNOX9B4PjeLLh6KFfAaxED3KpnfNge52fd1sQbefIcZo7qORrTTi7Ng1Cloly_9xm31y1dIo9oVYLUoO7iA3G7s1vrtmSmWF6SLF7KcKnflW6NvgdY5iNp9JKMvt2rpGUGzl6d_mmB3xQrUtWsvdz8ml4x3RzkwvaIhdUtTBcYm3RhfF1M-rOAEdLKy3JpUBkBDMFZJPB8Q46T9e0Uv5e_6bao_sxK-PGI1MRUw3UejLTVHFQS38sSqDjVeULazPLIVXfSzupSppQFH4qgqfUBbbpo1-8X1VAmIsMkSAj6oGo2XrPLg7hatQlbMh8X2hKR9BTz2vqh3nj8wqV0RAqseYtrXfb8xpKlu4lNWvNBpcTFQwEwDfNTaMLgR96MjXfdqZ6RuvrnGK8I0aWGxeC8jUsdTsw0lPAq4HVQkFoH2DtuBWPsETqm-tYaFqb93zL4-ofwyAWBnduClxbI_zvuJnhEEIoECtkuvnoWTcKfZOmyyP9aHxjOTqKAZkZdVDYvKw1sX1A2KfytsRi05attA_jdKJ3POAIXJwvvr6X2Dk82dg=w1064-h1418-no?authuser=0",
                                        "size"=> "full",
                                        "aspectMode"=> "cover",
                                        "aspectRatio"=> "2:3",
                                        "gravity"=> "top"
                                    ),
/*                                    
                                    1=> array(
                                        "type"=> "box",
                                        "layout"=> "vertical",
                                        "contents"=> array(
                                            0=> array(
                                                "type"=> "box",
                                                "layout"=> "vertical",
                                                "contents"=> array(
                                                    0=> array(
                                                        "type"=> "text",
                                                        //"text"=> "Brown's T-shirts",
                                                        "text"=> "System Notification",
                                                        "size"=> "xl",
                                                        "color"=> "#ffffff",
                                                        "weight"=> "bold"
                                                    )
                                                )
                                            ),
                                            1=> array(
                                                "type"=> "box",
                                                "layout"=> "baseline",
                                                "contents"=> array(
                                                    0=> array(
                                                        "type"=> "text",
                                                        //"text"=> "¥35,800",
                                                        "text"=> "Order No.",
                                                        "color"=> "#ebebeb",
                                                        "size"=> "sm",
                                                        "flex"=> 0
                                                    ),
                                                    1=> array(
                                                        "type"=> "text",
                                                        //"text"=> "¥75,000",
                                                        "text"=> $customer_order_number,
                                                        "color"=> "#ffffffcc",
                                                        //"decoration"=> "line-through",
                                                        "gravity"=> "bottom",
                                                        "flex"=> 0,
                                                        "size"=> "sm",
                                                        "spacing"=> "lg"
                                                    ),
                                                    2=> array(
                                                        "type"=> "box",
                                                        "layout"=> "vertical",
                                                        "contents"=> array(
                                                            0=> array(
                                                                "type"=> "filler"
                                                            ),
                                                            1=> array(
                                                                "type"=> "box",
                                                                "layout"=> "baseline",
                                                                "contents"=> array(
                                                                    0=> array(
                                                                        "type"=> "filler"
                                                                    ),
                                                                    1=> array(
                                                                        "type"=> "icon",
                                                                        "url"=> "https://scdn.line-apps.com/n/channel_devcenter/img/flexsnapshot/clip/clip14.png"
                                                                    ),
                                                                    2=> array(
                                                                        "type"=> "text",
                                                                        //"text"=> "Add to cart",
                                                                        "text"=> "Go to order",
                                                                        "color"=> "#ffffff",
                                                                        "flex"=> 0,
                                                                        "offsetTop"=> "-2px"
                                                                    ),
                                                                    3=> array(
                                                                        "type"=> "filler"
                                                                    )
                                                                ),
                                                                "spacing"=> "sm"
                                                            ),
                                                            2=> array(
                                                                "type"=> "filler"
                                                            )
                                                        ),
                                                        "borderWidth"=> "1px",
                                                        "cornerRadius"=> "4px",
                                                        "spacing"=> "sm",
                                                        "borderColor"=> "#ffffff",
                                                        "margin"=> "xxl",
                                                        "height"=> "40px"
                                                    )
                                                )
                                            ),
                                            "position"=> "absolute",
                                            "offsetBottom"=> "0px",
                                            "offsetStart"=> "0px",
                                            "offsetEnd"=> "0px",
                                            "backgroundColor"=> "#03303Acc",
                                            "paddingAll"=> "20px",
                                            "paddingTop"=> "18px"
                                        )
                                    ),
                                    2=> array(
                                        "type"=> "box",
                                        "layout"=> "vertical",
                                        "contents"=> array(
                                            0=> array(
                                                "type"=> "text",
                                                "text"=> "SALE",
                                                "color"=> "#ffffff",
                                                "align"=> "center",
                                                "size"=> "xs",
                                                "offsetTop"=> "3px"
                                            )
                                        ),
                                        "position"=> "absolute",
                                        "cornerRadius"=> "20px",
                                        "offsetTop"=> "18px",
                                        "backgroundColor"=> "#ff334b",
                                        "offsetStart"=> "18px",
                                        "height"=> "25px",
                                        "width"=> "53px"
                                    )
*/                                    
                                ),
                                "paddingAll"=> "0px"                                
                            )
                        )
                    )
                );

                $template = $json_templates->get_json('Restaurant');

                $template = $json_templates->get_json('Apparel');
                $template = wp_unslash($template);
                $contents = json_decode($template, true);
                $contents["contents"][0]["body"]["contents"][0]["url"] = "https://lh3.googleusercontent.com/m3y0WMGmo6HbkBO_GrgNyVE1jgkQwu1r5qdWV1hoq5dMy8S82j7TLW6CPQlx83xYX0AFzW1A6cIkrV7AL8iyUDaBC-OrFMXjSdScM5HnF4Jxs8NA5IYBIjcNGC2N8GnmnY9nTD-XhRimNbwjrWdtZlBwHzAx8IvuYhWhllglb3ato0JqNT-lMd6Am8N1fcHlNQw7qJp0hIteqmkryy-PzZ9jDV5_hMH51Ck5I-u5v_cqFryEPi6glCpWek7REHOYBoKnaIH7KfJ5zrwy2otAlLuL9pbkL73nDW2O1cIPniUoy_Fzq3i1ve4LW4xWsz0DL_uQuZ5hm2UqyQM-RrvTJSlkPmuBGxuqRiPG99zihgFav6Oo6osO9oASXUU85WjNOX9B4PjeLLh6KFfAaxED3KpnfNge52fd1sQbefIcZo7qORrTTi7Ng1Cloly_9xm31y1dIo9oVYLUoO7iA3G7s1vrtmSmWF6SLF7KcKnflW6NvgdY5iNp9JKMvt2rpGUGzl6d_mmB3xQrUtWsvdz8ml4x3RzkwvaIhdUtTBcYm3RhfF1M-rOAEdLKy3JpUBkBDMFZJPB8Q46T9e0Uv5e_6bao_sxK-PGI1MRUw3UejLTVHFQS38sSqDjVeULazPLIVXfSzupSppQFH4qgqfUBbbpo1-8X1VAmIsMkSAj6oGo2XrPLg7hatQlbMh8X2hKR9BTz2vqh3nj8wqV0RAqseYtrXfb8xpKlu4lNWvNBpcTFQwEwDfNTaMLgR96MjXfdqZ6RuvrnGK8I0aWGxeC8jUsdTsw0lPAq4HVQkFoH2DtuBWPsETqm-tYaFqb93zL4-ofwyAWBnduClxbI_zvuJnhEEIoECtkuvnoWTcKfZOmyyP9aHxjOTqKAZkZdVDYvKw1sX1A2KfytsRi05attA_jdKJ3POAIXJwvvr6X2Dk82dg=w1064-h1418-no?authuser=0";
                return var_dump($contents["contents"][0]["body"]["contents"][1]);
                $contents["contents"][0]["body"]["contents"][1]["contents"][0]["contents"]["text"] = 'System Notification';
                $contents["contents"][1] = json_decode($see_more, true);

                $curtain_service->push_flex_messages(
                    array(
                        'line_user_id' => $result->line_user_id,
                        'alt_text' => 'Order Number: '.$customer_order_number,
                        'link_uri' => get_permalink(get_page_by_title('Orders')).'/?_print='.$customer_order_number,
                        'template' => $template,
                        'contents' => $contents
                    )
                );
/*
                $curtain_service->push_bubble_messages(
                    array(
                        'line_user_id' => $result->line_user_id,
                        'alt_text' => 'Order Number: '.$customer_order_number,
                        'link_uri' => get_permalink(get_page_by_title('Orders')).'/?_print='.$customer_order_number,
                        'template' => json_decode($template, true),
                        
                        'contents' => array(
                            'header' => 'System Notification',
                            'body' => array(
                                'Order Number: '.$customer_order_number,
                                'Order Status: '.$system_status->get_name($customer_order_status)
                            )
                        )                            

                        'contents' => array(
                            "hero" => array(
                                "type" => "image",
                                "url" => "https://scdn.line-apps.com/n/channel_devcenter/img/flexsnapshot/clip/clip10.jpg",
                                "size" => "full",
                                "aspectMode" => "cover",
                                "aspectRatio" => "320:213"                                        
                            ),
                        )
                    )
                );
*/                
/*
                $curtain_service->push_carousel_messages(
                    array(
                        'line_user_id' => $result->line_user_id,
                        'alt_text' => 'Order Number: '.$customer_order_number,
                        'link_uri' => get_permalink(get_page_by_title('Orders')).'/?_print='.$customer_order_number,
                        'contents' => array(
                            array(
                                "hero" => array(
                                    array(
                                        "type" => "image",
                                        "url" => "https://scdn.line-apps.com/n/channel_devcenter/img/flexsnapshot/clip/clip10.jpg",
                                        "size" => "full",
                                        "aspectMode" => "cover",
                                        "aspectRatio" => "320:213"                                        
                                    )
                                ),
                                'body' => array(
                                    'Order Number: '.$customer_order_number,
                                    'Order Status: '.$system_status->get_name($customer_order_status)
                                )    
                            ),
                            array(
                                'header' => 'System Notification',
                                'body' => array(
                                    'Order Number: '.$customer_order_number,
                                    'Order Status: '.$system_status->get_name($customer_order_status)
                                )    
                            ),    
                        )
                    )
                );
*/                
            }    
        }

        public function list_shopping_items() {
            global $wpdb;
            $curtain_users = new curtain_users();
            $curtain_agents = new curtain_agents();
            $curtain_categories = new curtain_categories();
            $curtain_models = new curtain_models();
            $curtain_remotes = new curtain_remotes();
            $curtain_specifications = new curtain_specifications();
            $serial_number = new serial_number();
            $curtain_service = new curtain_service();
            $wp_pages = new wp_pages();
            $system_status = new system_status();

            if( isset($_GET['_id']) ) {
                $_SESSION['line_user_id'] = $_GET['_id'];
            }

            $curtain_agent_id = 0;
            if( isset($_SESSION['line_user_id']) ) {
                $user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE line_user_id = %s", $_SESSION['line_user_id'] ), OBJECT );
                if (is_null($user->curtain_agent_id) || $user->curtain_agent_id==0 || !empty($wpdb->last_error)) {
                    $output = '<h2>You have to complete the agent registration first.</h2>';
                    $output .= '請利用電腦<i class="fa-solid fa-desktop"></i>上的Line, 在我們的官方帳號聊天室中輸入經銷商代碼,<br>';
                    $output .= '完成經銷商註冊程序<br>';
                    return $output;
                } else {
                    $curtain_agent_id = $user->curtain_agent_id;
                }
            }

            //* Print Customer Order */
            if( isset($_POST['_status_submit']) ) {
                $this->update_customer_orders(
                    array(
                        'customer_order_status'=>$_POST['_customer_order_status'],
                    ),
                    array(
                        'customer_order_number'=>$_POST['_customer_order_number'],
                    )
                );
                $this->order_status_notice($_POST['_customer_order_number'], $_POST['_customer_order_status']);
            }

            if( isset($_GET['_print']) ) {
                $_id = $_GET['_print'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}customer_orders WHERE customer_order_number={$_id}", OBJECT );
                $output  = '<div style="text-align:center;"><h2>Customer Orders</h2></div>';
                $output .= '<div class="ui-widget">';
                $output .= '<table id="order-header" class="ui-widget ui-widget-content">';
                $output .= '<tr>';
                $output .= '<td>Order Number:</td><td><span id="select-order-number">'.$row->customer_order_number.'</span></td>';
                $output .= '<td>Order Date:</td><td>'.wp_date( get_option('date_format'), $row->create_timestamp ).'</td>';
                $output .= '</tr>';
                $output .= '<tr>';
                $output .= '<td>Agent:</td><td>'.$curtain_agents->get_name($row->curtain_agent_id).'</td>';
                $output .= '<td>Status:</td>';
                if ($curtain_users->is_admin($_SESSION['line_user_id'])){
                    $output .= '<form method="post">';
                    $output .= '<input type="hidden" name="_customer_order_number" value="'.$row->customer_order_number.'">';
                    $output .= '<td>';
                    $output .= '<select name="_customer_order_status" id="select-order-status">'.$system_status->select_options($row->customer_order_status).'</select>';
                    //$output .= '<span id="btn-check"><i class="fa-solid fa-check"></i></span>';
                    $output .= '<input class="wp-block-button__link" type="submit" value="Submit" name="_status_submit">';
                    $output .= '</td>';
                    $output .= '</form>';
                } else {
                    $output .= '<td>'.$system_status->get_name($row->customer_order_status).'</td>';
                }
                $output .= '</tr>';
                $output .= '</table>';

                $output .= '<table id="orders" class="ui-widget ui-widget-content">';
                $output .= '<thead><tr class="ui-widget-header ">';
                $output .= '<th>#</th>';
                $output .= '<th>Category</th>';
                $output .= '<th>Model</th>';
                $output .= '<th>Specification</th>';
                $output .= '<th>Dimension</th>';
                $output .= '<th>QTY</th>';
                $output .= '<th>Amount</th>';
                $output .= '</tr></thead>';
                $output .= '<tbody>';
                $x=0;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE customer_order_number={$row->customer_order_number}", OBJECT );
                foreach ( $results as $index=>$result ) {
                    $output .= '<tr>';
                    $x=$x+1;
                    $output .= '<td style="text-align: center;">'.$x.'</td>';
                    $output .= '<td>'.$curtain_categories->get_name($result->curtain_category_id).'</td>';
                    $output .= '<td>'.$curtain_models->get_description($result->curtain_model_id);
                    $output .= '<br>'.$curtain_remotes->get_name($result->curtain_remote_id).'</td>';
                    $output .= '<td>'.$curtain_specifications->get_description($result->curtain_specification_id).'</td>';
                    $output .= '<td>Width:'.$result->curtain_width;
                    if ($result->curtain_category_id==1){
                        $output .= '</td>';
                    } else {
                        $output .= '<br>Height:'.$result->curtain_height.'</td>';
                    }
                    $output .= '<td style="text-align:center;">'.$result->order_item_qty.'</td>';
                    $output .= '<td style="text-align:center;">'.number_format_i18n($result->order_item_amount).'</td>';
                    $output .= '</tr>';
                }
                $output .= '<tr>';
                $output .= '<td style="text-align:right;" colspan="6">Sub Total: </td>';
                $output .= '<td style="text-align:center;">'.number_format_i18n($row->customer_order_amount).'</td>';
                $output .= '</tr>';
                $output .= '</tbody></table></div>';
                if ($curtain_users->is_admin($_SESSION['line_user_id'])){
                }
                return $output;
            }

            //* Customer Orders List */
            if( isset($_POST['_customer_orders']) ) {
                if ($curtain_agent_id==0) {return 'You have to register as the agent first!';}
                if ($curtain_users->is_admin($_SESSION['line_user_id'])){
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}customer_orders", OBJECT );
                } else {
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}customer_orders WHERE curtain_agent_id={$curtain_agent_id}", OBJECT );
                }
                $output  = '<h2>Customer Orders - '.$curtain_agents->get_name($curtain_agent_id).'</h2>';
                $output .= '<form method="post">';
                $output .= '<div class="ui-widget">';
                $output .= '<table id="orders" class="ui-widget ui-widget-content">';
                $output .= '<thead><tr class="ui-widget-header ">';
                $output .= '<th></th>';
                $output .= '<th>Date</th>';
                $output .= '<th>Order No.</th>';
                $output .= '<th>Agent</th>';
                $output .= '<th>Amount</th>';
                $output .= '<th>Status</th>';
                $output .= '<th></th>';
                $output .= '</tr></thead>';
                $output .= '<tbody>';
                foreach ( $results as $index=>$result ) {
                    $output .= '<tr>';
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span id="btn-print-'.$result->customer_order_number.'"><i class="fa-solid fa-print"></i></span>';
                    $output .= '</td>';
                    $output .= '<td>'.wp_date( get_option('date_format'), $result->create_timestamp ).'</td>';
                    $output .= '<td>'.$result->customer_order_number.'</td>';
                    $output .= '<td>'.$curtain_agents->get_name($result->curtain_agent_id).'</td>';
                    $output .= '<td style="text-align: center;">'.$result->customer_order_amount.'</td>';
                    $output .= '<td>'.$system_status->get_name($result->customer_order_status).'</td>';
                    //$output .= '<td></td>';
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span id="btn-print-'.$result->customer_order_number.'"><i class="fa-solid fa-print"></i></span>';
                    $output .= '</td>';
                    $output .= '</tr>';
                }
                $output .= '</tbody></table></div>';
                return $output;
            }

            //* Checkout */
            if( isset($_POST['_checkout_submit']) ) {
                $customer_order_number=time();
                $customer_order_amount=0;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_agent_id={$curtain_agent_id} AND is_checkout=0", OBJECT );                
                foreach ( $results as $index=>$result ) {
                    $_is_checkout = '_is_checkout_'.$index;
                    if ( $_POST[$_is_checkout]==1 ) {
                        $this->update_shopping_items(
                            array(
                                'customer_order_number'=>$customer_order_number,
                                'is_checkout'=>1
                            ),
                            array(
                                'curtain_order_id'=>$result->curtain_order_id
                            )
                        );

                        $customer_order_amount=$customer_order_amount+$result->order_item_amount;

                        $x = 0;
                        while ($x < $result->order_item_qty) {
                            $serial_number->insert_serial_number(
                                array(
                                    'curtain_model_id'=>$result->curtain_model_id,
                                    'specification'   =>$curtain_specifications->get_name($result->curtain_specification_id).$result->curtain_width,
                                    'curtain_agent_id'=>$result->curtain_agent_id
                                ),
                                $x
                            );
                            $x = $x + 1;
                        }
                    }
                }

                // Convert the shopping items to customer orders and purchase order
                $this->insert_customer_order(
                    array(
                        'customer_order_number' => $customer_order_number,
                        'curtain_agent_id'      => $curtain_agent_id,
                        'customer_order_amount' => $customer_order_amount,
                        'customer_order_status' => 'order01'
                        // order01: completed the checkout but did not purchase yet
                    )
                );

                // Notice the admin about the order status
                $this->order_status_notice($customer_order_number, 'order01');
            }
            
            /** Shopping Cart Item Editing*/
            if( isset($_POST['_create']) ) {
                $width = 1;
                $height = 1;
                $qty = 1;
                if (is_numeric($_POST['_curtain_width'])) {
                    $width = $_POST['_curtain_width'];
                }
                if (is_numeric($_POST['_curtain_height'])) {
                    $height = $_POST['_curtain_height'];
                }
                if (is_numeric($_POST['_shopping_item_qty'])) {
                    $qty = $_POST['_shopping_item_qty'];
                }
                $m_price = $curtain_models->get_price($_POST['_curtain_model_id']);
                $r_price = $curtain_remotes->get_price($_POST['_curtain_remote_id']);
                $s_price = $curtain_specifications->get_price($_POST['_curtain_specification_id']);
                if ($curtain_specifications->is_length_only($_POST['_curtain_specification_id'])==1){
                    $amount = $m_price + $r_price + $width/100 * $s_price * $qty;
                } else {
                    $amount = $m_price + $r_price + $width/100 * $height/100 * $s_price * $qty;
                }
                $this->insert_shopping_item(
                    array(
                        'curtain_agent_id'=>$curtain_agent_id,
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'curtain_remote_id'=>$_POST['_curtain_remote_id'],
                        'curtain_specification_id'=>$_POST['_curtain_specification_id'],
                        'curtain_width'=>$_POST['_curtain_width'],
                        'curtain_height'=>$_POST['_curtain_height'],
                        'order_item_qty'=>$_POST['_shopping_item_qty'],
                        'order_item_amount'=>$amount,
                        'is_checkout'=>0
                    )
                );
            }

            if( isset($_POST['_update']) ) {
                $width = 1;
                $height = 1;
                $qty = 1;
                if (is_numeric($_POST['_curtain_width'])) {
                    $width = $_POST['_curtain_width'];
                }
                if (is_numeric($_POST['_curtain_height'])) {
                    $height = $_POST['_curtain_height'];
                }
                if (is_numeric($_POST['_shopping_item_qty'])) {
                    $qty = $_POST['_shopping_item_qty'];
                }
                $m_price = $curtain_models->get_price($_POST['_curtain_model_id']);
                $r_price = $curtain_remotes->get_price($_POST['_curtain_remote_id']);
                $s_price = $curtain_specifications->get_price($_POST['_curtain_specification_id']);
                if ($curtain_specifications->is_length_only($_POST['_curtain_specification_id'])==1){
                    $amount = $m_price + $r_price + $width/100 * $s_price * $qty;
                } else {
                    $amount = $m_price + $r_price + $width/100 * $height/100 * $s_price * $qty;
                }
                $this->update_shopping_items(
                    array(
                        'curtain_category_id'=>$_POST['_curtain_category_id'],
                        'curtain_model_id'=>$_POST['_curtain_model_id'],
                        'curtain_remote_id'=>$_POST['_curtain_remote_id'],
                        'curtain_specification_id'=>$_POST['_curtain_specification_id'],
                        'curtain_width'=>$_POST['_curtain_width'],
                        'curtain_height'=>$_POST['_curtain_height'],
                        'order_item_qty'=>$_POST['_shopping_item_qty'],
                        'order_item_amount'=>$amount,
                    ),
                    array(
                        'curtain_order_id'=>$_POST['_curtain_order_id'],
                    )
                );
                ?><script>window.location.replace("?_update=");</script><?php
            }

            if( isset($_GET['_delete']) ) {
                $this->delete_shopping_items(
                    array(
                        'curtain_order_id'=>$_GET['_delete']
                    )
                );
            }

            /** Shopping Cart List */
            if( isset($_POST['_where']) ) {
                $table = $wpdb->prefix.'order_items';
                $where='"%'.$_POST['_where'].'%"';
                get_search_results($table,$where);

                $existing_columns = $wpdb->get_col("DESC {$wpdb->prefix}order_items", 0);
                $where_condition = '';
                $x = count($existing_columns);
                foreach ($existing_columns as $existing_column) {
                    $where_condition .= $existing_column.'="'.$_POST['_where'].'"';
                    $x = $x -1 ;
                    if ($x > 0) {
                        $where_condition .= ' OR ';
                    }
                }
                if ($where_condition == '') {
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_agent_id={$curtain_agent_id}", OBJECT );
                } else {
                    //return $where_condition;
                    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_agent_id={$curtain_agent_id} AND ({$where_condition})", OBJECT );
                }
                unset($_POST['_where']);
            } else {
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_agent_id={$curtain_agent_id} AND is_checkout=0", OBJECT );
            }
            //$agent = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_agents WHERE curtain_agent_id = %d", $curtain_agent_id ), OBJECT );            
            
            $output  = '<h2>Cart</h2>';
            $output .= '<div style="display: flex; justify-content: space-between; margin: 5px;">';
            $output .= '<div>';
            $output .= '<form method="post">';
            $output .= '<input class="wp-block-button__link" type="submit" value="New Item" name="_add">';
            $output .= '<input class="wp-block-button__link" type="submit" value="My Orders" name="_customer_orders">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '<div style="text-align: right;">';
            $output .= '<form method="post">';
            $output .= '<input style="display:inline" type="text" name="_where" placeholder="Search...">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Search" name="submit_action">';
            $output .= '</form>';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '<div class="ui-widget">';
            $output .= '<table id="orders" class="ui-widget ui-widget-content">';
            $output .= '<thead><tr class="ui-widget-header ">';
            $output .= '<th></th>';
            $output .= '<th></th>';
            $output .= '<th>date/time</th>';
            $output .= '<th>category</th>';
            $output .= '<th>model</th>';
            $output .= '<th>spec</th>';
            $output .= '<th>dimension</th>';
            $output .= '<th>QTY</th>';
            $output .= '<th>amount</th>';
            $output .= '<th></th>';
            $output .= '</tr></thead>';
            $output .= '<form method="post">';
            $output .= '<tbody>';
            foreach ( $results as $index=>$result ) {
                $output .= '<tr>';
                if ( $result->is_checkout==1 ) {
                    $output .= '<td></td>';
                    $output .= '<td></td>';
                } else {
                    $output .= '<td style="text-align: center;">';
                    $output .= '<input style="display:inline" type="checkbox" value="1" name="_is_checkout_'.$index.'">';
                    $output .= '</td>';
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span style="margin-left:5px;" id="btn-edit-'.$result->curtain_order_id.'"><i class="fa-regular fa-pen-to-square"></i></span>';
                    $output .= '</td>';
                }
                $output .= '<td>';
                $output .= wp_date( get_option('date_format'), $result->create_timestamp ).' '.wp_date( get_option('time_format'), $result->create_timestamp );
                $output .= '</td>';
                $output .= '<td>'.$curtain_categories->get_name($result->curtain_category_id).'</td>';
                $output .= '<td style="text-align: center;">'.$curtain_models->get_name($result->curtain_model_id).'</td>';
                $output .= '<td>'.$curtain_specifications->get_description($result->curtain_specification_id).'</td>';
                $output .= '<td>Width:'.$result->curtain_width;
                if ($result->curtain_category_id==1){
                    $output .= '</td>';
                } else {
                    $output .= '<br>Height:'.$result->curtain_height.'</td>';
                }
                $output .= '<td style="text-align: center;">'.$result->order_item_qty.'</td>';
                $output .= '<td style="text-align: center;">'.number_format_i18n($result->order_item_amount).'</td>';
                if ( $result->is_checkout==1 ) {
                    $output .= '<td>checkout already</td>';
                } else {
                    $output .= '<td style="text-align: center;">';
                    $output .= '<span id="btn-del-'.$result->curtain_order_id.'"><i class="fa-regular fa-trash-can"></i></span>';
                    $output .= '</td>';
                }
                $output .= '</tr>';
            }
            $output .= '</tbody></table></div>';
            $output .= '<input class="wp-block-button__link" type="submit" value="Checkout" name="_checkout_submit">';
            $output .= '</form>';

            if( isset($_GET['_edit']) ) {
                $_id = $_GET['_edit'];
                $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}order_items WHERE curtain_order_id={$_id}", OBJECT );
                $output .= '<div id="dialog" title="Items update">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<input type="hidden" name="_curtain_order_id" value="'.$row->curtain_order_id.'">';
                $output .= '<label for="select-category-id">Curtain Category</label>';
                $output .= '<select name="_curtain_category_id" id="select-category-id">'.$curtain_categories->select_options($row->curtain_category_id).'</select>';
                $output .= '<label for="select-model-id">Model</label>';
                $output .= '<select name="_curtain_model_id" id="select-model-id">'.$curtain_models->select_options($row->curtain_model_id, $row->curtain_category_id).'</select>';
                $output .= '<label for="select-remote-id">Remote</label>';
                $output .= '<select name="_curtain_remote_id" id="select-remote-id">'.$curtain_remotes->select_options($row->curtain_remote_id).'</select>';
                $output .= '<label for="select-specification-id">Specification</label>';
                $output .= '<select name="_curtain_specification_id" id="select-specification-id">'.$curtain_specifications->select_options($row->curtain_specification_id, $row->curtain_category_id).'</select>';
                $output .= '<label for="curtain-dimension">Dimension</label>';
                $output .= '<div style="display: flex;">';
                $output .= '<span>Width</span>';
                $output .= '<input type="text" name="_curtain_width" value="'.$row->curtain_width.'" id="curtain-dimension" class="text ui-widget-content ui-corner-all">';
                $output .= '<span>x</span>';
                $output .= '<input type="text" name="_curtain_height" value="'.$row->curtain_height.'" id="curtain-dimension" class="text ui-widget-content ui-corner-all">';
                $output .= '<span>Height</span>';
                $output .= '</div>';
                $output .= '<label for="order_item_qty">QTY</label>';
                $output .= '<input type="text" name="_shopping_item_qty" value="'.$row->order_item_qty.'" id="order_item_qty" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="_update" id="update-btn-'.$row->curtain_order_id.'">';
                $output .= '</form>';
                $output .= '</div>';
            }

            if( isset($_POST['_add']) ) {
                $output .= '<div id="dialog" title="Create new item">';
                $output .= '<form method="post">';
                $output .= '<fieldset>';
                $output .= '<label for="select-category-id">Curtain Category</label>';
                $output .= '<select name="_curtain_category_id" id="select-category-id">'.$curtain_categories->select_options().'</select>';
                $output .= '<label for="select-model-id">Model</label>';
                $output .= '<select name="_curtain_model_id" id="select-model-id">'.$curtain_models->select_options().'</select>';
                $output .= '<label for="select-remote-id">Remote</label>';
                $output .= '<select name="_curtain_remote_id" id="select-remote-id">'.$curtain_remotes->select_options().'</select>';
                $output .= '<label for="select-specification-id">Specification</label>';
                $output .= '<select name="_curtain_specification_id" id="select-specification-id">'.$curtain_specifications->select_options().'</select>';
                $output .= '<label for="curtain-dimension">Dimension</label>';
                $output .= '<div style="display: flex;">';
                $output .= '<span>Width</span>';
                $output .= '<input type="text" name="_curtain_width" id="curtain-width" class="text ui-widget-content ui-corner-all">';
                $output .= '<span>x</span>';
                $output .= '<input type="text" name="_curtain_height" id="curtain-height" class="text ui-widget-content ui-corner-all">';
                $output .= '<span>Height</span>';
                $output .= '</div>';
                $output .= '<label for="order_item_qty">QTY</label>';
                $output .= '<input type="text" name="_shopping_item_qty" id="order_item_qty" class="text ui-widget-content ui-corner-all">';
                $output .= '</fieldset>';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="_create">';
                $output .= '</form>';
                $output .= '</div>';
            }
            return $output;
        }

        public function insert_customer_order($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'customer_orders';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        public function update_customer_orders($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'customer_orders';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_customer_orders($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'customer_orders';
            $wpdb->delete($table, $where);
        }

        public function insert_shopping_item($data=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'order_items';
            $data['create_timestamp'] = time();
            $data['update_timestamp'] = time();
            $wpdb->insert($table, $data);
            return $wpdb->insert_id;
        }

        public function update_shopping_items($data=[], $where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'order_items';
            $data['update_timestamp'] = time();
            $wpdb->update($table, $data, $where);
        }

        public function delete_shopping_items($where=[]) {
            global $wpdb;
            $table = $wpdb->prefix.'order_items';
            $wpdb->delete($table, $where);
        }

        public function create_tables() {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
            $sql = "CREATE TABLE `{$wpdb->prefix}customer_orders` (
                customer_order_id int NOT NULL AUTO_INCREMENT,
                customer_order_number varchar(20) UNIQUE,
                curtain_agent_id int(10),
                customer_order_amount decimal(10,0),
                customer_order_status varchar(10),
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (customer_order_id)
            ) $charset_collate;";
            dbDelta($sql);
        
            $sql = "CREATE TABLE `{$wpdb->prefix}order_items` (
                curtain_order_id int NOT NULL AUTO_INCREMENT,
                customer_order_number varchar(20),
                curtain_agent_id int(10),
                curtain_category_id int(10),
                curtain_model_id int(10),
                curtain_remote_id int(10),
                curtain_specification_id int(10),
                curtain_width int(10),
                curtain_height int(10),
                order_item_qty int(10),
                order_item_amount decimal(10,0),
                is_checkout tinyint,
                create_timestamp int(10),
                update_timestamp int(10),
                PRIMARY KEY (curtain_order_id)
            ) $charset_collate;";
            dbDelta($sql);
        }
/*
        function select_order_status() {
            global $wpdb;
            $customer_order_number = $_POST['number'];
            $customer_order_status = $_POST['status'];

            $this->update_customer_orders(
                array(
                    'customer_order_status'=>$customer_order_status,
                ),
                array(
                    'customer_order_number'=>$customer_order_number
                )
            );

            // Notice the admin about the order status
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}curtain_users WHERE is_admin = %d", 1 ), OBJECT );
            foreach ( $results as $index=>$result ) {
                $header = array();
                $header[] = 'System Notification';
                //$hero = array();
                //$hero[] = 'System Notification';
                $body = array();
                $body[] = 'Order Number: '.$customer_order_number;
                //$body[] = 'Order Status: Completed checkout but did not purchase yet';
                $body[] = 'Order Status: '.$system_status->get_name($customer_order_status);
                $curtain_service->push_bubble_messages(
                    array(
                        'line_user_id' => $result->line_user_id,
                        'link_uri' => get_permalink(get_page_by_title('Orders')).'/?_print='.$customer_order_number,
                        'header' => 'System Notification',
                        //'hero' => $hero,
                        //'body' => $body
                    )
                );
            }

            wp_die();
        }
*/
        function select_category_id() {
            global $wpdb;
            $_id = $_POST['id'];

            $models = array();
            $models[] = '<option value="0">-- Select an option --</option>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_models WHERE curtain_category_id={$_id}" , OBJECT );
            foreach ($results as $index => $result) {
                $models[] = '<option value="'.$result->curtain_model_id.'">'.$result->curtain_model_name.'('.$result->model_description.')</option>';
            }
            $models[] = '<option value="0">-- Remove this --</option>';

            $specifications = array();
            $specifications[] = '<option value="0">-- Select an option --</option>';
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}curtain_specifications WHERE curtain_category_id={$_id}" , OBJECT );
            foreach ($results as $index => $result) {
                $specifications[] = '<option value="'.$result->curtain_specification_id.'">'.$result->curtain_specification_name.'('.$result->specification_description.')</option>';
            }
            $specifications[] = '<option value="0">-- Remove this --</option>';

            $response = array();
            $response['currenttime'] = wp_date( get_option('time_format'), time() );
            $response['models'] = $models;;
            $response['specifications'] = $specifications;;
            echo json_encode( $response );

            wp_die();
        }
    }
    $my_class = new curtain_orders();
}
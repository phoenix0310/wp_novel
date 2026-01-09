<?php

if (!defined('MANGA_BOOTH_SPECIAL_SECRET_KEY')) {
    define('MANGA_BOOTH_SPECIAL_SECRET_KEY', '5a7e6075d997c3.19308574');
}

if (!defined('MANGA_BOOTH_LICENSE_SERVER_URL')) {
    define('MANGA_BOOTH_LICENSE_SERVER_URL', 'https://mangabooth.com');
} 

define('MADARA_CHILD_NOVELHUB_ITEM_REFERENCE', 'Madara-Child-NovelHub');

define('MADARA_CHILD_NOVELHUB_LICENSE_KEY', 'mangabooth_madara_child_novelhub_license_key');
define('MADARA_CHILD_NOVELHUB_SUPPORT', 'nadara_child_x_license_support_until');

add_action('admin_menu', 'madara_child_novelhub_license_menu');

function madara_child_novelhub_license_menu()
{
    add_options_page(
        esc_html__('Madara-Child-NovelHub Activation', 'madara-child'), 
        esc_html__('Madara-Child-NovelHub License', 'madara-child'), 
        'manage_options', 
        'madara-child-novelhub', 
        'madara_child_novelhub_license_management_page');
}

function madara_child_novelhub_license_management_page()
{
    echo '<div class="wrap">';
    echo '<h2>'.esc_html__( 'Madara-Child-NovelHub License', 'madara-child' ).'</h2>';

    /*** License activate button was clicked ***/
    if (isset($_REQUEST['activate_license'])) {
        $license_key = $_REQUEST[MADARA_CHILD_NOVELHUB_LICENSE_KEY];

        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_activate',
            'secret_key' => MANGA_BOOTH_SPECIAL_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode(MADARA_CHILD_NOVELHUB_ITEM_REFERENCE),
        );

        // Send query to the license manager server
        $query = esc_url_raw(add_query_arg($api_params, MANGA_BOOTH_LICENSE_SERVER_URL));
        $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)) {
            echo "<br /><span style='color: red'>Unexpected Error! The query returned with an error.</span>";
        } else {
			// License data.
			$license_data = json_decode(wp_remote_retrieve_body($response));

			// TODO - Do something with it.

			if (isset($license_data->result)) {
				if ($license_data->result == 'success') {//Success was returned for the license activation

					echo '<br />The following message was returned from the server: ' . '<span style="color: blue">'.$license_data->message.'</span>';

					//Save the license key in the options table
					update_option(MADARA_CHILD_NOVELHUB_LICENSE_KEY, $license_key);
                    update_option(MADARA_CHILD_NOVELHUB_SUPPORT, $license_data->support_until);
				} else {
					if($license_data->error_code == 110 || $license_data->error_code == 40){
                        $registered_domain = str_replace('Reached maximum activation. License key already in use on ','', $license_data->message);
                        
                        $registered_domain = str_replace('License key already in use on ','', $license_data->message);
                        
                        if($registered_domain == $_SERVER['SERVER_NAME']){
                            echo '<br />The following message was returned from the server: ' . '<span style="color: blue">'.$license_data->message.'</span>';

                            //Save the license key in the options table
                            update_option(MADARA_CHILD_NOVELHUB_LICENSE_KEY, $license_key);
                            update_option(MADARA_CHILD_NOVELHUB_SUPPORT, $license_data->support_until);
                        } else {
                            $err = $license_data->message;
                        //Show error to the user. Probably entered incorrect license key.
                        echo '<br />The following message was returned from the server: ' . '<span style="color: red">' . $err . '</span>';
                        }
                    } else {
                        $err = $license_data->message;
                        //Show error to the user. Probably entered incorrect license key.
                        echo '<br />The following message was returned from the server: ' . '<span style="color: red">' . $err . '</span>';
                    }
				}
			} else {
				if(isset($response['response'])){
					echo "<br /><span style='color: red'>There are some errors occur when connecting to server: Code - " . $response['response']['code'] . " || Message: " . $response['response']['message'] . ", please contact to plugin's author to get support for this situation</span>";
				} else {
					echo "<br /><span style='color: red'>There are some errors occur when connecting to server, please contact to plugin's author to get support for this situation.</span>";
				}
			}
		}
    }
    /*** End of license activation ***/

    /*** License activate button was clicked ***/
    if (isset($_REQUEST['deactivate_license'])) {
        $license_key = $_REQUEST[MADARA_CHILD_NOVELHUB_LICENSE_KEY];

        // API query parameters
        $api_params = array(
            'slm_action' => 'slm_deactivate',
            'secret_key' => MANGA_BOOTH_SPECIAL_SECRET_KEY,
            'license_key' => $license_key,
            'registered_domain' => $_SERVER['SERVER_NAME'],
            'item_reference' => urlencode(MADARA_CHILD_NOVELHUB_ITEM_REFERENCE),
        );

        // Send query to the license manager server
        $query = esc_url_raw(add_query_arg($api_params, MANGA_BOOTH_LICENSE_SERVER_URL));
        $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));

        // Check for error in the response
        if (is_wp_error($response)) {
            echo "Unexpected Error! The query returned with an error.";
        }

        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));

        // TODO - Do something with it.
        if (isset($license_data->result)) {
            if ($license_data->result == 'success') {//Success was returned for the license activation

                echo '<br />The following message was returned from the server: ' . '<span style="color: blue">'.$license_data->message.'</span>';

                //Remove the licensse key from the options table. It will need to be activated again.
                update_option(MADARA_CHILD_NOVELHUB_LICENSE_KEY, '');
            } else {
                if($license_data->error_code == 110 || $license_data->error_code == 40){
                    $registered_domain = str_replace('Reached maximum activation. License key already in use on ','', $license_data->message);
                    
                    $registered_domain = str_replace('License key already in use on ','', $license_data->message);
                    
                    if($registered_domain == $_SERVER['SERVER_NAME']){
                        update_option(MADARA_CHILD_NOVELHUB_LICENSE_KEY, $license_key);     
                        update_option(MADARA_CHILD_NOVELHUB_SUPPORT, $license_data->support_until);                
                        
                        echo esc_html__('Re-activate successfully. However, please deactivate & activate again to get correct license info','madara-child');
                        
                    } else {
                        $err = $license_data->message;
                    }
                } else {
                    $err = $license_data->message;
                }

                if($err){
                    //Show error to the user. Probably entered incorrect license key.
                    echo '<br />The following message was returned from the server: ' . '<span style="color: red">' . $err . '</span>';
                }                
            }
        } else {
            echo "<br />There're some errors occur when activate license from server, please contact to plugin's author to get support for this situation.";
        }
    }
    /*** End of sample license deactivation ***/

    ?>
    <?php 
    
    $key = get_option(MADARA_CHILD_NOVELHUB_LICENSE_KEY);
    if($key){?>
        <p>
            <span style="font-weight:bold; color:#00a339"><?php echo esc_html__("License Key Activated", 'madara-child');?></span>
            <span style=""><?php echo esc_html__("Premium support until: ", 'madara-child');?><?php echo get_option(MADARA_CHILD_NOVELHUB_SUPPORT) ? get_option(MADARA_CHILD_NOVELHUB_SUPPORT) : esc_html__('[Please re-activate to update this value]', 'madara-child');?></span>
        </p>
    <?php } else { ?>
    <p><?php esc_html_e( 'Please enter the license key for this product to activate it. You were given a license key when you purchased this item.', 'madara-child' ); ?></p>
    <?php } ?>
    <form action="" method="post">
        <table class="form-table">
            <tr>
                <th style="width:100px;"><label for="<?php echo MADARA_CHILD_NOVELHUB_LICENSE_KEY; ?>"><?php esc_html_e('License Key','madara-child');?></label></th>
                <td><input class="regular-text" <?php if($key) echo 'readonly';?> type="text" id="<?php echo MADARA_CHILD_NOVELHUB_LICENSE_KEY; ?>"
                           name="<?php echo MADARA_CHILD_NOVELHUB_LICENSE_KEY; ?>"
                           value="<?php echo $key; ?>"></td>
            </tr>
        </table>
        <p class="submit">
            <?php if($key){?>
                <input type="submit" name="deactivate_license" value="Deactivate" class="button"/>
            <?php } else { ?>
                <input type="submit" name="activate_license" value="Activate" class="button-primary"/>
            <?php } ?>
        </p>
    </form>
    <?php

    echo '</div>';
}

function madara_child_novelhub_admin_notice__warning()
{
    $class = 'notice notice-warning is-dismissible';
    $message = sprintf(__('Child theme is not activated, you should activate this plugin to use it,  %1$sactivate.%2$s ', 'madara-child'), '<a href="' . admin_url('options-general.php?page=madara-child-novelhub') . '">', '</a>');

    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
}
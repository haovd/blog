<?php
if ( !defined( 'ABSPATH' ) ) exit;

require_once 'includes/repository.php';
require_once 'includes/helper.php';

ignore_user_abort( true );
set_time_limit( 0 ); // disable the time limit for this script

$is_direct_access = isset( $_GET['is_direct_access'] ) ? $_GET['is_direct_access'] : '';
if ( $is_direct_access === 'true' ) {
    error_log("is_direct_access ============== " . $is_direct_access);
    check_stop_image_hotlinking();
    check_file_is_prevented();
} else {
    show_file_from_private_link();
}

function check_stop_image_hotlinking() {
    $pda_option = get_option('FREE_PDA_SETTINGS');
    if(is_array($pda_option) && array_key_exists('enable_image_hot_linking', $pda_option) && $pda_option['enable_image_hot_linking'] === "on") {
        $file_type = $_GET['file_type'];
        $images = ['jpg', 'png', 'PNG' , 'gif'];
        if(in_array($file_type, $images)) {
            if ((isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))) {
                $referer_host = parse_url($_SERVER['HTTP_REFERER'])['host']; //localhost
                $my_domain = $_SERVER['HTTP_HOST']; //staging.ymese.com
                if ($referer_host !== $my_domain) {
                    file_not_found();
                }
            }
        }
    }
}

function get_page_404(){
    $pda_settings = get_option('FREE_PDA_SETTINGS');
    if (isset($pda_settings['search_result_page_404'])) {
        $page_404 = $pda_settings['search_result_page_404'];
        $link_page_404 = explode(";", $page_404);
        return $link_page_404[0];
    } else {
        return null;
    }
}

function check_file_is_prevented() {
    $configs = Pda_Helper::get_plugin_configs();
    $endpoint = $configs['endpoint'];
    $file_name = $_GET[$endpoint];

//    $guid = $_SERVER['REQUEST_URI'];
    $file_type = $_GET['file_type'];

    $original_file = "$file_name.$file_type";
    $guid = remove_crop_numbers( $original_file, $file_type );

    $repository = new Repository;

    $post = $repository->get_post_by_guid( $guid );
    if ( isset( $post ) ) {
        error_log("[download.25]PostId: " . $post->ID);
        _check_advance_file( $post->ID, $original_file );
    } else {
        $meta_value = ltrim(substr($file_name, 0) . ".$file_type", '/');
        $meta_value = remove_crop_numbers( $meta_value, $file_type );
        $post_meta = $repository->get_post_meta_by_value( $meta_value );
        if( isset( $post_meta ) ) {
            _check_advance_file( $post_meta->post_id, $original_file );
        } else {
            error_log("Could not find file in post and post_meta, try to find in folder and return to client");
            $file_path = get_full_file_path( $original_file );
            send_file_to_client( $file_path );
        }
    }
}

function is_under_limited_downloads( $advance_file ) {
    if(isset($advance_file->limit_downloads)) {
        return $advance_file->hits_count >= $advance_file->limit_downloads;
    } else {
        return false;
    }
}

function is_expired( $advance_file ) {
    if( !isset( $advance_file->expired_date ) ) {
        return false;
    }
    $expired_date = date('m/d/Y', $advance_file->expired_date);
    $today = date('m/d/Y');
    return $today >= $expired_date;
}

function show_file_from_private_link() {
    $configs = Pda_Helper::get_plugin_configs();
    $endpoint = $configs['endpoint'];
    if(isset($_GET[$endpoint])) {
        $private_url = $_GET[$endpoint];
        $repository = new Repository;
        $advance_file = $repository->get_advance_file_by_url( $private_url );
        if ( isset( $advance_file ) &&
            $advance_file->is_prevented === "1" &&
            !is_under_limited_downloads( $advance_file ) &&
            !is_expired( $advance_file ) ) {
            $post_id = $advance_file->post_id;

            $post = $repository->get_post_by_id( $post_id );
            if ( isset( $post ) ) {
                $new_hits_count = isset($advance_file->hits_count) ? $advance_file->hits_count + 1 : 1;
                $repository->update_advance_file_by_id($advance_file->ID, array('hits_count' => $new_hits_count));
            } else {
                echo '<h2>Sorry! Invalid post!</h2>';
            }
            if (isset($post)) {
                download_file( $post );
            }  else {
                $post = $repository->get_post_meta_by_post_id ( $post_id );
                download_file_by_meta_value($post);
            }
        } else {
            file_not_found();
        }
    } else {
        file_not_found();
    }
}

function try_to_send_file ( $file ) {
}

function is_pdf ( $mime_type ) {
    return $mime_type == "application/pdf";
}

function is_video ( $mime_type ) {
    return strstr($mime_type, "video/");
}

function is_image ( $file, $mime_type ) {
    if (function_exists('exif_imagetype'))	{
        return exif_imagetype( $file );
    } else {
        return strstr($mime_type, "image/");
    }
}

function is_audio ( $mime_type ) {
    return strstr($mime_type, "audio/");
}

function send_file_to_client( $file ) {

    if ( !is_file( $file ) ) {
        file_not_found();
    }

    $mime = wp_check_filetype( $file );

    if ( false === $mime[ 'type' ] && function_exists( 'mime_content_type' ) ) {
        $mime[ 'type' ] = mime_content_type( $file );
    }
    if ( $mime[ 'type' ] ) {
        $mimetype = $mime[ 'type' ];
    }
    else {
        $mimetype = 'image/' . substr( $file, strrpos( $file, '.' ) + 1 );
    }

    if( is_image( $file, $mimetype ) == FALSE && is_pdf( $mimetype ) == FALSE && is_video( $mimetype ) == FALSE && is_audio( $mimetype ) == FALSE ) {
        $file_name = basename( $file );
        header("Content-Disposition: attachment; filename=$file_name");
    }

    //set header
    header( 'Content-Type: ' . $mimetype ); // always send this
    if ( false === strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS' ) ) {
        header( 'Content-Length: ' . filesize( $file ) );
    }

    $last_modified = gmdate( 'D, d M Y H:i:s', filemtime( $file ) );
    $etag = '"' . md5( $last_modified ) . '"';

    header( "Last-Modified: $last_modified GMT" );
    header( 'ETag: ' . $etag );
    header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 100000000 ) . ' GMT' );
    // Support for Conditional GET
    $client_etag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) : false;
    if ( ! isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) )
        $_SERVER['HTTP_IF_MODIFIED_SINCE'] = false;
    $client_last_modified = trim( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
    // If string is empty, return 0. If not, attempt to parse into a timestamp
    $client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;
    // Make a timestamp for our most recent modification...
    $modified_timestamp = strtotime( $last_modified );

    if ( ( $client_last_modified && $client_etag )
        ? ( ( $client_modified_timestamp >= $modified_timestamp ) && ( $client_etag == $etag ) )
        : ( ( $client_modified_timestamp >= $modified_timestamp ) || ( $client_etag == $etag ) )
    ) {
        status_header( 304 );
        exit;
    }

    status_header ( 200 );
    readfile( $file );
}

function get_full_file_path ( $original_file ) {
    $upload_base_dir = wp_upload_dir()['basedir'];
    $file_path = $upload_base_dir ."/_pda". $original_file;
    return $file_path;
}


function _check_advance_file( $id, $original_file ) {
    $repository = new Repository;
    $advance_file = $repository->get_status_advance_file_by_post_id( $id, true );
    //error_log("$advance_file = " . $advance_file->ID);
    //check whether the file is prevented
    if ( !is_in_whitelist() && isset( $advance_file ) && $advance_file->is_prevented === "1" ) {
        error_log("Is prevented " . $original_file);
        file_not_found();
    } else {
        $file_path = get_full_file_path( $original_file );
        send_file_to_client($file_path);
    }
}

function file_not_found() {
    $page_404 = get_page_404();
    if (isset($page_404) && !empty($page_404)) {
        header( "Location: " . $page_404, true, 301 );
    } else {
        header( "Location: " . get_site_url() . "/pda_404", true, 301 );
    }
}


function remove_crop_numbers( $guid, $file_type ) {
    $pattern = "/-\d+x\d+.$file_type$/";
    $result = preg_replace( $pattern, ".$file_type", $guid );
    return $result;
}

function download_file_by_meta_value ( $post ) {
    $meta_value = $post->meta_value;
    $upload_base_dir = wp_upload_dir()['basedir'] . '/';
    $filePath = $upload_base_dir . $meta_value;

    send_file_to_client( $filePath );
}

function download_file( $post ) {
    $fullPath = $post->guid; //http://localhost:8888/abc/cdf/wordpress-2/wp-content/uploads/2016/12/IMG_0789.JPG.jpg
    // echo($fullPath);
    $wpDir = ABSPATH; //Applications/MAMP/htdocs/abc/cdf/wordpress-2/
    $upload_base_dir = wp_upload_dir()['basedir']; //Applications/MAMP/htdocs/abc/cdf/wordpress-2/wp-content/uploads
    $upload_path = str_replace($wpDir, '', $upload_base_dir);
    // var_dump($upload_path); // wp-content/uploads
    $filePath = $upload_base_dir . '/' . get_post_meta( $post->ID , '_wp_attached_file', true );
    // $pattern = '/^((http|https|ftp):\/\/)?([^\/]+\/)/i';
    // $fullPath = preg_replace( $pattern, $wpDir, $fullPath );
    send_file_to_client( $filePath );
}

function is_in_whitelist() {
    $user = wp_get_current_user();
    if ( 0 === $user->ID ) {
        return false;
    } else {
        $white_list_roles = get_option( 'whitelist_roles' );
        if(is_array($white_list_roles)) {
            $result = array_intersect($white_list_roles, $user->roles);
            return !empty($result);
        } else {
            return false;
        }
    }
}

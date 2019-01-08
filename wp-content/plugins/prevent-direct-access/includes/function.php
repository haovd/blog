<?php
if (!defined('ABSPATH')) die('You do not have sufficient permissions to access this file.');

class Pda_Function {

    function get_htaccess_file_path() {

        //global $wp_rewrite;
        $home_path = get_home_path();
        $htaccess_file = $home_path . '.htaccess';

        return $htaccess_file;
    }

    function htaccess_writable() {

    	$htaccess_file = $this->get_htaccess_file_path();

    	if (!file_exists($htaccess_file)) {
    		error_log( '.htaccess file not existed ');
    		return '.htaccess file not existed';
    	}

    	error_log( '.htaccess is writeable: ' . is_writable($htaccess_file));
    	if(is_writable($htaccess_file)) {
    		return true;
    	}

    	@chmod($htaccess_file, 0666);

    	if (!is_writable($htaccess_file)) {
    		error_log( 'Please ask host manager to grant write permission for .htaccess file.');
    		return 'Please ask host manager to grant write permission for .htaccess file.';
    	}

    	return true;
    }

    function get_htaccess_content() {

        //global $wp_rewrite;

        $htaccess_file = $this->get_htaccess_file_path();

        if (!file_exists($htaccess_file)) {
            return false;
        }

        if (!is_writable($htaccess_file)) {
            @chmod($htaccess_file, 0666);
        }

        if (!$f = fopen($htaccess_file, 'r')) {
            return false;
        }

        return file_get_contents($htaccess_file);
    }

    function sanitized_rule($rule) {
        $rule = trim($rule);
        $rule = str_replace('\\\\', '\\', $rule);
        $rule = str_replace('\"', '"', $rule);

        return $rule;
    }


}

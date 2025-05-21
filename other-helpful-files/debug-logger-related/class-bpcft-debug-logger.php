<?php
/* 
 * Logs debug data to a file. Here is an example usage
 */
class BPCFT_Debug_Logger
{
    var $log_folder_path;
    var $default_log_file = 'bp-cf-turnstile-log.txt';
    var $debug_enabled = false;
    var $debug_status = array('SUCCESS','STATUS','NOTICE','WARNING','FAILURE','CRITICAL');
    var $section_break_marker = "\n----------------------------------------------------------\n\n";
    var $log_reset_marker = "-------- Log File Reset --------\n";
    
    function __construct()
    {
        $this->log_folder_path = BPCFT_PATH . '/logs';
        //TODO - check config and if debug is enabled then set the enabled flag to true
        //$this->debug_enabled = true;
    }
    
    function get_debug_timestamp()
    {
        return '['.gmdate('m/d/Y g:i A').'] - ';
    }
    
    function get_debug_status($level)
    {
        $size = count($this->debug_status);
        if($level >= $size){
            return 'UNKNOWN';
        }
        else{
            return $this->debug_status[$level];
        }
    }
    
    function get_section_break($section_break)
    {
        if ($section_break) {
            return $this->section_break_marker;
        }
        return "";
    }

	function append_to_file($content, $file_name = '') {
		global $wp_filesystem;

		if (empty($file_name)) {
			$file_name = $this->default_log_file;
		}
		$debug_log_file = trailingslashit($this->log_folder_path) . $file_name;

		// Ensure WP_Filesystem is initialized
		if (!($wp_filesystem instanceof WP_Filesystem)) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$wp_filesystem->put_contents(
			$debug_log_file,
			$content,
			FS_CHMOD_FILE | FS_METHOD_DIRECT | WP_FILESYSTEM_PUT_CONTENTS_ATOMIC
		);
	}

	function reset_log_file($file_name = '') {
		global $wp_filesystem;

		if (empty($file_name)) {
			$file_name = $this->default_log_file;
		}
		$debug_log_file = trailingslashit($this->log_folder_path) . $file_name;
		$content = $this->get_debug_timestamp() . $this->log_reset_marker;

		// Ensure WP_Filesystem is initialized
		if (!($wp_filesystem instanceof WP_Filesystem)) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$wp_filesystem->put_contents(
			$debug_log_file,
			$content,
			FS_CHMOD_FILE | FS_METHOD_DIRECT | WP_FILESYSTEM_PUT_CONTENTS_ATOMIC
		);
	}

    function log_debug($message,$level=0,$section_break=false,$file_name='')
    {
        if (!$this->debug_enabled) return;
        $content = $this->get_debug_timestamp();//Timestamp
        $content .= $this->get_debug_status($level);//Debug status
        $content .= ' : ';
        $content .= $message . "\n";
        $content .= $this->get_section_break($section_break);
        $this->append_to_file($content, $file_name);
    }
    
}
<?php
class BPCFT_Config{
    var $configs;
    var $message_stack;
    static $_instance;
    
    function __construct(){
        $this->message_stack = new stdClass();
    }

	function load_config() {
		$this->configs = get_option( 'bpcft_configs', array() );
	}

	function get_value( $key, $default = '' ) {
		$value = isset( $this->configs[ $key ] ) ? $this->configs[ $key ] : '';
		if ($value === '' && $default !== ''){
			$value = $default;
		}

		return $value;
	}
    
    function set_value($key, $value){
    	$this->configs[$key] = $value;
    }
    
    function add_value($key, $value){
    	if (array_key_exists($key, $this->configs)){
            //Don't update the value for this key
    	}
    	else{//It is safe to update the value for this key
            $this->configs[$key] = $value;
    	}    	
    }

    function save_config(){
    	update_option('bpcft_configs', $this->configs);
    }

    function get_stacked_message($key){
        if(isset($this->message_stack->{$key}))
            return $this->message_stack->{$key};
        return "";
    }
    
    function set_stacked_message($key,$value){
        $this->message_stack->{$key} = $value;
    }
    
    static function get_instance(){
        if(empty(self::$_instance)){
            self::$_instance = new BPCFT_Config();
            self::$_instance->load_config();
            return self::$_instance;
        }
        return self::$_instance;
    }
}

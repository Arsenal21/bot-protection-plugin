

When debug logger is needed, do the following:

#1) Add this to the inlcudes()

include_once( BPCFT_PATH . '/classes/class-bpcft-debug-logger.php' );

#2) Add this to initialize: 

//Initialize and run classes here
$this->debug_logger = new BPCFT_Debug_Logger();

#3) Add the "" file to the "classes" folder.

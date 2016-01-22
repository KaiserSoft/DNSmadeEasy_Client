<?PHP
$CONFIG = array();
$RECORDS = array();


$CONFIG['username'] = ''; // your account username
$CONFIG['password'] = ''; // your account password - may be empty if you use per record passwords
$CONFIG['ipscript'] = 'http://www.dnsmadeeasy.com/myip.jsp'; // URL to script returning your IP
$CONFIG['updateURL'] = 'https://cp.dnsmadeeasy.com/servlet/updateip'; // update URL
$CONFIG['loop_enabled'] = true; // set to TRUE to loop this script indefinitely or FALSE to exit after one run
$CONFIG['loop_delay'] = 600; // time in seconds between checks if the IP has changed


// record_id = the DNS record id as displayed in the DNSmadeEasy control panel
// password = password for the record, keep empty if you use the account password - overrides $CONFIG[password] if not empty
// 
//$RECORDS[] = array( 'record_id' => '' , 'password' => '' );
$RECORDS[] = array( 'record_id' => '' , 'password' => '' );
$RECORDS[] = array( 'record_id' => '' , 'password' => '' );
$RECORDS[] = array( 'record_id' => '' , 'password' => '' );
$RECORDS[] = array( 'record_id' => '' , 'password' => '' );
$RECORDS[] = array( 'record_id' => '' , 'password' => '' );





?>
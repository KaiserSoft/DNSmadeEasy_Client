<?PHP
$CONFIG = array();
$RECORDS = array();


$CONFIG['username'] = ''; // your account username
$CONFIG['password'] = ''; // your account password - may be empty if you use per record passwords
$CONFIG['ipscript'] = 'http://www.dnsmadeeasy.com/myip.jsp'; // URL to script returning your IP
$CONFIG['updateURL'] = 'https://cp.dnsmadeeasy.com/servlet/updateip'; // update URL
$CONFIG['cache_ip'] = 'client.tmp'; // file name used to cache the external IP address


// List of dynamic DNS records. Entries with an empty record_id are ignored.
//
// record_id = the DNS record id as displayed in the DNSmadeEasy control panel
// password = password for the record, keep empty if you use the account password - overrides $CONFIG[password] if not empty
//
//$RECORDS[] = array( 'record_id' => '12345678' , 'password' => 'super secret' );
//
$RECORDS[] = array( 'record_id' => '' , 'password' => '' );
$RECORDS[] = array( 'record_id' => '' , 'password' => '' );
$RECORDS[] = array( 'record_id' => '' , 'password' => '' );
$RECORDS[] = array( 'record_id' => '' , 'password' => '' );
$RECORDS[] = array( 'record_id' => '' , 'password' => '' );





?>
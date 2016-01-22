<?php
/* 
 * Project: DNSmadeEasy_Client
 * Description: Update script for dynamic DNS service offered by DNSmadeEasy.com
 * Author: Mirko Kaiser, http://www.KaiserSoft.net   
 * Project URL: https://github.com/KaiserSoft/DNSmadeEasy_Client    
 * Support the software with Bitcoins !thank you!: 157Gh2dTCkrip8hqj3TKqzWiezHXTPqNrV    
 * Copyright (C) 2016 Mirko Kaiser    
 * First created in Germany on 2016-01-221   
 * License: New BSD License
 * 
 * Usage:
 *  First, ensure that config.php contains your valid account information.
 * 
 *  The script can run continously and check for IP changes every n seconds. 
 *  Your DNS record will be updated if a change is detected
 * 
 *      php -f client.php
 * 
 *  You may pass the 'skip' argument to prevent the script from updating DNS record on first run
 *      
 *      php -f client.php skip
 * 
 *  You may pass a custom IP to the script to prevent it from using an external script to lookup your external IP
 *  This option may be combined with 'skip' as well. Order of arguments does not matter.
 * 
 *      php -f client.php 192.168.1.1
 */

date_default_timezone_set('Europe/Berlin');
require_once 'config.php';
$skip_first = ((isset($argv[1]) && $argv[1] === 'skip') || (isset($argv[2]) && $argv[2] === 'skip') ) ? true : false; //do not update on first run
$ip_cache = '';
$dir_slash = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? '\\' : '/';

// hard coded config values - DO NOT CHANGE!
$CONFIG['client_id'] = 'DNSmadeEasy.com Updater/2016.01.22 https://www.mysupportforum.com/kaisersoft/';
$CONFIG['script_path'] = dirname(realpath($argv[0]));
$CONFIG['loop_delay'] = ($CONFIG['loop_delay'] < 10 ) ? 30 : $CONFIG['loop_delay']; //prevent abuse


while(true){
    
    // get external IP of system
    $external_ip = IP_argument( $argv, $CONFIG['ipscript']);

    if( $external_ip !== false && strlen($external_ip) > 15 )
    {

        echo date('r')." - server returned invalid data. $external_ip\n";
        sleep(1800); // wait for 30 minutes since the server appears to have issues

    }elseif( $external_ip !== false && strpos( $external_ip, '.' ) !== false && $external_ip !== $ip_cache ) {

        if( $skip_first === true ){
            echo date('r')." - script started with 'skip'. Not updating records this time\n";
            $skip_first = false;
            $ip_cache = $external_ip;
        }else{
            process_records( $CONFIG, $RECORDS, $external_ip);
        }
    }

    // keep running or exit?
    if( $CONFIG['loop_enabled'] === true ){ sleep($CONFIG['loop_delay']); }else{ exit(0); }
}




/* function checks if an IP was passed to the script
 * passing an IP will not trigger an external IP lookup
 */
function IP_argument( &$argv, &$ip_script ){

    if( isset($argv[1]) && $argv[1] !== 'skip' ){
        return trim($argv[1]);
        
    }elseif( isset($argv[2]) && $argv[2] !== 'skip' ){
        return trim($argv[2]);
        
    }else{
        return trim(file_get_contents($ip_script));
    }
}




/* process all entries in $RECORDS array  */
function process_records( &$CONFS, &$RECS, &$IP ){
    $update_return = '';
    
    foreach( $RECS as $entry ){
        if( $entry['record_id'] != '' ){
            
            $tmp_pw = ($entry['password'] == '' ) ? $CONFS['password'] : $entry['password'];
            $update_return = update_dns_record( $CONFS['username'], $tmp_pw, $entry['record_id'], $IP);
            checks_update_return($update_return, $entry['record_id'], $IP);
        }
    }
}



/* submit DNS update */
function update_dns_record( $username, $password, $record_id, $ipaddress ){
    global $CONFIG;
    global $dir_slash;

    // create a new cURL resource
    $ch = curl_init();

    // prepare values for http transmission
    $encoded = array();
    $encoded['username'] = urlencode($username);
    $encoded['password'] = urlencode($password);
    $encoded['record_id'] = urlencode($record_id);
    $encoded['ipaddress'] = urlencode($ipaddress);
    $encoded['client_id'] = urlencode(trim($CONFIG['client_id']));

    // assemble variables to be sent as POST request
    $post_vars = "username={$encoded['username']}"
                . "&password={$encoded['password']}"
                . "&id={$encoded['record_id']}"
                . "&ip={$encoded['ipaddress']}"
                . "&client_id={$encoded['client_id']}";

    // set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, $CONFIG['updateURL']);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, count(explode('&', $post_vars)));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vars);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4); //max runtime for CURL
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4); //only the connection timeout
    
    // handle certificate for https
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_CAINFO, $CONFIG['script_path'] . "{$dir_slash}certs{$dir_slash}cacert.pem");
    curl_setopt($ch, CURLOPT_CAPATH, $CONFIG['script_path'] . "{$dir_slash}certs");

    
    // send request to update DNS record
    $return = curl_exec($ch);
    
    if( $return === FALSE ){
        echo date('r').' - cURL ERROR ' . curl_error($ch) . "\n";   
    }

    // close cURL resource to free up system resources
    curl_close($ch);

    return $return;
}

// checks the return message
function checks_update_return( &$code, &$record_id, &$ipaddress ){
    switch($code){
        
        case 'success':
            echo date('r')." - id: $record_id IP: $ipaddress - OK\n";
            break;
        case 'error':
            echo date('r')." - id: $record_id - UNKOWN ERROR\n";
            exit(99);
            break;
        case 'error-system':
            echo date('r')." - id: $record_id - GENERAL SYSTEM ERROR\n";
            exit(99);
            break;
        case 'error-record-ip-same':
            echo date('r')." - id: $record_id - WARNING IP is still the same\n";
            break;
        case 'error-record-auth':
            echo date('r')." - id: $record_id - AUTHENTICATION ERROR\n";
            echo date('r')." - Your account does not have access to this record\n";
            exit(99);
            break;
        case 'error-record-invalid':
            echo date('r')." - id: $record_id - RECORD ERROR\n";
            echo date('r')." - Record does not exist or unable to update database\n";
            exit(99);
            break;
        case 'error-auth-voided':
            echo date('r')." - id: $record_id - ACCOUNT ERROR\n";
            echo date('r')." - Your account has been permanently suspended\n";
            echo date('r')." - Please stop using this software right away!!!!\n";
            exit(99);
            break;
        case 'error-auth-suspend':
            echo date('r')." - id: $record_id - ACCOUNT ERROR\n";
            echo date('r')." - Your account has been suspended due to abuse. Please contact customer support ASAP!\n";
            exit(99);
            break;
        case 'error-auth':
            echo date('r')." - id: $record_id - AUTHENTICATION ERROR\n";
            echo date('r')." - Invalid username, password or record id. Please check your settings in config.php and try again.\n";
            exit(99);
            break;
        default:
            echo date('r')." - DNSmadeEasy.com returned an unkown value. '$code'\n";
            exit(99);
            break;
    }
}

?>
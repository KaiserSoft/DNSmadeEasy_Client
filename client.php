<?php
/*
 * Project: DNSmadeEasy_Client
 * Description: Update script for dynamic DNS service offered by DNSmadeEasy.com
 *  The script is designed to be run as a cronjob or Windows task. It will lookup your
 *  external IP, cache the result and check if anything changed.
 *  DNS records are updated using POST requests over https.
 *
 * Author: Mirko Kaiser, http://www.KaiserSoft.net
 * Project URL: https://github.com/KaiserSoft/DNSmadeEasy_Client
 * Support the software with Bitcoins !thank you!: 157Gh2dTCkrip8hqj3TKqzWiezHXTPqNrV
 * Copyright (C) 2016 Mirko Kaiser
 * First created in Germany on 2016-01-21
 * License: New BSD License
 *
 * Usage:
 *  1) Enter your account information in config.php
 *
 *  2) Run the script manually to ensure everything is working
 *        php -f client.php
 *
 *     or use the following to to force a custom IP
 *        php -f client.php 127.0.0.1
 *
 *     make sure you receive a success message before setting it up as a task
 *      "Sun, 24 Jan 2016 11:54:33 +0100 - updating record with aaa.bbb.ccc.ddd"
 *
 */

// setup default values and load config file
date_default_timezone_set('Europe/Berlin');
$PWD = dirname(realpath($argv[0]));
$OS = strtoupper(substr(PHP_OS, 0, 3));
$DIR_SLASH = ($OS === 'WIN') ? '\\' : '/';
$LF = ($OS === 'WIN') ? "\r\n" : "\n";
require_once $PWD.$DIR_SLASH.'config.php';
check_permissions(); //ensue that we can read and write to the cache file
$cached_ip = get_cached_IP(  $PWD.$DIR_SLASH.$CONFIG['cache_ip'] );
$CONFIG['client_id'] = 'DNSmadeEasy.com Updater/2016.01.24 https://www.mysupportforum.com/kaisersoft/';



// get external IP of system
$external_ip = get_external_ip( $argv, $CONFIG['ipscript']);

// update DNS if it changed
if(  $external_ip == '' )
{
    echo date('r')." - IP lookup server returned invalid data. $external_ip".$LF;
    exit(99);

}elseif( $external_ip !== $cached_ip && strpos( $external_ip, '.' ) !== false ) {

    // update DNS record
    process_records( $CONFIG, $RECORDS, $external_ip);
    put_cached_IP( $PWD.$DIR_SLASH.$CONFIG['cache_ip'], $external_ip);
    exit(0);

}elseif( $external_ip === $cached_ip ){
    echo date('r')." - external IP has not changed: $external_ip".$LF;
    exit(0);

}else{
    echo date('r')." - ERROR: unhandeld script condition".$LF;
    exit(99);
}

/*
 * ###################
 * # functions below #
 * ###################
 */






/**
 * get external IP from script agrument or from URL
 * @param array &$argv script arguments array (CLI)
 * @param string $ip_script URL to external IP lookup script
 * @return string string containing an IP or empty
 */
function get_external_ip( &$argv, &$ip_script ){
    $ret = '';

    if( isset($argv[1]) && filter_var($argv[1], FILTER_VALIDATE_IP) ){
        $ret = trim($argv[1]);

    }else{
        $ret = trim(file_get_contents($ip_script));
        if( ! filter_var($ret, FILTER_VALIDATE_IP) ){
            $ret = '';
        }
    }
    return $ret;
}




/* process all entries in $RECORDS array  */
function process_records( &$CONFS, &$RECS, &$IP ){
    $update_return = '';

    reset($RECS);
    foreach( $RECS as $entry ){
        if( $entry['record_id'] != '' ){

            // use account or per record password
            $tmp_pw = ($entry['password'] == '' ) ? $CONFS['password'] : $entry['password'];

            // submit new DNS info
            $update_return = update_dns_record( $CONFS['username'], $tmp_pw, $entry['record_id'], $IP);
            checks_update_return($update_return, $entry['record_id'], $IP);
        }
    }
}



/* submit DNS update */
function update_dns_record( $username, $password, $record_id, $ipaddress ){
    global $CONFIG;
    global $DIR_SLASH;
    global $PWD;

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
    curl_setopt($ch, CURLOPT_CAINFO, $PWD . "{$DIR_SLASH}certs{$DIR_SLASH}cacert.pem");
    curl_setopt($ch, CURLOPT_CAPATH, $PWD . "{$DIR_SLASH}certs");


    // send request to update DNS record
    $return = curl_exec($ch);

    if( $return === FALSE ){
        echo date('r').' - cURL ERROR ' . curl_error($ch) . $LF;
    }

    // close cURL resource to free up system resources
    curl_close($ch);

    return $return;
}

// checks the return message
function checks_update_return( &$code, &$record_id, &$ipaddress ){
    global $LF;
    switch($code){

        case 'success':
            echo date('r')." - id: $record_id IP: $ipaddress - OK".$LF;
            break;
        case 'error':
            echo date('r')." - id: $record_id - UNKOWN ERROR".$LF;
            exit(99);
            break;
        case 'error-system':
            echo date('r')." - id: $record_id - GENERAL SYSTEM ERROR".$LF;
            exit(99);
            break;
        case 'error-record-ip-same':
            echo date('r')." - id: $record_id - WARNING IP is still the same".$LF;
            break;
        case 'error-record-auth':
            echo date('r')." - id: $record_id - AUTHENTICATION ERROR".$LF;
            echo date('r')." - Your account does not have access to this record".$LF;
            exit(99);
            break;
        case 'error-record-invalid':
            echo date('r')." - id: $record_id - RECORD ERROR".$LF;
            echo date('r')." - Record does not exist or unable to update database".$LF;
            exit(99);
            break;
        case 'error-auth-voided':
            echo date('r')." - id: $record_id - ACCOUNT ERROR".$LF;
            echo date('r')." - Your account has been permanently suspended".$LF;
            echo date('r')." - Please stop using this software right away!!!!".$LF;
            exit(99);
            break;
        case 'error-auth-suspend':
            echo date('r')." - id: $record_id - ACCOUNT ERROR".$LF;
            echo date('r')." - Your account has been suspended due to abuse. Please contact customer support ASAP!".$LF;
            exit(99);
            break;
        case 'error-auth':
            echo date('r')." - id: $record_id - AUTHENTICATION ERROR".$LF;
            echo date('r')." - Invalid username, password or record id. Please check your settings in config.php and try again.".$LF;
            exit(99);
            break;
        default:
            echo date('r')." - DNSmadeEasy.com returned an unkown value. '$code'".$LF;
            exit(99);
            break;
    }
}



/**
 * returns cached IP from cache file
 * @param string $filepath path and name of cache file
 * @return string string containing the IP or an empty string
 */
function get_cached_IP( $filepath ){
  $ret = '';

  if( file_exists($filepath) ){
    $ret = trim(file_get_contents($filepath));
    if( ! filter_var($ret, FILTER_VALIDATE_IP) ){
        $ret = '';
    }
  }

  return $ret;
}

/**
 * writes data to cache file. clears file if contant is empty
 * @param string $filepath path with filename as string
 * @param string $content content to be written to file
 */
function put_cached_IP( $filepath, $content='' ){
  file_put_contents($filepath, $content);
}


function check_permissions(){
  global $PWD;
  global $DIR_SLASH;
  global $LF;
  global $CONFIG;

  // check if the cache file exists and if we can read it
  if( file_exists($PWD.$DIR_SLASH.$CONFIG['cache_ip']) && !is_readable($PWD.$DIR_SLASH.$CONFIG['cache_ip']) ) {
      echo date('r')." - ERROR: can not read from cache. Please check permission for: ".$PWD.$DIR_SLASH.$CONFIG['cache_ip'].$LF;
      exit(99);
  }

  // ensure that the cache file or directory is writable
  if( !is_writable( $PWD.$DIR_SLASH )
          || ( file_exists($PWD.$DIR_SLASH.$CONFIG['cache_ip']) && !is_writable($PWD.$DIR_SLASH.$CONFIG['cache_ip'])) ){
      echo date('r')." - ERROR: can not write to cache. Please check permission for: ".$PWD.$DIR_SLASH.$CONFIG['cache_ip'].$LF;
      exit(99);
  }
}
?>
DNSmadeEasy.com Dynamic DNS (DDNS) Update Script
================================================
Update script for dynamic DNS service offered by DNSmadeEasy.com    
The script is designed to be run as a FreeBSD/Linux cronjob or Windows task. It will lookup    
your external IP, cache the result and update any changes.   
    
DNS records are updated using POST requests over https.   
    
Author: Mirko Kaiser, http://www.KaiserSoft.net   
Project URL: https://github.com/KaiserSoft/DNSmadeEasy_Client    
Support the software with Bitcoins !thank you!: 157Gh2dTCkrip8hqj3TKqzWiezHXTPqNrV    
Copyright (C) 2016 Mirko Kaiser    
First created in Germany on 2016-01-21    
License: New BSD License    
    

Requirements
============
* Tested with PHP 5.6 on FreeBSD, Linux and on Windows 10 with [XAMPP 5.6](https://www.apachefriends.org/index.html)
* PHP 5 CLI
* php-filter module
* php-curl module


Usage
=====
*  (Download the latest release)[https://github.com/KaiserSoft/DNSmadeEasy_Client/archive/2016-01-24.zip]
*  Use the DNSmadeEasy.com control panel to create a dynamic record
*  Enter your account information in config.php
*  Run the script manually to ensure everything is working    
   php -f client.php    
* or use the following to to force a custom IP    
   php -f client.php 127.0.0.1    
* the script will print a return message on screen and exit with 0 (zero) on success or 99 on failure.    
  "Sun, 24 Jan 2016 11:54:33 +0100 - updating record with aaa.bbb.ccc.ddd"    
* FreeBSD / Linux - use the following line for your cronjob    
  */15 * * * * cd /path/to/script && php -f client.php >>client.log 2>&1    
* Windows Task    
  Setup the task to execute the following command every 15 minutes    
  c:\xampp\php.exe -f client.php >> client.log    
  and set it to start in the directory containing 'client.php'

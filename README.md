DNSmadeEasy.com Dynamic DNS Updater
===================================
This is a PHP cli script to update a dynamic DNS record for DNSmadeEasy.com
The script will run under Linux, Windows or any other operating system with PHP 5+ installed.

Author: Mirko Kaiser, http://www.KaiserSoft.net   
Project URL: https://github.com/KaiserSoft/DNSmadeEasy_Client    
Support the software with Bitcoins !thank you!: 157Gh2dTCkrip8hqj3TKqzWiezHXTPqNrV    
Copyright (C) 2016 Mirko Kaiser    
First created in Germany on 2016-01-21    
License: New BSD License  


Requirements
============
* PHP 5.x or later. Tested with PHP 5.6 on Linux and with XAMPP on Windows 10^

 
USAGE
=====
* Use the DNSmadeEasy.com control panel to create a dynamic record
* Enter your account details in config.php     
  This file uses Unix style line endings so Windows Notepad will not work.     
  Wordpad is fine as long as you don't try to apply formatting.
* Execute script with 'php -f client.php'
* Exit status of zero on regular exit or 99 on error
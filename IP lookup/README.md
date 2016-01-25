Description
===========
This directory contains scripts which you may use to get the external IP for you dynamic IP device.
The scripts need to be executed on a server with permanent Internet connection and a static IP.

This is optional since the IP lookup script provided by DNSmadeEasy.com works fine.

Usage
=====
* copy onto your server
* add the URL as the $CONFIG['ipscript'] setting in config.php

Example:
$CONFIG['ipscript'] = 'https://www.foo.bar/ip.php';



Please submit any alternative lookup scripts you would like to share.
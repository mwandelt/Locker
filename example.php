<?php

// Preparation:
// 1. Create a directory and make it writable for PHP scripts
// 2. Store the path of the directory in the $lockDir variable

$lockDir = '';

require_once 'locker.class.php';
$locker = new Locker( $lockDir );

if ( ! $locker->get_lock('myProcess',10,60) )
{
   die('Could not get lock');
}

sleep(10);

$locker->release_lock('myProcess');

// end of file example.php

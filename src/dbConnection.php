<?php

/* 
 * Defines DB connection parameters using RedBean PHP
 */

require '../vendor/rb/rb.php';

R::setup('mysql:host=localhost;dbname=csv_db','root','');
R::freeze(true);
<?php

setlocale (LC_TIME, 'fr_FR.utf8','fra');
date_default_timezone_set('Europe/Paris');

spl_autoload_register(function($class) {
  $tmp = '/var/www/esProjet/ajax/' . $class . '.php';

  if (file_exists($tmp))
    include $tmp;
  else  {
    $tmp = '/var/www/esProjet/ajax/DB/' . $class . '.php';

  if (file_exists($tmp))
    include $tmp;
  }
});

function debug(){
  $vars = func_get_args();
  echo '<pre>';
  foreach($vars as $var)
    var_dump($var);
  echo '</pre>';
}

define('AUTOLOAD', true);

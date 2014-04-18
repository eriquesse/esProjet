<?php
if (!defined('AUTOLOAD')) require_once('../autoload.php');
require_once('Testunit.php');

class InfoAPI {
  public function __construct(){
    echo
<<<EOT
<pre>
DB
  Gestion des tables

* users
* sessions

----------------------------------
users
 Gestion des membres

--------------------------
sessions
  Gestion des sessions

En general:
-----------
Retourne un dictionnaire (cle/aleur) JSON avec :
- errors : null ou string = message de l'erreur recontree
- datas  : null ou donnees retournees par la fonction

exemples :
  {"errors":null,"datas":[1,2,3]}
  {"errors":"Impossible de calculer", datas:null}
</pre>
EOT;
  }
}

class scan {
  static $filesTests;
  
  static public function run($dir){
    self::$filesTests = array();
    self::getAllTestsFiles($dir);
    return self::$filesTests;
  }
  static private function getAllTestsFiles($dir){
    
    $files = scandir($dir);
    foreach($files as $file) {
      if ($file != '.' && $file != '..') {
        
        if (is_dir($file)) {
          self::getAllTestsFiles($dir . '/' . $file);
        } else {
          $ext = explode('_tests.php', $file);
          if (count($ext) == 2)
            self::$filesTests[] = $dir . '/' . $file;
        }
      } 
    }
  }
}
Testunit::start();
Init_database::run();

$files = Scan::run('.');
foreach($files as $file)
  include $file;

Testunit::stop();

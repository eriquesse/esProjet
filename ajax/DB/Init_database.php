<?php

class Init_database {
  static public $error;
  static private $iniFile;

  static public function run(){
    self::initClass();
    
    if (!defined('testUnit'))
      $path = __DIR__;
    else
      $path = '/var/www/esProjet/ajax/testUnit';

    self::setIniFile($path);

    $tables = DB::showTables();
    if (empty($tables)) {
      
      $dbDefinitionTables = self::getDBdescriptionTables($path);
      $tablesDescription  = self::getDBdescription($dbDefinitionTables);
      DB::initTables($tablesDescription);

      $dbPopulateFile = self::getDBPopulateFile($path);
      if (is_null($dbPopulateFile)) return;

      $tablesPopulate = self::getDBPopulate($dbPopulateFile);
      DB::populate($tablesPopulate);
    }
  }

  static private function setIniFile($path){
    self::$iniFile = new Ini($path . '/config.ini');

    $fileName = self::$iniFile->get('db','filename');
    Error::showIfNull($fileName);

    $fileName = $path . '/' . $fileName;
    //@unlink ($path . '/' . $fileName);
    
    if (!file_exists($fileName))
      touch ($fileName);
  }
  static private function getDBdescriptionTables($path){
    $fileName = self::$iniFile->get('tables','create');
    Error::showIfNull($fileName);

    $fileName = $path . '/' . $fileName;    
    Error::showIfFalse(file_exists($fileName), $fileName);

    return $fileName;
  }
  static private function getDBdescription($dbDescriptionFile){
    $tablesDescription = file_get_contents($dbDescriptionFile);
    $tablesDescription = json_decode($tablesDescription);
    Error::showIfTrue(empty($tablesDescription), $dbDescriptionFile);

    return $tablesDescription;
 }
  static private function getDBPopulateFile($path){
    $fileName = self::$iniFile->get('tables','populate');
    $fileName = $path . '/' . $fileName;

    if (is_null($fileName) || !file_exists($fileName))
      return null;
    else
      return $fileName;
  }
  static private function getDBPopulate($dbPopulateFile){
    $tablesPopulate = file_get_contents($dbPopulateFile);
    $tablesPopulate = json_decode($tablesPopulate);

    if (empty($tablesPopulate)) //return;
      Error::showIfTrue(empty($tablesPopulate), $fileName);

    return $tablesPopulate;
 }

  static protected function initClass() {
    Error::initClass('{
      "title"    : "Init_database",
      "messages" : {
        "getIniFile"    : [ "Nom de fichier indéfini dans fichier config.ini" ],
        "getDBdescriptionTables" : [
          "Nom du fichier contenant la définition des tables indéfini dans fichier config.ini",
          "Fichier de définition des tables introuvable [_data_]" ],
        "getDBdescription" : [ "Définition des tables incorrecte dans fichier [_data_]" ],
        "getDBPopulate"    : [ "Définition des données incorrecte dans fichier [_data_]" ]
      }
    }');
  }
}

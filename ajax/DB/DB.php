<?php

class DB {
  private static $link = null ;
  private static $methods = null;
  private static $currentTables = null;
  private static $args   = null;
  private static $argsID = null;
  private static $keysNull;
  
  static public $error;
  
  // ---------------------------------------------------- Fonctions personnelles
  public static function showTables(){
    if (is_null(self::$currentTables))
      self::updateCurrentTables();

    return self::$currentTables;
  }
  public static function createTable($tableName, $fields) {
    self::showTables();
    
    if (!is_null(self::$currentTables) && in_array($tableName, self::$currentTables))
      return false;
      
    //Creation de la requete
    $requete = array();
    foreach($fields as $fieldName => $type)
      $requete[] = $fieldName . ' ' . $type;

    $requete = 'CREATE TABLE ' . $tableName . ' (' .implode(', ', $requete) . ');';
    DB::exec($requete);
    
    self::updateCurrentTables();
    
    return true;
  }
  public static function get($requete){
    $db = self::prepare($requete);
    $db->execute();
    return $db->fetchAll(PDO::FETCH_ASSOC);
  }
  public static function initTables($tables){
    foreach($tables as $table => $fields)
      self::createTable($table, $fields);
  }
  public static function populate($tables){
    self::showTables();
    
    foreach($tables as $table => $records) {

      //Protection contre l'injection sql
      if (in_array($table, self::$currentTables)) {

        $args = self::getArgsKeys(
                    $records->keys,
                    isset($records->null) ? $records->null : null);
                    
        //construction de la requete
        $requete = 'INSERT INTO `'. $table .
                  '` ('. $args['name'] .
                   ') VALUES(' . $args['keyCode']  . ');';
        $db      = DB::prepare($requete);
        
        //Execution pour chaque enregistrement
        foreach($records->datas as $record) {
         Error::show($requete, !$db->execute(self::getArgsDatas($record)));
        }
        
        $db->closeCursor();
      }
    }
  }

  public static function initArgs($args, $index = null){
    self::$args   = $args;
    self::$argsID = $index;
  }
  public static function getArgsPrepare(){
    if (is_null(self::$args))
      return '';

    $args = array();
    if (!is_null(self::$argsID))
      $args[] = self::$argsID;
      
    foreach(self::$args as $key => $value)
      $args[] = $key;

    return ':' . implode(', :', $args);
  }
  public static function getArgsExcute(){
    if (is_null(self::$args))
      return '';
      
    $args = array();
    if (!is_null(self::$argsID))
      $args[':' . self::$argsID] = null;

    foreach(self::$args as $key => $value)
      $args[':' . $key] = $value;

    return $args;
  }
  public static function getArgsKeys($keys, $keysNull=null){
    self::$args    = array();
    $names = array();
    
    //Les clées fournies
    foreach($keys as $key) {
      self::$args[] = ':' . $key;
      $names[]      = '`' . $key . '`';
    }
    
    //Le cas échéant les clés non fournies
    if (!is_null($keysNull)) {
      self::$keysNull = array_fill_keys($keysNull, null);
      foreach($keysNull as $key) {
        self::$args[] = ':' . $key;
        $names[]      = '`' . $key . '`';
      }
      
    } else
      self::$keysNull = array();
        
    return array(
      'keyCode' => implode(', ', self::$args),
      'name'    => implode(', ', $names));
  }
  public static function getArgsDatas($datas){
    return array_combine(
      self::$args,
      array_merge($datas, self::$keysNull));
  }
  
  public static function __callStatic($name, $args ) {

    //Méthodes personnelles
    if (in_array($name, self::getMethods()))
      $callback = array(self, $name);

    //Fonctions PDO
    else
      $callback = array (self::getLink(), $name);

    return call_user_func_array($callback, $args) ;
  }

  // ------------------------------------------------------------------- Privées
  private static function getLink ( ) {
    if (self::$link)
      return self::$link ;

    if (!defined('testUnit'))
      $path = __DIR__;
    else
      $path = '/var/www/esProjet/ajax/testUnit';

    $ini = new Ini($path . '/config.ini');
    $dsn = $ini->get('db','driver');

    if ($dsn === 'sqlite')
      $dsn .= ':' . $path . '/' . $ini->get('db','filename');
    
    $user       = $ini->get('db','user');
    $password   = $ini->get('db','password');
    $options    = $ini->get('db_options');
    $attributes = $ini->get('db_attributes');

    if ($ini->isKeyExist('dsn')) {
      $dsnData = $ini->get('dsn');
      if (!empty($dsnData)) {
        $dsn .= ':';
        foreach($dsnData as $key => $value)
          $dsn .= $key .'='. $value;
      }
    }

    if (is_null($user))
      self::$link = new PDO ($dsn) ;
    else
      self::$link = new PDO ($dsn, $user, $password, $options ) ;

    if (!is_null($attributes))
      foreach ( $attributes as $key => $value )
        self :: $link->setAttribute(
          constant( "PDO::{$key}" ),
          constant( "PDO::{$value}" ));
          
    self::setFunctionsPerso();
    self::initClass();

    return self :: $link ;
  }
  private static function getMethods(){
    if (!self::$link)
      self::getLink();

    return self::$methods;
  }
  private static function setFunctionsPerso(){
    self::$methods = get_class_methods('DB');

    foreach(array(
      '__callStatic',
      'getLink',
      'getMethods',
      'setFunctionsPerso',
      'updateCurrentTables'
      ) as $function)
      unset(self::$methods[array_search($function, self::$methods)]);
  }
  private static function updateCurrentTables(){
    $tables = self::get('SELECT tbl_name FROM sqlite_master;');
    
    self::$currentTables = array();

    foreach($tables as $table)
      self::$currentTables[] = $table['tbl_name'];
  }

  static protected function initClass() {
    Error::initClass('{
      "title"    : "DB",
      "messages" : {
        "populate" : [ "Erreur dans la requete [_data_]" ]
      }
    }');
  }
}

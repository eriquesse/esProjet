<?php

class Database {
  public $request   = null;
  protected $filters   = null;

  static private $errorMessages = array();
  static private $errorIndex    = array();
         private $class         = null;
  
  public function __construct(){
    $this->request = new Sql();

    $this->setClass();

    if (!array_key_exists($this->class, self::$errorMessages))
      self::$errorMessages[$this->class] = json_decode(static::getErrorMessages());

    self::$errorIndex[$this->class]    = array();
#debug($this);
#debug(get_class_vars('Database'));
  }
  
  private function setClass(){
    $this->class = debug_backtrace();
    $this->class = basename($this->class[1]['file'], '.php');
  }
  static private function getFunction(){
    $tmp = debug_backtrace();
    return $tmp[2]['function'];
  }

  public function ErrorIfParameterNotValid($test){
    $function = self::getFunction();
    if (!array_key_exists($function, self::$errorIndex[$this->class]))
      self::$errorIndex[$this->class][$function] = 0;

    if ($test === true) {
      $function  = self::getFunction();
      $messages  = self::$errorMessages[$this->class]->$function;
      $noMessage = self::$errorIndex[$this->class][$function];
      throw (new InvalidArgumentException($messages[$noMessage]));
    }
    
    self::$errorIndex[$this->class][$function]++;
  }
  static public function ErrorBecauseNotDesigned(){
    throw (new BadMethodCallException());
  }

  public function isParameterDefined() {
  }
}

if (!function_exists('debug')) {
  function debug(){
    $vars = func_get_args();
    echo '<pre>';
    foreach($vars as $var)
      print_r($var);
    echo '</pre>';
  }
}

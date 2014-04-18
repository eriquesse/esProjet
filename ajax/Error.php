<?php

class Error {
  public $messages;     //messages d'erreur
  public $title;        //Fonction/chemin d'ou provient l'erreur
  public $index;        //Indicateur du numéro d'erreur dans la fonction
  public $found = null; //Message de l'erreur trouvée
  
  private static $function; //Fonction ou a été déclarée l'erreur
  private static $class;    //Classe
  private static $noLine;   //No de ligne
  private static $additionnalData;  //Pour compléter un message d'erreur

  const MODE_THROW = 1;
  const MODE_ECHO  = 2;
  const MODE_DEBUG = 3;
  const MODE_PROD  = 4;
  private static $mode = 4;

  const OPTIONAL = false;
  const NEEDFUL  = true;
  const NOERR_READ = true;
  const NOERR_TEST = false;
  
  static public function showIfParameterNotDefined($parameterName, $additionnalData = ''){
    if (self::isFound($additionnalData, true)) return;
    
    if (!self::isParameterDefined($parameterName))
      self::send();

  }
  static public function showIfNull($parameter, $additionnalData = ''){
    if (self::isFound($additionnalData, true)) return;
    
    if (is_null($parameter))
      self::send();
  }
  static public function showIfFalse($test, $additionnalData = ''){
    if (self::isFound($additionnalData, true)) return;
    
    if ($test ===  false)
      self::send();
  }
  static public function showIfTrue($test, $additionnalData = ''){
    if (self::isFound($additionnalData, true)) return;
    
    if ($test === true)
      self::send();
  }
  static public function showIfEmpty($parameter, $additionnalData = ''){
    if (self::isFound($additionnalData, true)) return;
    
    if (empty($parameter))
      self::send();
  }
  static public function showNotFinish($additionnalData = ''){
    self::setParameters('', false);
    self::$mode = self::MODE_DEBUG;
    $class      = self::$class;
    
    self::sendToOutput (
      'Fonctionnalite non devellopee : A toi de jouer ;-) ' .
      self::$class . '::' .
      self::$function . '(' .
      $additionnalData . ')');
    die();
  }
  static public function showIfError($result, $additionnalData = ''){
    self::setParameters($additionnalData, false);

    if (is_null($result) || !is_null($result->errors))
      self::send();
  }
  static public function show($additionnalData = ''){
    self::setParameters($additionnalData, false);
    self::send();
  }

  static public function initClass($datas){
    $datas = json_decode($datas);
    if (is_null($datas))
      die('Erreur dans la definition des messages d\'erreur [' . self::$class . ']');
      
    self::setParameters();

    $class = self::$class;
    $class::$error = new Error();

    $error = self::getObjectError();
    
    if (isset($datas->messages)) $error->messages = $datas->messages;
    if (isset($datas->title))    $error->title    = $datas->title;
  }
  static public function isFound($additionnalData='', $interne = false){
    self::setParameters($additionnalData, $interne, self::NOERR_TEST);
    return (!is_null(self::getObjectError()->found));
  }
  
  static private function send(){
    self::eraseDatas();
    $message = self::getMessageFound();
#var_dump(self::$class, self::$function);
    if (is_null($message)) {
      self::$mode = self::MODE_THROW;
      self::sendToOutput (
        'Erreur dans le gestionnaire d\'exception  [' .
        self::$function . ':' .
        self::getIndex() . ']');
      
    } else
      self::sendToOutput ('[' . 
        self::getTitle() . ' -> ' . 
        self::$function . ':' . 
        self::$noLine . '] : ' .
        self::getUpdateMessageFound($message));
  }
  
  static private function sendToOutput($message){
var_dump($message);
    switch (self::$mode) {
      case self::MODE_THROW : //Gestion par les exceptions
        throw new Exception ($message);
        break;
      
      case self::$mode == self::MODE_PROD : //Transmettre l'erreur
        $message       = explode('] :', $message);
        $object        = self::getObjectError();
        $object->found = trim($message[1]);
        break;

      case self::$mode == self::MODE_ECHO : //Afficher l'erreur simpliée
        $message = explode('] :', $message);
        $message = trim($message[1]);
        var_dump($message);
      default  :
        echo $message;
        die();
    }
  }
  static private function eraseDatas(){
    $class         = self::$class;
    if (isset($class::$datas))
      $class::$datas = null;    
  }

  static private function setParameters($additionnalData='', $interne=false, $lecture = self::NOERR_READ){
    
    $tmp                   = debug_backtrace();
    $no   = 1 + ($interne ? 1 : 0); //fonctions appelé à partir de ce fichier ?

    self::$function        = $tmp [$no+1] ['function'];
    self::$class           = $tmp [$no+1] ['class'];
    self::$noLine          = $tmp [$no] ['line'];
    self::$additionnalData = $additionnalData;

    self::setNextIndex($lecture);
  }
  static private function setNextIndex($lecture){
    $error = self::getObjectError();

    if (!isset($error->index[self::$function]))
      $error->index[self::$function] = -1;

    if ($lecture === self::NOERR_READ || $error->index[self::$function] == -1)
      $error->index[self::$function]++;
  }

  static protected function isParameterDefined($varName) {
    $class = self::$class;
    return (is_array($class::$datas) && array_key_exists($varName, $class::$datas));
  }

  static private function getMessageFound(){
    $messages = self::getObjectMessages();
    $function = self::$function;

    //La fonction a été déclarée
    if (isset($messages->$function)){
      $messages = $messages->$function;
      $noErreur = self::getIndex();
      
      //Il y a bien une erreur déclarée
      if (isset($messages[$noErreur]))
        return $messages[$noErreur];
    }
    
    return null;
  }
  static private function getUpdateMessageFound($message){
    return str_replace('_data_', self::$additionnalData, $message);
  }

  static private function getObjectError(){
    $class = self::$class;
    return $class::$error;    
  }
  static private function getTitle(){
    return self::getObjectError()->title;
  }
  static private function getIndex(){
    return self::getObjectError()->index[self::$function];
  }
  static private function getObjectMessages(){
    return self::getObjectError()->messages;
  }
  static public function showIfValueParameterNotAutorized($parameterName, $possibilities, $optional = self::OPTIONNAL){
    if (self::isFound($parameterName, true)) return;

    if (($optional === self::NEEDFUL) && (!self::isParameterDefined($parameterName)))
      self::send();
      
    if (!self::isFound('', true) && self::isParameterDefined('time')) {
      $class = self::$class;
      if (in_array( $class::$datas[$parameterName], $possibilities)){
        $class::$$parameterName = $class::$datas[$parameterName];
      } else {
        self::$additionnalData = $parameterName . ' = ' . $class::$datas[$parameterName];
        self::send();
      }
    }
  }
}

/*
* showIfParameterNotDefined
*   si le parametre n'est pas dans la liste des parametres reçus -> erreur
* 
* showIfNull
*   si le parametre est null -> erreur
*
* showIfFalse
*   si le test est faux -> erreur
*
* showIfTrue
*   si le test est vrai -> erreur
*
* show
*   Envoie une erreur
*
* showNotFinish
*   Affiche un message indiquant que cette partie du programme est encore à concevoir
* 
* isFound
*   retourne true/false si il y a déjà une erreur
* 
*  initClass
*   Defini les parametres necessaires à l agestion des erreurs
*/

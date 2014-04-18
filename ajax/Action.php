<?php

class Action {
  static protected $route;
  static protected $requestMode;

  const GET_HTML = 1;
  const GET      = 2;
  const POST     = 3;
  const GET_JSON = 4;
  
  static public $datas;
  static public $error;    

  const JSONDECODE = 1;
  const UNMODIFIED = 2;
  
  public function __construct($route) {
    self::$route = $route;
    self::$datas = $route->args;

    static::initClass();
    self::$error->title = self::$route->chemin;

    static::isParametersValid();

    if (!Error::isFound())
      static::askBdD();

    //Mode HTML -> affichage direct
    if (self::$requestMode === self::GET_HTML) {
      if (!Error::isFound())
        $this->makeHtml();
      echo self::$datas;
      
    //Mode JSON -> pas de message d'erreur dans le retour
    } elseif (self::$requestMode === self::GET_JSON)
      echo json_encode(self::$datas);
      
    //Mode GET et POST *standards*
    else
      static::showResult();
  }

  static protected function setRequestMode($requestMode){
    self::$requestMode = $requestMode;
  }
  static protected function run($table, $command, $args = null){

    $script = explode('ajax', $_SERVER['SCRIPT_NAME']);
    $script = $_SERVER['HTTP_HOST'] . $script[0] . 'ajax/' . $table;

    $argsCmd = array();

    if (!is_null($args)) {
      foreach($args as $name => $value)
        $argsCmd[] = urlencode($name) . '=' . urlencode($value);
        
      $args = '&' . implode('&', $argsCmd);

    } else
      $args = '';
#var_dump('http://' . $script . '?' .  $command . $args);
    echo file_get_contents('http://' . $script . '?' .  $command . $args);
  }
  static protected function get($table, $command, $args = null, $json_decode = self::JSONDECODE){
    ob_start();                           //Bloque la sortie
    self::run($table, $command, $args);   //Lance l'action'
    $tmp = ob_get_contents();             //mémorise la sortie
    ob_end_clean();                       //efface la sortie
#echo $tmp;
    if ($json_decode == self::JSONDECODE)
      return json_decode($tmp);
    else
      return $tmp;                          //retourne la sortie

  }
  static protected function isParameterDefined($varName) {
    return is_array(self::$datas) && array_key_exists($varName, self::$datas);
  }
  static protected function isParameterAutorized($varName, $autorizedValues) {
    return in_array (
          $varName,
          array_map('trim', explode(',', $autorizedValues)));
  }

  static protected function isvalid_UserId(){
    if (Error::isFound()) return;
    
    if (self::isParameterDefined('userID'))
      static::$userID = intval(self::$datas['userID']);
  }
  static protected function isvalid_UserIdentity(){
    if (Error::isFound()) return;
    
    //Déjà trouvé
    if (!static::$userID) {

      //Autre moyen de trouver le membre
      if(self::isParameterDefined('userIdentity')) {

        //Lecture du UserID
        $user = json_decode(self::get(
          'DB/users',
          'get',
          array(
            'userIdentity' => self::$datas['userIdentity'],
            'field'        => 'id')));

        Error::showIfError($user, self::$datas['userIdentity']);
        static::$userID = $user->datas;

      //Par defaut tous    
      } else
        static::$userID = 'all';
    }
  }
  static protected function getOnlyOneUser($usersList){
    foreach($usersList as $userData)
      if (strcasecmp($userData['name'] . ' ' . $userData['forename'], static::$userIdentity) == 0 ) {
        return $userData;
        break;
      }

    return null;
  }
  static protected function getFirstWordOfIdentity(){
    return substr(static::$userIdentity, 0, strpos(static::$userIdentity, ' '));
  }
  
  static private   function showResult(){
    echo json_encode(array(
      'errors' => self::$error->found,
      'datas'  => self::$datas));
  }
}


/*
* setRequestMode
*   Défini le mode de requete (et surtout de retour d'une classe)
*   particularité : GET_JSON (pas de retour d'erreur)
*
* run
*   Requete sur une API répertoire DB -> retour en echo (javascript)
*
* get
*   Idem run mais retour dans une variable
*
* isParameterDefined
*   retourn esi le parametre est défini dans les parametres transmis via l'URL
*
* isvalid_UserId
*   Defini self::$userID
*
* isvalid_UserIdentity
*   Défini self::$userID via l'identité
* 
* Les classes sont cherchées dans les répertoires ajax et ajax/DB
*/

<?php

if (!defined('ROUTE')) $route  = include '../Routage.php';

class getListe extends Action { //======================================= object user
  static public $time;
  static public $userID;

  public    function __construct($route) {
    parent::__construct($route);
  }

  protected function isParametersValid(){

    self::isvalid_UserId();
    self::isvalid_UserIdentity();
    self::isValid_Time();

    return $this;
  }
  protected function askBdD() {
    $sessions = new Sessions();

    self::db_Time($sessions);
    self::db_UserIdentity($sessions);

    self::db_Format($sessions->read()); 
  }

  static private function isValid_Time(){
    Error::showIfValueParameterNotAutorized(
      'time',
      array('past', 'futur', 'all'),
      Error::OPTIONAL);
  }

  static private function db_Time($sessions) {
    switch (self::$time) {
      case 'past':
        $sessions->setFilter('time', Sessions::TIME_PAST);
        break;
      case 'futur':
        $sessions->setFilter('time', Sessions::TIME_FUTURE);
        break;
    }
  }
  static private function db_UserIdentity($sessions) {
    if (self::$userID != 'all')
      $sessions->setUserID(self::$userID);
  }
  static private function db_Format($sessions) {
    if (count($sessions) > 0) {
      
      foreach($sessions as $no => $session){
        $sessions[$no] = new Session(array(
          'id'        => $session->id,
          'timeStart' => new DateTime("@" . $session->timeStart),
          'timeStop'  => new DateTime("@" . $session->timeStop)));
      }
    }
    
    self::$datas = $sessions;
  }

  static protected function initClass() {
    Error::initClass('{
      "messages"    : {
        "isValid_Time"         : [ "Parametre invalide [time = _data_]" ]
      }
    }');
    self::setRequestMode(self::GET);
  }
}

class InfoAPI {
  public function __construct(){
    echo
<<<EOT
<pre>
sessions

* getListe

----------------------------------
getListe([userIdentity:string, time:string])
  Retourne la liste des sessions
  Format JSON : tableau de string : [id, Tstart, Tend]

Parametres optionnels
- userIdentity : limitation a un membre (identite:string)
- time : [futur/past] : limitation au futur ou au passe

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

$route->go('getListe');

/* Error::showNotFinish('userIdentity'); */

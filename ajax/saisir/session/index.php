<?php

if (!defined('ROUTE')) $route  = include '../../Routage.php';
define('SAISIR_SESSION', true);

class init extends Action { //======================== Affichage de la page HTML
  static private $dateNow = null;
  static protected $userID  = null;
  
  public    function __construct($route) {
    parent::__construct($route);
  }

  protected function isParametersValid(){

    self::isvalid_UserId();
    self::isvalid_UserIdentity();

    return $this;
  }
  protected function askBdD() {
    $sessions = new Sessions();

    $sessions = $sessions
      ->setUserID(self::$userID)
      ->read();

    self::$datas = self::convertToDateTime($sessions);
  }
  protected function makeHtml(){
    
    $tmp      = "";
    $premiere = true;

    foreach(self::$datas as $session){
      $tmp .=
        '<a href="#" class="list-group-item ' .
          ($premiere ? 'active ' : '') .
        'infoBulle" data-toggle="tooltip" data-placement="top" title="' .
        $this->getNbDayToDie($session) .
        '" sessionID="' .
        $session->id .
        '">' .
        $this->getMessage($session) .
        '<span class="badge">' .
        $this->getduration($session) . '</span></a>';
        $premiere = false;
    }
   
    self::$datas = str_replace(
      '$listeSession$',
      $tmp,
      file_get_contents('init.html'));
 
    return $this;
  }

  static protected function initClass() {
    Error::initClass('{
      "messages"    : {
        "askBdD"            : [""],
        "isParametersValid" : [ "Il manque un parametre pour determiner qui ?" ],
        "getProjectStudent" : [ "Un seul projet par etudiant [_data_]" ],
        "getGroupsStudent"  : [ "Pas un seul groupe pour cet etudiant [_data_]" ],
        "getWithIdentity"   : [ "Membre inconnu [_data_]" ]
      }
    }');
    self::setRequestMode(self::GET_HTML);
  }

  static private function convertToDateTime($sessions){
    foreach($sessions as $no => $session) {
      $sessions[$no]->Tend   = new DateTime($session->Tend->date);
      $sessions[$no]->Tstart = new DateTime($session->Tstart->date);
    }
    return $sessions;
  }

  private function getNbDayToDie($session){
    $delay      = $this->getDateNow()->diff($session->Tend);

    if ($delay->invert == 1) {
      if ($delay->days == 0) {
        if ($delay->h < 6)
          return 'Séance d\'aujourd\'hui : Saisir maintenant est une très bonne idée';
        else
          return 'Il y a '. s('heure', $delay->h).' que cette séance aurait du être renseignée!';
          
      } else
        return 'Il y a '. s('jour', $delay->days).' que cette séance aurait du être renseignée!';
    } else
      return 'Séance à venir ...';
  }
  private function getMessage($session){
    return strftime('%A %e %B', $session->Tstart->getTimestamp()) .
           date(' \d\e G\hi', $session->Tstart->getTimestamp()) .
           date(' à G\hi', $session->Tend->getTimestamp());
  }
  private function getduration($session){
    return $session->Tend->diff($session->Tstart)->format('%h:%I');
  }
  private function getDateNow(){
    if (is_null(self::$dateNow))
      self::$dateNow = new DateTime();

    return self::$dateNow;
  }
}
class valider extends Action { //======================================= valider

  function __construct($route) {
    parent::__construct($route);
  }

  protected function isParametersValid(){
    var_dump(self::$datas);
    //Vérification -> null ou texte d'erreur

    /*self::$erreur = array(
      'Etudiant [toto] inconnu',
      'Commentaire entre parenthèses',
      'Etudiants entre crochets',
      'Description de la tache non conforme'
      );*/
    
    return $this;
  }
  protected function askBdD(){
    //Enregistrment dans la base de données
    return $this;
  }
  
  static protected function init() {
    self::$modeRequete = self::POST;
    self::$errorMsg    = array();
  }
}
class ajouter extends Action { //=========================== Ajouter une session
  private static $userID = null;
  private static $Tstart = null;
  private static $Tend   = null;
  private static $users  = null;
  private static $groups = null;
  
  public    function __construct($route) {
    parent::__construct($route);
  }

  protected function isParametersValid(){

    Error::showIfParameterNotDefined('userID');
    Error::showIfEmpty('userID');
    self::$userID = self::$datas['userID'];
  
    Error::showIfParameterNotDefined('Tend');
    Error::showIfEmpty('Tend');
    self::$Tend = self::$datas['Tend'];
  
    Error::showIfParameterNotDefined('Tstart');
    Error::showIfEmpty('Tstart');
    self::$Tstart = self::$datas['Tstart'];

    if (self::isParameterDefined('users')) {
      Error::showIfEmpty('users');
      self::$users = array_map('trim',
                                explode(',', self::$datas['users']));
    }
    
    if (self::isParameterDefined('groups')) {
      Error::showIfEmpty('groups');
      self::$groups = self::getUserIDbyGroup(
        array_map('trim',
                  explode(',', self::$datas['groups'])));
    }
    
    Error::showIfFalse(is_null(self::$groups) xor is_null(self::$users));
    
    return $this;
  }
  protected function askBdD() {
    
    var_dump(Sessions::isExist()); die();
    $sessionID = self::getSessionID();
    
    self::setStudentsOfGroups();              #Liste des étudiants si groups
    self::deleteSessionUserExist($sessionID); #Eviter les doublons
    self::setSessionPerStudent($sessionID);   #Mémorisation

    return $this;
  }
  static private function getSessionID(){
    $sessionID = self::isSessionExist();

    if (is_null($sessionID))
      $sessionID = self::insertSession();

    return $sessionID;
  }
  static private function insertSession(){
    $Sql = new Sql(Sql::REQUEST_SECURE);
    
    $sessionID = $Sql
      ->into  ('sessions')
      ->values(new Value('Tstart', self::$Tstart),
               new Value('Tend',   self::$Tend))
      ->insert();

    if (is_null($sessionID))
      Error::show('Ajout d\'une nouvelle session impossible');
      
    return $sessionID;
  }
  static private function isSessionExist(){
    $Sql = new Sql(Sql::REQUEST_SECURE);
    
    $sessionID = $Sql
      ->select('id')
      ->from  ('sessions')
      ->where (new Value('Tstart', intval(self::$Tstart), ' = '),
               new Value('Tend',   intval(self::$Tend),   ' = '))
      ->get();

    if (!empty($sessionID))
      return $sessionID[0]['id'];
    else
      return null;
  }

  static private function setStudentsOfGroups(){
    if (is_null(self::$groups)) return;
    
    self::$users = array();

    $Sql = new Sql(Sql::REQUEST_SECURE);
    
    $users = $Sql
      ->select  ('userID as id')
      ->from    ('student_group')
      ->whereIn ('groupID', self::$groups)
      ->get     ();

    var_dump($users);
    
    self::$groups = null;
  }
  static private function setSessionPerStudent($sessionID){
    if (empty(self::$users)) return;
    
    $session_user = array();
    
    foreach(self::$users as $userID)
      $session_user[] = array(
        new Value('sessionID', $sessionID),
        new Value('etdID',     $userID),
        new Value('reccord',   'false'));
      
    $Sql = new Sql(Sql::REQUEST_SECURE);
    
    $sessionID = $Sql
      ->into  ('session_user')
      ->values($session_user)
      ->insert();

    if (is_null($sessionID))
      Error::show('Affectation de nouvelles sessions aux étudiants impossible');
    
  }
  static private function deleteSessionUserExist($sessionID){
    $session_user = array();
    
    $db = DB::prepare('SELECT * FROM session_user WHERE etdID = :etdID AND sessionID = :sessionID');

    foreach(self::$users as $userID) {

      //Erreur à l'exécution de la requete
      if ($db->execute(array(
        ':etdID' => $userID,
        ':sessionID' => $sessionID)) !== true) {
        Error::show();
        break;

      //Session jamais mémorisée pour cet étudiant
      } else if (count($db->fetchAll()) == 0) {
        $session_user[] = $userID;
      }
    }

    self::$users = $session_user;
  }
  static private function getUserIDbyGroup($groups){
    if (Error::isFound()) return;

    if (!is_array($groups))
      $groups = array($groups);

    $usersID = array();
    
    $db = DB::prepare('
      SELECT userID as id
      FROM student_group
      WHERE groupID = ?');
      
    foreach($groups as $groupID){
      $db->execute(array($groupID));
      $result = $db->fetchAll();
      var_dump($result);
    }
  }

  static protected function initClass() {
    Error::initClass('{
      "messages"    : {
        "isParametersValid" : [
          "L\'utilisateur n\'est pas défini",
          "L\'utilisateur est inconnu dans la base",
          "La date de début n\'est pas définie",
          "La date de début est incorrecte",
          "La date de fin n\'est pas définie",
          "La date de fin est incorrecte",
          "Le(s) étudiant(s) est(sont) incorrect(s)",
          "Le(s) groupes(s) est(sont) incorrect(s)",
          "Il faut définir soit les groupes, soit les étudiants" ],
        "askBdD" : [
          "Le type [_data_] est inconnu" ]
      }
    }');
    self::setRequestMode(self::GET);
  }
}

class InfoAPI {
  public function __construct(){
    echo 'Impossible d\'acceder a ce repertoire';
  }
}
$route->go('init', 'valider', 'ajouter');

<?php

if (!defined('ROUTE')) $route  = include '../Routage.php';

class getList extends Action { //====================== liste des étudiants
  static public $userID       = null;
  static public $projectID    = null;
  static public $groupID      = null;
  static public $returnFormat = null;

  public    function __construct($route) {
    parent::__construct($route);
  }

  protected function isParametersValid(){

    self::isvalid_UserId();
    self::isvalid_GroupID();
    self::isvalid_ProjectID();

    if(is_null(self::$userID))
      self::$userID = 'all';

    if (self::isParameterDefined('format') && self::$datas['format'] == 'object')
      self::$returnFormat = 'object';
    else
      self::$returnFormat = 'string';
      
    return $this;
  }
  static protected function isvalid_GroupID(){
    if (self::isParameterDefined('groupID'))
      self::$groupID = intval(self::$datas['groupID']);
  }
  static protected function isvalid_ProjectID(){
    if (self::isParameterDefined('projectID'))
      self::$projectID = intval(self::$datas['projectID']);
  }
  protected function askBdD() {

    //Un projet en particulier
    if (!is_null(self::$projectID)){
      self::getProjectStudents();
      return;      
    }

    //Un groupe en particulier
    if (!is_null(self::$groupID)){
      self::getGroupStudents();
      return;      
    }

    //tous les étudiants
    if (self::$userID != 'all' && self::getMemberClass() == 'Etd') {
      self::setProjectID();
      self::getProjectStudents();
      return;
    }
    
    self::getAllStudents();
    return;
  }

  static private function getMemberClass(){
    $Sql = new sql();
    $Sql->select (array('class' => 'code'))
        ->from   ('class as c')
        ->join   ('status as s', 'c.id = s.classID')
        ->join   ('user_status as us', 's.id = us.statusID')
        ->where  ('us.userID = ' . self::$userID);

    try {
      $classCode = DB::query($Sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      debug($Sql, $Sql.'', $e); die();
    }
    unset($Sql);
    
    if (is_null($classCode) || count($classCode) != 1)
      Error::show(self::$userID);
    else
      return $classCode[0]['code'];
  }
  static private function getAllStudents(){
    if (Error::isFound()) return;
    
    $Sql = new sql();
    $Sql->select (array('users' => 'id as id, name, forename'))
        ->from   ('users as u')
        ->join   ('class as c',    'c.id = s.classID')
        ->join   ('status as s',   'u.id = us.userID')
        ->join   ('user_status as us', 's.id = us.statusID')
        ->where  ('c.code = "Etd"')
        ->orderBy('name, forename ASC;');

    try {
      self::$datas = DB::query($Sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      debug($Sql, $Sql.'', $e); die();
    }
    unset($Sql);
    
    self::formatDatas();
  }
  static private function getProjectStudents(){
    if (Error::isFound()) return;

    $Sql = new sql();
    $Sql->select (array('users' => 'id as id, name, forename'))
        ->from   ('users as u')
        ->join   ('student_project as sp', 'u.id = sp.userID')
        ->where  ('sp.projectID = ' . self::$projectID)
        ->orderBy('name, forename ASC;');

    try {
      self::$datas = DB::query($Sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      debug($Sql, $Sql.'', $e); die();
    }
    unset($Sql);

    self::formatDatas();
  }
  static private function getGroupStudents(){
    $Sql = new sql();
    $Sql->select (array('users' => 'id as id, name, forename'))
        ->from   ('users as u')
        ->join   ('student_group as sg', 'u.id = sg.userID')
        ->where  ('sg.groupID = ' . self::$groupID)
        ->orderBy('name, forename DESC;');

    try {
      self::$datas = DB::query($Sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      debug($Sql, $Sql.'', $e); die();
    }
    unset($Sql);
    
    self::formatDatas();
   }
  static private function setProjectID(){
    $Sql = new sql();
    $Sql->select ('projectID')
        ->from   ('student_project as sp')
        ->join   ('users as u', 'u.id = sp.userID')
        ->where  ('sp.userID = ' . self::$userID)
        ->orderBy('name, forename DESC');

    try {
      $projectID = DB::query($Sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      debug($Sql, $Sql.'', $e); die();
    }
    unset($Sql);
    
    if (is_null($projectID) || count($projectID) != 1)
      Error::show(self::$userID);
    else
      self::$projectID = $projectID[0]['projectID'];
   }

  static private function formatDatas(){
    switch (self::$returnFormat) {
      
      case 'object' :
        foreach(self::$datas as $no => $data)
          self::$datas[$no] = array(
            'id' => $data['id'],
            'identity' => $data['name'] . ' ' . $data['forename']);
        break;
        
      default:
        foreach(self::$datas as $no => $data)
          self::$datas[$no] = $data['name'] . ' ' . $data['forename'];
    }
  }

  static protected function initClass() {
    Error::initClass('{
      "messages"    : {
        "isvalid_UserIdentity" : [
          "Etudiant inconnu [_data_]"],
        "getMemberClass" : [
          "Erreur sur la classe de cet etudiant [id = _data_]"],
        "setProjectID" : [
          "Erreur sur le projet de cet etudiant [id = _data_]"]
      }
    }');
  }
}

class getMultiples extends Action { //===== Renvoi l'ID' d'une liste d'étudiants
  static protected $usersIdentity = null;
  static protected $groups        = null;
  static protected $userIdentity;
  
  public    function __construct($route) {
    parent::__construct($route);
  }

  protected function isParametersValid() {

    self::isValid_UsersIdentity();
    self::isValid_Groups();

    Error::showIfFalse(is_null(self::$usersIdentity) xor is_null(self::$groups));
    
    return $this;
  }
  protected function askBdD() {
    $Sql = new Sql();

    if (!is_null(self::$groups))
      self::db_SelectGroups($Sql);
      
    elseif(!is_null(self::$usersIdentity))
      self::db_SelectIdentitys($Sql);
      
    else
      Error::show();
       
  }

  static protected function isValid_UsersIdentity() {
    if (Error::isFound()) return;

    if (self::isParameterDefined('usersIdentity'))
      self::$usersIdentity = self::$datas['usersIdentity'];
  }
  static protected function isValid_Groups() {
    if (Error::isFound()) return;

    if (self::isParameterDefined('groups'))
      self::$usersIdentity = self::$datas['groups'];
  }
  static private function db_SelectIdentitys($Sql){
    $list = array_map('trim', explode(',',self::$usersIdentity));
    self::$usersIdentity = array();

    $requet = DB::prepare('
      SELECT id
      FROM   users
      WHERE  name like "?%"');

    foreach($list as self::$userIdentity){
      $firstWord = self::getFirstWordOfIdentity();
      $requet->execute($firstWord);
      $result = $requet->fetchAll();
      
    }
  }
  static private function db_SelectGroups($Sql){
    self::$groups = explode(',',self::$groups);
    var_dump(self::$groups); die();
    $Sql->where ;
  }

  static protected function initClass() {
    Error::initClass('{
      "messages"    : {
        "isParametersValid" : [
          "Il faut definir groups ou usersIdentity"],
        "askBdD"               : [
          "Membre inconnu [_data_]" ]
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
students

* getList

--------------------------
getList([userID, groupID, projectID, format])
  Retourne la liste des etudiants
  Format JSON
    si format = 'string' [string, ...] (par défaut)
              = 'object' [{id:integer, identity:string}, ...]

- aucun parametre -> tous
- userID : index du membre connecte
 (si etudiant -> membres du même projet,
  si tuteur   -> liste de tous les étudiants)
- groupID     : membres du groupe (index))
- projectID   : membres du projet (index))

----------------------------------
getMultiples([usersIdentity, groups])
  Retourne la liste des index des membres correspondant au critère
  Format JSON : [id,id, ...]
- usersIdentity : identite du membre
- groups        : code des groupes

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

$route->go('getList', 'getMultiples');

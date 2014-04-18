<?php

if (!defined('ROUTE')) $route  = include '../Routage.php';

class get extends Action { //==================== Renvoi des infos sur un membre
  static private $field;
  static protected $userIdentity = null;
  static protected $userID = null;
  
  public    function __construct($route) {
    parent::__construct($route);
  }

  protected function isParametersValid() {

    self::isvalid_UserId();
    self::isValid_UserIdentity();
    self::isValid_Field();
    
    return $this;
  }
  protected function askBdD() {
    $Sql       = new Sql();
    
    self::db_SelectFields($Sql);
    self::db_Where($Sql);
    
    $Sql->from  ('users as u');
       
    try {
      $usersList = DB::query($Sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      debug($Sql, $Sql.'', $e); die();
    }

    //Erreur sur l'identite -> fini
    if (empty($usersList)) {
      if (!is_null(self::$userID))
        Error::show(self::$userID);
      else
        Error::show(self::$userIdentity);
      return;
    }

    $userData = self::getOnlyOneUser($usersList);

    if (!isset($userData)) {
      Error::show(self::$userIdentity);
      return;
    }
 
          $userData = self::getMoreIfUserIsStudent($userData);
    self::$datas = self::getOnlyOneField($userData);
  }

  static protected function isValid_UserIdentity() {
    if (Error::isFound() || self::$userID) return;

    Error::showIfParameterNotDefined('userIdentity');
    self::$userIdentity = self::$datas['userIdentity'];
  }
  static private function isValid_Field() {
    
    if ( self::isParameterDefined ('field') ) {
      if (self::isParameterAutorized(
                self::$datas['field'],
                'id, projectID, all'))
        self::$field = self::$datas['field'];
        
      else
        Error::show();
        
    } else
      self::$field = 'all';
  }

  static private function db_SelectFields($Sql){
    switch(self::$field){
      case 'id':
        $Sql->select('id, name, forename');
        break;
        
      case 'projectID':
        if (is_null(self::$userID))
          $Sql->select(array(
          'users'  => 'id as id, name, forename'));
          
        $Sql->join  ('student_project as ep', 'u.id = ep.userID')
            ->select('ep.projectID as projectID');
        break;
        
      default:
        $Sql->select(array(
          'users'  => 'id as id, name, forename, imel, cost',
          'status' => 'id as statutID,    code as status',
          'class'  => 'id as classID,     code as class'))
          
          ->join  ('class as c',    'c.id = s.classID')
          ->join  ('status as s',   'u.id = us.userID')
          ->join  ('user_status as us', 's.id = us.statusID');
          break;
    }
  }
  static private function db_Where($Sql){
    
    //Recherche par l'index ...
    if (!is_null(self::$userID))
      $Sql->where ('u.id =' . self::$userID . ';');

    //... ou par default par l'identité
    else {
      $firstWord = self::getFirstWordOfIdentity();
      $Sql->where ('name like "' . $firstWord . '%";');
    }
  }
  static private function getMoreIfUserIsStudent($userData){
    if (self::$field == 'all' && $userData['class'] == 'Etd'){
      return array_merge(
        $userData,
        self::getProjectStudent($userData['id']),
        self::getGroupsStudent ($userData['id']));
        break;

    } else
      return $userData;
  }
  static private function getOnlyOneField($userData){
    if (self::$field != 'all')
      return $userData[self::$field];
    else
      return $userData;
  }
  static private function getProjectStudent($userID){
    $Sql = new Sql();
    $Sql->select(array(
                  'projects' => 'id as projectID, code as project'))
        ->from  ('projects as p')
        ->join  ('student_project as ep', 'p.id = ep.projectID')
        ->where ('ep.userID = ' . $userID . ';');
        
    try {
      $project = DB::query($Sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      debug($Sql, $Sql.'', $e); die();
    }

    //Pas de projet pour cet étudiant
    if (is_null($project))
      $project = array('projectID' => null, 'project' => null);

    //Trop de projets ...
    else if (count($project) > 1)
      Error::show(sef::$userIdentity);

    //Un seul projet
    else
      $project = $project[0];

    return $project;
  }
  static private function getGroupsStudent($userID){
    $Sql = new Sql();
    
    $Sql->select(array('groups' => 'id as groupID, code as groupe'))
        ->from  ('groups as g')
        ->join  ('student_group as eg', 'g.id = eg.groupID')
        ->where ('eg.userID = ' . $userID . ';');
        
    try {
      $groups = DB::query($Sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      debug($Sql, $Sql.'', $e); die();
    }

    Error::showIfNull($groups, self::$userIdentity);

    return array('groups' => $groups);
  }

  static protected function initClass() {
    Error::initClass('{
      "messages"    : {
        "isValid_UserIdentity" : [
          "Il manque un parametre pour determiner qui ?" ],
        "isValid_Field"        : [ "Parametre invalide [field = _data_]" ],
        "getProjectStudent"    : [ "Un seul projet par etudiant [_data_]" ],
        "getGroupsStudent"     : [ "Pas un seul groupe pour cet etudiant [_data_]" ],
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
users

* get

----------------------------------
get(userIdentity, [field:string])
  Retourne les donnees d'un membre
  Format JSON : {objet complexe ...}
  Donnees differentes suivant la classe (Etd/Per)
- userIdentity : identite du membre
- field        : [id/project] champ retourne (si non precise -> tous))

En general:
-----------
Les Majuscules sont prises en compte dans la commande
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

$route->go('get');

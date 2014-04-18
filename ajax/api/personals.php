<?php

if (!defined('ROUTE')) $route  = include '../Routage.php';

class getList extends Action { //==================== Renvoi des infos sur un membre
  
  public    function __construct($route) {
    parent::__construct($route);
  }

  protected function isParametersValid() {
    return $this;
  }
  protected function askBdD() {
    $Sql       = new Sql();
    
    $Sql->select (array(
          'users'  => 'name, forename'))
        ->from   ('users as u')
        ->join   ('user_status as us', 'u.id = us.userID')
        ->join   ('status as s', 's.id = us.statusID')
        ->join   ('class as c',    'c.id = s.classID')
        ->where  ('c.code != "Etd"')
        ->orderBy('name, forename');
       
    try {
      $usersList = DB::query($Sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      debug($Sql, $Sql.'', $e); die();
    }
    
    self::$datas = $usersList;
    self::formatDatas();
  }
  static private function formatDatas(){
    foreach(self::$datas as $no => $data)
      self::$datas[$no] = $data['name'] . ' ' . $data['forename'];
  }
  static protected function initClass() {
    Error::initClass('{
      "messages"    : {}
    }');
    self::setRequestMode(self::GET);
  }
}

class InfoAPI {
  public function __construct(){
    echo
<<<EOT
<pre>
personals

* get

----------------------------------
get()
  Retourne les liste des membres non etudiant
  Format JSON : {[string]}

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

$route->go('getList');

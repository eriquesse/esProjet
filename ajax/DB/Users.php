<?php
if (!defined('AUTOLOAD')) require_once('../autoload.php');

class Users extends Database {

  //------------------------------------------------------------------ Constants
  const DEFINE_IMELPRO = 1;
  const DEFINE_IMELPERSO = 2;
  const DEFINE_MOBILE = 3;
  
  //----------------------------------------------------------------- Properties
  private $userID    = null;
  private $projectID = null;
  private $groupID   = null;

  private $name      = null;
  private $forename  = null;
  private $imelPro   = null;
  private $imelPerso = null;
  private $mobile    = null;
  private $cost      = null;
  private $password  = null;
  private $noUniv    = null;
  private $logOn     = null;
  
  //--------------------------------------------------------------------- Public
  public function __construct(){
    parent::__construct();

    $this->filters = (object) array('defined' => null);
  }

  public function read(){
    if (!is_null($this->userID))
      return $this->readByUserID();
      
    else if (!is_null($this->projectID))
      return $this->readByProjectID();
      
    else if (!is_null($this->groupID))
      return $this->readByGroupID();

    else
      return $this->readAll();
  }
  public function create(){
    $this->checkParameter_Create();

    $userID = $this->request
      ->anotherOne (Sql::REQUEST_SECURE)
      ->into  ('users')
      ->values(new Value('name', $this->name),
               new Value('forename', $this->forename),
               new Value('imelPro', $this->imelPro),
               new Value('imelPerso', $this->imelPerso),
               new Value('mobile', $this->mobile),
               new Value('cost', $this->cost),
               new Value('password', $this->password),
               new Value('noUniv', $this->noUniv),
               new Value('logOn', $this->logOn))
      ->insert();
      
    return $userID;
  }
  public function update(){
    self::ErrorBecauseNotDesigned();
    
    return $this;
  }
  public function delete(){
    if (!is_null($this->userID))
      return $this->deleteByUserID();
      
    else
      return $this->deleteAll();
      
    return $this;
  }
  
  public function setName($name){
    if (is_string($name) && !empty($name))
      $this->name = trim(strtoupper($name));
    else
      throw (new DomainException('Excepted String non empty'));
    return $this;
  }
  public function setForename($forename){
    if (is_string($forename) && !empty($forename))
      $this->forename = trim(ucfirst(strtolower($forename)));
    else
      throw (new DomainException('Excepted String non empty'));
    return $this;
  }
  public function setNoUniv($noUniv){
    if (is_string($noUniv))
      $this->noUniv = trim($noUniv);
      
    else
      throw (new DomainException('Expected String not empty'));
    return $this;
  }

  public function setImelPro($imelPro = null){
    if (is_null($imelPro) ||
       (is_string($imelPro) &&
        filter_var($imelPro, FILTER_VALIDATE_EMAIL)))
      $this->imelPro = trim($imelPro);
      
    else
      throw (new DomainException('Expected String and imel valid OR nothing'));
    return $this;
  }
  public function setImelPerso($imelPerso = null){
    if (is_null($imelPerso) ||
       (is_string($imelPerso) &&
        filter_var($imelPerso, FILTER_VALIDATE_EMAIL)))
      $this->imelPerso = trim($imelPerso);
      
    else
      throw (new DomainException('Expected String and imel valid OR nothing'));
    return $this;
  }
  public function setMobile($mobile = null){
    if (is_null($mobile) ||
       (is_string($mobile) &&
        substr($mobile, 0, 2) == '06' &&
        strlen($mobile) === 8))
      $this->mobile = trim($mobile);
      
    else
      throw (new DomainException('Expected String with "06" AND 8 caracters  OR nothing'));
    return $this;
  }
  public function setCost($cost = null){
    if (is_null($cost) ||
       (is_numeric($cost)))
      $this->cost = $cost;
      
    else
      throw (new DomainException('Expected Numeric OR nothing'));
    return $this;
  }
  public function setPassword($password = null){
    if (is_null($password))
      $password = self::passwordGenerate();
      
    if (is_string($password))
      $this->password = self::passwordEncrypt(trim($password));
      
    else
      throw (new DomainException('Expected String Or nothing'));
    return $this;
  }
  public function setLogOn($logOn = null){
    if (is_null($logOn) ||
        is_numeric($logOn))
      $this->logOn = $logOn;
      
    else
      throw (new DomainException('Expected String Or nothing'));
    return $this;
  }

  public function setUserID($userID){
    $this->ErrorIfParameterNotValid(!is_numeric($userID));

    $this->userID = intval($userID);
    return $this;
  }
  public function setProjectID($projectID){
    $this->ErrorIfParameterNotValid(!is_numeric($projectID));

    $this->projectID = intval($projectID);
    return $this;
  }
  public function setGroupID($groupID){
    $this->ErrorIfParameterNotValid(!is_numeric($groupID));
    
    $this->groupID = intval($groupID);
    return $this;
  }
  public function setFilter($filterName, $filterValue) {
    switch(strtolower($filterName)){
      case 'defined':
        $this->setFilterDefined($filterValue);
        break;
    }
    return $this;
  }

  //-------------------------------------------------------------------- Private
  private function checkParameter_Create(){
    #Données non précisées
    $this->ErrorIfParameterNotValid(
      is_null($this->name) ||
      is_null($this->forename) ||
      is_null($this->noUniv));

    #Déjà dans la base
    $this->ErrorIfParameterNotValid(
     !is_null($this->readIDwithDatas()));
  }

  private function filter(){
    if (!is_null($this->userID)) return;
    
    if (!is_null($this->filters->defined))
      $this->filterDefined();
  }
  private function filterDefined(){
    switch($this->filters->defined){
      case self::DEFINE_IMELPERSO:
        $this->request->where('imelPerso != null');
        break;
        
      case self::DEFINE_IMELPRO:
        $this->request->where('imelPro != null');
        break;
        
      case self::DEFINE_MOBILE:
        $this->request->where('mobile != null');
        break;
        
    }
  }
 
  private function readIDwithDatas(){
    $userID = $this->request
      ->anotherOne (Sql::REQUEST_SECURE)
      ->select('id')
      ->from  ('users')
      ->where (new Value('name', $this->name, ' = '),
               new Value('forename', $this->forename, ' = '),
               new Value('noUniv', $this->noUniv, ' = '))
      ->get();

    if (!empty($userID))
      return $userID[0]['id'];
    else
      return null;    
  }
  private function readByUserID(){
    $this->request->anotherOne(Sql::REQUEST_SECURE);
    $this->filter();
    
    $users = $this->request
      ->select ()
      ->from   ('users')
      ->where  (new value('id', $this->userID, '='))
      ->get    ();

    return self::resultToObject($users);
    
  }
  private function readByProjectID(){
    self::ErrorBecauseNotDesigned();
  }
  private function readByGroupID(){
    self::ErrorBecauseNotDesigned();
  }
  private function readAll(){
    $this->request->anotherOne();
    $this->filter();

    $users = $this->request
      ->select ()
      ->from   ('users')
      ->orderBy('name, forename ASC')
      ->get    ();

    return self::resultToObject($users);
  }

  private function deleteAll(){
    $this->request->anotherOne();
    $this->filter();

    $users = $this->request
      ->from   ('users')
      ->delete ();

    return self::resultToObject($users);
  }
  private function deleteByUserID(){
    $this->request->anotherOne(Sql::REQUEST_SECURE);
    $this->filter();
    
    $nbReccords = $this->request
      ->from   ('users')
      ->where  (new value('id', $this->userID, '='))
      ->delete ();

    return $nbReccords;
  }

  private function setFilterDefined($property){
    if ($property === self::DEFINE_IMELPERSO ||
        $property === self::DEFINE_IMELPRO ||
        $property === self::DEFINE_MOBILE)
      $this->filters->defined = $property;
    else
      throw (new DomainException('Excepted DEFINED_XXX constant'));
    return $this;
  }

  //------------------------------------------------------------- Static Private
  static private function resultToObject($users){
    if (!is_array($users)) return $users;
    
    foreach($users as $noUser => $user) {
      $users[$noUser] = new user($user);
    }
    return $users;
  }
  static private function passwordGenerate(){
    $password = "";
    $possible = "_#-*=+/2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
    $nbCars   = count($possible) -1;
    
    for($i=0; $i<8; $i++) {
      $noCar = mt_rand(0, $nbCars);
      $car   = substr($possible, $noCar, 1);
      $password .= car;
      $possible = str_replace($car, '');
      $nbCars--;
    }

    return $password;
  }
  static private function passwordEncrypt($unencrypted) {
    $password = rand(10, 9999999999);
    $salt     = substr(sha1($password), 0, 4);
    $password = sha1($salt . $unencrypted) . $salt;
    return $password;
}
  static private function passwordMatch($encrypted, $unencrypted) {
    $salt = substr($encrypted, strlen($encrypted)-4, strlen($encrypted));
    return $password == sha1($salt . $unencrypted) . $salt;
}

  //------------------------------------------------------------- Error Messages
  static protected function getErrorMessages(){
    return <<<EOT
    {"checkParameter_Create" : [
      "Il faut fournir name, forename et noUniv",
      "Deja dans la base"]
      }
EOT;
  }
}

class user  extends Table {

  public $id;
  public $name;
  public $forename;
  public $imelPro;
  public $imelPerso;
  public $mobile;
  public $cost;
  public $password;
  public $noUniv;
  public $logOn;

  public function __construct(){
    parent::__construct(func_get_args());
   }
  public function __toString(){
    return $this->id . '->[' . $this->name . ' ' . $this->forename . 
           ', @' .$this->imelPro . ',' . $this->imelPerso . 
           ', &#9742;' .$this->mobile . 
           ', €' . $this->cost .
           ', &copy;' . $this->password . ']';
  }
}

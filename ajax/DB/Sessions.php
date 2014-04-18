<?php
if (!defined('AUTOLOAD')) require_once('../autoload.php');

class Sessions extends Database {

  //------------------------------------------------------------------ Constants
  //sessions du passé/futur
  const TIME_PAST   = 1;
  const TIME_FUTURE = 2;
  const TIME_BOTH   = 3;

  //sessions saisies ou non
  const RECCORD_TRUE  = 1;
  const RECCORD_FALSE = 2;
  const RECCORD_BOTH  = 3;

  //----------------------------------------------------------------- Properties
  private $sessionID = null;
  private $userID    = null;
  private $projectID = null;
  private $groupID   = null;

  private $timeStart = null;
  private $timeStop  = null;
  
  //--------------------------------------------------------------------- Public
  public function __construct(){
    parent::__construct();

    $this->filters = (object) array(
      'time'    => self::TIME_BOTH,
      'reccord' => self::RECCORD_BOTH);
  }

  public function read(){
    if (!is_null($this->sessionID))
      return $this->readBySessionID();
      
    else if (!is_null($this->userID))
      return $this->readByUserID();
      
    else if (!is_null($this->projectID))
      return $this->readByProjectID();
      
    else if (!is_null($this->groupID))
      return $this->readByGroupID();

    else if (!is_null($this->timeStart) && !is_null($this->timeStop))
      return $this->readIDwithDatas();
      
    else
      return $this->readAll();
  }
  public function create(){
    $this->checkParameter_Create();

    $sessionID = $this->request
      ->anotherOne (Sql::REQUEST_SECURE)
      ->into  ('sessions')
      ->values(new Value('Tstart', $this->timeStart->format('U')),
               new Value('Tend',   $this->timeStop->format('U')))
      ->insert();
      
    return $sessionID;
  }
  public function update(){
    self::ErrorBecauseNotDesigned();
    
    return $this;
  }
  public function delete(){
    if (!is_null($this->sessionID))
      return $this->deleteBySessionID();
      
    else
      return $this->deleteAll();
      
    return $this;
  }
  
  public function setTimeStart($timeStart){
    if (is_object($timeStart) && get_class($timeStart) == 'DateTime')
      $this->timeStart = $timeStart;
    else
      throw (new DomainException('Excepted DateTime object'));
    return $this;
  }
  public function setTimeStop($timeStop){
    if (is_object($timeStop) && get_class($timeStop) == 'DateTime')
      $this->timeStop = $timeStop;
    else
      throw (new DomainException('Excepted DateTime object'));
    return $this;
  }
  public function setReccord($reccord){
    if (is_bool($reccord))
      $this->reccord = $reccord;
    else
      throw (new DomainException('Expected boolean'));
    return $this;
  }

  public function setUserID($userID){
    $this->ErrorIfParameterNotValid(!is_numeric($userID));

    $this->userID = intval($userID);
    return $this;
  }
  public function setSessionID($sessionID){
    $this->ErrorIfParameterNotValid(!is_numeric($sessionID));

    $this->sessionID = intval($sessionID);
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
      case 'time':
        $this->setFilterTime($filterValue);
        break;
      case 'reccord':
        $this->setFilterReccord($filterValue);
        break;
    }
    return $this;
  }

  //-------------------------------------------------------------------- Private
  private function checkParameter_Create(){
    #Données non précisées
    $this->ErrorIfParameterNotValid(
      is_null($this->timeStart) ||
      is_null($this->timeStop));

    #Fin avant début
    $difference = $this->timeStop->diff($this->timeStart);
    $this->ErrorIfParameterNotValid(
      $difference->invert === 0);

    #Déjà dans la base
    $this->ErrorIfParameterNotValid(
     !is_null($this->readIDwithDatas()));
  }

  private function filter(){
    if (!is_null($this->sessionID)) return;
    
    if ($this->filters->time !== self::TIME_BOTH)
      $this->filterTime();
      
    else if ($this->filters->reccord !== self::RECCORD_BOTH)
      $this->filterReccord();
  }
  private function filterTime(){
    switch($this->filters->time){
      case self::TIME_PAST:
        $this->request->where('Tstart < ' . strtotime('now'));
        break;
        
      case self::TIME_FUTURE:
        $this->request->where('Tend > ' . strtotime('now'));
        break;
    }
  }
  private function filterReccord(){
    if (is_null($this->projectID) &&
        is_null($this->userID) &&
        is_null($this->groupID)) return;
        
    switch($this->filters->reccord){
      case self::RECCORD_FALSE:
        $this->request->where('su.reccord = 0');
        break;
        
      case self::RECCORD_TRUE:
        $this->request->where('su.reccord = 1');
        break;
    }
  }

  private function readIDwithDatas(){
    $sessionID = $this->request
      ->anotherOne (Sql::REQUEST_SECURE)
      ->select('id')
      ->from  ('sessions')
      ->where (new Value('Tstart', $this->timeStart->format('U'), ' = '),
               new Value('Tend',   $this->timeStop->format('U'),   ' = '))
      ->get();

    if (!empty($sessionID))
      return $sessionID[0]['id'];
    else
      return null;    
  }
  private function readBySessionID(){
    self::ErrorBecauseNotDesigned();
  }
  private function readByUserID(){
    $this->request->anotherOne(Sql::REQUEST_SECURE);
    $this->filter();
    
    $sessions = $this->request
      ->select (array('sessions' => 'id as id, Tstart as timeStart, Tend as timeStop'))
      ->from   ('sessions as s')
      ->join   ('session_user as su', 'su.sessionID = s.id')
      ->where  (new value('etdID', $this->userID, '='))
      ->orderBy('Tend ASC')
      ->get    ();

    return self::resultToObject($sessions);
    
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

    $sessions = $this->request
      ->select (array('sessions' => 'id as id, Tstart as timeStart, Tend as timeStop'))
      ->from   ('sessions as s')
      ->orderBy('Tend ASC')
      ->get    ();

    return self::resultToObject($sessions);
  }

  private function deleteAll(){
    $this->request->anotherOne();
    $this->filter();

    $sessions = $this->request
      ->from   ('sessions')
      ->delete ();

    return self::resultToObject($sessions);
  }
  private function deleteBySessionID(){
    $this->request->anotherOne(Sql::REQUEST_SECURE);
    $this->filter();
    
    $nbReccords = $this->request
      ->from   ('sessions')
      ->where  (new value('id', $this->sessionID, '='))
      ->delete ();

    return $nbReccords;
  }

  private function setFilterTime($time){
    if ($time === self::TIME_BOTH ||
        $time === self::TIME_FUTURE ||
        $time === self::TIME_PAST)
      $this->filters->time = $time;
    else
      throw (new DomainException('Excepted TIME_XXX constant'));
    return $this;
  }
  private function setFilterReccord($reccord){
    if ($reccord === self::RECCORD_BOTH ||
        $reccord === self::RECCORD_FALSE ||
        $reccord === self::RECCORD_TRUE)
      $this->filters->reccord = $reccord;
    else
      throw (new DomainException('Excepted RECCORD_XXX constant'));
    return $this;
  }

  //------------------------------------------------------------- Static Private
  static private function resultToObject($sessions){
    if (!is_array($sessions)) return $sessions;
    
    foreach($sessions as $noSession => $session) {
      $sessions[$noSession] = new session($session);
    }
    return $sessions;
  }

  //------------------------------------------------------------- Error Messages
  static protected function getErrorMessages(){
    return <<<EOT
    {"checkParameter_Create" : [
      "Il faut fournir timeStart et timeStop",
      "timeStart avant timeStop",
      "Deja dans la base"]
      }
EOT;
  }
}

class session  extends Table {
  public $id;
  public $timeStart;
  public $timeStop;

  public function __construct(){
    parent::__construct(func_get_args());
   }
  public function __toString(){
    $timeStart = new DateTime('@' . $this->timeStart);
    $timeStop  = new DateTime('@' . $this->timeStop);
    return $this->id . '->[' . $timeStart->format('U (d/m/Y H:i:s)') . ' , ' . $timeStop->format('U (d/m/Y H:i:s)') . ']';
  }
}

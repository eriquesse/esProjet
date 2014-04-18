<?php

class Sessions_tests extends Testunit {
  static private $sessions = array();
  
  static function test_Sessions_new(){
    $refObject = self::newObjectSessions();
    
    #Tableau
    $test = new session(array($refObject->id, $refObject->timeStart, $refObject->timeStop));
    self::assertObjectEgal($test, $refObject);
    self::assertObjectClass($test, 'session');
    
    #Dictionnaire
    $test = new session(array(
      'id'        => $refObject->id,
      'timeStart' => $refObject->timeStart,
      'timeStop'  => $refObject->timeStop));
    self::assertObjectEgal($test, $refObject);
    self::assertObjectClass($test, 'session');
    
    #parametres
    $test = new session($refObject->id, $refObject->timeStart, $refObject->timeStop);
    self::assertObjectEgal($test, $refObject);
    self::assertObjectClass($test, 'session');
  }
  static function test_Sessions_create(){
    $test = new Sessions();
    $test->delete();
    
    self::$sessions['present'] = self::newObjectSessions(0,2);  #Présent
    self::$sessions['futur']   = self::newObjectSessions(24,2);  #Futur
    self::$sessions['past']    = self::newObjectSessions(-24,2); #Passé
    self::assertEqual(self::$sessions['futur']->id, self::$sessions['present']->id + 1);
    self::assertEqual(self::$sessions['past']->id, self::$sessions['present']->id + 2);

    #Doublon
    $test = new Sessions();
    try {
      $result = $test
        ->setTimeStart(self::getNow())
        ->setTimeStop(self::getNow(2))
        ->create();
    } catch (exception $e) {
      $error = true;
      self::assertErrorAppear($e->getMessage(), 'Deja dans la base');
    }
    self::assertNoError(isset($error), 'Deja dans la base');
  }
  static function test_Sessions_read() {
    #Futur
    $test = new Sessions();
    $result = $test
      ->setFilter('time', Sessions::TIME_FUTURE)
      ->read();
    self::assertEqual(count($result), 2);
    self::assertObjectClass($result[0], 'session');
    self::assertObjectEgal($result[0], self::$sessions['present']);

    #Passé
    $test = new Sessions();
    $result = $test
      ->setFilter('time', Sessions::TIME_PAST)
      ->read();

    self::assertEqual(count($result), 1);
    self::assertObjectClass($result[0], 'session');
    self::assertObjectEgal($result[0], self::$sessions['past']);

    #Toutes
    $test = new Sessions();
    $result = $test
      ->read();

    self::assertEqual(count($result), 3);
    self::assertObjectClass($result[0], 'session');

  }
  static function test_Sessions_delete(){
    #sessionID
    $test = new Sessions();
    $nbReccords = $test
      ->setSessionID(self::$sessions['present']->id)
      ->delete();
    self::assertEqual($nbReccords, 1);
    self::$sessions['present'] = self::newObjectSessions(0,2);

    #with Data -> Futur
    $test = new Sessions();
    $nbReccords = $test
      ->setFilter('time', Sessions::TIME_FUTURE)
      ->delete();
    self::assertEqual($nbReccords, 2);
    self::$sessions['present'] = self::newObjectSessions(0,2);
    self::$sessions['futur'] = self::newObjectSessions(24,2);

    #with Data -> Passé
    $test = new Sessions();
    $nbReccords = $test
      ->setFilter('time', Sessions::TIME_PAST)
      ->delete();
    self::assertEqual($nbReccords, 1);

    #All
    $test = new Sessions();
    $nbReccords = $test
      ->delete();
    self::assertEqual($nbReccords, 2);
  }

  static private function getNow($nbHours = 0){
    $now = new DateTime('now');
    $now = $now->getTimestamp();
    return new DateTime('@' . ($now + $nbHours * 3600));
  }
  static private function newObjectSessions($hourStart = 0, $delay = 2){
    
    $timeStart = self::getNow($hourStart);
    $timeStop  = self::getNow($hourStart + $delay);
    
    $test      = new Sessions();
    $sessionID = $test
      ->setTimeStart($timeStart)
      ->setTimeStop($timeStop)
      ->create();
      
    return (object) array(
      'id'        => $sessionID,
      'timeStart' => $timeStart->getTimeStamp(),
      'timeStop'  => $timeStop->getTimeStamp());
  }
}
Sessions_tests::run();

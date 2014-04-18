<?php

class Users_tests extends Testunit {
  static private $users = array();
  
  static function test_Users_new(){
    $refObject = self::newObjectUsers(1);
    
    #Tableau dans l'ordre aphabetique
    $user = new user(array(
      $refObject->cost,
      $refObject->forename,
      $refObject->id,
      $refObject->imelPerso,
      $refObject->imelPro,
      $refObject->logOn,
      $refObject->mobile,
      $refObject->name,
      $refObject->noUniv,
      $refObject->password));
    self::assertObjectEgal($user, $refObject);
    self::assertObjectClass($user, 'user');
    
/*    #Dictionnaire
    $user = new user(array(
      'name'      => $refObject->name,
      'forename'   => $refObject->forename,
      'imelPro'   => $refObject->imelPro,
      'imelPerso' => $refObject->imelPerso,
      'mobile'    => $refObject->mobile,
      'cost'      => $refObject->cost,
      'noUniv'    => $refObject->noUniv,
      'password'  => $refObject->password));
    self::assertObjectEgal($user, $refObject);
    self::assertObjectClass($user, 'user');
    
    #parametres
    $user = new user(
      $refObject->name,
      $refObject->forename,
      $refObject->imelPro,
      $refObject->imelPerso,
      $refObject->mobile,
      $refObject->cost,
      $refObject->noUniv,
      $refObject->password);
    self::assertObjectEgal($user, $refObject);
    self::assertObjectClass($user, 'user');
    
    #parametres (au minimum)
    $user = new user(
      $refObject->id,
      $refObject->name,
      $refObject->forename,
      $refObject->noUniv);
    self::assertObjectEgal($user, $refObject);
    self::assertObjectClass($user, 'user');*/
  }
  /*
  static function test_Users_create(){
    $user = new Users();
    $user->delete();
    
    self::$users['present'] = self::newObjectUsers(0,2);  #Présent
    self::$users['futur']   = self::newObjectUsers(24,2);  #Futur
    self::$users['past']    = self::newObjectUsers(-24,2); #Passé
    self::assertEqual(self::$users['futur']->id, self::$users['present']->id + 1);
    self::assertEqual(self::$users['past']->id, self::$users['present']->id + 2);

    #Doublon
    $user = new Users();
    try {
      $result = $user
        ->setTimeStart(self::getNow())
        ->setTimeStop(self::getNow(2))
        ->create();
    } catch (exception $e) {
      $error = true;
      self::assertErrorAppear($e->getMessage(), 'Deja dans la base');
    }
    self::assertNoError(isset($error), 'Deja dans la base');
  }
  static function test_Users_read() {
    #Futur
    $user = new Users();
    $result = $user
      ->setFilter('time', Users::TIME_FUTURE)
      ->read();
    self::assertEqual(count($result), 2);
    self::assertObjectClass($result[0], 'user');
    self::assertObjectEgal($result[0], self::$users['present']);

    #Passé
    $user = new Users();
    $result = $user
      ->setFilter('time', Users::TIME_PAST)
      ->read();

    self::assertEqual(count($result), 1);
    self::assertObjectClass($result[0], 'user');
    self::assertObjectEgal($result[0], self::$users['past']);

    #Toutes
    $user = new Users();
    $result = $user
      ->read();

    self::assertEqual(count($result), 3);
    self::assertObjectClass($result[0], 'user');

  }*/
  static function test_Users_delete(){
/*    #userID
    $user = new Users();
    $nbReccords = $user
      ->setUserID(1)
      ->delete();
    self::assertEqual($nbReccords, 1);
    #self::$users[1] = self::newObjectUsers(1);

    #with Data -> Futur
    $user = new Users();
    $nbReccords = $user
      ->setFilter('defined', Users::DEFINE_IMELPERSO)
      ->delete();
    self::assertEqual($nbReccords, 2);
    #self::$users[1] = self::newObjectUsers(1);
    #self::$users[2] = self::newObjectUsers(2);*/

    #All
    $user = new Users();
    $nbReccords = $user
      ->delete();
    self::assertEqual($nbReccords, 1);
  }

  static private function newObjectUsers($noUser = 1){
    if ($noUser < 1 OR $noUser > 4) die('noUser entre 1 et 4');
    
    $users = array(
      1 => array("AGENEAU", "Emile",    'emile.ageneau@univ-nantes.fr',
      'emile@ageneau.fr', '0611111111', '1000',
      'E1411111', 'motDePasse', strtotime('now')),
      2 => array("ATTIMONT", "Benoit", 'benoit.attimont@univ-nantes.fr',
      'benoit@attimont.fr', '0622222222', '2000',
      'E1422222', 'motDePasse', strtotime('today')),
      3 => array("AVRIL", "Aurelien",   'aurelien.avril@univ-nantes.fr',
      'aurelien@avril.fr', '0633333333', '3000',
      'E1433333', 'motDePasse', strtotime('tomorow')),
      4 => array("BELLOIR", "Thomas",   'thomas.belloir@univ-nantes.fr',
      'thomas@belloir.fr', '0644444444', '4000',
      'E1444444', 'motDePasse', strtotime('yesterday')));
      
    $user   = new Users();
    $userID = $user
      ->setName      ($users[$noUser][0])
      ->setForename  ($users[$noUser][1])
      ->setImelPro   ($users[$noUser][2])
      ->setImelPerso ($users[$noUser][3])
      ->setCost      ($users[$noUser][4])
      ->setCost      ($users[$noUser][5])
      ->setNoUniv    ($users[$noUser][6])
      ->setPassword  ($users[$noUser][7])
      ->setLogOn     ($users[$noUser][8])
      ->create       ();
      
    return (object) array(
      'id'        => $userID,
      'name'      => $users[$noUser][0],
      'forename'  => $users[$noUser][1],
      'imelPro'   => $users[$noUser][2],
      'imelPerso' => $users[$noUser][3],
      'mobile'    => $users[$noUser][4],
      'cost'      => $users[$noUser][5],
      'noUniv'    => $users[$noUser][6],
      'password'  => $users[$noUser][7],
      'logOn'     => $users[$noUser][8]);
  }
}
Users_tests::run();

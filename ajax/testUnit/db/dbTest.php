<?php
spl_autoload_register(function($class) {include '/var/www/esProjet/ajax/' . $class . '.php'; });
require_once 'PHPUnit/Autoload.php';
require_once '../DB.php';

class dbTest extends PHPUnit_Framework_TestCase {
  static $path;
  
  protected function setUp() {
    self::$path = '/var/www/esProjet/ajax/phpunit/db/';
  }
  public function test_Create() {
    @unlink(self::$path . 'db.sqlite');
   
    $this->assertEquals(array(), DB::showTables());

    DB::createTable(
      'sessions',
      array(
        'id'    => 'INTEGER PRIMARY KEY',
        'date'  => 'DATETIME',
        'start' => 'CHAR',
        'end'   => 'CHAR',   
        ));
  }
}

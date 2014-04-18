<?php

spl_autoload_register(function($class) {
  $tmp = '/var/www/esProjet/ajax/' . $class . '.php';

  if (file_exists($tmp))
    include $tmp;
  else  {
    $tmp = '/var/www/esProjet/ajax/DB/' . $class . '.php';

  if (file_exists($tmp))
    include $tmp;
  }
});

require_once 'PHPUnit/Autoload.php';

class iniTest extends PHPUnit_Framework_TestCase {
  static $path;
  
  protected function setUp() {
    self::$path = '/var/www/esProjet/ajax/phpunit/Ini/';
    
    @unlink(self::$path.'testNo.ini');
    @touch(self::$path . 'testNo.ini');
    $this->iniNo = new Ini(self::$path . 'testNo.ini');

    @unlink(self::$path . 'testOk.ini');
    @copy(self::$path . 'testOk-mem.ini',
          self::$path . 'testOk.ini');
   
    $this->iniOk = new Ini(self::$path . 'testOk.ini');
  }

  /**
  @expectedException PHPUnit_Framework_Error
  */
  public function test_Erreur() {
    $ini = new Ini();
  }
  public function test_Extension_Incorrete() {
    $ini = new Ini('toto.abc');
    $this->assertEquals('/var/www/esProjet/ajax/toto.ini', $ini->getFilename());
  }
  public function test_Chemin_Relatif() {
    $ini = new Ini('./toto.abc');
    $this->assertEquals('/var/www/esProjet/ajax/toto.ini', $ini->getFilename());
  }
  public function test_Chemin_Absolu() {
    $ini = new Ini('ajax/toto.abc');
    $this->assertEquals('ajax/toto.ini', $ini->getFilename());
  }
  public function test_No_Extension() {
    $ini = new Ini('toto');
    $this->assertEquals('/var/www/esProjet/ajax/toto.ini', $ini->getFilename());
  }
  public function test_Fichier_Inexistant() {
    @unlink(self::$path . 'toto.ini');
    $ini = new Ini(self::$path . 'toto');
    $this->assertFalse($ini->getIsReplace());
  }
  public function test_Fichier_Inexistant_Commentaire() {
    $datas = array();
    $text  = ';Fichier de configuration';

    @unlink(self::$path . 'toto.ini');
    $ini = new Ini(self::$path . 'toto', 'Commentaire');
    
    $this->assertFalse($ini->getIsReplace());
    $this->assertEquals($datas, $ini->getDatas());
    $this->assertEquals($text,  $ini->getText());

  }
  public function test_Fichier_Existant_Vide() {
    $datas = array();
    $text  = ';Fichier de configuration';
        
    $this->assertTrue($this->iniNo->getIsReplace());
    $this->assertEquals($datas, $this->iniNo->getDatas());
    $this->assertEquals($text,  $this->iniNo->getText());
  }
  public function test_Fichier_Existant_Correct() {
    $datas = array (
      'première Section' => array (
          'item1' => '1',
          'item2' => 'deux',
          'item3' => 'trois, 3',
          'item4' => '12.56'),
        'deuxième Section' => array (
          'item1' => array (
            0 => '1',
            1 => '2',
            2 => '3',
            3 => '4')));

    $text  = ';Configuration correcte' . "\n";

    $this->assertTrue($this->iniOk->getIsReplace());
    $this->assertEquals($datas, $this->iniOk->getDatas());
    $this->assertEquals($text,  $this->iniOk->getText());
  }

  public function test_existe() {
    $this->assertTrue($this->iniOk->isKeyExist('première Section','item1'));
    $this->assertEquals('1', $this->iniOk->get('première Section','item1'));
  }
  public function test_ajoute() {
    $datas = array (
      'première Section' => array (
          'item1' => '1',
          'item2' => 'deux',
          'item3' => 'trois, 3',
          'item4' => '12.56',
          'item5' => '5'),
        'deuxième Section' => array (
          'item1' => array (
            0 => '1',
            1 => '2',
            2 => '3',
            3 => '4')),
        'troisième' => array (
          'item1' => array(0 => '1', 1 => '2'),
          'item2' => '2',
          'item3' => '3',
          'item4' => array(0 => '1', 1 => '2')));

    $this->iniOk->addValues('première Section', array('item5' => 5));
    $this->iniOk->addValues('troisième', array('item1' => 1,'item2' => 2,'item3' => 3));
    $this->iniOk->addValues('troisième', array('item1' => 2));
    $this->iniOk->addValues('troisième', array('item4' => array(1,2)));
//var_export($this->iniOk->getDatas());
    $this->assertEquals($datas, $this->iniOk->getDatas());
  }
  public function test_supprime() {
    $this->iniOk = new Ini(self::$path . 'testOk.ini');
    
    $datas = array (
      'première Section' => array (
          'item1' => '1'));
            
    $this->iniOk->delValues('deuxième Section');
    $this->iniOk->delValues('première Section', 'item3');
    $this->iniOk->delValues('première Section', array('item2', 'item4'));

    $this->assertEquals($datas, $this->iniOk->getDatas());
  }
  /**
  @expectedException Exception
  */
  public function test_update_erreur1() {
    $this->iniOk = new Ini(self::$path . 'testOk.ini');
            
    $this->iniOk->updateValues('Section', array('item1' => 2));
  }
  /**
  @expectedException Exception
  */
  public function test_update_erreur2() {
    $this->iniOk = new Ini(self::$path . 'testOk.ini');
            
    $this->iniOk->updateValues('deuxième Section', array('item9' => 2));
  }
  /**
  @expectedException Exception
  */
  public function test_update_erreur3() {
    $this->iniOk = new Ini(self::$path . 'testOk.ini');
            
    $this->iniOk->updateValues('deuxième Section', array('item1' => 2));
  }

  public function test_update_Reussi() {
    $this->iniOk = new Ini(self::$path . 'testOk.ini');
    
    $datas = array (
      'première Section' => array (
          'item1' => 'un',
          'item2' => '2',
          'item3' => '3',
          'item4' => '12.56'),
        'deuxième Section' => array (
          'item1' => array (
            0 => '1',
            1 => '2',
            2 => '3',
            3 => '4')));
            
    $this->iniOk->updateValues(
      'première Section',
      array('item1' => 'un'));
    $this->iniOk->updateValues(
      'première Section',
      array(
        array('item2' => '2'),
        array('item3' => '3')));
//var_export($this->iniOk->getDatas());
    $this->assertEquals($datas, $this->iniOk->getDatas());
  } 
  public function test_update_Fichier() {
    $this->iniOk = new Ini(self::$path . 'testOk.ini');
            
    $this->iniOk->updateValues(
      'première Section',
      array('item1' => 'un'));
    $this->iniOk->updateValues(
      'première Section',
      array(
        array('item2' => '2'),
        array('item3' => '3')));
    $this->iniOk->write();
    
    similar_text(
      file_get_contents(
        self::$path . 'testOk-write.ini'),
      file_get_contents(
        self::$path . 'testOk.ini'),
      $resultat);

    $this->assertGreaterThan(99, $resultat);
  } 
}

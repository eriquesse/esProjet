<?php

class Testunit {
  static private $codeHtml = "";
  static private $stateTests;
  static private $errorMessages;
  static private $datasTransmittedAndReceived;
  static private $methodsTested = array();
  static private $explanationTest = array();
  static private $statistics = array();
  static private $className;
  
  //--------------------------------------------------------------------- Public
  static public function start(){
    define('testUnit', true);
    
    self::initTitleMessage();
  }
  static public function stop(){
    echo self::header() . self::$codeHtml . self::end();
  }
  static public function run(){
    self::$stateTests = '';
    self::$statistics = array('fail'=>0, 'ok'=>0);
    self::$methodsTested = array();
    
    $class           = get_called_class(); 
    self::$className = str_replace('_tests', '', $class);
    
    $reflection  = new ReflectionClass($class);
    $methodsList = $reflection->getMethods(ReflectionMethod::IS_STATIC);

    foreach($methodsList as $item)
      if (substr($item->name, 0, 5) === 'test_') {
        $method = $item->name;
        if (!in_array($method, self::$methodsTested))
          self::$methodsTested [] = $method;
        $class::$method();
      }
    self::setCorps();
  }

  static public function assertObjectEgal($object1, $object2){
    self::setDatas(json_encode($object2, true), json_encode($object1, true));

    foreach($object1 as $property => $value)
      if (!isset($object2->$property) ||
          $object2->$property != $value) {
#        echo ('A ' . $property . '[' . $value . '] , O [' . $object2->$property .']' );
#        debug($object1, $object2);
        return self::result(false);
      }
        
    foreach($object2 as $property => $value)
      if (!isset($object1->$property) ||
          $object1->$property != $value) {
#        echo ('(2) ' . $property . '[' . $value . '] , [' . $object1->$property .']' );
        return self::result(false);
      }
        
    self::result(true);
  }
  static public function assertObjectClass($object, $class){
    self::setDatas($class, get_class($object));
    
    self::result($object instanceof $class);
  }

  static public function assertErrorAppear($messageObtained, $messageRequired){
    self::setDatas($messageRequired, $messageObtained);

    self::result($messageRequired == $messageObtained);
  }
  static public function assertNoError($test, $messageRequired){
    self::setDatas($messageRequired, 'Pas d\'erreur');

    self::result($test);
  }

  static public function assertEqual($obtained, $required){
    self::setDatas($obtained, $required);

    self::result($obtained == $required);
  }
  static public function assertStrictEqual($obtained, $required){
    self::setDatas($obtained, $required);

    self::result($obtained === $required);
  }
  
  //-------------------------------------------------------------------- Private
  static private function result($test){
    if ($test === true) {
      self::$stateTests .= '<span class="ok">.</span>';
      self::$statistics['ok']++;
    } else {
      self::$stateTests .= '<span class="fail">F</span>';
      self::$errorMessages[] = self::getMessage();
      self::$statistics['fail']++;
    }
  }
  static private function getMessage(){
    $tmp = debug_backtrace();
    $typeTest   = $tmp[2]['function'];
    $nameMethod = str_replace('test_' . self::$className . '_', '', $tmp[3]['function']);
    $noLine     = $tmp[2]['line'];

    return
      $nameMethod . ' (' . $noLine . ') : <b>' . self::$explanationTest->$typeTest . '</b> ' .
      '<span class="required">' . self::$datasTransmittedAndReceived['required'] . '</span>' .
      '<span class="obtained">' . self::$datasTransmittedAndReceived['obtained'] . '</span>';
  }
  static private function setDatas($required, $obtained){
    self::$datasTransmittedAndReceived = array(
    'required' => $required,
    'obtained' => $obtained);
   }   

  static private function header(){
    return <<<EOT
<html>
  <head>
    <style>
      .fail     { background-color : #F88; }
      .ok       { background-color : #8F8; }
      .required { background-color : #8F8; }
      .obtained { background-color : #F88; }
      .methodsTested  { background-color : #DDF; }
      .errors   { background-color : #FDD; }
      .fail, .ok           { padding: 1px 1px; margin: 0 0; }
      .required, .obtained { padding: 2px 5px; margin: 0 2px;}
      .errors, .methodsTested    { padding: 2px 20px; margin: 0 2px;}
      .errors li, .methodsTested li { list-style-type: square }
      h3 { margin: 10px 0 0 5px; border-bottom: solid #000 1px;}
      h1 { margin: 2px 0 0 0; border-top: solid 2px #888;}
      pre { margin: 0 0 5px 0;}
    </style>
  </head>
  <body>
EOT;
}
  static private function end(){
    return <<<EOT
    </body>
</html>
EOT;
  }
  static private function setCorps(){
    $codeHtml = <<<EOT
    <h1>£class£</h1>
    <pre>£test£</pre>
    £errors£
    <h3>Methodes</h1>
    <ul class="methodsTested">£methodsTested£</ul>
    <p>Réussis : £ok£, Echec : £fail£</p>
EOT;

    if (empty(self::$errorMessages))
      $erreurs = '';
    else
      $erreurs = '<h3>Erreurs</h1>
      <ul class="errors"><li>' .
      implode("</li>\n<li>", self::$errorMessages) .
      '</li></ul>';

    $methods = array();
    foreach(self::$methodsTested as $method)
      $methods[] = str_replace('test_' . self::$className . '_', '', $method);
      
    $variables = array(
      'test'  => self::$stateTests,
      'ok'    => self::$statistics['ok'],
      'fail'  => self::$statistics['fail'],
      'class' => self::$className,
      'methodsTested' => '<li>' . implode("</li>\n<li>", $methods) . '</li>',
      'errors'        => $erreurs
    );
      
    foreach($variables as $variable => $text)
      $codeHtml = str_replace('£' . $variable . '£', $text, $codeHtml);

    self::$codeHtml .= $codeHtml;
  }
  static function initTitleMessage(){
    self::$explanationTest = json_decode(
<<<EOT
{
  "assertObjectEgal"  : "Objets différents",
  "assertObjectClass" : "Classe inatendue",
  "assertErrorAppear" : "Erreur inatendue",
  "assertNoError"     : "Erreur non présente",
  "assertEqual"       : "Valeurs différentes",
  "assertStrictEqual" : "Valeurs strictement différentes"
}
EOT
);
  }
}

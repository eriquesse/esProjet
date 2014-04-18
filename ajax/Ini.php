<?php

class Ini {
  private $text;
  private $filename;
  private $isReplace;
  
  static public $error;
        private $datas;
 
  public function __construct($filename, $comment = null) {

    //Enlever extension et mettre .ini
    $posExt = strrpos($filename,'.');
    if ($posExt !== false)
      $filename = substr($filename, 0, $posExt) . '.ini';
    else
      $filename .= '.ini';
      
    //Ajout du chemin absolu si omis
    if (dirname($filename) == '' || dirname($filename) == '.')
      $this->filename  = dirname(__file__) . '/' . basename($filename);
    else
      $this->filename  = $filename;

    //Remplacer si existant
    $this->isReplace = file_exists($this->filename);

    //Lecture ou initialisation
    if ($this->isReplace) {
      $this->datas = parse_ini_file($this->filename, true);
      $this->getComment();

    } else {
      $this->datas = array();
      $this->text  = (!is_null($comment)) ? ';Fichier de configuration' : ';' . $comment;
    }
    
    self::initClass();
  }
  public function write() {

    //gestion du fichier .bak
    if ($this->isReplace) {
      $filenameBak = str_replace('.ini', '.bak', $this->filename);
      if (file_exists($filenameBak)) {
        @unlink($filenameBak);
      }
      @rename($this->filename, $filenameBak);
    }

    $fichier = fopen($this->filename, 'w');
    Error::showIfFalse($fichier, $this->filename);
    
    $resultat = fwrite($fichier, $this->getTextIni());
    Error::showIfFalse($resultat, $this->filename);
    
    fclose($fichier);
  }

  public function addValues($section, $values) {
    Error::showIfFalse(is_array($values), $this->filename);
      
    foreach ($values as $key => $value) {
      if (is_array($value)) {
        foreach($values as $item){
          $this->addValue($section, $key, $item) ;
        }
        
      } else {
        $this->addValue($section, $key, $value) ;
      }
    }
  }
  public function delValues($section, $keys = null){
    //Section entière
    if (is_null($keys)){
      unset($this->datas[$section]);

    //Quelques valeurs ou une seule
    } else {
      if (!is_array($keys)) {
        $keys = array($keys);
      }
      foreach($keys as $key) {
        unset($this->datas[$section][$key]);
      }
    }
  }
  public function upDateValues($section, $values){
    Error::showIfFalse(is_array($values));
      
    foreach ($values as $key => $value) {
      if (is_array($value)) {
        foreach($value as $key => $item){
          $this->upDateValue($section, $key, $item) ;
        }
        
      } else {
        $this->upDateValue($section, $key, $value) ;
      }
    }
  }

  public function isKeyExist($section, $key = null){
    if (is_null($key))
      return isset($this->datas[$section]);
    else
      return (isset($this->datas[$section]) && isset($this->datas[$section][$key]));
  }
  public function get($section, $key = null){
    if (!$this->isKeyExist($section, $key))
      return null;
    else {
      if (is_null($key))
        return $this->datas[$section];
      else
        return $this->datas[$section][$key];
    }
  }
  
  private function addValue($section, $key, $value) {
    if (!array_key_exists($section, $this->datas)) {
      $this->datas[$section] = array();
    }

    //Type de valeur autorisée
    if (is_string($value) OR is_double($value) OR is_int($value)) {

      //Déjà un item -> série
      if (isset($this->datas[$section][$key])) {
        
        if(!is_array($this->datas[$section][$key])) {
            $this->datas[$section][$key] = array($this->datas[$section][$key]);
        }
        $this->datas[$section][$key][] = $value;
        
      } else {
        $this->datas[$section][$key] = $value;
      }

    //Tableau de valeur (un seul niveau autorisé)
    } else if (is_array($value) && isset($value[0]))  {
      foreach ($value as $item) {
        $this->addValue($section, $key , $item);
      }

    //Type de valeur interdite
    }  else {
      Error::show(gettype($value));
    }
  }
  private function upDateValue($section, $key, $value) {

    Error::showIfFalse(array_key_exists($section, $this->datas));
    Error::showIfFalse(array_key_exists($key, $this->datas[$section]));
    Error::showIfTrue (is_array($this->datas[$section][$key]));
    
    $this->datas[$section][$key] = $value;
  }

  private function getComment(){
    $lines      = file($this->filename);
    $this->text = '';

    if (!empty($lines)) {
      foreach($lines as $line){
        if ($line[0] == ';')
          $this->text .= trim($line) . "\n";
        else
          break;
      }

    } else {
      $this->text = ';Fichier de configuration';
    }
  }
  private function getTextIni(){
    foreach($this->datas as $section => $data){
      $this->text .= "\n" . '[' . $section . ']';
      foreach($data as $key => $value){
        if (is_array($value)){
          foreach($value as $item){
            $this->text .= "\n" . $key . '[]="' . $item .'"';
          }
        } else {
          $this->text .= "\n" . $key . '="' . $value .'"';
        }
      }
    }
    return $this->text;
  }

  static protected function initClass() {
    Error::initClass('{
      "title"    : "Fichier ini",
      "messages" : {
        "upDateValues" : [ "Chaque donnée est fournie array(key=>value)" ],
        "addValues"    : [ "Chaque donnée est fournie array(key=>value)" ],
        "upDateValue"  : [
          "La section [_data_] n\'existe pas",
          "La clé [_data_] n\'existe pas",
          "Impossible de modifier les clés *tableau*" ],
        "write"  : [
          "Impossible d\'ouvrir en écriture le fichier [_data_]",
          "Impossible de modifier le fichier [_data_]" ],
        "addValue"   : [ "Le type de donnée [_data_] n\'est pas supporté dans les fichiers ini." ]
      }
    }');
  }

  //--------------------------------------------- Requis par les Tests Unitaires
  public function getText() { return $this->text;}
  public function getDatas() { return $this->datas;}
  public function getFilename() { return $this->filename;}
  public function getIsReplace() { return $this->isReplace;}
}

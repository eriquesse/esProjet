<?php

class Sql {
  private $datas;
  private $command;
  private $secureRequest;

  static public  $error = null;
  
  const SELECT = 1;
  const INSERT = 2;
  const DELETE = 3;
  
  const REQUEST_SECURE = 1;
  const REQUEST_NORMAL = 2;
  
  function __construct($secureRequest = self::REQUEST_NORMAL){
    $this->datas         = array();
    $this->secureRequest = $secureRequest;

    if (is_null(self::$error)) {
      self::initClass();
      self::$error->title = 'SQL';
    }
  }

  public function anotherOne($secureRequest = self::REQUEST_NORMAL){
    $this->datas         = array();
    $this->secureRequest = $secureRequest;

    return $this;
  }

  public function select ($fieldsSelect='*'){
    $this->command = self::SELECT;
    
    if (!$this->isParameterDefined('fieldsSelect'))
      $this->datas['fieldsSelect'] = array();

    if (is_string($fieldsSelect))
      $this->datas['fieldsSelect'][] = $fieldsSelect;
      
    else if (is_array($fieldsSelect))
      $this->datas['fieldsSelect'] = array_merge(
        $this->datas['fieldsSelect'],
        $this->getFieldsSelectComplex($fieldsSelect));
      
    return $this;
  }
  public function join   ($table, $condition){
    $this->from($table)
          ->where($condition);

    return $this;
  }
  public function get    (){
      
    try {
      if ($this->secureRequest === self::REQUEST_SECURE) {
        $db = DB::prepare((string)$this);
        if ($db->execute($this->datas['values']) === true)
          $return = $db->fetchAll(PDO::FETCH_ASSOC);
        else
          Error::show();
   
      } else {
        $return = DB::query($this.'')->fetchAll(PDO::FETCH_ASSOC);
      }
      
    } catch (Exception $e) {
      debug($this, $this.'', $e); die();
    }
 
    return $return;
  }

  public function into  ($table){
    if (!$this->isParameterDefined('table'))
      $this->datas['table'] = $table;
    else
      Error::show('Impossible d\'inserer des valeurs dans 2 tables en meme temps');

    return $this;
  }
  public function values(){
    if (!$this->isParameterDefined('values')) {
      $this->datas['fieldNames'] = array();
      $this->datas['values']     = array();
    }
    
    $reccords = func_get_args();

    if (!is_array($reccords[0])) //Au cas où un tableau de tableau est passé
      $reccords = array($reccords);
    else if (is_array($reccords[0][0])) //Au cas où un tableau de tableau est passé
      $reccords = $reccords[0];
      
    $codeFieldMemorized = false;
    
    foreach($reccords as $reccord) {
    $values = array();
      foreach($reccord as $field) {
        if (!is_object($field) || !($field instanceof Value))
          Error::show();
          
        $codeFieldName = ':' . $field->key;

        if (!$codeFieldMemorized &&
            !in_array($field->key, $this->datas['fieldNames']))
          $this->datas['fieldNames'][] = $field->key;

        $values[$codeFieldName] = is_null($field->value) ? 'null' : $field->value;         
      }
      
      $this->datas['values'][] = $values;

      if (!$codeFieldMemorized)
        $codeFieldMemorized = true;
    }

    return $this;
  }
  public function insert(){
    $this->command = self::INSERT;

    try {
      $db = DB::prepare((string)$this);
      foreach($this->datas['values'] as $values)
        if ($db->execute($values) !== true)
          Error::show();
      
    } catch (Exception $e) {
      debug($this, $this.'', $e); die();
    }

    return DB::lastInsertId();
  }

  public function delete(){
    $this->command = self::DELETE;

    try {
      if (array_key_exists('values', $this->datas)) {
        $db         = DB::prepare((string)$this);
        $db->execute($this->datas['values']);
        $nbReccords = $db->rowCount();
      } else
        $nbReccords = DB::query((string)$this)->rowCount();
        
      if ($nbReccords === false)
          Error::show();
      
    } catch (Exception $e) {
      debug($this, $this.'', $e); die();
    }

    return intval($nbReccords);
  }

  public function from    ($tables){
    if (!$this->isParameterDefined('tables'))
      $this->datas['tables'] = array();
      
    $this->datas['tables'][] = $tables;

    return $this;
  }
  public function where   (){
    if (!$this->isParameterDefined('conditions')) {
      $this->datas['conditions'] = array();
      $this->datas['values']     = array();
    }
    
    $conditions = func_get_args();
    
    foreach($conditions as $condition){

      //condition securisée
      if (is_object($condition)){
        $codeName = ':' . $condition->key;
        $this->datas['conditions'][] =
          $condition->key .
          $condition->operation .
          $codeName;
        $this->datas['values'][$codeName] = $condition->value;

      //condition non sécurisée
      } else {
        $this->datas['conditions'][] = $condition;
      }
    }
    
    return $this;
  }
  public function whereIn ($fieldName, $values){
    $this->datas['valuesIn'] = $fieldName;
    $this->datas['values']   = $values;
    
    return $this;
  }
  public function orderBy ($sort){
    $this->datas['sort'] = $sort;

    return $this;
  }

  public function __toString(){
    switch ($this->command) {
      case self::SELECT:
        return self::toStringSelect();
        
      case self::INSERT:
        return self::toStringInsert();

      case self::DELETE:
        return self::toStringDelete();
    }
  }
  private function toStringSelect(){
    //valeurs par defaut
    if (!$this->isParameterDefined('fieldsSelect'))
      $fieldsSelect = '*';
    else
      $fieldsSelect = $this->getStringFieldsSelect();
      
    if (!$this->isParameterDefined('conditions')) {
      if($this->isParameterDefined('valuesIn')) {
        $this->secureRequest = self::REQUEST_SECURE;
        $conditions = $this->datas['valuesIn'] .
          ' IN (' . substr(str_repeat("?,", count($this->datas['values'])), 0, -1) . ')';
      } else
        $conditions = array('1');
        
    } else
      $conditions = $this->datas['conditions'];

    if ($this->isParameterDefined('sort'))
      $sort = ' ORDER BY ' . $this->datas['sort'];
    else
      $sort = '';

    return
      'SELECT '   . $fieldsSelect .
      ' FROM '     . implode(', ', $this->datas['tables']) .
      ' WHERE '    . implode(' AND ', $conditions) .
      $sort;
  }

  private function getFieldsSelectComplex($fieldsSelect){
    $string = array();
    
    foreach($fieldsSelect as $table => $fields){
      $fields = array_map('trim', explode(',', $fields));
      foreach($fields as $field){
        $string[] = $table . '.' . $field;
      }
    }
   return $string;
  }
  private function getStringFieldsSelect(){
    if (is_array($this->datas['fieldsSelect'])) {
      $string = implode(', ', $this->datas['fieldsSelect']);
      foreach($this->datas['tables'] as $table){
        $table = array_map('trim', explode(' as ', $table));
        if (count($table) == 2)
          $string = str_replace($table[0] . '.', $table[1].'.', $string);
      }
      return $string;
    } else
      return $this->datas['fieldsSelect'];
  }

  private function toStringInsert(){    
    return
      'INSERT' .
      ' INTO ' . $this->datas['table'] .
      ' (' . $this->getFieldsName() . ')' .
      ' VALUES (' . $this->getcodeField() .')';
  }
  private function getFieldsName(){
    return implode (',', $this->datas['fieldNames']);
  }
  private function getcodeField(){
    return ':' . implode (', :', $this->datas['fieldNames']);
  }

  private function toStringDelete(){
    if (!$this->isParameterDefined('conditions')) {
      if($this->isParameterDefined('valuesIn')) {
        $this->secureRequest = self::REQUEST_SECURE;
        $conditions = $this->datas['valuesIn'] .
          ' IN (' . substr(str_repeat("?,", count($this->datas['values'])), 0, -1) . ')';
      } else
        $conditions = null;
        
    } else
      $conditions = $this->datas['conditions'];

    return
      'DELETE' .
      ' FROM ' . $this->datas['tables'][0] .
      (is_null($conditions) ? '' : (' WHERE ' . implode(' AND ', $conditions)));
  }

  private function isParameterDefined($varName) {
    return array_key_exists($varName, $this->datas);
  }

  static private function initClass() {
    Error::initClass('{
      "messages"    : {
        "values" : [
          "Insert into : utilisation obligatoire des objects Value"],
        "insert" : [
          "Erreur lors de l\'insertion des valeurs"],
        "delete" : [
          "Impossible de supprimer des sessions"]
      }
    }');
  }

  //--------------------------------------------- Requis par les Tests Unitaires
  public function getPath() {
    $tmp = debug_backtrace();
    var_dump($tmp);
    return $this->text;
  }
}

class Value {
  public $key;
  public $value;
  public $operation;
  
  function __construct($fieldName, $value = null, $operation = '='){
    $this->key       = $fieldName;
    $this->value     = $value;
    $this->operation = $operation;
  }
}

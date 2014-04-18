<?php

class Table {
  const CONSTRUCT_ARRAY = 1;
  const CONSTRUCT_ITEM  = 2;
  const CONSTRUCT_DICT  = 3;
  
  //--------------------------------------------------------------------- Public
  public function __construct($parameters){
    $mode       = self::getModeConstruct($parameters);
    $parameters = self::getParameters($parameters);
    $properties = $this->getProperties();

    switch($mode) {
      
      #Un tableau associatif
      case Table::CONSTRUCT_DICT:
        foreach($properties as $property) {
          $this->$property = $parameters[$property];
        }
        break;
        
       #Un tableau
      case Table::CONSTRUCT_ARRAY:
        foreach($properties as $no => $property) {
          $this->$property = $parameters[$no];
        }
        break;
        
     #x parametres
      case Table::CONSTRUCT_ITEM:
#debug($properties, $parameters);
        foreach($properties as $no => $property) {
          $this->$property = $parameters[$no];
        }
        break;
    }
#debug($this->properties, $this->parameters);
  }

  //-------------------------------------------------------------------- Private
  private function getProperties(){
    $tmp        = debug_backtrace();
    $properties = get_object_vars($tmp[1]['object']);
    
    #Supprimer les propriétés de Table
    $propertiesTable = get_class_vars('Table');
    foreach($propertiesTable as $properties => $value)
      unset ($properties[$properties]);

    $properties = array_keys($properties);
    sort($properties);

    return $properties;
   }
  static private function getParameters($parameters){
    if (is_array($parameters[0]))
      return $parameters[0];
    else
      return $parameters;
  }
  static private function getModeConstruct($parameters){
    #2 construteurs possibles
    # - un tableau
    # - les parametres un par un dans l'ordre alphabétique

    if (is_array($parameters[0])) {
      if (isset($parameters[0][0]))
        return self::CONSTRUCT_ARRAY;
      else
        return self::CONSTRUCT_DICT;
    } else
      return self::CONSTRUCT_ITEM;
  }
}

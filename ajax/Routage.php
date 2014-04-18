<?php

if (!defined('AUTOLOAD')) require_once('autoload.php');

define('ROUTE', true);

class Route {
  public $chemin;
  public $commande;
  public $args;
  
  static public $error;

  const DEBUG = true;
  
  function __construct(){
    self::initClass();
    
    $this->setChemin()
          ->setCommande();
          
    Init_database::run();
  }

  public function go(){

    //commande reconnue
    if (in_array($this->commande, func_get_args())) {
      new $this->commande($this);
      
    //Commande inconnue et mode DEBUG -> affichage API
    } else if (self::DEBUG) {
      new infoAPI();
      
    //Commande inconnue -> retour page accueil
    } else {
      $tmp = $_SERVER['SCRIPT_NAME'];
      $tmp = explode('/ajax/', $tmp);
      var_dump('Erreur commande go [' . $this->commande .' : '. $this->chemin .']');
      /* header('location: http://' . $_SERVER['HTTP_HOST'] . '/' . $tmp[0]);*/
      }
  }

  private function setChemin(){
    $tmp = debug_backtrace();

    try {
      $tmp = array_pop($tmp);
      $tmp = $tmp['file'];
      $tmp = explode('/ajax/', str_replace('/index.php', '', $tmp));
      if (count($tmp) == 2)
        $this->chemin = $tmp[1];
      else
        $tmp = 'PHPunit';
    } catch (Exception $e) {
      Error::show(implode('', $tmp));
    }

    return $this;
  }
  private function setCommande(){

    //Commande en GET
    if (count($_GET) > 0) {
      $tmp = $_GET;
      
      reset($tmp);
      $this->commande = key($tmp);
      array_shift($tmp);
      
    //Commande en POST
    } else if (count($_POST) > 0) {
      $tmp = $_POST;

      Error::showIfFalse(array_key_exists('cmd', $tmp), implode('', $tmp));
#var_dump($tmp); die();        
      $this->commande  = $tmp['cmd'];
      unset($tmp['cmd']);
      
    } else
      $tmp = null;
    
    $this->args = $tmp;
  
    return $this;
  }

  static protected function initClass() {
    Error::initClass('{
      "title"    : "Routage",
      "messages" : {
        "setCommande"   : [ "Pas de commande définie [_data_]" ],
        "setChemin"     : [ "Erreur dans le chemin de routage [_data_]" ],
        "getIniFile"    : [ "Nom de fichier indéfini dans fichier config.ini" ],
        "getDBdescriptionTables" : [
          "Nom du fichier contenant la définition des tables indéfini dans fichier config.ini",
          "Fichier de définition des tables introuvable [_data_]" ],
        "getDBdescription" : [ "Définition des tables incorrecte dans fichier [_data_]" ],
        "getDBPopulate"    : [ "Définition des données incorrecte dans fichier [_data_]" ]
      }
    }');
  }
}

function s($text, $nombre, $singulier = '', $pluriel = 's') {
  return  $nombre . ' ' . $text . (($nombre <= 1) ? $singulier : $pluriel);
}

return new Route();

/*
* go
*   defini la liste des classes possibles et instancie celle qui est demandée
*
* Les classes sont cherchées dans les répertoires ajax et ajax/DB
*/

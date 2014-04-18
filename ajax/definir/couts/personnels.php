<?php

#Lecture de la base de données
$personnels = array(
  'Enseignants' => array(
    'id'    => 'Ens',
    'cout'  => 9000,
    'liste' => array(
      'Nicolas' => array('detail' => null),
      'Flavien' => array('detail' => null),
      'Eric'    => array('detail' => null),
      'Patrick' => array('detail' => null),
      )),
 'Technicien qualifié' => array(
    'id'    => 'Qua',
    'cout'  => 5000,
    'liste' => array(
      'Roland'  => array('detail' => 'électronique / informatique'),
      'Fabrice' => array('detail' => 'Menuiserie'),
      'Didier'  => array('detail' => 'Fabrication'),
      'Alain'   => array('detail' => 'Plomberie / soudage aluminium'),
      'Antony'  => array('detail' => 'Électricité'),
      )),
 'Technicien' => array(
    'id'    => 'Tec',
    'cout'  => 3000,
    'liste' => array(
      'Mickaël'  => array('detail' => 'Petits travaux'),
    )));
define('SPE', 'Per');
include 'commun.php';

  rappel('Les frais de personnel représentent le coût <b>réel</b> par heure d\'un membre de l\'encadrement.<br>
    Ces frais comprennent toutes les charges : salariales, patronales, les frais généraux, &hellip;');
  
  colonne('liste', 'init', 'Liste des personnels');
  liste($personnels);
  colonne('liste', 'clore');
  
  colonne('formulaire', 'init', basename(__file__, '.php'));
  foreach($personnels as $titre => $donnees)
    input(MNU.SPE.$donnees['id'], $titre, $donnees['cout']);
  colonne('formulaire', 'clore');

<?php

#Lecture de la base de données
$frais = array(
  'Gestion & énergie' => array(
    'id'    => 'Nrj',
    'cout'  => 500,
    'liste' => array(
      'Appliqué sur le coût total du projet.' => null
      )),
 'Struture & entretien' => array(
    'id'    => 'Str',
    'cout'  => 50000,
    'liste' => array(
      'Ajouté au coût total du projet'  => null
    )));
define('SPE', 'Gen');
include 'commun.php';

  rappel('Les frais <b>généraux</b> représentent les coûts de structure, de gestion, d\'entretien, d\'énergie.<br>
      Un % sur le coût total du projet par heure pour la gestion et l\'énergie.<br>
      Un coût par projet pour la structure et l\'entretien.');
  
  colonne('liste', 'init', 'Liste des types de frais');
  liste($frais);
  colonne('liste', 'clore');
  
  colonne('formulaire', 'init', basename(__file__, '.php'));
  foreach($frais as $titre => $donnees)
    input(MNU.SPE.$donnees['id'], $titre, $donnees['cout']);
  colonne('formulaire', 'clore');

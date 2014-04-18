<?php

#Lecture de la base de données
$production = array(
  'Fabrication mécanique' => array(
    'id'    => 'Mec',
    'liste' => array(
      'Fraiseuse conventionnelle' => array(
        'id'     => 'FraCon',
        'cout'   => 2050,
        'detail' => null),
      'Fraiseuse à commande numérique' => array(
        'id'     => 'FraCN',
        'cout'   => 5000,
        'detail' => null),
      'Tour conventionnel' => array(
        'id'     => 'TourCon',
        'cout'   => 2050,
        'detail' => null),
      'Perceuse à colonne' => array(
        'id'     => 'Perce',
        'cout'   => 1500,
        'detail' => null),
      'Presse hydraulique' => array(
        'id'     => 'Presse',
        'cout'   => 1050,
        'detail' => null),
      'Scie à ruban' => array(
        'id'     => 'Scie',
        'cout'   => 500,
        'detail' => null),
      'Plieuse' => array(
        'id'     => 'Plieuse',
        'cout'   => 250,
        'detail' => null),
      'Cisaille' => array(
        'id'     => 'Cisaille',
        'cout'   => 250,
        'detail' => null),
      'Poste à souder à l\'arc' => array(
        'id'     => 'SoudArc',
        'cout'   => 500,
        'detail' => null),
      'Poste à souder MIG' => array(
        'id'     => 'SoudMig',
        'cout'   => 1000,
        'detail' => null),
      'Découpe plasma' => array(
        'id'     => 'Plasma',
        'cout'   => 1000,
        'detail' => null),
      'Imprimante 3D' => array(
        'id'     => 'Print3D',
        'cout'   => 550,
        'detail' => null),
      'Matériel électroportatif' => array(
        'id'     => 'Elect',
        'cout'   => 250,
        'detail' => null),
      )),
 'Informatique' => array(
    'id'    => 'Inf',
    'liste' => array(
      'Poste bureautique' => array(
        'id'     => '',
        'cout'   => 5000,
        'detail' => null),
      'Poste de programmation en automatisme' => array(
        'id'     => '',
        'cout'   => 5000,
        'detail' => null),
      'Imprimante laser N&amp;B' => array(
        'id'     => '',
        'cout'   => 5000,
        'detail' => null),
      'Imprimante laser couleur' => array(
        'id'     => '',
        'cout'   => 5000,
        'detail' => null),
      'Appareil photo' => array(
        'id'     => '',
        'cout'   => 5000,
        'detail' => null),
      )),
 'Electronique' => array(
    'id'    => 'Elc',
    'liste' => array(
      'Fer à souder' => array(
        'id'     => '',
        'cout'   => 5000,
        'detail' => null),
      'Multimêtre' => array(
        'id'     => '',
        'cout'   => 5000,
        'detail' => null),
    )));
define('SPE', 'Fab');
include 'commun.php';


  rappel('Les frais du matériel de production représentent le coût de <b>possession</b>.<br>
      Prendre en compte les coûts d\'achat, de maintenance, d\'énergie, de consomable et de destruction pour une durée de vie estimée (en heures).');
  
  colonne('liste', 'init', 'Liste des moyens de production');
  liste($production);
  colonne('liste', 'clore');
  
  colonne('formulaire', 'init', basename(__file__, '.php'));
  foreach($production as $titre => $donnees) {
    echo
    '          <fieldset>' .CR.
    '            <legend>' . $titre . '</legend>' .CR;
    
    foreach($donnees['liste'] as $nom => $info)
      input(MNU.SPE.$donnees['id'] . $info['id'], $nom, $info['cout']);
      
    echo
    '          </fieldset>';
  } 
  colonne('formulaire', 'clore');

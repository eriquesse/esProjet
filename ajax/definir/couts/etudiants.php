<?php

#Lecture de la base de données
$etudiants = array(
  'Première année' => array(
    'id'    => 'Pre',
    'cout'  => 9000,
    'liste' => array(
      'Projets de première année peu encadré.' => null
      )),
 'Deuxième année' => array(
    'id'    => 'Snd',
    'cout'  => 5000,
    'liste' => array(
      'Projets de fin d\'étude très encadré.'  => null
      )),
 'Licence professionnelle' => array(
    'id'    => 'LPro',
    'cout'  => 3000,
    'liste' => array(
      'Projets de fin de formation peu encadré.'  => null
    )));
define('SPE', 'Etd');
include 'commun.php';

rappel('Les frais des étudiants représentent le coût <b>virtuel</b> par heure d\'un technicien débutant.<br>
  Ces frais comprennent uniquement les charges salariales &amp; patronales');

colonne('liste', 'init', 'Liste des groupes d\'étudiants');
liste($etudiants);
colonne('liste', 'clore');

colonne('formulaire', 'init', basename(__file__, '.php'));
foreach($etudiants as $titre => $donnees)
  input(MNU.SPE.$donnees['id'], $titre, $donnees['cout']);
colonne('formulaire', 'clore');

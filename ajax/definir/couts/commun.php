<?php
define('CR', "\n");
define('MNU','cout');

function input($id, $nom, $value){
  echo
    '          <div class="form-group">' .CR.
    '            <label for="' . $id .
    '" class="col-sm-8 control-label">' . $nom . '</label>' .CR.
    '            <div class="col-sm-4">' .CR.
    '              <div class="input-group">'  .CR.
    '                <input type="number" class="form-control" id="' . $id .
    '" placeholder="€/h"' .
    (!is_null($value) ? (' value="' . $value/100 .'"') : '') .
    '>' .CR.
    '                  <span class="input-group-addon">'.
    (($id=='coutGenNrj')? '%' : '€') .
    '</span>' .CR.
    '                </div>' .CR.   
    '              </div>' .CR.
    '          </div>' .CR;
}

function liste($liste) {
  foreach($liste as $titre => $donnees) {
    echo
    '          <div class="row">'.CR.
    '            <dt class="col-sm-3">' . $titre . '</dt>' . CR .
    '            <dd class="col-sm-9">' . CR .
    '              <ul>' . CR;
    foreach($donnees['liste'] as $nom => $info) {
      echo
      '              <li>' . $nom . (!is_null($info['detail'])?
      '<span class="detail">(' . $info['detail']. ')</span>':'') . '</li>' .CR;
    }
    echo
    '              </ul>' .CR.
    '            </dd>' .CR.
    '          </div>'.CR;
  }
}

function colonne($laquelle, $mode){
  switch ($laquelle . '/' . $mode){
    case 'liste/init':
      echo
      '    <div class="row">'.CR.
      '      <div class="col-sm-6 filet-right">'.CR.
      '        <h3>' . func_get_arg(2) . '</h3>'.CR.
      '        <dl>'.CR;
      break;
    case 'liste/clore':
      echo
      '        </dl>'.CR.
      '      </div>'.CR;
      break;
    case 'formulaire/init':
      echo
      '      <div class="col-sm-6">'.CR.
      '        <h3>Saisie des données</h3>'.CR.
      '        <form'.CR.
      '          class    = "form-horizontal"'.CR.
      '          role     = "form"'.CR.
      '          onsubmit = "if(W3DOM) return false;"'.CR.
      '          id       = "definir_' . func_get_arg(2) . '">' .CR;
      break;
    case 'formulaire/clore':
      echo
      '          <div class="form-group">'.CR.
      '          <div class="col-sm-offset-1">'.CR.
      '            <input type="submit" class="btn btn-primary" value="Mémoriser les modifications">'.CR.
      '          </div>'.CR.
      '        </div>'.CR.
      '        </form>'.CR.
      '      </div>'.CR.
      '    </div>'.CR;
      break;
    default:
  }
}

function rappel($message){
  echo
  '      <dl class="dl-horizontal">'.CR.
  '      <dt>Rappel</dt>'.CR.
  '      <dd>'.CR.
  $message .CR.
  '      </dd>'.CR.
  '    </dl>'.CR;
}

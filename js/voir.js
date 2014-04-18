/* ========================================================================
 * esProjet: voir.js v0.0.1
 * http://
 * ========================================================================
 * Copyright 2014 E. SUET
 * GNU GPL http://org.rodage.com//gpl-3.0.fr.txt
 * ======================================================================== */

/*jslint vars: true, plusplus: true, devel: true, nomen: true, maxerr: 50 */
/*global $, window, app, document */

var voir = {
  //----------------------------------- Variables
  how      : "voir",
  what     : null,
  register : ["bilan", "historique", "documents", "archives", "projets", "personnel"],

  //----------------------------------- Fonctions
  go              : function(what){
    app.setWhat(this, what)
       .menu.go({ how  : this.how, what : this.what});
  },
  init            : function() {
  },

  //----------------------------------- Classes
  personnel  : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  projets    : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  archives   : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  documents  : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  historique : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  bilan      : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
};
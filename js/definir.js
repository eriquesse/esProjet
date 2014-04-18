/* ========================================================================
 * esProjet: definir.js v0.0.1
 * http://
 * ========================================================================
 * Copyright 2014 E. SUET
 * GNU GPL http://org.rodage.com//gpl-3.0.fr.txt
 * ======================================================================== */

/*jslint vars: true, plusplus: true, devel: true, nomen: true, maxerr: 50 */
/*global $, window, app, document */

var definir = {
  //----------------------------------- Variables
  how      : "definir",
  what     : null,
  register : ["sessions", "projets", "etudiants", "couts", "listes"],

  //----------------------------------- Fonctions
  go              : function(what){
    app.setWhat(this, what)
       .menu.go({ how  : this.how, what : this.what});
  },
  init            : function() {
  },

  //----------------------------------- Classes
  sessions : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  projets : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  etudiants : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  couts : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  listes : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
};
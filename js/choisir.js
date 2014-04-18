/* ========================================================================
 * esProjet: choisir.js v0.0.1
 * http://
 * ========================================================================
 * Copyright 2014 E. SUET
 * GNU GPL http://org.rodage.com//gpl-3.0.fr.txt
 * ======================================================================== */

/*jslint vars: true, plusplus: true, devel: true, nomen: true, maxerr: 50 */
/*global $, window, app, document */

var choisir = {
  //----------------------------------- Variables
  how      : "choisir",
  what     : null,
  register : ["projet", "etudiant", "personnel", "production", "cout"],

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
  projet     : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  etudiant   : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  cout       : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
  production : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  },
};
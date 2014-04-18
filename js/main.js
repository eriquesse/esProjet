/* ========================================================================
 * esProjet: main.js v0.0.1
 * http://
 * ========================================================================
 * Copyright 2014 E. SUET
 * GNU GPL http://org.rodage.com//gpl-3.0.fr.txt
 * ======================================================================== */

/*jslint vars: true, plusplus: true, devel: true, nomen: true, indent: 2, maxerr: 50 */
/*global $, window, document */

/* ------------------------------------------------------------------ Commun */
$(document).ready(function () {
  "use strict";
  app.init({ menu : { how  : "saisir", what : "session"}});
  //app.init({ menu : { how  : "voir", what : "bilan"}});
});

var app = {
  //----------------------------------- Variables
  error        : null,
  userIdentity : null,
  userID       : null,

  //----------------------------------- Fonctions
  init         : function() {
    var args = arguments[0];
    
    this.menu.init();
    this.setUserID()
        .setUserIdentity();
    
    if (args && args.menu)
      this.menu.go(args.menu);
  },
  debug        : function() {
    if (app.error === null)
      return true;
    
    var message = "Erreur indéfinie";
    switch(this.error.code) {
      case "missingArg":
        message = "Il manque des arguments";
        break;
    }
    
    app.error.code = message;
    console.table(app.error);
    return false;
  },
  loadScript   : function (nomScript) {
    if (typeof window[nomScript] === "undefined")
      $.getScript("js/" + nomScript + '.js')
        .done(function() {
          app.runScript();
        });
    else
      app.runScript();
  },
  runScript    : function() {
    if (app.isRegistered(window[app.menu.how], app.menu.what)) {
      app.menu.href = app.menu.how + ".load('" + app.menu.what + "')";
      app.menu.path = app.menu.how + "/" + app.menu.what;

      app.menu.spolight()
              .loadPage();
      window[app.menu.how].init(); //Initialisation du script
      
    //Erreur   -> affichage dans la console
    } else
      console.error("Mauvais what : " +  app.menu.how + "('" + app.menu.what + "')");
  },
  initTooltip  : function() {
    $('body').tooltip({
      selector  : ".infoBulle",
      container : "body"
    });
  },
  isRegistered : function(parent, what) {
    this.setWhat(parent, what);
    return (parent.register.indexOf(parent.what) >= 0);
  },
  setWhat      : function(parent, what) {
    if (typeof parent.what  !== "undefined")
      parent.what = what;
    return this;
  },
  setFocus     : function(element){
    window.setTimeout(
      function() {
        $(element).focus();
      }, 0);
  },
  setUserIdentity : function() {
    this.userIdentity = 'ATTIMONT Benoit';
    $("a.navbar-brand").attr("title", this.userIdentity);
    return this;
  },
  setUserID    : function() {
    this.userID = 2;
    return this;
  },
  
  //----------------------------------- Classes
  menu : {
    //----------------------------------- Variables
    how  : null,
    what : null,
    href : null,
    path : null,

    //----------------------------------- Fonctions
    go           : function() {
      this.setArguments(arguments[0]);
      
      if (app.debug())
        app.loadScript(this.how);
    },
    init         : function() {
      this.event.click();
      this
        .setHref()
        .setButton();
    },
    spolight     : function(){
      //Annuler tous les autres
      $(".navbar li").removeClass("active");
      
      //Mettre en évidence le menu selectionné
      $(".navbar a[menu='" + app.menu.how + "']")
        .parent("li")
        .addClass('active')
        .find("a[menu='" + app.menu.what + "']")
        .parent("li")
        .addClass('active');
      
      return this;
    },
    loadPage     : function(){
      $("#contenu").load(
        "ajax/" + this.path + '/index.php',
        {
          userID : app.userID,
          cmd    : 'init'
        },
        function(response, status, xhr) {
          if ( status == "error" )
            $( "#contenu" ).html(
              "Une erreur est apparue : " + xhr.status + " " + xhr.statusText );
          else {
            window[app.menu.how][app.menu.what].init(); //Initialisation de la page
          }
        });
    },
    setArguments : function () {
      this.how  = arguments[0].how;
      this.what = arguments[0].what;
      
      if (this.how && this.what)
        return;
      
      else
        app.error = {
          fctn : this.getArguments,
          code : "missingArg"
        };
    },
    setHref      : function() {
      $(".navbar a")
        .attr("href", "#");
      return this;
    },
    setButton    : function() {
      $(".navbar button")
        .attr("type", "button")
        .addClass("btn");
      return this;
    },
    
    //----------------------------------- Classes
    event : {
      click : function() {
        $("nav").on("click", "a[menu]:not(.dropdown-toggle)", function() {
          app.menu.go({
            how  : $(this).parents("li.dropdown").find("a.dropdown-toggle").attr("menu"),
            what : $(this).attr("menu")
          });
        });
        return this;
      },
    },
  },
  time : {
    getFromString : function(texte) {
      var pos;
      if (texte) {
        texte = texte.trim();

        if (texte.indexOf(":") !== -1)
          pos     = texte.indexOf(":");
        else if (texte.indexOf("h") !== -1)
          pos     = texte.indexOf("h");
        else
          return -1;
        
        var heure   = parseInt(texte.substr(0,pos));
        var minutes = parseInt(texte.substr(pos+1));
        
        return heure * 60 + minutes;
      } else
        return -1;
    },
    toString      : function(time) {
      return parseInt(time/60) + ":" + ("0" + (time % 60).toString()).substr(-2,2);
      
    },
    reformat      : function(text) {
      var myTime = this.getFromString(text);
      if (myTime === -1)
        return "";
      else
        return this.toString(myTime);
    },
  },
  date : {
    jours : ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi",
             "Samedi"],
    mois  : ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet",
                      "Août", "Septembre", "Octobre", "Novembre", "Décembre"],

    setModif        : function(text, modif) {
      var dateValid;
  
      if (typeof modif === "undefined")
        modif = 0;

      //aujourd'hui
      if (text === '') {
        dateValid = new Date();
        
      //Verification
      } else {
        dateValid = this.getFromString(text.trim());
        if (dateValid === -1 )
          return "";
      }
      
      dateValid.setHours(0);
      dateValid.setMinutes(0);
      dateValid.setDate(dateValid.getDate() + modif);
      dateValid.setHours(0);
      dateValid.setMinutes(0);
     
      return this.toLongString(dateValid);
    },
    getFromString   : function(text){
      var result;

      //format jj/mm/aaaa
      if (text.indexOf("/") > 0)
        result = this.shortString2JMA(text);

      //format Lundi 01 Janvier 2000
      else if (text.indexOf(" ") > 0)
        result = this.longStringToJMA(text);
      
      //test de validite des données recues
      if (result && result.length === 3)
        return this.getFromJMA(result);
      else
        return -1;
    },
    toLongString    : function(maDate){
      return  this.jours[maDate.getDay()] + " " +
              maDate.getDate() + " " +
              this.mois[maDate.getMonth()] + " " +
              maDate.getFullYear();
    },
    shortString2JMA : function(maDate){
      if (!(maDate.length === 10 || maDate.length === 8))
        return [,,];
      
      maDate = maDate.split("/");

      if (maDate.length != 3)
        return [,,];

      var jour  = parseInt(maDate[0],10);
      var mois  = parseInt(maDate[1],10) - 1;
      var annee = parseInt(maDate[2],10);
      if (maDate[2].length == 2)
        annee += 2000;
      
      //Format jj/mm/aaaa
      if ((isNaN(annee)) || (isNaN(mois)) || (isNaN(jour)) ||
        (maDate[0].length < 2) ||
        (maDate[1].length < 2) ||
        (maDate[2].length != 4 && maDate[2].length != 2))
        return [,,];

      return [jour, mois, annee];
    },
    longStringToJMA : function(maDate){
      maDate = maDate.split(" ");

      if (maDate.length != 4)
        return [,,];

      var jour  = parseInt(maDate[1],10);
      var mois  = this.mois.indexOf(maDate[2]);
      var annee = parseInt(maDate[3],10);

      //Format jj/mm/aaaa
      if ((isNaN(annee)) || (isNaN(mois)) || (isNaN(jour)))
        return [,,];

      return [jour, mois, annee];
    },
    getFromJMA      : function(jma){
      var maDate = new Date(jma[2], jma[1], jma[0], 0,0,0);
      jma[2]     = maDate.getFullYear();

      if ((maDate.getDate()     != jma[0]) ||
          (maDate.getMonth()    != jma[1]) ||
          (maDate.getFullYear() != jma[2] ))
        return -1;

      return maDate;
    },
    reformat        : function(text){
      var maDate = this.getFromString(text);
      if (maDate === -1)
        return "";
      
      return this.toLongString(maDate);
    },
    toDB            : function(myDate, myTime) {
      //mise à jour de l'heure
      myDate.setHours(parseInt(myTime/60));
      myDate.setMinutes(myTime % 60);
      myDate.setSeconds(0);
      
      return (myDate.getTime()/1000);
    },
  },
};

/*function initCallbacksNav(){
  callbacks = {
    'voir/bilan'    : viewPie,
    'definir/couts' : definirCouts,
    'definir/couts/voir' : null,
  };
}*/
/*
function article(quoi, comment, ou){
  var href =  quoi + '/' + comment;

  $("#" + ou).load(
    "ajax/" + quoi + '/index.php?' + comment + "=" + ou, function() {
      //Gestion de la validation du formulaire
      oFocusNext._Init('definir_' + ou);
      
      if (callbacks[href])
        callbacks[href]();
    });
}*/

/* ------------------------------------------------------------------- Couts */
/*function definirCouts(){
  article('definir/couts', 'voir', 'personnels');
}
*/
/* -------------------------------------------------------------------- Voir */
/*function configPie(){
  $.fn.peity.defaults.pie = {
    delimiter : null,
    diameter  : 128,
    fill      : ["#ff9900", "#fff4dd", "#ffd592"],
    height    : null,
    width     : null
  };  
}
function viewPie(){
  $(".pie:not(.time)").peity("pie",  {
    fill: [ "#a9a9a9", "#fac551", "#ba2124", "#64a9e2", "#00a900"]
  });
  
  viewPieTime();
}
function viewPieTime(){
  var pieTime = $(".time").peity("pie",  {
    fill: [ "#ba2124", "#fac551"],
  });
  
  pieTime.bornes = pieTime.text().split(",");
  pieTime.text("0," + (parseInt(pieTime.bornes[0]) + parseInt(pieTime.bornes[1])));
  
  var autoTime = window.setInterval(function() {
    var bornes = pieTime.text().split(",");
    bornes[0] = parseInt(bornes[0]) + 20;
    bornes[1] = parseInt(bornes[1]) - 20;
    
    $(".duree li.no3 span").html(bornes[0]);
    $(".duree li.no2 span").html(bornes[1]);
    
    if (bornes[0] >= pieTime.bornes[0]) {
      pieTime.text(pieTime.bornes.join(",")).change();
      $(".duree li.no3 span").html(pieTime.bornes[0]);
      $(".duree li.no2 span").html(pieTime.bornes[1]);
      window.clearInterval(autoTime);
    }
    
    pieTime
      .text(bornes.join(","))
      .change();
  }, 30);  
}
*/
/* ------------------------------------------------------------------ Saisir */





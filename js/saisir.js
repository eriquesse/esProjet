/* ========================================================================
 * esProjet: saisir.js v0.0.1
 * http://
 * ========================================================================
 * Copyright 2014 E. SUET
 * GNU GPL http://org.rodage.com//gpl-3.0.fr.txt
 * ======================================================================== */

/*jslint vars: true, plusplus: true, devel: true, nomen: true, maxerr: 50 */
/*global $, window, app, document */

var saisir = {
  //----------------------------------- Variables
  how          : "saisir",
  what         : null,
  register     : ['session', 'document'],

  //----------------------------------- Fonctions
  go              : function(what){
    app.setWhat(this, what)
       .menu.go({ how  : this.how, what : this.what});
  },
  init            : function() {
    app.setUserIdentity();
  },

  //----------------------------------- Classes
  session   : {
    //----------------------------------- Variables
    duree : null,

    //----------------------------------- Fonctions
    init      : function() {
      app.initTooltip();
      
      this.event
        .actions()
        .affichage()
        .clavier();
      
      this.typeTache
        .load()
        .init();
      this.tache       .init();
      this.typeActivite.init();
      this.details     .init();
      this.durees      .init();

      this.setDuree()
          .majListe()
          .setButton();
    },
    majListe  : function(){
      saisir.session.tache.hideDetails();
      
      if (this.isNoMore()) {
        this.showAdd();
        saisir.session.tache.hide();

      } else {
        this.showNext();
        saisir.session.setDuree();
        saisir.session.tache.newOne();
        this.durees.majBarre();
      }
      return this;
    },
    setDuree  : function() {
      this.duree = app.time.getFromString($("#sessions .list-group-item.active span.badge").html());
      return this;
    },
    setButton : function() {
      $("#contenu button")
        .attr("type", "button")
        .addClass("btn");
      return this;
    },

    valider   : function(){
      var taches = this.getTaches();
      
      if (!saisir.session.showError(taches[1]))
        $.post(
          "ajax/saisir/session/",
          { cmd       : 'valider',
            sessionID : $("#sessions a.active").attr("sessionid"),
            taches    : taches[0],
            user      : app.userIdentity },
          function(resultats) {
            resultats = JSON.parse(resultats);

            //pas d'erreur
            if (resultats.errors === null || resultats.errors === "") {
              $("#sessions a.active").remove();
              saisir.session.majListe();

            //Affichage
            } else
              saisir.session.showError();
          });
    },
    ajouter   : function(){
      var dates = getDates();
      var errors = testValid(dates);

      if (!saisir.session.showError(errors)) {
        $.post(
          "ajax/saisir/session/",
          { cmd     : 'ajouter',
            Tstart  : app.date.toDB(dates.myDate, dates.start),
            Tend    : app.date.toDB(dates.myDate, dates.end),
            userID  : app.userID,
            users   : getUsers()
          },
          function (resultats) {
            resultats = JSON.parse(resultats);

            //pas d'erreur
            if (errors === null || errors === "") {

              $("#session").hide();
              saisir.session.majListe();

            //Affichage
            } else
              saisir.session.showError();
          });
      }
      
      function getDates(){
        var myDate = $("#session input[type='date']").val(),
            start  = $("#session input[type='time']:first").val(),
            end    = $("#session input[type='time']:last").val();
      
        return {
          start  : app.time.getFromString(start),
          end    : app.time.getFromString(end),
          myDate : app.date.getFromString(myDate)};
      }
      function testValid(dates){
        var errors = [];
        
        if (dates.myDate === -1)
          errors.push("Le format de date est incorrect");
        if (dates.end === -1)
          errors.push("Le format de l'heure de début est incorrect");
        if (dates.start === -1)
          errors.push("Le format de l'heure de fin est incorrect");
        if (dates.start >= dates.end)
          errors.push("L'heure de fin doit être plus tard que l'heure de début");
        
        return errors;
      }
      function getUsers(){
         var users = [];
          $("#session input:checked").each( function(no, input) {
            users.push($(input).val());
          });
        return users.join(",");
      }
    },
    
    showError : function(errors){
      if ($.isEmptyObject(errors))
        return false;
      
      var titre;

      if (errors.length == 1)
        titre = "Une erreur est apparue :";
      else
        titre = "Des erreurs sont apparues :";

      $("#erreur")
        .find(".modal-body ul")
        .html("<li>" + errors.join("</li><li>") + "</li>")
        .end()
        .find(".modal-title")
        .html(titre)
        .end()
        .modal('show');
      
      return true;
    },
    showAjout : function(){
      $("#sessions")
        .find("div[role='duree']").show()
        .end()
        .find("div[role='validation']").hide();
      $("#taches div.input-group:last")
        .show();
      saisir.session.typeTache.show();
      this.modifDate();
    },
    showAdd   : function(){
      $.getJSON(
        "ajax/DB/students",
        { getList : "",
          userID  : app.userID,
          format  : "object" },
        function(resultats){
          if (resultats.errors === null) {
            $.each(resultats.datas, function(no, student) {
              var newStudent = $("#itemStudent div")
                .clone()
                .show();
              newStudent
                .find("input")
                .after(student.identity);
              newStudent
                .find("input")
                .val(student.id);
              
              if (student.identity == app.userIdentity) {
                newStudent
                  .find("input")
                  .attr("checked", "checked");
              }
              newStudent
                .insertAfter("#session div.checkbox h4");
            });
            $("#sessions p").show();
            $("#sessions div[role='duree']").hide();
            $("#sessions div[role='ajout']").show();
            $("#session").show();
          }
        });
    },
    showNext  : function() {
      $("#sessions .list-group-item:first").addClass("active");
      $("#session").hide();
      $("#sessions div[role='duree']").show();
      $("#sessions div[role='ajout']").hide();
    },

    modifDate : function(modif) {
      var maDate = '';
      
      //+1 ou -1
      if (typeof modif  !== "undefined") {
        maDate = $("#session input:first").val();
      }
      $("#session input:first").val(app.date.setModif(maDate, modif));
    },
    getTaches : function(){
      var taches = [],
          tache,
          duree,
          dureeTotale = 0,
          errors = [];
      $("#taches div.input-group[id]").each(function(index, element){
        duree = $(this).find("button[role='duree']").html();
        tache = $(this).find("input").val();
        
        saisir.session.durees.checkTime(duree, errors);
        saisir.session.tache.checkTache(tache, errors);
        dureeTotale += app.time.getFromString(duree);
        
        taches.push({
          duree : duree,
          texte : tache});
        });
      this.checkTime(dureeTotale, errors);
      
      return [taches, errors];
    },
    checkTime : function(dureeTotale, errors){
      if (dureeTotale != this.duree)
        errors.push("La somme des durées des taches n'est pas égale à la durée de la séance");
    },
    isNoMore  : function(){
      return ($("#sessions a").length === 0);
    },
  
    //----------------------------------- Classes
    tache        : { //-------------------------------------------------- tache
      //----------------------------------- Variables
      durationSum : 0,
      noSelected  : null,
      
      //----------------------------------- Fonctions
      init          : function() {
        this.event
          .actions()
          .del()
          .isValid();
        this.show();
      },
      setSommeDuree : function() {
        saisir.session.tache.durationSum = 0;

        $("#taches > .input-group[id] button[role='duree']").each( function(index, Element) {
          saisir.session.tache.durationSum +=  app.time.getFromString($(Element).html());
        });
      },
      addTexte      : function(texte, ajout, typeListe) {
        if (texte.indexOf(ajout) > 0 ) return false;

        var finZone = texte.indexOf(typeListe[1]);
        var separateur = ", ";

        if ( finZone < 0) {
          separateur = "";
          texte = texte + " " + typeListe;
          finZone = texte.length - 1;
        }
        
        $("#taches input:last")
          .val(
            texte.substr(0, finZone) + separateur + 
            ajout +
            texte.substring(finZone, texte.length)
          )
          .trigger("change");
      },
      hide          : function() {
        $("#taches").hide();
        saisir.session.typeTache.hide();
      },
      show          : function() {
        $("#taches").show();
        saisir.session.typeTache.show();
      },
      showDetails   : function() {
        saisir.session.typeTache.hide();
        saisir.session.details.show();
      },
      hideDetails   : function() {
        saisir.session.details.hide();
      },
      newOne        : function() {
        $("#taches div").remove();
        this.add();
      },
      add           : function() {
        $("#newTache").children().clone(true).appendTo("#taches");
      },
      getNo         : function(element) {
        var noTache = $(element).attr("id");

        //Definir le No de tache
        if (!noTache)
          return 0;
        else
          return parseInt(noTache.replace("tache_",""));
      },
      valider       : function(element) {

        var tache       = $(element).parents(".input-group");
        var btnValider  = $(tache).find("button[role=valider]");
        var noLastTache = saisir.session.tache.getNo($("#taches .input-group[id]:last")) + 1;

        //Modifier l'apparence de la tache
        $(btnValider).children("span")
          .removeClass("glyphicon-ok")
          .addClass("glyphicon-trash")
          .end()
          .removeClass("btn-primary")
          .addClass("btn-danger")
          .attr("title", "Cliquez pour supprimer la tache")
          .attr("role", "supprimer");
        
        $(tache)
          .attr("id", "tache_" + noLastTache)
          .find("input").attr("readOnly","readOnly");

        this.add();
        
        saisir.session.durees.majBarre();
        saisir.session.typeTache.show();
      },
      checkTache    : function(tache, errors){
        var posC = tache.indexOf("["),
            posD = tache.indexOf("]"),
            posP = tache.indexOf("("),
            posQ = tache.indexOf(")"),
            text = tache.trim();
        
        //Ressources obligatoires
        if ( posC === -1 || posD  === -1 || posC > posD)
          errors.push("Le(s) étudiants et/ou ressources doivent être indiqués entre accolades.");
        
        //Commentaires optionnels
        if ((posP !== -1 && posQ  === -1)  ||
            (posP === -1 && posQ  !== -1) ||
            (posP  > posQ))
          errors.push("Les commentaires doivent être indiqués entre parenthèses.");

        if (posP === 0 || posC === 0)
          errors.push("Les commentaires et les ressources doivent être indiqués après le type de tache.");
        
        //Erreurs -> pas possible de continuer
        if (errors.length !==0 ) return;
        
        var ressources, typeTache, commentaires;
        
        //commentaires avant ressources ... ou l'inverse
        if (posP < posC && posP !== -1) {
          text         = text.split("(");
          typeTache    = text[0].trim();
          text         = text[1].split(")");
          commentaires = text[0].trim();
          text         = text[1].split("[");
          text         = text[1].split("]");
          ressources   = text[0].trim();
          
        } else {
          text         = text.split("[");
          typeTache    = text[0].trim();
          text         = text[1].split("]");
          ressources   = text[0].trim();
          if (posP !== -1) {
            text         = text[1].split("(");
            text         = text[1].split(")");
            commentaires = text[0].trim();
          }
        }

        saisir.session.typeTache.checkExist(typeTache, errors);
        saisir.session.details.checkExist(ressources, commentaires, errors);
      },
      
      //----------------------------------- Classes
      event    : {
        actions : function() {
          $("#taches").on("click", "button", function() {
            var tache  = $(this).parents(".input-group");
            var action = $(this).attr("role");

            switch(action) {
              case "supprimer":
                saisir.session.tache.noSelected = saisir.session.tache.getNo(tache);
                $("#suppTache").modal('show');
                break;

              case "valider":
                if ($(tache).find("input").val() === "")
                  return false;
                else
                  saisir.session.tache.valider($(this));
                break;

              default:
                return false;
            }
          });
          return this;
        },
        del     : function() {
          $("#suppTache").on("click", "button", function() {
            
            if ($(this).attr("role") == "confirmer")
                $("#tache_" + saisir.session.tache.noSelected).remove();
            
            $('#suppTache').modal('hide');
          });
          return this;
        },
        isValid : function() {
          $("#taches").on("blur keyup change", "input", function() {
            var btnValider = $(this).parents(".input-group").find("button[role='valider']");

            if ($(this).val() === "") {
              $(btnValider)
                .addClass("disabled")
                .removeClass("btn-primary")
                .find("span")
                .removeClass("glyphicon-ok")
                .addClass("glyphicon-edit");
              saisir.session.typeTache.show();
            } else
              $(btnValider)
                .removeClass("disabled")
                .addClass("btn-primary")
                .find("span")
                .removeClass("glyphicon-edit")
                .addClass("glyphicon-ok");
          });
          return this;
        },
      },
    },
    typeTache    : { //---------------------------------------------- typeTache
      //----------------------------------- Variables
      liste : null,
       
      //----------------------------------- Fonctions
      init           : function() {
        this.event
          .showChild();
      },
      load           : function() {
        this.liste = [
          [ { code : "conception",
              text : "Conception",
              fils : [
                [ "Existant : Chercher", "Analyser" ],
                [ "Dessin : Réflechir", "Dessiner CAO" ],
                [ "Fournisseur : Chercher", "Contacter" ]]},
            { code : "realisation",
              text : "Réalisation",
              fils : [
                ["Éditer et/ou imprimer des plans et/ou schémas", "Fabriquer", "Assembler"],
                ["Exposer", "Animer", "Concourir", "Se transporter"]]},
            { code : "miseAuPoint",
              text : "Mise au point",
              fils : [
                ["Contrôler", "Fabriquer", "Ajuster et/ou adapter"]]}],
          [ { code : "organisation",
              text : "Organisation",
              fils : [
                ["Réunion", "Gestion de projet", "Ranger et/ou nettoyer"],
                ["Soutenance", "Préparation oral", "Préparation écrit"]]}],
          [ { code : "absence",
              text : "Absence",
              fils : [
                ["Justifiée", "Injustifiée"]]},
            { code : "pause",
              text : "Pause",
              fils : [
                ["Café / cigarette / nourriture", "Santé", "Re-motivation"]]},
            { code : "attente",
              text : "Attente",
              fils : [
                ["Enseignant / technicien", "Moyen de production"]]}]];
        this.genererButtons(); //en attendant php
        
        return this;
      },    
      genererButtons : function() {
        //Création des liste de taches et d'activités proposées
        for (var noGrp = 0, nbGrp = this.liste.length, btnGroup ; noGrp < nbGrp ; noGrp++) {
          btnGroup = this.liste[noGrp];
          
          $("#typeTache").append('<div></div>');
          
          for (var noBtn = 0, nbBtn = btnGroup.length, btn ; noBtn < nbBtn ; noBtn++) {
            btn = btnGroup[noBtn];

            $("#typeTache div:last").append('<button>' + btn.text + '</button>');
            $("#typeTache button:last").attr("tache", btn.code);
            
            for (var noGrpA = 0, nbGrpA = btn.fils.length, btnGrpA ; noGrpA < nbGrpA ; noGrpA++) {
              btnGrpA = btn.fils[noGrpA];
              
              saisir.session.typeActivite.genererButtons("groupe", btn.code);
              
              for (var noBtnA = 0, nbBtnA = btnGrpA.length, btnA ; noBtnA < nbBtnA ; noBtnA++)            
                saisir.session.typeActivite.genererButtons("button", btnGrpA[noBtnA]);
            }
          }
        }
        $("#typeTache div")   .addClass("btn-group");
        $("#typeTache button").addClass("btn btn-default") .attr("type", "button");
        
        saisir.session.typeActivite.genererButtons("class");
         
        return this;
     },
      getTexte       : function(element) {
        var typeTache = element
          .parents(".btn-group")
          .attr('class')
          .replace('btn-group','')
          .trim();
        return $("#typeTache button[tache='" + typeTache + "']")[0].innerHTML.trim();
      },
      show           : function() {
        $("#typeTache").show();
        saisir.session.typeActivite.show();
        saisir.session.details.hide();
      },
      hide           : function() {
        $("#typeTache").hide();
        saisir.session.typeActivite.hide();
      },
      checkExist     : function(text, errors) {
        text = text.split("->");
        var typeTache    = text[0].trim(),
            typeActivite = text[1].trim(),
            find        = false,
            no, nof, nb, nbf;

       top1:
       for(no = 0, nb = this.liste.length; no < nb; no ++){
          for(nof = 0, nbf = this.liste[no].length; nof < nbf; nof++) {
            if (this.liste[no][nof].text === typeTache) {
              find = true;
              break top1;
            }
          }
        }
        
        
        if (!find)
          errors.push("Le type de tache est inconnu");
        else {
          find = false;
          var fils = this.liste[no][nof].fils;

          top2:
          for(no = 0, nb = fils.length; no < nb; no++){
           for(nof = 0, nbf = fils[no].length; nof < nbf; nof++) {
             if (fils[no][nof] === typeActivite) {
                find = true;
                break top2;
              }
            }
          }

          if (!find)
            errors.push("Le détail de la tache est inconnue");
        }
      },
      
      //----------------------------------- Classes
      event    : {
        showChild : function() {
          $("#typeTache").on("mouseover", "button", function() {
            saisir.session.typeActivite.show($(this).attr("tache"));
          });
          return this;
        },
      },
    }, 
    typeActivite : { //------------------------------------------- typeActivite
     //----------------------------------- Fonctions
      init           : function() {
       this.event
        .selActivite()
        .showParent();
      },
      genererButtons : function(mode, donnees) {
        switch(mode){
          case "groupe":
            $("#typeActivite").append('<div></div>');
            $("#typeActivite div:last").addClass(donnees);
            break;
          case "button":
            $("#typeActivite div:last").append('<button>' + donnees + '</button>');
            break;
          case "class":
            $("#typeActivite div")   .addClass("btn-group");
            $("#typeActivite button").addClass("btn btn-default") .attr("type", "button");
              break;
        }
      },
      show           : function(typeTache) {
        if (typeof typeTache  === "undefined")
          typeTache = "_";

        $("#typeActivite .btn-group").hide();
        $("#typeActivite ." + typeTache).show();
        
        return this;
      },
      hide           : function() {
        $("#typeActivite .btn-group").hide();
      },
      getTexte       : function(element) {
        return element[0]
          .innerHTML
          .trim();
      },
 
      //----------------------------------- Classes
      event    : {
        selActivite : function() {
          $("#typeActivite").on("click", "button", function () {
            var texte = 
                saisir.session.typeTache.getTexte($(this)) +
                " -> " + 
                saisir.session.typeActivite.getTexte($(this)) +
                " [" + app.userIdentity + "]";
            $("#taches input:last")
              .val(texte)
              .trigger("change");
            saisir.session.typeTache.hide();
            saisir.session.details.show();
            $("#typeTache button").removeClass("active");
          });
          return this;
        },
        showParent : function() {
          $("#typeActivite").on("mouseover", "button", function () {
            var parent = $(this).parent().attr('class');
            parent = parent.replace("btn-group","").trim();

            $("#typeTache button").removeClass("active");
            $("#typeTache button[tache='" + parent + "']").addClass("active");
          });
          return this;
        },
      },
    }, 
    details      : { //-------------------------------------------------- details
      //----------------------------------- Variables
      personnels  : null,
      etudiants   : null,
      productions : null,
      
     //----------------------------------- Fonctions
      init            : function() {
        this.event
          .addTextResource()
          .addTextComment();
        
        this.loadCompletion();
      },
      loadCompletion  : function() {
        $.getJSON("ajax/DB/students",
          { getList : "" },
          function(resultats) {
          if (resultats.errors === null) {
            saisir.session.details.etudiants = resultats.datas;
            $("#etudiants").autocomplete({
              source : saisir.session.details.etudiants
            });
          }
          else
            throw resultats.errors.join("\n");
        });
        $.getJSON("ajax/DB/personals",
          { getList : "" },
          function(resultats) {
          if (resultats.errors === null) {
            saisir.session.details.personnels   = resultats.datas;
            $("#personnels").autocomplete({
              source : saisir.session.details.personnels
            });
         } else
            throw resultats.errors.join("\n");
        });
        $.getJSON("ajax/DB/resources",
          { getList : "" },
          function(resultats) {
          if (resultats.errors === null) {
            saisir.session.details.productions   = resultats.datas;
            $("#production").autocomplete({
              source : saisir.session.details.productions
            });
          } else
            throw resultats.errors.join("\n");
        });
      },
      hide            : function() {
        $("#details").hide();
      },
      show            : function() {
        $("#details").show();
      },
      checkExist      : function(text, errors) {   
      },
      
      //----------------------------------- Classes
      event : {
        addTextResource : function() {
          $("#details").on("click", "button[role='plus']", function () {
            saisir.session.tache.addTexte(
              $("#taches input:last").val(),
              $(this).parents(".input-group").children("input").val(),
              '[]');
          });
          return this;
        },
        addTextComment  : function() {
          $("#details").on('click',  "button[role='edit']", function () {
            saisir.session.tache.addTexte(
              $("#taches input:last").val(),
              $(this).parents(".input-group").children("input").val(),
              '()');
          });
          return this;
        },
      },
    }, 
    durees       : { //------------------------------------------------- durees
      //----------------------------------- Fonctions
      init     : function() {
        this.event
          .affPopOver()
          .define()
          .setNoSelected();
      },
      majBarre : function() {
        //Verifications
        saisir.session.tache.setSommeDuree();
        if (saisir.session.duree === null)
          saisir.session.setDuree();
        
        var ratio = Math.round(100 * saisir.session.tache.durationSum / saisir.session.duree);

        if (ratio > 100)
          ratio = 0;

        else if (saisir.session.duree < 0)
          app.date.set();
          
        $("#barre div.progress-bar-info")   .width(ratio + "%");
        $("#barre div.progress-bar-warning").width((100 - ratio) + "%");
        $("div[role='duree'] p span")
          .text(app.time.toString(saisir.session.duree - saisir.session.tache.durationSum));

                if (ratio === 100) {
          $("#sessions")
            .find("div[role='duree']").hide()
            .end()
            .find("div[role='validation']").show();
          $("#taches div.input-group:last")
            .hide();
          saisir.session.typeTache.hide();
        } else {
          $("#sessions")
            .find("div[role='duree']").show()
            .end()
            .find("div[role='validation']").hide();
          $("#taches div.input-group:last")
            .show();
          saisir.session.typeTache.show();
        }
      },
      checkTime: function(duree, errors) {
        if (duree != app.time.toString(app.time.getFromString(duree)))
          errors.push("Le format de la durée dune tache est incorrect [ex : 1:30]");
      },
      
      //----------------------------------- Classes
      event    : {
        affPopOver    : function(){
          $('#taches').popover({
            selector  : ".infoBulle[role=duree]",
            container : "body",
            html      : true,
            title     : "Choissisez une durée",
            content   : function() {
              var html = ['<div class="btn-group" id="choixDuree">'];
              var no   = Math.max(0, saisir.session.duree - saisir.session.tache.durationSum);

              html.push(getBtn(no, "primary"));
              for ( no = 5; no <= 20; no+=5)   html.push(getBtn(no));
              for ( no = 30; no <= 60; no+=15) html.push(getBtn(no));
              for ( no = 90 ; no<=540; no+=30) html.push(getBtn(no));
              
              html.push('</div>');
              
              return html.join("");
              function getBtn(no, classe) {
                if (typeof classe === "undefined")
                  classe = "default";

                return '<button class="btn btn-'+ classe + '">' + app.time.toString(no) + '</button>';
              }
            }
          });
          return this;
        },
        define        : function(){
          $('body').on('click' , "#choixDuree button" , function(e) {

            //Fermer le popOver
            $('.infoBulle[role=duree]').popover('destroy');

            //Tache en cours pas encore validée
            if (saisir.session.tache.noSelected === 0)
              $("#taches .input-group:last button:first").html($(this).html());

            //Tache en cours validée
            else
              $("#taches .input-group[id='tache_" + saisir.session.tache.noSelected + "'] button:first")
                .html($(this).html());

            //Mise à jour de la durée cumulée des taches
            saisir.session.durees.majBarre();
          });
          return this;
        },
        setNoSelected : function() {
          $("#taches").on("show.bs.popover", ".infoBulle[role=duree]", function () {
            saisir.session.tache.noSelected =
              saisir.session.tache.getNo($(this).parents(".input-group"));
          });
        },
      },
    },
    event        : {
      actions   : function(){
        $("#sessions, #session").on("click", "button", function() {
          var action = $(this).attr("role");

          switch(action){
            case 'valider':
              saisir.session.showAjout();
              break;
              
            case 'ajouter':
              saisir.session.ajouter();
              break;
              
            case 'definir':
              saisir.session.valider();
              break;
              
            case 'avant':
              saisir.session.modifDate(-1);
              break;
            case 'apres':
              saisir.session.modifDate(+1);
              break;
          }
        });
        return this;
      },
      affichage : function() {
        $("#session").on("change", "input", function() {
          var type = $(this).attr('type');
          if (type == 'date')
            $(this).val(
              app.date.reformat($(this).val()));
          else if (type == 'time')
            $(this).val(
              app.time.reformat($(this).val()));
        });
        return this;
      },
      clavier   : function(){
        $("#session").on("keyup", "input", function(e) {
          var type = $(this).attr('type');
         
          if (e.key === "Enter" && $(this).val() !== "") {
            if (type == 'date')
              app.setFocus($("#session input[type='time']:first"));
            else if (type == "time"){
              app.setFocus($("#session input[type='time']:last"));
            }
          }
        });
        return this;
      },
    },
  },
  document : {
    //----------------------------------- Variables
    //----------------------------------- Fonctions
    init : function() {
    },
    
    //----------------------------------- Classes   
  }
};
//for(var parameter in typeTache) console.log(parameter+' => ' + typeTache[parameter]);
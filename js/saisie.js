var W3DOM = document.getElementById && document.getElementsByTagName;

// fonction addEvent (http://www.scottandrew.com/weblog/jsjunk)
function addEvent(oElem, sEvType, fn, bCapture) {
	return oElem.addEventListener?
		oElem.addEventListener(sEvType, fn, bCapture):
		oElem.attachEvent?
			oElem.attachEvent('on' + sEvType, fn):
			oElem['on' + sEvType] = fn;
}
function removeEvent(oElem, sEvType, fn, bCapture) {
	return oElem.removeEventListener?
		oElem.removeEventListener(sEvType, fn, bCapture):
		oElem.detachEvent?
			oElem.detachEvent('on' + sEvType, fn):
			oElem['on' + sEvType] = fn;
}

// ajout de trim
String.prototype.trim = function() {
	return this.replace(/(?:^\s+|\s+$)/g, "");
}

// fonction focus sur INPUT suivant ou soumission
var oFocusNext = {
	// Initialisation
	_Init: function(IdForm) {
		oFocusNext.IdForm = IdForm;
		

		// Pour chaque INPUT on initialise la fonction _FocusNext
		for(iIter = 0; iIter < oFocusNext._INPUTs().length; iIter++) {
			addEvent(oFocusNext._INPUTs()[iIter], 'keydown', oFocusNext._FocusNext, false);
		}
		
		// Focus sur le premier INPUT
		oFocusNext._INPUTs()[0].focus();
	},
	
	_Close: function(IdForm) {
		oFocusNext.IdForm = IdForm;

		// Pour chaque INPUT on initialise la fonction _FocusNext
		for(iIter = 0; iIter < oFocusNext._INPUTs().length; iIter++) {
			removeEvent(oFocusNext._INPUTs()[iIter], 'keydown', oFocusNext._FocusNext, false);
		}
		
		// Focus sur le premier INPUT
		oFocusNext._INPUTs()[0].focus();
		
	},
	
	// Focus sur INPUT suivant
	_FocusNext: function(event) {
		var key = event.which || event.keyCode;
		// on ne fait rien sauf sur Entrée
		if(key != 13) return;

		var oElem = event.target || window.event.srcElement;
		var oNextInput = '';
		for(iIter = oFocusNext._INPUTs().length - 1; iIter >= 0; iIter--) {
			if(oFocusNext._INPUTs()[iIter].id == oElem.id) {
				if(oNextInput != '') {
					oNextInput.focus();
					return false;
				} else { // Dernier INPUt -> validation du formulaire
					oFocusNext._VerifForm();
				}
			} else {
				oNextInput = oFocusNext._INPUTs()[iIter];
			}
		}
	},
	
	// Fonction vérifier le formulaire
	_VerifForm: function() {
		for(iIter = 0; iIter < oFocusNext._INPUTs().length-1; iIter++) {
			oFocusNext._INPUTs()[iIter].value = oFocusNext._INPUTs()[iIter].value.trim();
			var obj = $("#" + oFocusNext.IdForm + " input:eq(" + iIter + ")").parents().filter('.form-group');
			if(oFocusNext._INPUTs()[iIter].value == '') {
				oFocusNext._INPUTs()[iIter].focus();
				obj.addClass('has-error');
				return false;
			} else {
				obj.removeClass('has-error');
			}
		}
		oFocusNext._Cont().submit();
	},

	// Conteneur
	_Cont: function() {
		var Cont = document.getElementById(oFocusNext.IdForm);
		return Cont;
	},
	
	// éléments INPUT
	_INPUTs: function()	{
		var oInputs = oFocusNext._Cont().getElementsByTagName('input');
		return oInputs;
	}
};
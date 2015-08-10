// Validações
function DinamizeValidateForm(form){
    var elem = form.elements;
    var dateElements = [];

    jQuery(form).find('.form-msg-error').remove();
    
    jQuery(elem).each(function() {
        var me = jQuery(this);
        if (me.prop('type') == 'hidden') {
            return;
        }
        
        // Validação de e-mail
        if (me.hasClass('type_EMAIL')) {
            if (!DinamizeValidateEmail(me.prop('value'))) {
                DinamizeAppendError(form, me.parent(), 'emailInvalid');
            }
        }
        if (me.hasClass("type_DT") || me.hasClass("type_DH")) {
            if (me.prop('value') != "") {
            	if (!DinamizeExistDate(DinamizePrepareDate(me.prop('value'), me.prop('placeholder')), me.hasClass("type_DH"))) {
                    DinamizeAppendError(form, me.parent(), 'dataInvalid');
            	}
                dateElements.push(me.prop('id'));
            }
        }
        
        if (me.hasClass('field-required')) {
            // Campos multi valores
            if(me.hasClass('type_LVM')) {
                var container = me.parents('.containerMultiple:first').first();
                if (container.find(':checked').length == 0) {
                    DinamizeAppendError(form, container, 'required');
                }
            } else {
                // Campos "normais"
                if (me.prop('value').trim() == "") {
                    DinamizeAppendError(form, me.parent(), 'required');
                }
            }
        }
    });

    var enviar = (jQuery(form).find('.form-msg-error').length == 0);
    
    if (enviar) {
    	jQuery(form).find('.type_LVM').each(function() {
    		var me = jQuery(this);
    		var checkedValues = [];
    		
    		jQuery(form).find('.chk_'+me.prop('id')+':checked').each(function() {
    			checkedValues.push(jQuery(this).prop('value'));
    		});
    		
    		jQuery(form).find('#hd_'+me.prop('id')).val(checkedValues.join('|'));
        });
    	
        if(dateElements.length != 0){
            for (var i = 0; i < dateElements.length; i++) {
            	var inp = jQuery(form).find('#'+dateElements[i]);
                var newDate = DinamizePrepareDate(inp.prop('value'), inp.prop('placeholder'));
                jQuery(form).find('#hd_'+dateElements[i]).val(newDate);
            }
        }
    	
        jQuery(form).find('.type_FLT').each(function() {
        	var me = jQuery(this);
        	jQuery(form).find('#hd_'+me.prop('id')).val(me.prop('value').replace(",","."))
        });
    }

    return enviar;
}

function DinamizeValidateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function DinamizePrepareDate(date, format){
    var newDate, time;
    format = format.substr(0,10).replace("/","").replace("/","").replace("-","").replace("-","");
    time = date.substr(10,15);

    if(format == "DDMMAAAA"){
        newDate = date[6]+date[7]+date[8]+date[9] +"-"+ date[3]+date[4] +"-"+ date[0]+date[1];
    }else if(format == "MMDDAAAA"){
        newDate = date[6]+date[7]+date[8]+date[9] +"-"+ date[0]+date[1] +"-"+ date[3]+date[4];
    }else if(format == "AAAAMMDD"){
        newDate = date[0]+date[1]+date[2]+date[3] +"-"+ date[5]+date[6] +"-"+ date[8]+date[9];
    }

    return newDate+time;
}

function DinamizeExistDate(date,time){
    if(time){
        if(date.length != 16){
            return false;
        }
    }else if(!time){
        if(date.length != 10){
            return false;
        }
        date +=" 00:00";
    }

    var NEWDATE = new Date(date.replace("-","/").replace("-","/"));
    var strNewdate;

    var y = NEWDATE.getFullYear().toString();
    var m = (NEWDATE.getMonth()+1).toString();
    var d  = NEWDATE.getDate().toString();
    var h  = NEWDATE.getHours().toString();
    var min  = NEWDATE.getMinutes().toString();
    strNewdate = y +"-"+ (m[1]?m:"0"+m[0]) +"-"+ (d[1]?d:"0"+d[0]) +" "+ (h[1]?h:"0"+h[0]) + ":" + (min[1]?min:"0"+min[0]);

    if(date != strNewdate){
        return false;
    }

    return true;
}                    

function DinamizeAppendError(form, elem, msg) {
	var m = jQuery(form).find('.'+msg).prop('value');
	var err_msg = jQuery('<div class="form-msg-error">'+m+'</div>');
	elem.append(err_msg);
}
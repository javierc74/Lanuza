
jQuery(function ($) {

    'use strict';

    window.Adapta_RGPD = window.Adapta_RGPD || {};  

          
    Adapta_RGPD.cookiesAceptar= function() {        
        localStorage.cookies_consentimiento = (localStorage.cookies_consentimiento || 0); 
        localStorage.cookies_consentimiento++; 
        $('#cookies-consentimiento').hide(); 
    }
    
    Adapta_RGPD.cookiesInit= function() {
        if (localStorage.cookies_consentimiento>0){ 
            $('#cookies-consentimiento').hide(); 
        } 
    }

    // load events     
    Adapta_RGPD.cargarEventos= function(){
        $("#argpd-cookies-btn").click(function(){            
            Adapta_RGPD.cookiesAceptar();
        });
    } 

    // init     
    Adapta_RGPD.init = function(){

        Adapta_RGPD.cargarEventos();
            
        Adapta_RGPD.cookiesInit();        
    } 

    Adapta_RGPD.init();

});


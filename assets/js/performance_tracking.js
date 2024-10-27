 ;
(function($) {
 function adsensei_ad_tracker(){
        
        setTimeout(function(){   
            
        var ad_ids ={};    
        $(".adsensei-location").each(function(index){
           ad_ids[index]= ($(this).attr('id'));
        });  
        
        if($.isEmptyObject( ad_ids ) == false){           
        $.ajax({
                    type: "POST",    
                    url:adsensei_analytics.ajax_url,                    
                    dataType: "json",
                    data:{action:"adsensei_insert_ad_impression", ad_ids:ad_ids, adsensei_front_nonce:adsensei_analytics.adsensei_front_nonce},                    
                    error: function(response){                    
                    console.log(response);
                    }
                });     
        } 
             $(".adsensei-location").on("click",function(){
         var ad_id = $(this).attr('id');
         var currentLocation = window.location.href;
                       var referrer = document.referrer;
         if(ad_id){
            $.post(adsensei_analytics.ajax_url, 
                  { action:"adsensei_insert_ad_clicks", ad_id:ad_id, adsensei_front_nonce:adsensei_analytics.adsensei_front_nonce,currentLocation:currentLocation,referrer:referrer},
                    function(response){
                    console.log(response);                
       });  
             }         
        });                  
        }, 1000);
        
   
                
        //Detecting click event on iframe based ads
         window.addEventListener('blur',function(){   
      if (document.activeElement instanceof HTMLIFrameElement) {
                var data = $(this);                   
                var el = data.context.activeElement;
                 while (el.parentElement) {
                     el = el.parentElement;     
                       if(el.attributes[0].name =='data-ad-id'){
                       var ad_id = el.attributes[0].value;
                       if(ad_id){
                          $.post(adsensei_analytics.ajax_url, 
                             { action:"adsensei_insert_ad_clicks", ad_id:ad_id},
                                function(response){
                                console.log(response);                
                              });  
                          }
                       }
                   }
         }
    });
        
      }  
      adsensei_ad_tracker();
})(window.jQuery);

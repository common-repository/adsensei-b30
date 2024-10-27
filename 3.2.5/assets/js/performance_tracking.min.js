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

        $('body').on('click', '.adsensei-location', function (e) {
         if(e.target.getAttribute('data-attr')) {
            var placement = $(this).attr('data-attr');
            if( placement == 'beginning_of_post' ){
               var ad_id = $(this).attr('id');
               var currentLocation = window.location.href;
               var referrer = document.referrer;
               if(ad_id){
                  $.post(adsensei_analytics.ajax_url,
                     { action:"adsensei_insert_ad_clicks_"+placement, ad_id:ad_id, adsensei_front_nonce:adsensei_analytics.adsensei_front_nonce,currentLocation:currentLocation,referrer:referrer},
                     function(response){
                        console.log(response);
                     }
                     );
                  }
               }
               else if( placement == 'end_of_post' ){
                  var ad_id = $(this).attr('id');
                  var currentLocation = window.location.href;
                  var referrer = document.referrer;
                  if(ad_id){
                     $.post(adsensei_analytics.ajax_url,
                        { action:"adsensei_insert_ad_clicks_"+placement, ad_id:ad_id, adsensei_front_nonce:adsensei_analytics.adsensei_front_nonce,currentLocation:currentLocation,referrer:referrer},
                        function(response){
                           console.log(response);
                        }
                        );
                     }
                  }
               else if( placement == 'middle_of_post' ){
                  var ad_id = $(this).attr('id');
                  var currentLocation = window.location.href;
                  var referrer = document.referrer;
                  if(ad_id){
                     $.post(adsensei_analytics.ajax_url,
                        { action:"adsensei_insert_ad_clicks_"+placement, ad_id:ad_id, adsensei_front_nonce:adsensei_analytics.adsensei_front_nonce,currentLocation:currentLocation,referrer:referrer},
                        function(response){
                           console.log(response);
                        }
                        );
                     }
                  }
               else if( placement == 'after_more_tag' ){
                  var ad_id = $(this).attr('id');
                  var currentLocation = window.location.href;
                  var referrer = document.referrer;
                  if(ad_id){
                     $.post(adsensei_analytics.ajax_url,
                        { action:"adsensei_insert_ad_clicks_"+placement, ad_id:ad_id, adsensei_front_nonce:adsensei_analytics.adsensei_front_nonce,currentLocation:currentLocation,referrer:referrer},
                        function(response){
                           console.log(response);
                        }
                        );
                     }
                  }

               }

            });
            $(document).on('mouseover', "#rotater_id", function(e){
               var rotater_id = $(this).attr('rotate_id');
               var element = document.getElementById("adsensei-rotate")
                     if(element){
                        element.id = rotater_id
                     }
                     } )

            $('.adsensei_ad_container').mouseout( function(){
                  document.querySelector('.adsensei-rotatorad').id = 'adsensei-rotate'
               } )

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
                var ad_id = "";
                if(data.context)
                {                   
                  var el = data.context.activeElement;
                  while (el.parentElement) {
                        el = el.parentElement;     
                        if(el.attributes[0].name =='data-ad-id'){
                           ad_id = el.attributes[0].value;
                        }
                     }
               }
               else if(typeof infolinks_adid=='number'){
                     ad_id=infolinks_adid;
               }

               if(ad_id){
                  $.post(adsensei_analytics.ajax_url, 
                     { action:"adsensei_insert_ad_clicks", ad_id:ad_id},
                        function(response){
                        console.log(response);                
                      });  
                  }

         }
    });
    
      }  
      adsensei_ad_tracker();

      function set_adsensei_Cookie_(name,value,days) {
         var expires = "";
         if (days) {
             var date = new Date();
             date.setTime(date.getTime() + (days*24*60*60*1000));
             expires = "; expires=" + date.toUTCString();
         }
         document.cookie = name + "=" + (value || "")  + expires + "; path=/";
     }

      setTimeout( () => {
         var close_btn =  document.getElementsByClassName("adsensei-sticky-ad-close")[0]
         var adsensei_sticky =  document.getElementsByClassName("adsensei-sticky")[0]
         if(close_btn){
         close_btn.addEventListener('click', function(){
            adsensei_sticky.style.display = "none"
            set_adsensei_Cookie_('adsensei_sticky','sticky_ad',1);
         }  )
      }
      }, 100);

})(window.jQuery);

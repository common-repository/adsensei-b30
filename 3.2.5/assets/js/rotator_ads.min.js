
jQuery( document ).ready(function($) {

    /**
     * we are here iterating on each group div to display all ads
     * randomly or ordered on interval or on reload
     */
    let ads_img_href = [];
    let grid_ads_hreflink = [];
    let ads_img_src = [];
    let grid_ads_image_src = [];
    let this_ads = [];
    $(".adsensei-groups-ads-json").each(function(){
        var ad_data_json = $(this).attr('data-json');
        var obj = JSON.parse(ad_data_json);
        var ads_group_id = obj.adsensei_group_id;
        var ads_group_refresh_type = obj.adsensei_refresh_type;
        var ads_group_refresh_type_grid_column = obj.adsensei_refresh_type_grid_column;
        var ads_group_refresh_type_grid_row = obj.adsensei_refresh_type_grid_row;
        // number of ads to show count
        var ads_group_refresh_type_grid_nofadstoshow = obj.adsensei_refresh_type_grid_num_of_ats;
        var ads_group_ref_interval_sec = obj.adsensei_group_ref_interval_sec;
        var ads_ids = obj.ads;
        var ads_ids_length = Object.keys(ads_ids).length;

        var mymin = ads_ids[0].ad_id;
        var mymax = ads_ids[0].ad_id+ads_ids_length-1;
        var min = 0
        var max = ads_ids_length
        var i=0;
        let shouldSkip = false;
        
        // For Number of Ads to show at a Time
        if( ads_group_refresh_type ==='on_interval' && ads_group_refresh_type_grid_column >=1 && ads_group_refresh_type_grid_row >=1 && ads_group_refresh_type_grid_nofadstoshow>=1 ){

            var grid_function_forNofats = () => {

                var content ='';
                var container = jQuery(".adsensei_ad_container[data-id='"+ads_group_id+"']");
                var container_pre = jQuery(".adsensei_ad_container_pre[data-id='"+ads_group_id+"']");

                getRandonmad_id = Math.floor(Math.random() * (max - min)) + min
                adbyindex = ads_ids[getRandonmad_id]
                  var mymin2 = 0;
                  var mymax2 = ads_ids_length-1

                function range2(mymin2, mymax2) {
                    return Array(mymax2 - mymin2 + 1).fill().map((_, idx) => mymin2 + idx)
                }
                var result2 = range2(mymin2, mymax2);

                var loop_to_run = ads_group_refresh_type_grid_nofadstoshow-1
                var ad_ids={};
                for ( let index = 0; index <= loop_to_run; index++ ) {
                 function final_rand2(){
                        var a2 = result2
                        for (a2, i_two = a2.length; i_two--; ) {
                            return a2.splice(Math.floor(Math.random() * (i_two + 1)), 1)[0];
                        }
                    }
                var myres2 = final_rand2()
                function final_rand(){
                    var a = result
                    for (a, i = a.length; i--; ) {
                        return a.splice(Math.floor(Math.random() * (i + 1)), 1)[0];
                    }
                }
                    if(grid_ads_hreflink[mymin]===undefined){
                        grid_ads_hreflink[mymin] = [];
                    }
                
                    if(grid_ads_image_src[mymin]===undefined){
                        grid_ads_image_src[mymin] = [];
                    }
                    grid_ads_hreflink[mymin][index] = ads_ids[myres2].image_redirect_url[0]
                    grid_ads_image_src[mymin][index] = ads_ids[myres2].ad_image[0]

                    // Begin Code to capture Individual AD Impressions
                    ad_ids[index]= 'adsensei-ad'+ads_ids[myres2].ad_id+'';
                    // End Code to capture Individual AD Impressions
                    switch(adbyindex.ad_type[0]){   
                        case "ad_image":
                            setTimeout( () => {
                            var grid_anchor = document.getElementsByClassName("grid_anchor");
                            var grid_anchor_final = grid_ads_hreflink[mymin][index]
                            // grid_anchor[myres2].setAttribute('href',grid_anchor_final);
                            
                            var grid_image = document.getElementsByClassName("grid_image");
                            var grid_image_final = grid_ads_image_src[mymin][index]
                            // grid_image[myres2].setAttribute('src',grid_image_final);
                        }, ads_group_ref_interval_sec);
                        content +='<span id="rotater_id" rotate_id=adsensei-ad'+ads_ids[myres2].ad_id+' index='+index+' random='+myres2+' class="main_gridads"><a target="_blank" class="grid_anchor" href="'+ads_ids[myres2].image_redirect_url[0]+'"><img class="grid_image" src="'+ads_ids[myres2].ad_image[0]+'"></a></span>';
                        container.html(content);
                        break;
                    }
                }
                // Start Ajax to capture impressions 
                if ( document.getElementById('adsensei_ads_front-js') ) {
                $.ajax({
                    type: "POST",    
                    url:adsensei_analytics.ajax_url,                    
                    data:{
                        action:"adsensei_insert_ad_impression",
                        ad_ids:ad_ids,
                        adsensei_front_nonce:adsensei_analytics.adsensei_front_nonce},
                    });
                    // End Ajax to capture impressions 
                }
                setTimeout( grid_function_forNofats, ads_group_ref_interval_sec );
            }
            grid_function_forNofats();
        }

        else if( ads_group_refresh_type ==='on_interval' && ads_group_refresh_type_grid_column >=1  ){
            var grid_function = () =>{
                if(ads_group_refresh_type_grid_column>=1 && ads_group_refresh_type_grid_row == 2 ){
                    $(".adsensei_ad_container").css("grid-template-columns", "auto auto");
                }
                else if(ads_group_refresh_type_grid_column>=1 && ads_group_refresh_type_grid_row == 3 ){
                    $(".adsensei_ad_container").css("grid-template-columns", "auto auto auto");
                }
                else if(ads_group_refresh_type_grid_column>=1 && ads_group_refresh_type_grid_row == 4 ){
                    $(".adsensei_ad_container").css("grid-template-columns", "auto auto auto auto");
                }
                var content ='';
                var container = jQuery(".adsensei_ad_container[data-id='"+ads_group_id+"']");
                var container_pre = jQuery(".adsensei_ad_container_pre[data-id='"+ads_group_id+"']");

                getRandonmad_id = Math.floor(Math.random() * (max - min)) + min
                adbyindex = ads_ids[getRandonmad_id]
                function range(mymin, mymax) {
                    return Array(mymax - mymin + 1).fill().map((_, idx) => mymin + idx)
                  }
                  var result = range(mymin, mymax);
                  var mymin2 = 0;
                  var mymax2 = ads_ids_length-1

                function range2(mymin2, mymax2) {
                    return Array(mymax2 - mymin2 + 1).fill().map((_, idx) => mymin2 + idx)
                }
                var result2 = range2(mymin2, mymax2);

                ads_ids.forEach( (e,index) => {
                    ads_img_href[index] = ads_ids[index].image_redirect_url[0]
                    ads_img_src[index] = ads_ids[index].ad_image[0]
                    function final_rand2(){
                        var a2 = result2
                        for (a2, i_two = a2.length; i_two--; ) {
                            return a2.splice(Math.floor(Math.random() * (i_two + 1)), 1)[0];
                        }
                    }
                    var myres2 = final_rand2()

                switch(adbyindex.ad_type[0]){          
                    case "ad_image":
                        setTimeout( () => {

                            var grid_anchor = document.getElementsByClassName("grid_anchor");
                            var grid_anchor_final = ads_img_href[myres2]
                            grid_anchor[index].setAttribute('href',grid_anchor_final);

                            var grid_image = document.getElementsByClassName("grid_image");
                            var grid_image2 = ads_img_src[myres2]
                            grid_image[index].setAttribute('src',grid_image2);
                        }, 100);
                        
                        content +='<span class="main_gridads"><a target="_blank" class="grid_anchor" href="'+ads_ids[myres2].image_redirect_url[0]+'"><img class="grid_image" src="'+ads_ids[myres2].ad_image[0]+'"></a></span>';
                        container.html(content);
                        break;
                    }
                } );
                setTimeout( grid_function, ads_group_ref_interval_sec );
            }
            grid_function();
        }
        
        else if(ads_group_refresh_type ==='on_interval'){
            var i=0;
            var j = 0;
            j = 1;

            adsenseiShowAdsById(ads_group_id, ads_ids[i], j);
            i++;

            j++;
            var adsensei_ad_on_interval = function () {
                if(i >= ads_ids_length){
                    i = 0;
                }
                var adbyindex = adidrandom = '';
                getRandonmad_id = Math.floor(Math.random() * (max - min)) + min
                adbyindex = ads_ids[getRandonmad_id]
                adsenseiShowAdsById(ads_group_id, adbyindex, j);
                i++;

                j++;
                setTimeout(adsensei_ad_on_interval, ads_group_ref_interval_sec);
            };
            adsensei_ad_on_interval();
        }
        
    });
});

function adsenseiShowAdsById(ads_group_id, adbyindex, j){
    var container = jQuery(".adsensei_ad_container[data-id='"+ads_group_id+"']");
    var container_pre = jQuery(".adsensei_ad_container_pre[data-id='"+ads_group_id+"']");
    var content ='';
    switch(adbyindex.ad_type[0]){
        case "plain_text":
            content +=adbyindex.code[0];
            container.html(content);
            break;
        case "adsense":
            // var bannersize =(adbyindex.ad_banner_size).split("x");
            var width = adbyindex.width[0];
            var height = adbyindex.height[0];
            if(adbyindex.ad_adsense_type[0] == "normal"){
                content +='<script async="" src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>';
                content +='<ins class="adsbygoogle" style="display:inline-block;width:'+width+'px;height:'+height+'px" data-ad-client="'+adbyindex.ad_data_client_id+'" data-ad-slot="'+adbyindex.ad_data_ad_slot+'"></ins>';
            }
            container.html(content);
            break;
        case "double_click":
            var width = adbyindex.width[0];
            var height = adbyindex.height[0];
            var data_slot ="googletag.defineSlot('"+adbyindex.network_code+"/"+adbyindex.ad_unit_name+"/', ["+width+", "+height+"], 'wp_adsensei_dfp_"+ads_group_id+"');";
            content +="<script async src='https://securepubads.g.doubleclick.net/tag/js/gpt.js'></script><script>window.googletag = window.googletag || {cmd: []}; googletag.cmd.push(function() { "+data_slot+"googletag.pubads().enableSingleRequest();googletag.enableServices(); });  </script>";

            content +='<div id="wp_adsensei_dfp_'+ads_group_id+'" style="height:'+height+'px; width:'+width+'px;"><script>googletag.cmd.push(function() { googletag.display("wp_adsensei_dfp_'+ads_group_id+'"); });</script></div>';
            container.html(content);
            break;
        case "yandex":
            var width = adbyindex.width[0];
            var height = adbyindex.height[0];
            var data_slot ="googletag.defineSlot('"+adbyindex.network_code+"/"+adbyindex.ad_unit_name+"/', ["+width+", "+height+"], 'wp_adsensei_dfp_"+ads_group_id+"');";

            content +='<div id="yandex_rtb_'+adbyindex.block_id+'" ></div>\n' +
                '                       <script type="text/javascript">\n' +
                '    (function(w, d, n, s, t) {\n' +
                '        w[n] = w[n] || [];\n' +
                '        w[n].push(function() {\n' +
                '            Ya.Context.AdvManager.render({\n' +
                '                blockId: "'+adbyindex.block_id+ '",\n' +
                '                renderTo: "yandex_rtb_'+adbyindex.block_id+'",\n' +
                '                async: true\n' +
                '            });\n' +
                '        });\n' +
                '        t = d.getElementsByTagName("script")[0];\n' +
                '        s = d.createElement("script");\n' +
                '        s.type = "text/javascript";\n' +
                '        s.src = "//an.yandex.ru/system/context.js";\n' +
                '        s.async = true;\n' +
                '        t.parentNode.insertBefore(s, t);\n' +
                '    })(this, this.document, "yandexContextAsyncCallbacks");\n' +
                '</script>';
            container.html(content);
            break;
        case "mgid":
            content +=' <div id="'+adbyindex.data_container+'"></div> <script src="'+adbyindex.data_js_src+'" async></script>';
            container.html(content);
            break;
        case "taboola":
            content +='<script type="text/javascript">window._taboola = window._taboola || [];\n' +
                '              _taboola.push({article:"auto"});\n' +
                '              !function (e, f, u) {\n' +
                '                e.async = 1;\n' +
                '                e.src = u;\n' +
                '                f.parentNode.insertBefore(e, f);\n' +
                '              }(document.createElement("script"), document.getElementsByTagName("script")[0], "//cdn.taboola.com/libtrc/'+adbyindex.taboola_publisher_id+'/loader.js");\n' +
                '              </script>';
            container.html(content);
            break;
        case "media_net":
            var width = adbyindex.width[0];
            var height = adbyindex.height[0];

            content +='<script id="mNCC" language="javascript">';
            content +='medianet_width = '+width+';';
            content +='medianet_height = '+height+';';
            content +='medianet_crid ='+adbyindex.data_crid;
            content +='medianet_versionId ="3111299";';
            content +='</script>';
            content +='<script src="//contextual.media.net/nmedianet.js?cid='+adbyindex.data_cid+'"></script>';
            container.html(content);
            break;
        case "mediavine":
            content += '<link rel="dns-prefetch" href="//scripts.mediavine.com" />\n' +
                '                  <script type="text/javascript" async="async" data-noptimize="1" data-cfasync="false" src="//scripts.mediavine.com/tags/'+adbyindex.mediavine_site_id+'.js?ver=5.2.3"></script>';
            container.html(content);
            break;
        case "outbrain":
            content += '<script type="text/javascript" async="async" src="http://widgets.outbrain.com/outbrain.js "></script>' +
                '<div class="adsensei_ad_amp_outbrain" data-widget-id="'+adbyindex.outbrain_widget_ids+'"></div>';
            container.html(content);
            break;
        case "ad_image":
            content +='<a target="_blank" href="'+adbyindex.image_redirect_url+'"><img src="'+adbyindex.ad_image+'"></a>';
            container.html(content);
            break;

    }
}


<?php
/**
 * VI Revenue
 */
?>
  
    <div id="adsensei-vi-revenue-wrapper" >
        <div style="clear:both;">
            <strong>vi Stories</strong> is a native video unit, creating a premium ad unit opportunity and monetizing it. 
            It engages your users and increase the time on site due to <strong>contextual video content.</strong>
        </div>
           
        <div id="adsensei-vi-revenue-sum-wrapper" style="float:left;width:50%;">
            Total earnings<br>
            <span id="adsensei-vi-revenue-sum">
            <?php 
            global $adsensei;
            echo $adsensei->vi->getRevenue()->netRevenue;
            ?>
            </span>
        </div>
        <div style="position: relative; height:200px; width:300px">
            <canvas id="adsensei-vi-revenue" width="300" height="200"></canvas>
        </div>
    </div>
<div style="clear:both;"></div>
<div id="adsensei-vi-loggedin-buttons" style="clear:both;display:inline-block;width:100%;">
    <div style="width:50%;float:left;"><a href="<?php echo $dashboardURL; ?>" class="button button-primary" id="adsensei-vi-dashboard" target="_blank"> Publisher Dashboard </a> </div> 
    <div style="width:50%;float:left;"><a href="<?php echo admin_url() . '?adsensei-action=logout_vi&page=adsensei-settings#adsensei_settingsvi_header' ?>" class="button button-secondary"> Logout </a></div>
</div>
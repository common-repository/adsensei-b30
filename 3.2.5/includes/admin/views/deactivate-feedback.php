<?php 
$reasons = array(
    		1 => '<li><label><input type="radio" name="adsensei_disable_reason" value="temporary"/>' . __('It is only temporary', 'adsenseib30') . '</label></li>',
		2 => '<li><label><input type="radio" name="adsensei_disable_reason" value="stopped showing ads"/>' . __('I stopped showing ads on my site', 'adsenseib30') . '</label></li>',
		3 => '<li><label><input type="radio" name="adsensei_disable_reason" value="missing feature"/>' . __('I miss a feature', 'adsenseib30') . '</label></li>
		<li><input type="text" name="adsensei_disable_text[]" value="" placeholder="Please describe the feature"/></li>',
		4 => '<li><label><input type="radio" name="adsensei_disable_reason" value="technical issue"/>' . __('Technical Issue', 'adsenseib30') . '</label></li>
		<li><textarea name="adsensei_disable_text[]" placeholder="' . __('Can we help? Please describe your problem', 'adsenseib30') . '"></textarea></li>',
		5 => '<li><label><input type="radio" name="adsensei_disable_reason" value="other plugin"/>' . __('I switched to another plugin', 'adsenseib30') .  '</label></li>
		<li><input type="text" name="adsensei_disable_text[]" value="" placeholder="Name of the plugin"/></li>',
		6 => '<li><label><input type="radio" name="adsensei_disable_reason" value="other"/>' . __('Other reason', 'adsenseib30') . '</label></li>
		<li><textarea name="adsensei_disable_text[]" placeholder="' . __('Please specify, if possible', 'adsenseib30') . '"></textarea></li>',
    );
shuffle($reasons);
?>


<div id="adsenseib30-feedback-overlay" style="display: none;">
    <div id="adsenseib30-feedback-content">
	<form action="" method="post">
	    <h3><strong><?php _e('If you have a moment, please let us know why you are deactivating:', 'adsenseib30'); ?></strong></h3>
	    <ul>
                <?php 
                foreach ($reasons as $reason){
                    echo $reason;
                }
                ?>
	    </ul>
	    <?php if ($email) : ?>
    	    <input type="hidden" name="adsensei_disable_from" value="<?php echo $email; ?>"/>
	    <?php endif; ?>
	    <input id="adsenseib30-feedback-submit" class="button button-primary" type="submit" name="adsensei_disable_submit" value="<?php _e('Submit & Deactivate', 'adsenseib30'); ?>"/>
	    <a class="button"><?php _e('Only Deactivate', 'adsenseib30'); ?></a>
	    <a class="adsenseib30-feedback-not-deactivate" href="#"><?php _e('Don\'t deactivate', 'adsenseib30'); ?></a>
	</form>
    </div>
</div>
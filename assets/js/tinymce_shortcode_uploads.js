(function() {
	tinymce.create( 'tinymce.plugins.adsensei_shortcode', {
		/**
		 * Initializes the plugin
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function( ed, url ) {
			ed.addButton( 'adsensei_shortcode_button', {
				title: ed.getLang( 'adsensei_shortcode.title', 'WPAdsensei ads shortcodes' ),
				image : '../wp-content/uploads/wpadsensei/wpadsensei_classic_icon.png',
				classes: 'adsensei-tinymce-content-button', 
				cmd: 'adsensei_shortcode_command'
			});
			
			ed.addCommand( 'adsensei_shortcode_command', function() {
					ed.windowManager.open({
						title: ed.getLang( 'adsensei_shortcode.title', 'Adsensei Ads shortcodes' ),
						inline: 1,
						body: [{
							id: 'adsensei-shortcode-modal-container',
							type: 'container',
							minWidth: 220,
							minHeight: 20,
							html: '<span class="spinner adsensei-ad-parameters-spinner adsensei-spinner"></span>',
						}],
						buttons: [{
							text: ed.getLang( 'adsensei_shortcode.ok', 'Add shortcode' ),
							id: 'adsensei-shortcode-button-insert-wrap',
							
							onclick: function( e ) {
								if ( jQuery( '#adsensei-shortcode-modal-container-body #adsensei-select-for-shortcode' ).length > 0 ) {
									var item = jQuery( '#adsensei-select-for-shortcode option:selected' ).val();
									item = item.split( 'ad' );
									if ( item ) {
										console.log(item[1]);
											ed.insertContent( '[adsensei id=' + item[1] + ']' );
									}
								}
								ed.windowManager.close();
							},
						},
						{
							text: ed.getLang( 'adsensei_shortcode.cancel', 'Cancel' ),
							onclick: 'close'
						}],
						
					});

				append_select_field();

			});
		},         
	});
 
	// Register the plugin
	tinymce.PluginManager.add( 'adsensei_shortcode', tinymce.plugins.adsensei_shortcode );

	function append_select_field() {
		var insert_button_wrap = jQuery( '#adsensei-shortcode-button-insert-wrap' ),
			insert_button      = jQuery( '#adsensei-shortcode-button-insert-wrap button' ),
			container_body     = jQuery( '#adsensei-shortcode-modal-container-body' );

		insert_button_wrap.addClass( 'mce-disabled' );
		insert_button.prop( 'disabled', true );

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				'action': 'wpadsensei_ads_for_shortcode',
				'wpadsensei_security_nonce' :adsensei.nonce
			}
		})
		.done( function( data, textStatus, jqXHR ) {
			container_body.html( data );
  
			jQuery( '#adsensei-select-for-shortcode' ).on( 'change', function() {
				if ( jQuery( this ).prop( 'selectedIndex' ) === 0 ) {
					insert_button_wrap.addClass( 'mce-disabled' );
					insert_button.prop( 'disabled', true );
				} else {
					insert_button_wrap.removeClass( 'mce-disabled' );
					insert_button.prop( 'disabled', false );					
				}
			});

		})
		.fail( function( jqXHR, textStatus, errorThrown ) {
			container_body.html( errorThrown );
		});
	}
})();
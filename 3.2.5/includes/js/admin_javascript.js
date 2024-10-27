
jQuery(document).ready(function(){
  for (i = 1; i <= 10; i++) {
    bind_on_change_on_shortcode_select(i);
    add_disabled_class_and_text(i);
  }
})
function add_disabled_class_and_text(adNumber){
  select_ad_position = get_select_in_section(adNumber,3);
  if (select_ad_position.val()=='shortcode'){
    disable_position_ad_settings(adNumber);
  }
}
function bind_on_change_on_shortcode_select(adNumber){
  select_ad_position = get_select_in_section(adNumber,3);
  jQuery(select_ad_position).on('change', function() {
      if (this.value=='shortcode') disable_position_ad_settings(adNumber);
      else enable_position_ad_settings(adNumber);
  });
}

function getAdNameByNumber(adNumber){
  return jQuery("#adsenseib30_settings\\[adName" + adNumber +"\\]");
}
function getAdTextAreaByNumber(adNumber){
  return jQuery("#adsenseib30_settings\\[adCode" + adNumber +"\\]");
}
function getAdEnableStateByNumber(adNumber){
  return jQuery("#adsenseib30_settings\\[adEnabled" + adNumber +"\\]");
}
function getAdTitle(adNumber){
  return jQuery(".rm_title.ad" + adNumber);
}
function getAdSection(adNumber){
  return jQuery(".rm_section.ad" + adNumber);
}
function changeTitleName(adNumber, newValue){
  var title = jQuery("#adTitle"+adNumber);
  title.html(newValue);
}
function changeNameOfAd(adNumber, newValue){

  var ad = getAdNameByNumber(adNumber);
  ad.val(newValue);

  changeTitleName(adNumber, newValue);
}
function newName(adNumber) {
    var title = prompt(objectL10n.newAdName,"");//"Introduce un nuevo nombre para este anuncio", "");

    if (title != null) {
        changeNameOfAd(adNumber,title);
        jQuery("#adsenseib30_form").submit()
    }
}
function adEnableState(adNumber, enabled){
  var adTextArea = getAdTextAreaByNumber(adNumber);

  if (enabled) {
    adTextArea.attr("readonly","false");
    adTextArea.removeAttr('readonly');
  } else {
    adTextArea.attr("readonly","readonly");
  }
  var adEnableState = getAdEnableStateByNumber(adNumber);
  adEnableState.val(enabled);

  var title = getAdTitle(adNumber);
  title.toggleClass("titleDisabled")

  var section = getAdSection(adNumber);
  section.toggleClass("sectionDisabled")

}
function disableAd(adNumber){

  addEnableLink = jQuery("#enableAd"+adNumber);
  addDisableLink = jQuery("#disableAd"+adNumber);
  addEnableLink.show();
  addDisableLink.hide();
  adEnableState(adNumber, false);
  disable_select_in_rm_section(adNumber,1);
  disable_select_in_rm_section(adNumber,2);
  disable_select_in_rm_section(adNumber,3);
  disable_input_in_rm_section(adNumber);
  jQuery("#adsenseib30_form").submit()

}
function enableAd(adNumber){
  addEnableLink = jQuery("#enableAd"+adNumber);
  addDisableLink = jQuery("#disableAd"+adNumber);
  addEnableLink.hide();
  addDisableLink.show();
  adEnableState(adNumber,true);
  enable_select_in_rm_section(adNumber,1);
  enable_select_in_rm_section(adNumber,2);
  enable_select_in_rm_section(adNumber,3);
  enable_input_in_rm_section(adNumber);
  jQuery("#adsenseib30_form").submit()
}
function disable_position_ad_settings(adNumber){
  ad_position_right_text = jQuery(".rm_section.ad"+adNumber+" > .rm_select small")[0];
  ad_position_right_text.innerHTML= "Este anuncio se usará únicamente como shortcode";
  disable_select_in_rm_section(adNumber,1)
}
function enable_position_ad_settings(adNumber){
  ad_position_right_text = jQuery(".rm_section.ad"+adNumber+" > .rm_select small")[0];
  ad_position_right_text.innerHTML="Posición dónde saldrá el anuncio";
  enable_select_in_rm_section(adNumber,1)
}
/** select_number: 1 for first select in section, 2 for the second... etc**/
function disable_select_in_rm_section(adNumber, select_number){
  select = get_select_in_section(adNumber, select_number);
  select.addClass('b30_select_disabled');
  select.attr('disabled', 'disabled');
}
function enable_select_in_rm_section(adNumber, select_number){
  select = get_select_in_section(adNumber, select_number);
  select.removeClass('b30_select_disabled');
  select.removeAttr('disabled');
}
function get_select_in_section(adNumber, select_number){
  select_number = select_number - 1;
  select_jquery = jQuery(".rm_section.ad"+adNumber+" > .rm_select select")[select_number];
  return jQuery(select_jquery);
}
function disable_input_in_rm_section(adNumber){
  input = get_input_in_section(adNumber);
  input.addClass('b30_select_disabled');
  input.attr('disabled', 'disabled');
}
function enable_input_in_rm_section(adNumber, select_number){
  input = get_input_in_section(adNumber, select_number);
  input.removeClass('b30_select_disabled');
  input.removeAttr('disabled');
}
function get_input_in_section(adNumber){
  select_jquery = jQuery(".rm_section.ad"+adNumber+" > .rm_input input");
  return jQuery(select_jquery);
}

<?php
class CategoryText {

  private function __construct() {}


  public static function addCategoryText() {

    ob_start();
    $thisCategory = get_category(get_query_var('cat'),false);
    $category_name = (($thisCategory->name));
    $cat_id= (($thisCategory->cat_ID));
    $category_text_settings = get_option('category_text_settings');
    $categoryText = $category_text_settings[$category_name];
    echo '<div style="color:initial; font-size:initial">'.$categoryText.'</div>';
    $description = ob_get_clean();
    return $description;

  }

  public static function removeCategoryTitle($title){
    if ( is_category() ) {
        $title = single_cat_title( '', false );
    }
    return $title;
  }

	public static function isCallFrom_All_in_One_SEO_Pack() {
		$trace = self::getBackTrace();

		$foundClass = self::foundInBackTrace('All_in_One_SEO_Pack', $trace, 'class');

		return $foundClass;
	}

	private static function getBackTrace() {
		return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	}


	private static function foundInBackTrace($searchFor, $backTrace, $field) {
		foreach ($backTrace as $call) {
			if ( !empty($call[$field]) && $call[$field] == $searchFor ) {
				return true;
			}
		}
		return false;
	}
}
?>

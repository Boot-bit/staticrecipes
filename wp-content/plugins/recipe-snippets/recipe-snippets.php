<?php
/**
 * @link              http://uwaterloo.ca
 * @since             1.0.0
 * @package           Recipe_Snippets
 *
 * @recipe-snippets
 * Plugin Name:       SEO Recipe Snippets
 * Plugin URI:        http://wordpress.org/plugins/recipe-snippets/
 * Description:       Add recipe snippets to Google search results for SEO.
 * Version:           1.0.1
 * Author:            Waterloo Plugins
 * Author URI:        http://uwaterloo/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       recipe-snippets
 */
error_reporting(E_ALL);
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class RecipeSnippets {
	private $props;
	private $showRecipe=false;

	private function time_to_iso8601_duration($time) {
	    $units = array(
	        "Y" => 365*24*3600,
	        "D" =>     24*3600,
	        "H" =>        3600,
	        "M" =>          60,
	        "S" =>           1,
	    );

	    $str = "P";
	    $istime = false;

	    foreach ($units as $unitName => &$unit) {
	        $quot  = intval($time / $unit);
	        $time -= $quot * $unit;
	        $unit  = $quot;
	        if ($unit > 0) {
	            if (!$istime && in_array($unitName, array("H", "M", "S"))) { // There may be a better way to do this
	                $str .= "T";
	                $istime = true;
	            }
	            $str .= strval($unit) . $unitName;
	        }
	    }

	    return $str;
	}

	private function stringToDuration($str) {
		$str=preg_replace('/\band\b/i','',$str);

		$time=strtotime($str,0);

		return $this->time_to_iso8601_duration($time);
	}

	private function format_string($str) {
		$str=preg_replace('/<br\s*\/?>/i',"\n",$str);
		$str=trim($str);
		return $str;
	}

	public function __construct() {
		$this->props=array(
			'@context'=> 'http://schema.org/',
			'@type'=> 'Recipe'
		);
	}

	public function setupPost() {
		if(!is_single())
			return;

		global $post;
		$this->props['name']=$post->post_title;
		$this->props['datePublished']=date('Y-m-d',strtotime($post->post_date));
		if(has_post_thumbnail())
			$this->props['image']=wp_get_attachment_url(get_post_thumbnail_id($post->ID));
	}

	public function processShortcode($attrs, $content='') {
		if(!isset($attrs['type']))
			return;

		$this->showRecipe=true;
		$content=trim($content);

		$type=$attrs['type'];
		$props=&$this->props;
		if($type==='name')
			$props['name']=$content;
		if($type==='image')
			$props['image']=$content;
		if($type==='description')
			$props['description']=$this->format_string($content);
		if($type==='rating')
			$props['ratingValue']=$content;
		if($type==='ratingCount')
			$props['reviewCount']=$content;
		if($type==='prepTime')
			$props['prepTime']=$this->stringToDuration($content);
		if($type==='cookTime')
			$props['cookTime']=$this->stringToDuration($content);
		if($type==='totalTime')
			$props['totalTime']=$this->stringToDuration($content);
		if($type==='yield')
			$props['recipeYield']=$this->format_string($content);
		if($type==='instructions')
			$props['recipeInstructions']=$this->format_string($content);
		if($type==='ingredient'){
			if(!isset($props['recipeIngredient']))
				$props['recipeIngredient']=[];
			$props['recipeIngredient'][]=$content;
		}

		if(!isset($attrs['display'])||$attrs['display']!=='none')
			return $content;
	}

	public function printData() {
		if(!$this->showRecipe)
			return;

		$props=$this->props;

		if(isset($props['ratingValue'])||isset($props['reviewCount'])){
			$t=array('@type'=>'AggregateRating');
			if(isset($props['ratingValue'])){
				$t['ratingValue']=$props['ratingValue'];
				unset($props['ratingValue']);
			}
			if(isset($props['reviewCount'])){
				$t['reviewCount']=$props['reviewCount'];
				unset($props['reviewCount']);
			}
			$props['aggregateRating']=(object)$t;
		}

		if(isset($props['name'])){
			?>
			<script type="application/ld+json">
			<?php echo json_encode((object) $props); ?>
			</script>
			<?php
		}
	}

	function add_action_links($links) {
		$links[] = '<a href="https://wordpress.org/plugins/recipe-snippets/" target="_blank">Instructions</a>';
		return $links;
	}
}

$recipeSnippets = new RecipeSnippets();

add_shortcode('recipe', array(&$recipeSnippets, 'processShortcode'));
add_action('wp', array(&$recipeSnippets, 'setupPost'));
add_action('wp_footer', array(&$recipeSnippets, 'printData'));
add_action('plugin_action_links_recipe-snippets/recipe-snippets.php', array(&$recipeSnippets, 'add_action_links'));

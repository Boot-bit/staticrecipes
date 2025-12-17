<?php
/**
 * Template part for the output of recipe shortcode
 *
 * You can override this template by
 * copying the file to /wp-content/theme_folder/newsplus-post-templates/recipe.php
 *
 * @package NewsPlus_Shortcodes
 * @since 3.0.0
 * @version 3.6.0
 */

$protocol = is_ssl() ? 'https' : 'http';
$meta = '<meta itemprop="url" content="' . esc_url( get_permalink() ) . '" />';
$rp_json = array( '@context' => $protocol . '://schema.org', '@type' => 'Recipe' );


echo '<div class="newsplus-recipe" itemscope itemtype="' . $protocol . '://schema.org/Recipe">';
    $name 			= ( 'custom' == $name_src && '' != $name_txt ) ? $name_txt : get_the_title();
	$author 		= ( 'custom' == $author_src && '' != $author_name ) ? $author_name : get_the_author();
	$author_url 	= ( '' !== $author_url ) ? $author_url : get_author_posts_url( get_the_author_meta( 'ID' ) );

	$img_obj = $image = '';
	if ( has_post_thumbnail() ) {
		$img_obj = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
		$image = $img_obj[0];
	}
	if ( 'media_lib' == $img_src && '' != $img_lib ) {
		$image = wp_get_attachment_image_src ( $img_lib, 'full' );
		$image = $image[0];
	}
	elseif ( 'ext' == $img_src && '' != $img_ext ) {
		$image = $img_ext;
	}

	if ( method_exists( 'NewsPlus_Shortcodes', 'newsplus_image_resize' ) && 'ext' !== $img_src ) {
		$image = NewsPlus_Shortcodes::newsplus_image_resize( $image, $imgwidth, $imgheight, $imgcrop, $imgquality, '', '' );
	}
	
	$meta .= $hide_name 	? '<meta itemprop="name" content="' . esc_attr( $name ) . '" />' : '';
	$meta .= $hide_author 	? '<meta itemprop="author" content="' . esc_attr( $author ) .'">' : '';
	$meta .= $hide_date 	? '<meta itemprop="datePublished" content="' . esc_attr( get_the_date( 'c' ) ) .'">' : '';
	$meta .= $hide_img		? '<meta itemprop="image" content="' . esc_url( $image ) .'">' : '';
	$meta .= ( $hide_summary && '' != $summary ) ? '<meta itemprop="description" content="' . stripslashes( do_shortcode( $summary ) ) .'">' : '';

	// Add to JSON
	$rp_json['name'] = esc_attr( $name );
	$rp_json['author'] = esc_attr( $author );
	$rp_json['image'] = esc_url( $image );
	$rp_json['datePublished'] = esc_attr( get_the_date( 'c' ) );
	$rp_json['url'] = esc_url( get_permalink() );

	// Output schema meta
	echo $meta;
	
	// Recipe heading
	if ( ! $hide_name ) {
		echo '<h2 class="entry-title recipe-title" itemprop="name">' . esc_attr( $name ) . '</h2>';
	}

	// Author and date meta
	if ( ! $hide_author || ! $hide_date ) {
		echo '<ul class="recipe-meta">';

		echo ( ! $hide_date ) ? '<li itemprop="datePublished" datetime="' . esc_attr( get_the_date( 'c' ) ) . '" class="post-date">' . esc_attr( get_the_time( get_option( 'date_format' ) ) ) . '</li>' : '';

		echo ( ! $hide_author ) ? '<li itemprop="author" class="post-author"><a href="' . esc_url( $author_url ) . '">' . esc_attr( $author ) . '</a></li>' : '';

		echo '</ul>';
	}

	// Featured image
 	if ( ! $hide_img && '' != $image ) {
		if ( '' !== $img_caption ) {
			printf( '<div class="wp-caption recipe-image%s"><img itemprop="image" src="%s" alt="%s" /><p class="wp-caption-text">%s</p></div>',
				'none' !== $img_align ? ' align' . esc_attr( $img_align ) : '',
				esc_url( $image ),
				esc_attr( $img_alt ),
				esc_attr( $img_caption )
			);
		}
		else {
			printf( '<img class="recipe-image%s itemprop="image" src="%s" alt="%s" />',
				'none' !== $img_align ? ' align' . esc_attr( $img_align ) : '',
				esc_url( $image ),
				esc_attr( $img_alt )
			);
		}
	}

	// Recipe summary
	if ( ! $hide_summary && '' != $summary ) {
		echo '<h3 class="recipe-summary" itemprop="description">' . stripslashes( do_shortcode( $summary ) ) . '</h3>';
	}
	
	$rp_json['description'] = ( '' != $summary ) ? stripslashes( do_shortcode( $summary ) ) : '';

 	// Prep and cooking time meta
	$total_time = NewsPlus_Shortcodes::newsplus_time_convert( (int)$prep_time + (int)$cook_time );
	$prep_time = NewsPlus_Shortcodes::newsplus_time_convert( (int)$prep_time );
	$cook_time = NewsPlus_Shortcodes::newsplus_time_convert( (int)$cook_time );

	$rp_json['cookTime'] = esc_attr( $prep_time[ 'schema' ] );
	$rp_json['prepTime'] = esc_attr( $cook_time[ 'schema' ] );
	$rp_json['totalTime'] = esc_attr( $total_time[ 'schema' ] );
	$rp_json['recipeYield'] = esc_attr( $recipe_yield );

	echo '<ul class="info-board">';

		echo sprintf( '<li class="prep-time"><meta itemprop="prepTime" content="%s"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
			esc_attr( $prep_time[ 'schema' ] ),
			__( 'Prep Time', 'newsplus' ),
			esc_attr( $prep_time[ 'readable' ] )
		);

		echo sprintf( '<li class="cook-time"><meta itemprop="cookTime" content="%s"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
			esc_attr( $cook_time[ 'schema' ] ),
			__( 'Cook Time', 'newsplus' ),
			esc_attr( $cook_time[ 'readable' ] )
		);

		echo sprintf( '<li class="total-time"><meta itemprop="totalTime" content="%s"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
			esc_attr( $total_time[ 'schema' ] ),
			__( 'Total Time', 'newsplus' ),
			esc_attr( $total_time[ 'readable' ] )
		);

		if ( '' !== $recipe_yield ) {
			echo sprintf( '<li class="recipe-yield"><span class="ib-label">%s</span><span class="ib-value" itemprop="recipeYield">%s</span></li>',
				_x( 'Yield', 'Recipe yield or outcome', 'newsplus' ),
				esc_attr( $recipe_yield )
			);
		}

		if ( '' !== $calories ) {
			echo sprintf( '<li class="recipe-cal"><span class="ib-label">%s</span><span class="ib-value">%s</span></li>',
				_x( 'Energy', 'Label for recipe calories', 'newsplus' ),
				sprintf( _x( '%s cal', 'xx calories', 'newsplus' ), number_format_i18n( (int)$calories ) )
			);
		}

	echo '</ul>';


 	// Cuisine meta
	$rcu 		= NewsPlus_Shortcodes::newsplus_create_list_items( $recipe_cuisine, $recipe_cuisine_other, 'recipeCuisine', true );
	$rcat 		= NewsPlus_Shortcodes::newsplus_create_list_items( $recipe_category, $recipe_category_other, 'recipeCategory', true );
	$rmethod 	= NewsPlus_Shortcodes::newsplus_create_list_items( $cooking_method, '', 'cookingMethod', true );
	$sfd 		= NewsPlus_Shortcodes::newsplus_create_diet_items( $suitable_for_diet, true );

	$rp_json['recipeCuisine'] 	= $rcu['arr'];
	$rp_json['recipeCategory'] 	= $rcat['arr'];
	$rp_json['cookingMethod'] 	= $rmethod['arr'];
	$rp_json['suitableForDiet'] = $sfd['arr'];

	if ( '' !== $rcu || '' !== $rcat || '' !== $rmethod || '' !== $sfd ) {
		echo '<ul class="cuisine-meta">';

			if ( '' !== $rcu ['html']) {
				echo sprintf( '<li><span class="cm-label">%s</span><ul class="cm-items">%s</ul></li>',
					__( 'Cuisine', 'newsplus' ),
					$rcu['html']
				);
			}

			if ( '' !== $rcat['html'] ) {
				echo sprintf( '<li><span class="cm-label">%s</span><ul class="cm-items">%s</ul></li>',
					__( 'Course', 'newsplus' ),
					$rcat['html']
				);
			}

			if ( '' !== $rmethod['html'] ) {
				echo sprintf( '<li><span class="cm-label">%s</span><ul class="cm-items">%s</ul></li>',
					__( 'Cooking Method', 'newsplus' ),
					$rmethod['html']
				);
			}

			if ( '' !== $sfd['html'] ) {
				echo sprintf( '<li><span class="cm-label">%s</span><ul class="cm-items">%s</ul></li>',
					__( 'Suitable for diet', 'newsplus' ),
					$sfd['html']
				);
			}

		echo '</ul>';
	}
	?>

    <div class="ingredients-section clearfix">
		<?php
        // Ingredients
        $ing_list = '';
		$ing_json = array();

        if ( '' !== $ing_heading ) {
			echo '<h3 class="recipe-heading ing-title">' . esc_html( $ing_heading ) . '</h3>';
		}

        foreach ( $ingredients as $ing ) {

            $ing_list = explode( "\n", str_replace("\r", "", $ing->list ) );

            if ( '' !== $ing->title ) {
                echo '<p class="list-subhead"><strong>' . $ing->title . '</strong></p>';
            }

            if ( ! empty( $ing_list ) && is_array( $ing_list ) ) {
                echo '<ul class="ing-list">';
                foreach ( $ing_list as $list_item ) {
                    echo '<li itemprop="recipeIngredient">' . $list_item . '</li>';
					$ing_json[] = $list_item;
                }
                echo '</ul>';
            }
        }

		$rp_json['recipeIngredient'] = $ing_json;
        ?>
    </div><!-- /.ingredients-section -->

    <?php
	if ( '' != $content ) {
	?>
        <div class="method-section clearfix">
            <?php
            // Method (Instructions)
            $num_class = '';
            $ins_json = array();
            $step_count = 1;
            if ( $enable_numbering ) {
				$num_class = ' number-enabled';
            }

            if ( '' !== $method_heading ) {
                echo '<h3 class="recipe-heading ins-title">' . esc_html( $method_heading ) . '</h3>';
            }

			echo '<div class="recipe-instructions' . $num_class . '">' . do_shortcode( $content ) . '</div>';
			
			// Reset global count for method steps
			$GLOBALS['np_recipe_method_count'] = 0;

			// Add instructions to JSON
			$rp_json['recipeInstructions'] = $ins_json;
            ?>
        </div><!-- /.method-section -->
    <?php
	}

	// Other notes
	if ( '' !== $other_notes ) {
		echo '<div class="recipe-other-notes">' . NewsPlus_Shortcodes::newsplus_return_clean( do_shortcode( $other_notes ) ) . '</div>';
	}

 	// Nutrition
	if ( ! $hide_nutrition ) {
		$nutrition_facts = apply_filters( 'newsplus_nutrition_facts_list', array(
			array(
				'id'			=> 'total_fat',
				'label'			=> __( 'Total Fat', 'newsplus' ),
				'schema'		=> 'fatContent',
				'liclass'		=> false,
				'labelclass'	=> 'font-bold',
				'sv'			=> apply_filters( 'total_fat_sv', 78 ),
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'saturated_fat',
				'label'			=> __( 'Saturated Fat', 'newsplus' ),
				'schema'		=> 'saturatedFatContent',
				'liclass'		=> 'nt-sublevel-1',
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'saturated_fat_sv', 20 ),
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'trans_fat',
				'label'			=> __( 'Trans Fat', 'newsplus' ),
				'schema'		=> 'transFatContent',
				'liclass'		=> 'nt-sublevel-1',
				'labelclass'	=> false,
				'sv'			=> false,
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'polyunsat_fat',
				'label'			=> __( 'Polyunsaturated Fat', 'newsplus' ),
				'schema'		=> 'unsaturatedFatContent',
				'liclass'		=> 'nt-sublevel-1',
				'labelclass'	=> false,
				'sv'			=> false,
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'monounsat_fat',
				'label'			=> __( 'Monounsaturated Fat', 'newsplus' ),
				'schema'		=> 'unsaturatedFatContent',
				'liclass'		=> 'nt-sublevel-1',
				'labelclass'	=> false,
				'sv'			=> false,
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'cholesterol',
				'label'			=> __( 'Cholesterol', 'newsplus' ),
				'schema'		=> 'cholesterolContent',
				'liclass'		=> '',
				'labelclass'	=> 'font-bold',
				'sv'			=> apply_filters( 'cholesterol_sv', 300 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'sodium',
				'label'			=> __( 'Sodium', 'newsplus' ),
				'schema'		=> 'sodiumContent',
				'liclass'		=> '',
				'labelclass'	=> 'font-bold',
				'sv'			=> apply_filters( 'sodium_sv', 2300 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'carbohydrate',
				'label'			=> __( 'Total Carbohydrate', 'newsplus' ),
				'schema'		=> 'carbohydrateContent',
				'liclass'		=> '',
				'labelclass'	=> 'font-bold',
				'sv'			=> apply_filters( 'carbohydrate_sv', 275 ),
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'fiber',
				'label'			=> __( 'Dietary Fiber', 'newsplus' ),
				'schema'		=> 'fiberContent',
				'liclass'		=> 'nt-sublevel-1',
				'labelclass'	=> '',
				'sv'			=> apply_filters( 'fiber_sv', 28 ),
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'sugar',
				'label'			=> __( 'Total Sugars', 'newsplus' ),
				'schema'		=> 'sugarContent',
				'liclass'		=> 'nt-sublevel-1',
				'labelclass'	=> '',
				'sv'			=> false,
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'added_sugar',
				'label'			=> __( 'Added Sugars', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> 'nt-sublevel-2',
				'labelclass'	=> '',
				'sv'			=> apply_filters( 'added_sugar_sv', 50 ),
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'sugar_alcohal',
				'label'			=> __( 'Sugar Alcohal', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> 'nt-sublevel-1',
				'labelclass'	=> '',
				'sv'			=> false,
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'protein',
				'label'			=> __( 'Protein', 'newsplus' ),
				'schema'		=> 'proteinContent',
				'liclass'		=> 'nt-sep sep-12',
				'labelclass'	=> 'font-bold',
				'sv'			=> apply_filters( 'protein_sv', 50 ),
				'unit'			=> 'g'
			),
			array(
				'id'			=> 'vitamin_d',
				'label'			=> __( 'Vitamin D (Cholecalciferol)', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_d_sv', 10 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'calcium',
				'label'			=> __( 'Calcium', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'calcium_sv', 1300 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'iron',
				'label'			=> __( 'Iron', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'iron_sv', 18 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'potassium',
				'label'			=> __( 'Potassium', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'potassium_sv', 4700 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'vitamin_a',
				'label'			=> __( 'Vitamin A', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_a_sv', 900 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'vitamin_c',
				'label'			=> __( 'Vitamin C (Ascorbic Acid)', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_c_sv', 90 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'vitamin_e',
				'label'			=> __( 'Vitamin E (Tocopherol)', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_e_sv', 15 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'vitamin_k',
				'label'			=> __( 'Vitamin K', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_k_sv', 120 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'vitamin_b1',
				'label'			=> __( 'Vitamin B1 (Thiamin)', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_b1_sv', 1.2 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'vitamin_b2',
				'label'			=> __( 'Vitamin B2 (Riboflavin)', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_b2_sv', 1.3 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'vitamin_b3',
				'label'			=> __( 'Vitamin B3 (Niacin)', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_b3_sv', 16 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'vitamin_b6',
				'label'			=> __( 'Vitamin B6 (Pyridoxine)', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_b6_sv', 1.3 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'folate',
				'label'			=> __( 'Folate', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'folate_sv', 400 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'vitamin_b12',
				'label'			=> __( 'Vitamin B12 (Cobalamine)', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_b12_sv', 2.4 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'biotin',
				'label'			=> __( 'Biotin', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'biotin_sv', 30 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'choline',
				'label'			=> __( 'Choline', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'choline_sv', 550 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'vitamin_b5',
				'label'			=> __( 'Vitamin B5 (Pantothenic acid)', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'vitamin_b5_sv', 5 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'phosphorus',
				'label'			=> __( 'Phosphorus', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'phosphorus_sv', 1250 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'iodine',
				'label'			=> __( 'Iodine', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'iodine_sv', 150 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'magnesium',
				'label'			=> __( 'Magnesium', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'magnesium_sv', 420 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'zinc',
				'label'			=> __( 'Zinc', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'zinc_sv', 11 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'selenium',
				'label'			=> __( 'Selenium', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'selenium_sv', 55 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'copper',
				'label'			=> __( 'Copper', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'copper_sv', 900 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'manganese',
				'label'			=> __( 'Manganese', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'manganese_sv', 2.3 ),
				'unit'			=> 'mg'
			),
			array(
				'id'			=> 'chromium',
				'label'			=> __( 'Chromium', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'chromium_sv', 35 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'molybdenum',
				'label'			=> __( 'Molybdenum', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> false,
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'molybdenum_sv', 45 ),
				'unit'			=> 'mcg'
			),
			array(
				'id'			=> 'chloride',
				'label'			=> __( 'Chloride', 'newsplus' ),
				'schema'		=> false,
				'liclass'		=> 'nt-sep',
				'labelclass'	=> false,
				'sv'			=> apply_filters( 'chloride_sv', 2300 ),
				'unit'			=> 'mg'
			)
		) );
		?>
		<div class="nutrition-section">
			<ul class="nutrition-table" itemprop="nutrition" itemscope itemtype="http://schema.org/NutritionInformation">
			<?php
			$display_style = 'std';
			$show_dv = false;
			$nutri_json = array( '@type' => 'NutritionInformation', 'calories' => $calories );

			if ( '' !== $nutri_heading ) {
				echo '<li class="nt-header"><span class="nt-title">' . esc_html( $nutri_heading ) . '</span></li>';
			}

			if ( '' !== $serving_size ) {
				printf( '<li class="nt-row sep-12 serving-size"><span class="nt-label col-%s">%s</span><span class="nt-value col-%s" itemprop="servingSize">%s</span></li>',
					'std' == $display_style ? '50' : ( $show_dv ? '80' : '70' ),
					__( 'Serving Size', 'newsplus' ),
					'std' == $display_style ? '50' : ( $show_dv ? '20' : '30' ),						
					esc_attr( $serving_size )
				);
				$nutri_json['servingSize'] = $serving_size;

			}

			printf( '<li class="nt-row b-0 amount-per-serving"><span class="nt-label col-100">%s</span></li>',						
				__( 'Amount per serving', 'newsplus' )
			);
			
			printf( '<li class="nt-row calories sep-6"><span class="nt-label col-%s">%s</span><span class="nt-value col-%s" itemprop="calories">%s</span></li>',
				$show_dv ? '80' : '70',
				__( 'Calories', 'newsplus' ),
				$show_dv ? '20' : '30',	
				number_format_i18n( (int)$calories )
			);
			
			printf( '<li class="nt-head"><span class="pdv-label col-100 text-right">%s</span></li>',
				__( '% Daily Value*', 'newsplus' )
			);				

			foreach( $nutrition_facts as $nf ) {

				if ( isset( ${ $nf['id'] } ) && '' !== ${ $nf['id'] } ) {
					if ( ! empty( $nf['sv'] ) ) {
						$dv = round( (float)${ $nf['id'] } * 100 / $nf['sv'], 2 );
					}
					if ( $show_dv ) {
						$format = '<li%1$s><span class="nt-label col-40%2$s">%3$s</span><span class="nt-amount col-20"%4$s>%5$s</span>%6$s%7$s</li>';
					}						
					else {
						$format = '<li%1$s><span class="nt-label col-40%2$s">%3$s</span><span class="nt-amount col-30"%4$s>%5$s</span>%7$s</li>';
					}

					printf( $format,
						$nf['liclass'] ? ' class="' . esc_attr( $nf['liclass'] ) . '"' : '',
						$nf['labelclass'] ? ' ' . esc_attr( $nf['labelclass']  ) : '',
						esc_attr( $nf['label'] ),
						$nf['schema']  ?  ' itemprop="' . esc_attr( $nf['schema'] ) . '"' : '',
						${ $nf['id'] } . ' ' . $nf['unit'],
						! empty( $nf['sv'] ) ? sprintf( '<span class="nt-sdv col-20">%s</span>', $nf['sv'] . ' ' . $nf['unit'] ) : '',
						! empty( $nf['sv'] ) ? sprintf( '<span class="nt-value col-%s">%s</span>',
							$show_dv ? '20' : '30',
							(int)$dv <= 100 ? $dv . '%' : '<b>' . $dv . '%</b>'
						) : ''
					);
				}
			}
			
			echo sprintf( '<li class="nt-footer">%s</li>',
				__( '*Percent Daily Values are based on a 2,000 calorie diet. Your daily values may be higher or lower depending on your calorie needs.', 'newsplus' )
			);

			// Add nutrition fatcs to JSON
			$rp_json['nutrition'] = $nutri_json;
		
		?>
		</ul><!-- /.nutrition-table -->
		</div><!-- /.nutrition-section -->
	<?php
    } // if not hide nutrition facts

	// Comments Schema
	$comment_num = get_comments_number();
	$rp_json['interactionStatistic'] = array( '@type' => 'InteractionCounter', 'interactionType' => $protocol . '://schema.org/Comment', 'userInteractionCount' => $comment_num );
	?>
    <div itemprop="interactionStatistic" itemscope itemtype="<?php echo $protocol; ?>://schema.org/InteractionCounter">
        <meta itemprop="interactionType" content="<?php echo $protocol; ?>://schema.org/CommentAction" />
        <meta itemprop="userInteractionCount" content="<?php echo esc_attr( $comment_num ); ?>" />
    </div>

    <?php
	/**
	 * User rating Schema
	 * Requires WP Review plugin
	 *
	 * @uses function mts_get_post_reviews( $post_id )
	 */
	if ( function_exists( 'mts_get_post_reviews' ) ) {
		$rating_arr = mts_get_post_reviews( get_the_id() );
		if ( isset( $rating_arr ) && is_array( $rating_arr ) ) {
			if ( $rating_arr['count'] > 0 && $rating_arr['rating'] > 0 ) {
				?>
				<div itemprop="aggregateRating" itemscope itemtype="<?php echo $protocol; ?>://schema.org/AggregateRating"><meta itemprop="ratingValue" content="<?php echo $rating_arr['rating']; ?>" /><meta itemprop="reviewCount" content="<?php echo $rating_arr['count']; ?>" /></div>
				<?php
				$rp_json['aggregateRating'] = array( '@type' => 'AggregateRating', 'ratingValue' => $rating_arr['rating'], 'reviewCount' => $rating_arr['count'] );
			}
        }
	}

	/**
	 * Output JSON LD data as script
	 * Websites like pinterest detect json data better as compared to inline microdata
	 */

	if ( $json_ld ) {
		echo '<script type="application/ld+json">' . json_encode( $rp_json ) . '</script>';
	}
?>
</div><!-- /.newsplus-recipe -->
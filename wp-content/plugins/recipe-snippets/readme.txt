=== Plugin Name ===
Contributors: Waterloo Plugins
Tags: recipe, google, seo, search engine optimization, rich snippets
Requires at least: 3.0
Tested up to: 5.2.2
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show recipe snippets on Google search results.

== Description ==

Enables Rich Snippets for search engines. Show recipe name, rating, image, preparation time, and more on Google's search results.

This plugin allows 1 recipe per post. For example, to set the preparation time of your recipe post, add the following shortcode into your post:
`[recipe type="prepTime"]1 hour and 45 minutes[/recipe]`

All supported properties with examples:

* name
  * The name of the dish.
* image
  * URL of an image of the dish being prepared. 
* description
  * A short summary describing the dish.
* rating
  * A numerical rating for the item.
* ratingCount
  * The count of total number of ratings.
* prepTime
  * The length of time it takes to prepare the recipe for dish.
* cookTime
  * The time it takes to actually cook the dish.
* totalTime
  * The total time it takes to prepare the cook the dish.
* yield
  * The quantity produced by the recipe. For example: number of people served, or number of servings.
* instructions
  * The steps to make the dish.
* ingredient
  * An ingredient used in the recipe. (can have multiples)

These properties will be used by search engines and other services to serve better content to your users. If you want to add a property but you don't want to display the property, you can add `display="none"`. For example:
`[recipe type="description" display="none"]You can see this on Google, but not on my blog[/recipe]`

Here's an example of a fully annotated post:
`[recipe type="name"]Grandma's Holiday Apple Pie[/recipe]
<img src="apple-pie.jpg">
[recipe type="image" display="none"]apple-pie.jpg[/recipe]
Published: [recipe type="datePublished"]November 5, 2009[/recipe]
[recipe type="description"]This is my grandmother's apple pie recipe. I like to add a dash of nutmeg.[/recipe]

[recipe type="rating"]4.0[/recipe] stars based on
[recipe type="ratingCount"]35[/recipe] reviews

Prep time: [recipe type="prepTime"]30 min[/recipe]
Cook time: [recipe type="cookTime"]1 hour[/recipe]
Total time: [recipe type="totalTime"]1 hour 30 min[/recipe]
Yield: [recipe type="yield"]1 9" pie (8 servings)[/recipe]

Ingredients:
[recipe type="ingredient"]Thinly-sliced apples: 6 cups[/recipe]
[recipe type="ingredient"]White sugar: 3/4 cup[/recipe]
...

Directions:
[recipe type="instructions"]
1. Cut and peel apples
2. Mix sugar and cinnamon. Use additional sugar for tart apples.
...
[/recipe]`


== Installation ==

1. Upload `recipe-snippet.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place shortcodes in your post

== Frequently Asked Questions ==

= Why aren't the Rich Snippets showing up in Google's search results? =
It takes a few days to a few weeks for Google to update your site. Also, Google doesn't display Rich Snippets for all sites or all searchs. For example, if you rate every recipe 5/5, then Google probably won't show the rating.

= How can I tell if Rich Snippets are set up correct? =
Enter your blog post's URL here: https://developers.google.com/structured-data/testing-tool/

== Screenshots ==

1. A Google search result with rich recipe snippets

== Changelog ==

= 1.0.1 =
* Clearer instructions

= 1.0 =
* Initial

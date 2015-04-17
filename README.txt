=== BB Edition Control ===
Contributors: Bruno Barros
Donate link: http://brunobarros.com/
Tags: edition, control. magazine, jornal
Requires at least: 3.5.1
Tested up to: 4.1.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin to group the content in editions, as magazines and jornals do.
Plugin para categorizar todo conteúdo em edições, como em um jornal.

== Description ==

Plugin para categorizar todo conteúdo em edições, como em um jornal.

= Shortcodes =

    [bbec-combo] Build a dropdown with the editions to choose.
    Options: id, class, name


    [bbec-list] Build a unordered list with the editions to choose.
    Options: id, class


    [bbec-active-name] Show the name of the current edition.



= Template Helpers = 

    // Returns the current edition object, or a specific field.
    bbec_current_edition( $field = null )


    // Returns the latest edition object, or a specific field.
    bbec_latest_edition( $field = null )


= Languages = 
- English (default)
- Português Brasil (pt_BR)


== Installation ==

== Screenshots ==

1. List of editions
2. Form to create new edition
3. Plugin options
4. Metabox on post edition


== Changelog ==

= 1.3.4 =
* Fix small translations
* Now unpublished editions will show up on post metabox tagged as (Not published)

= 1.3.3 =
* Update/fix admin messages to WP 4.1

= 1.3.0 =
* Add pagination when listing editions (40 per page)
* Add link to quick create a new edition row (just one click)
* Add option to delete an edition
* Add option to preformat the edition name when created by quick process
* Change the way editions were ordered. Now is by 'number' field
* Add Str::slugify() to parse the edition slug when saving

= 1.2.3 =
* Fixed the method `BbEditionControlAdmin::url()` that points to plugin admin page

= 1.2.2 =
* Fixed a small bug of an unused second parameter of `filter_add_new_columns`
* Add class `alternate` on list editions in admin page

= 1.2.1 =
* Setting the post type as "edition-control" independent of the URI (post type slug)
* So the template file is `archive-edition-control.php`


= 1.2.0 =
* Option to configure the URI to show editions
* The same URI string defines a custom post type, so we can have a `archive-slug.php` template
* On admin panel added a link to see the personalized editions page (above)
* Added shortcode [bbec-active-name]

= 1.1.1 =
* Fix array conversion in `admin/BbEditionControlAdmin.php`

= 1.1.0 =
* New options panel
* Screenshots
* New helper functions

= 1.0.0 =
* First commit
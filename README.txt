=== BB Edition Control ===
Contributors: Bruno Barros
Donate link: http://brunobarros.com/
Tags: edition, control. magazine, jornal
Requires at least: 3.5.1
Tested up to: 3.7.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin to group the content in editions, as magazines and jornals do.
Plugin para categorizar todo conteúdo em edições, como em um jornal.

== Description ==

Plugin para categorizar todo conteúdo em edições, como em um jornal.

= Shortcodes =
[bbec-combo] Build a dropdown with the editions to choose
'Options:' id, class, name

[bbec-list] Build a unordered list with the editions to choose
'Options:' id, class

= Template Helpers = 

    // Returns the current edition object, or a specific field.
    bbec_current_edition( $field = null )

    // Returns the latest edition object, or a specific field.
    bbec_latest_edition( $field = null )


= Languages = 
- English (default)
- Português Brasil (pt_BR)

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

= Using The WordPress Dashboard =

1. Na administração vá em 'Plugins' > 'Adicionar novo'
2. Pesquise por 'bb-edition-control'
3. Clique em 'Instalar agora'
4. Ative o plugin

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `plugin-name.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `plugin-name.zip`
2. Extract the `plugin-name` directory to your computer
3. Upload the `plugin-name` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


== Screenshots ==

1. List of editions
2. Form to create new edition
3. Plugin options

== Changelog ==

= 1.1.0 =
* New options panel
* Screenshots
* New helper functions

= 1.0.0 =
* First commit
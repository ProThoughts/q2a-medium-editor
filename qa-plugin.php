<?php

/*
    Plugin Name: Medium Editor
    Plugin URI:
    Plugin Description: Provides the Medium Editor as WYSIWYG editor for your question2answer.
    Plugin Version: 1.0
    Plugin Date: 2016-09-26
    Plugin Author: 38qa.net
    Plugin Author URI: https://38qa.net/
    Plugin Minimum Question2Answer Version: 1.7
    Plugin Update Check URI:
    Licence: MIT
*/


    if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
        header('Location: ../../');
        exit;
    }

    @define( 'MEDIUM_EDITOR_DIR', dirname( __FILE__ ) );

    require_once(MEDIUM_EDITOR_DIR.'/util/functions.php');

    // editor module
    qa_register_plugin_module('editor', 'q2a-medium-editor.php', 'qa_medium_editor', 'Medium Editor');

    // upload page
    qa_register_plugin_module('page', 'q2a-medium-editor-upload.php', 'qa_medium_editor_upload', 'Medium Editor Upload');

    // layer to insert script in head
    qa_register_plugin_layer('q2a-medium-editor-layer.php', 'Medium Editor Layer');

    // language file
    qa_register_plugin_phrases('q2a-medium-editor-lang-*.php', 'q2a_medium_editor_lang');

    // filter module
    qa_register_plugin_module('filter', 'q2a-medium-editor-filter.php', 'q2a_medium_editor_filter', 'Medium Editor Filter');

    /* custom function */

    // check if GIF is animated, credits go to http://php.net/manual/en/function.imagecreatefromgif.php#104473
    if (!function_exists('gif_is_animated')) {
        function gif_is_animated($filename) {
            if(!($fh = @fopen($filename, 'rb')))
                return false;

            $count = 0;
            // an animated gif contains multiple "frames", with each frame having a header made up of:
            // * a static 4-byte sequence (\x00\x21\xF9\x04)
            // * 4 variable bytes
            // * a static 2-byte sequence (\x00\x2C)

            // read through the file till we reach the end of the file, or we have found at least 2 frame headers
            while(!feof($fh) && $count < 2) {
                $chunk = fread($fh, 1024 * 100); // read 100kb at a time
                $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00\x2C#s', $chunk, $matches);
            }

            fclose($fh);
            return $count > 1;
        }
    }
/*
    Omit PHP closing tag to help avoid accidental output
*/

<?php

require_once MEDIUM_EDITOR_DIR.'/qa-plugin.php';

/*
    Plugin Name: Medium Editor
*/

class qa_html_theme_layer extends qa_html_theme_base
{
    const EDITOR_NAME = 'Medium Editor';
    const MAX_FILE_SIZE_MB = 100;

    private function is_medium_editor_active()
    {
        
        if(method_exists('qa_html_theme_layer', 'mdl_is_android_app') &&  $this->mdl_is_android_app()) {
          return false;
        }

        if (qa_is_logged_in()) {
          if (($this->template === 'ask' && qa_opt('editor_for_qs') === self::EDITOR_NAME)
          || ($this->template === 'question' && qa_opt('editor_for_as') === self::EDITOR_NAME)
          || ($this->template === 'question' && qa_opt('editor_for_cs') === self::EDITOR_NAME)
          || ($this->template === 'blog' && qa_opt('qas_blog_editor_for_cs') === self::EDITOR_NAME)
          || ($this->template === 'blog-new' && qa_opt('qas_blog_editor_for_ps') === self::EDITOR_NAME)
          || ($this->template === 'message')) {
              return true;
          }
        } else {
          if ($this->template === 'blog-new' && qa_opt('qas_blog_editor_for_ps') === self::EDITOR_NAME) {
            return true;
          }
        }

        return false;
    }

    public function head_script()
    {
        qa_html_theme_base::head_script();
        if (strpos(qa_opt('site_theme'), 'q2a-material-lite') !== false) {
            $this->output('<link rel="stylesheet" type="text/css" href="'.QA_HTML_THEME_LAYER_URLTOROOT.'css/dialog-polyfill.css" />');
            $this->output('<script src="'.QA_HTML_THEME_LAYER_URLTOROOT.'js/dialog-polyfill.js'.'"></script>');
        }
        if ($this->is_medium_editor_active()) {
            $this->output_css();
            $this->output_js();
        }
        $allow_templates = array(
            'ask',
            'question',
            'blog',
            'blog-new',
            'message'
        );
        if (in_array($this->template, $allow_templates)) {
            $this->output('<link rel="stylesheet" type="text/css" href="'.QA_HTML_THEME_LAYER_URLTOROOT.'css/custom.css" />');
        }
    }

    public function q_view_content($q_view)
    {
        if (isset($q_view['content'])) {
            $q_view['content'] = $this->filter_content($q_view['content']);
        }
        qa_html_theme_base::q_view_content($q_view);
    }

    public function a_item_content($a_item)
    {
        if (isset($a_item['content'])) {
            $a_item['content'] = $this->filter_content($a_item['content']);
        }
        qa_html_theme_base::a_item_content($a_item);
    }

    public function c_item_content($c_item)
    {
        if (isset($c_item['content'])) {
            $c_item['content'] = $this->filter_content($c_item['content']);
        }
        qa_html_theme_base::c_item_content($c_item);
    }

    public function body_footer()
    {
        qa_html_theme_base::body_footer();

        if ($this->is_medium_editor_active()) {
            if (strpos(qa_opt('site_theme'), 'q2a-material-lite') !== false) {
                $this->output_dialog();
                $this->output_js_warn();
            }
        }
    }

    public function medium_editor_embed_replace($text)
    {
        $types = array(
          'youtube' => array(
              array(
                  'https{0,1}:\/\/w{0,3}\.*youtube\.com\/watch\?\S*v=([A-Za-z0-9_-]+)[^< ]*',
                  '<div class="youtube-video"><iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe></div>',
              ),
              array(
                  'https{0,1}:\/\/w{0,3}\.*youtu\.be\/([A-Za-z0-9_-]+)[^< ]*',
                  '<div class="youtube-video"><iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe></video>',
              ),
          ),
        );
        foreach ($types as $t => $ra) {
            foreach ($ra as $r) {
                $text = preg_replace('/<a[^>]+>'.$r[0].'<\/a>/i', $r[1], $text);
                $text = preg_replace('/(?<![\'"=])'.$r[0].'/i', $r[1], $text);
            }
        }
        $text = preg_replace('/class="plain_url"/i', 'class="video video-youtube"', $text);


        $videoPlayer = file_get_contents(MEDIUM_EDITOR_DIR . '/html/video-player.html');
        $video = array(
            '\<div class=\"video-transloadit-id\"\>\[uploaded-video=\"([A-Za-z0-9_-]+)\"\]\<\/div\>',
            $videoPlayer
        );
        $text = preg_replace('/' . $video[0] . '/i',$video[1],$text);

        // 画像タグの変換
        $text = qme_remove_images_class($text);
        $imagetag = file_get_contents(MEDIUM_EDITOR_DIR . '/html/image-url.html');
        $image = array(
            "/\<div class=\"medium-insert-images\">(.*)\<div class=\"image-url\"\>\[image=\"?([^\"\]]+)\"?\]\<\/div\>(.*)<\/div\>/isU",
        );
        $text = qme_remove_anchor($text);
        $text = preg_replace($image[0], $imagetag, $text);

        return $text;
    }

    private function output_css()
    {
        $components = QA_HTML_THEME_LAYER_URLTOROOT.'bower_components/';
        // CSS files
        $this->output('<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">');
        $css_files = array(
            'medium-editor/dist/css/medium-editor.min.css',
            'medium-editor/dist/css/themes/default.min.css',
            'medium-editor-insert-plugin/dist/css/medium-editor-insert-plugin.min.css',
        );
        foreach ($css_files as $css) {
            $this->output('<link rel="stylesheet" type="text/css" href="'.$components.$css.'" />');
        }
    }

    private function output_js()
    {
        $components = QA_HTML_THEME_LAYER_URLTOROOT.'bower_components/';
        // JS files
        $js_files = array(
            'medium-editor/dist/js/medium-editor.min.js',
            'handlebars/handlebars.runtime.min.js',
            'jquery-sortable/source/js/jquery-sortable-min.js',
            'jquery-sortable/source/js/jquery-sortable-min.js',
            'blueimp-file-upload/js/vendor/jquery.ui.widget.js',
            'blueimp-file-upload/js/jquery.iframe-transport.js',
            '../js/jquery.fileupload.min.js',
            'medium-editor-insert-plugin/dist/js/medium-editor-insert-plugin.min.js',
        );
        foreach ($js_files as $js) {
            $this->output('<script src="'.$components.$js.'"></script>');
        }
        $this->output('<script src="'.QA_HTML_THEME_LAYER_URLTOROOT.'js/q2a-embeds.js'.'"></script>');
        $this->output('<script src="'.QA_HTML_THEME_LAYER_URLTOROOT.'js/q2a-videos.js'.'"></script>');
        $this->output('<script src="'.QA_HTML_THEME_LAYER_URLTOROOT.'js/q2a-editor.js'.'"></script>');
        $this->output('<script src="'.QA_HTML_THEME_LAYER_URLTOROOT.'js/override.js'.'"></script>');
        $this->output('<script src="//assets.transloadit.com/js/jquery.transloadit2-v2-latest.js"></script>');
        if (strpos(qa_opt('site_theme'), 'q2a-material-lite') !== false) {
            $this->output('<script src="'.QA_HTML_THEME_LAYER_URLTOROOT.'js/q2a-images.js'.'"></script>');
        }
    }

    public function output_dialog()
    {
        $imageErrorDialog = file_get_contents(MEDIUM_EDITOR_DIR.'/html/image-error-dialog.html');
        $videoDialogTmpl = file_get_contents(MEDIUM_EDITOR_DIR.'/html/video-dialog.html');
        $params = $this->create_params();
        $videoDialog = strtr(
          $videoDialogTmpl,
          $params
        );
        $this->output($imageErrorDialog.$videoDialog);
    }

		/*
		 * Overlayを消す
		 */
		private function remove_overlay($content)
		{
			$overlay = '<div class="medium-insert-videos-overlay"></div>';
			return str_replace($overlay, "", $content);
		}

    private function output_js_warn()
    {
      $warn_message = qa_lang_html('q2a_medium_editor_lang/warn_message');
      $script = QA_HTML_THEME_LAYER_URLTOROOT.'js/medium-editor-util.js';
      $script_mobile = QA_HTML_THEME_LAYER_URLTOROOT.'js/medium-editor-util-mobile.js';
      $this->output('<script>');
      $this->output('var warn_message ="'.$warn_message.'";');
      $this->output('</script>');
      $this->output('<script src="'.$script.'"></script>');
      if(qa_is_mobile_probably()) {
        $this->output('<script src="'.$script_mobile.'"></script>');
      }
    }

    private function create_params()
    {
      return array(
        '^maxFileSizeMB' => self::MAX_FILE_SIZE_MB,
        '^title' => qa_lang_html('q2a_medium_editor_lang/title'),
        '^message' => qa_lang_sub('q2a_medium_editor_lang/message', self::MAX_FILE_SIZE_MB),
        '^select_file' => qa_lang_html('q2a_medium_editor_lang/select_file'),
        '^video_file' => qa_lang_html('q2a_medium_editor_lang/video_file'),
        '^video_size' => qa_lang_html('q2a_medium_editor_lang/video_size'),
        '^video_note' => qa_lang_html_sub('q2a_medium_editor_lang/video_note', self::MAX_FILE_SIZE_MB),
        '^upload' => qa_lang_html('q2a_medium_editor_lang/upload'),
        '^do_upload' => qa_lang_html('q2a_medium_editor_lang/do_upload'),
        '^progress' => qa_lang_html('q2a_medium_editor_lang/progress'),
        '^cancel' => qa_lang_html('q2a_medium_editor_lang/cancel'),
        '^too_big' => qa_lang_html('q2a_medium_editor_lang/too_big'),
        '^upload_message' => qa_lang_html('q2a_medium_editor_lang/upload_message'),
        '^uploading_message' => qa_lang_html('q2a_medium_editor_lang/uploading_message'),
        '^uploaded_message' => qa_lang_html('q2a_medium_editor_lang/uploaded_message'),
        '^error_message' => qa_lang_html('q2a_medium_editor_lang/error_message'),
        '^disconnect_message' => qa_lang_html('q2a_medium_editor_lang/disconnect_message'),
        '^only_one_message' => qa_lang_html('q2a_medium_editor_lang/only_one_message'),
      );
    }

    /*
     * 不要なタグの削除や
     * 埋め込みタグの変換を行う
     */
    private function filter_content($content)
    {
        $tmp = qme_remove_progressbar($content);
        $tmp = qme_remove_style('span', $tmp);
        $tmp = qme_remove_br_tags_in_div($tmp);
        $tmp = $this->remove_overlay($tmp);
        return $this->medium_editor_embed_replace($tmp);
    }
} // end qa_html_theme_layer

/*
    Omit PHP closing tag to help avoid accidental output
*/

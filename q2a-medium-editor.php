<?php

/*
    Plugin Name: Medium Editor
*/

class qa_medium_editor
{
    public $urltoroot;

    public function load_module($directory, $urltoroot)
    {
        $this->urltoroot = $urltoroot;
    }

    public function option_default($option)
    {
        if ($option == 'medium_editor_upload_max_size') {
            require_once QA_INCLUDE_DIR.'qa-app-upload.php';

            return min(qa_get_max_upload_size(), 1048576);
        }
        switch ($option) {
            case 'medium_editor_enabled':
                return 1; // true
            case 'medium_editor_upload_images':
                return 1;
            case 'medium_editor_upload_maximgwidth':
                return 600;
            default:
                return;
        }
    }

    public function bytes_to_mega_html($bytes)
    {
        return qa_html(number_format($bytes / 1048576, 1));
    }

    public function admin_form($qa_content)
    {
        require_once QA_INCLUDE_DIR.'qa-app-upload.php';

        $ok = null;
        if (qa_clicked('medium_editor_save')) {
            qa_opt('medium_editor_upload_images', (bool) qa_post_text('medium_editor_upload_images'));
            qa_opt('medium_editor_upload_max_size', min(qa_get_max_upload_size(), 1048576 * (float) qa_post_text('medium_editor_upload_max_size')));
            qa_opt('medium_editor_upload_maximgwidth', (int) qa_post_text('medium_editor_upload_maximgwidth'));

            $ok = qa_lang('admin/options_saved');
        }
        $fields = array();
        $fields[] = array(
            'type' => 'checkbox',
            'id' => 'medium_editor_upload_images',
            'label' => qa_lang('q2a_medium_editor_lang/upload_images'),
            'tags' => 'name="medium_editor_upload_images" id="medium_editor_upload_images"',
            'value' => (int) qa_opt('medium_editor_upload_images'),
        );
        $fields[] = array(
            'type' => 'number',
            'id' => 'medium_editor_upload_max_size',
            'label' => qa_lang('q2a_medium_editor_lang/upload_max_size'),
            'suffix' => 'MB (max '.$this->bytes_to_mega_html(qa_get_max_upload_size()).')',
            'tags' => 'name="medium_editor_upload_max_size" id="medium_editor_upload_max_size"',
            'value' => $this->bytes_to_mega_html(qa_opt('medium_editor_upload_max_size')),
        );
        $fields[] = array(
            'type' => 'number',
            'id' => 'medium_editor_upload_maximgwidth',
            'label' => qa_lang('q2a_medium_editor_lang/upload_max_img_width'),
            'suffix' => 'px',
            'tags' => 'name="medium_editor_upload_maximgwidth" id="medium_editor_upload_maximgwidth"',
            'value' => (int) qa_opt('medium_editor_upload_maximgwidth'),
        );

        return array(
            'ok' => ($ok && !isset($error)) ? $ok : null,
            'fields' => $fields,
            'buttons' => array(
                array(
                    'label' => qa_lang_html('main/save_button'),
                    'tags' => 'name="medium_editor_save"',
                ),
            ),
        );
    }

    public function calc_quality($content, $format)
    {
        if ($format == '') {
            return 0.8;
        }

        if ($format == 'html') {
            return 1.0;
        }

        return 0;
    }

    public function get_field(&$qa_content, $content, $format, $fieldname, $rows)
    {
        $html = '';
        $placeholder = '';

        $content = $this->embed_replace($content);
        if (empty($format)) {
            $content = nl2br($content);
        }
        if (strpos($fieldname, 'a_') !== false) {
            $placeholder = qa_lang_html('q2a_medium_editor_lang/placeholder_a');
        } elseif (preg_match("/^c\d+/", $fieldname) > 0) {
          if (strpos(qa_request(), 'blog') === false) {
            $placeholder = qa_lang_html('q2a_medium_editor_lang/placeholder_c');
          } else {
            $placeholder = qa_lang_html('q2a_medium_editor_lang/placeholder_blog_c');
          }
        } else {
            $placeholder = qa_lang_html('q2a_medium_editor_lang/placeholder');
        }
        $embed_placeholder = qa_lang_html('q2a_medium_editor_lang/placeholder_embed');

        $maxfilesize = qa_opt('medium_editor_upload_max_size');
        $params = array(
          '^fieldname' => $fieldname,
          '^placeholder' => $placeholder,
          '^embed_placeholder' => $embed_placeholder,
          '^is_mdl' => 'true',
          '^max_image_filesize' => $maxfilesize,
          '^max_image_filesize_mb' => $this->bytes_to_mega_html($maxfilesize),
          '^site_url' => qa_opt('site_url'),
          '^image_type_error' => qa_lang_html('q2a_medium_editor_lang/image_type_error'),
          '^image_size_error' => qa_lang_html_sub('q2a_medium_editor_lang/image_size_error', $this->bytes_to_mega_html($maxfilesize)),
          '^image_type_error_title' => qa_lang_html('q2a_medium_editor_lang/image_type_error_title'),
          '^image_size_error_title' => qa_lang_html('q2a_medium_editor_lang/image_size_error_title'),
          '^image_upload_error_title' => qa_lang_html('q2a_medium_editor_lang/image_upload_error_title'),
        );

        $js = file_get_contents(MEDIUM_EDITOR_DIR.'/js/medium-editor-settings.js');
        $html = '<textarea name="'.$fieldname.'" id="'.$fieldname.'"  class="editable qa-form-tall-text">'.$content.'</textarea>';
        $html .= '<script type="text/javascript">'.strtr($js, $params).'</script>';

        return array(
            'type' => 'custom',
            'html' => $html,
        );
    }

    public function focus_script($fieldname)
    {
        // return "document.getElementById('" . $fieldname . "').focus();";
    }

    public function update_script($fieldname)
    {
        // write html text from sceditor-iframe to textarea - important!
        $jscode = "$('textarea[name=\'".$fieldname."\']').val(get_content('".$fieldname."'));";
        // debugging:
        // $jscode .= "console.log( 'textfield: '+ $('textarea[name=\'".$fieldname."\']').val() ); return false;";
        return $jscode;
    }

    public function embed_replace($text)
    {
        $types = array(
          'youtube' => array(
            array(
              '(https{0,1}:\/\/w{0,3}\.*youtube\.com\/watch\?\S*v=([A-Za-z0-9_-]+))[^< ]*',
              '<div class="video video-youtube"><iframe width="420" height="315" src="//www.youtube.com/embed/$2" frameborder="0" allowfullscreen></iframe></div><div class="plain_url">$1</div>',
            ),
            array(
              'https{0,1}:\/\/w{0,3}\.*youtu\.be\/([A-Za-z0-9_-]+)[^< ]*',
              '<div class="video video-youtube"><iframe width="420" height="315" src="//www.youtube.com/embed/$2" frameborder="0" allowfullscreen></iframe></div><div class="plain_url">$1</div>',
            ),
          ),
        );
        foreach ($types as $t => $ra) {
            foreach ($ra as $r) {
                $text = preg_replace('/<div class="plain_url">'.$r[0].'<\/div>/i', $r[1], $text);
            }
        }

        $videoPlayer = file_get_contents(MEDIUM_EDITOR_DIR . '/html/video-player.html');
        $video = array(
          '\<div class=\"video-transloadit-id\"\>\[uploaded-video=\"([A-Za-z0-9_-]+)\"\]\<\/div\>',
          $videoPlayer
        );
        $text = preg_replace('/' . $video[0] . '/i',$video[1],$text);

        $imagetag = file_get_contents(MEDIUM_EDITOR_DIR . '/html/image-url.html');
        $image = array(
            "/\<div class=\"medium-insert-images\">(.*)\<div class=\"image-url\"\>\[image=\"?([^\"\]]+)\"?\]\<\/div\>(.*)<\/div\>/isU",
            $imagetag
        );
        $text = qme_remove_anchor($text);
        $text = preg_replace($image[0], $image[1], $text);

        return $text;
    }

    public function read_post($fieldname)
    {
        $html = qa_post_text($fieldname);

        return array(
            'format' => 'html',
            'content' => qa_sanitize_html($html, false, true),
        );
    }

}

/*
    Omit PHP closing tag to help avoid accidental output
*/

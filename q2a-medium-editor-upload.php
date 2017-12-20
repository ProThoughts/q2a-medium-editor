<?php

/*
    Plugin Name: Medium Editor
*/

class qa_medium_editor_upload
{
    function match_request($request)
    {
        return ($request == 'medium-editor-upload');
    }

    function process_request($request)
    {
        $response = array();
        $files = array();
        $errormessage = '';
        $url = '';
        $format = '';
        $filename = '';
        $filesize = '';
        
        try {
            if(is_array($_FILES) && count($_FILES)) {
                $filename = $_FILES['files']['name'][0];
                $filetype = $_FILES['files']['type'][0];
                $filetmp = $_FILES['files']['tmp_name'][0];

                // ファイルサイズを取得してログに出力
                $filebytes = filesize($filetmp);
                $fileMB = number_format($filebytes/1048576, 1);
                error_log('upload_file_size: '. $fileMB .'MB');
                
                require_once QA_INCLUDE_DIR.'qa-app-upload.php';
                $img_maxwidth = qa_opt('medium_editor_upload_maximgwidth');
                if($filetype === 'image/gif') {
                    $fileTmpLoc = $_FILES['files']['tmp_name'][0];
                    if(gif_is_animated($fileTmpLoc)) {
                        $img_maxwidth = null;
                    }
                }
                $upload_max_size = qa_opt('medium_editor_upload_max_size');
                $this->file_rotate($filetmp, $filetype);
                $upload = qa_upload_file(
                    $filetmp,
                    $filename,
                    $upload_max_size,
                    false,
                    qa_opt('medium_editor_upload_images') ?
                    $img_maxwidth : null,
                    null
                );

                $errormessage = isset($upload['error']) ? $upload['error'] : '';
                $url = isset($upload['bloburl']) ? $upload['bloburl'] : '';
                $format = isset($upload['format']) ? $upload['format'] : '';

            }

            if(!empty($errormessage)) {
                $files[] = array(
                    'name' => $filename,
                    'error' => $errormessage
                );
            } else {
                $files[] = array(
                    'url' => $url,
                    'name' => $filename,
                    'type' => $filetype
                );
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $files[] = array(
                'name' => 'error',
                'error' => qa_lang_html('q2a_medium_editor_lang/image_upload_error_message'),
            );
        }
        $response['files'] = $files;
        if (isset($_SERVER['HTTP_ACCEPT']) &&
            (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-type: application/json');
        } else {
            header('Content-type: text/plain');
        }
        echo json_encode($response);

    }

    /*
     * EXIF情報のOrientationによって画像を回転
     */
    private function file_rotate($filetmp, $filetype)
    {
        // EXIF情報取得
        // jpeg 画像でEXIF情報が存在する場合
        if($filetype === 'image/jpeg'){
            $exif = exif_read_data($filetmp, 0, true);
            if(isset($exif["IFD0"]["Orientation"])) {
                $content=file_get_contents($filetmp);
                $image=@imagecreatefromstring($content);
                // Orientation によって画像を回転
                switch($exif["IFD0"]["Orientation"]){
                    case 3:
                        $image = imagerotate($image, 180, 0);
                        break;
                    case 6:
                        $image = imagerotate($image, 270, 0);
                        break;
                    case 8:
                        $image = imagerotate($image, 90, 0);
                        break;
                }
                // 回転した画像を元のtmpファイル名で上書き
                imagejpeg($image, $filetmp, 100);
                imagedestroy($image);
            }
        }
    }
}

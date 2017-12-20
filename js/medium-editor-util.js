$(document).ready(function(){
    var warn_on_leave = false;
    // タイトル入力開始
    $('#title').keydown(function(){
        warn_on_leave = true;
    });
    
    // 本文入力開始
    $('.editable').keydown(function(){
        warn_on_leave = true;
    });

    $('.editable').keyup(function(event) {
        var keyCode = event.keyCode;
        
        if (keyCode == 8) {
            var content = get_content($(this).attr('name'));
            if (content.length <= 0) {
                editor.setContent('<p class="medium-insert-active"><br></p>');
            }
        }
    });

    $('.editable').keypress(function(event) {
        var keyCode = event.keyCode;
        
        if (keyCode == 8) {
            var content = get_content($(this).attr('name'));
            if (content.length <= 0) {
                editor.setContent('<p class="medium-insert-active"><br></p>');
            }
        }
    });
    
    // 投稿、保存ボタンは対象外
    $('input[type="submit"]').click(function(){
        warn_on_leave = false;
    });
    
    // inputでなくbuttonの場合も対象外
    $('button').click(function(){
        warn_on_leave = false;
    });
    
    // 画面遷移時のイベント
    var onBeforeunloadHandler = function(e) {
        if(warn_on_leave) {
            return warn_message;
        }
    };
    $(window).on('beforeunload', onBeforeunloadHandler);
    
    $('form').on('submit', function(e) {
        $(window).off('beforeunload', onBeforeunloadHandler);
    });
    
});

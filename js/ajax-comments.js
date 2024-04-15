jQuery(document).ready(function($) {
    $('#comment-form').on('submit', function(event) {
        event.preventDefault();
        var commentContent = $('#comment-content').val();
        var security = $('#security').val();
        var post_id = $('#comm_post_id').val(); // Добавляем получение идентификатора поста
    
        // XXX Проверяем, не пустой ли комментарий
        if (!commentContent.trim()) {
            alert('Комментарий не может быть пустым!');
            return;
        }
    
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'post_comment',
                comment_content: commentContent,
                security: security,
                post_id: post_id
            },
            success: function(response) {
                if (response.success) {
                    $('#comment-content').val('');
                    loadNewComments();
                } else {
                    alert('Ошибка: ' + response.data);
                }
            }
        });
    });
    

    function loadNewComments() {
        // XXX Создаем AJAX-запрос для получения новых комментариев
        $.ajax({
            type: 'GET',
            url: ajax_object.ajax_url,
            data: {
                action: 'get_new_comments',
            },
            success: function(response) {
                // XXX Добавляем новые комментарии к существующим
                $('.comment-list').append(response);
            },
        });
    }
    
    
    
});
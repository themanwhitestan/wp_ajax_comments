<?php
/**
 * UnderStrap functions and definitions
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// UnderStrap's includes directory.
$understrap_inc_dir = 'inc';

// Array of files to include.
$understrap_includes = array(
	'/theme-settings.php',                  // Initialize theme default settings.
	'/setup.php',                           // Theme setup and custom theme supports.
	'/widgets.php',                         // Register widget area.
	'/enqueue.php',                         // Enqueue scripts and styles.
	'/template-tags.php',                   // Custom template tags for this theme.
	'/pagination.php',                      // Custom pagination for this theme.
	'/hooks.php',                           // Custom hooks.
	'/extras.php',                          // Custom functions that act independently of the theme templates.
	'/customizer.php',                      // Customizer additions.
	'/custom-comments.php',                 // Custom Comments file.
	'/class-wp-bootstrap-navwalker.php',    // Load custom WordPress nav walker. Trying to get deeper navigation? Check out: https://github.com/understrap/understrap/issues/567.
	'/editor.php',                          // Load Editor functions.
	'/block-editor.php',                    // Load Block Editor functions.
	'/deprecated.php',                      // Load deprecated functions.
);

// Load WooCommerce functions if WooCommerce is activated.
if ( class_exists( 'WooCommerce' ) ) {
	$understrap_includes[] = '/woocommerce.php';
}

// Load Jetpack compatibility file if Jetpack is activiated.
if ( class_exists( 'Jetpack' ) ) {
	$understrap_includes[] = '/jetpack.php';
}

// Include files.
foreach ( $understrap_includes as $file ) {
	require_once get_theme_file_path( $understrap_inc_dir . $file );
}


// XXX Аякс Комментарии

// XXX Добавляем кастомный JS
function add_ajax_script() {
    wp_enqueue_script( 'custom-ajax-script', get_template_directory_uri() . '/js/ajax-comments.js', array('jquery'), '1.0', true );
    wp_localize_script( 'custom-ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'add_ajax_script' );


function post_comment() {
    // XXX Проверка прав на отправку комментариев
    if ( !is_user_logged_in() ) {
        wp_send_json_error( 'Вы должны быть залогинены, чтобы отправить комментарий.' );
    }

    // XXX Проверка nonce
    if ( !check_ajax_referer( 'ajax-comment-nonce', 'security', false ) ) {
        wp_send_json_error( 'Ошибка безопасности. Попробуйте еще раз.' );
    }

    // XXX Получаем данные AJAX
    $comment_content = sanitize_text_field( $_POST['comment_content'] );
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0; // Получаем идентификатор поста из AJAX-запроса
    $parent_comment_id = isset( $_POST['parent_comment_id'] ) ? intval( $_POST['parent_comment_id'] ) : 0; // Получаем идентификатор родительского комментария из AJAX-запроса

    // XXX Проверка содержимого комментария
    if ( empty( $comment_content ) ) {
        wp_send_json_error( 'Комментарий не может быть пустым.' );
    }

    // XXX Создаем новый комментарий
    $comment_data = array(
        'comment_post_ID' => $post_id, // Используем идентификатор поста
        'comment_author' => $user->display_name, // Используем имя пользователя
        'comment_content' => $comment_content,
        'user_id' => $user_id,
        'comment_parent' => 0 // Устанавливаем родительский комментарий в 0
    );

    $comment_id = wp_new_comment( $comment_data );

    if ( $comment_id ) {
        wp_send_json_success( 'Комментарий отправлен' );
    } else {
        wp_send_json_error( 'Ошибка при отправке комментария' );
    }
}
add_action( 'wp_ajax_post_comment', 'post_comment' );
add_action( 'wp_ajax_nopriv_post_comment', 'post_comment' );



function get_new_comments() {
    $args = array(
        'status' => 'approve',
        'number' => 1, 
        'post_id' => get_the_ID(), 
        'order' => 'DESC', 
    );

    // XXX Получаем последний опубликованный комментарий
    $comments = get_comments($args);

    // XXX Проверяем, есть ли комментарии
    if ($comments) {
        // XXX Форматируем комментарий, в соответствии стилю wp_list_comments()
        $output = '';
        $output .= wp_list_comments(array(
            'style' => 'ol',
            'short_ping' => true,
            'echo' => false, // XXX Возвращаем разметку в переменную
        ), $comments);
    } else {
        // XXX Если комментариев нет, выводим пустой контейнер
        $output = '<ol class="comment-list"></ol>';
    }

    // XXX Возвращаем HTML-разметку последнего комментария
    echo $output;

    wp_die();
}
add_action('wp_ajax_get_new_comments', 'get_new_comments');
add_action('wp_ajax_nopriv_get_new_comments', 'get_new_comments');

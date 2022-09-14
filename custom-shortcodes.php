<?php

add_shortcode('post_list', 'post_list_function');

function post_list_function($args) : string {
    $args = wp_parse_args($args, [
        'type'  => 'post',
        'limit' => 6,
    ]);
    $out = [];

    $ids = [];
    // check if we have a predefined list of ids
    if ( ! empty($args['id'])) {
        $ids = array_filter(explode(',', $args['id']), function ($id) {
            return ! empty($id);
        });
        $ids = array_map('intval', $ids);
    }

    // if we don't have a predefined list of ids, get the latest posts based on 'limit' parameter
    if (empty($ids)) {
        $queryArgs = [
            'post_type'              => isset($args['type']) && post_type_exists($args['type']) ? $args['type'] : 'page',
            'posts_per_page'         => ! empty($args['limit']) && is_numeric($args['limit']) ? intval($args['limit']) : 10,
            'ignore_sticky_posts'    => true,
            'fields'                 => 'ids',
            'cache_results'          => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ];
        $ids = get_posts($queryArgs);
        wp_reset_postdata();
    }

    // assemble the output
    foreach ($ids as $id) {
        $img = has_post_thumbnail($id) ? get_the_post_thumbnail($id, 'post-preview')  : '<span class="post-list-item__no-image"></span>';
        $excerpt = has_excerpt($id) ? wpautop(get_the_excerpt($id)) : '';
        $category = !empty(get_the_category($id)) ? wpautop(get_the_category($id)[0]->name) : null;
        $out[] = "
            <div class='post-list-item'>
                <a href='".get_permalink( $id )."' class='post-list-item__image'>{$img}</a>
                <div class='post-list-item__category''>{$category}</div>
                <h4 class='post-list-item__title'>" . get_the_title($id) . "</h4>
                <div class='post-list-item__excerpt''>{$excerpt}</div>
            </div>
        ";
    }
    return "<div class='post-list'>" . implode('', $out) . "</div>";
}


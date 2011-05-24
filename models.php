<?php

function get_post_by_id($id) {
    $post = new stdClass();
    $post->id = $id;
    $post->title = 'Dummy title';
    $post->slug = 'dummy-title';
    return $post;
}

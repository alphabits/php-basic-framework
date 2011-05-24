<?php


function run_blogindex($vars) {
    return render_template('blogindex.html');
}

function run_blogpost($vars) {
    $post = get_post_by_id($vars['id']);
    
    if (!$post) {
        return http_404();
    }

    if ($post->slug !== $vars['slug']) {
        return redirect(
            url_for('blogpost', array(
                'id'=>$post->id, 
                'slug'=>$post->slug
            ))
        );
    }

    return render_template('blogpost.html', compact('post'));
}

function run_page($vars) {
    return url_for('page', array('pagename'=>'jorgen'));
}

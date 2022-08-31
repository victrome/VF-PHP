<?php
$app = get_post_meta($post->ID, 'VF_folder', true);
$action = get_post_meta($post->ID, 'VF_action', true);
$vf->app($app, $action);
<?php
    include_once 'includes/twig_loader.php';

    $team = array(
        array(
            'name' => 'tterrag',
            'img' => 'https://avatars2.githubusercontent.com/u/3751664?v=3&s=460'
        ),
        array(
            'name' => 'Drullkus',
            'img' => 'https://avatars1.githubusercontent.com/u/5010174?v=3&s=460'
        ),
        array(
            'name' => 'minecreatr',
            'img' => 'https://avatars3.githubusercontent.com/u/6035929?v=3&s=460'
        )
    );
    $loader->show('home.twig', array('team' => $team));
?>

<?php

require 'vendor/autoload.php';

// Create app
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
]);

// Get container
$container = $app->getContainer();

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig('templates', [
        'debug' => true,
        'cache' => 'compilation_cache'
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};

// Add 404 page handler
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['view']->render($response, '404.twig')->withStatus(404);
    };
};

$app->get('/', function ($req, $resp, $args) {
    return $resp->withRedirect("index");
});

function getTwitterIcon($user) {
    $settings = array(
        'oauth_access_token' => "898577948-87Ohfn3nkEltFwkGfeAnNyzsYrKIILa0CYmJhnWl",
        'oauth_access_token_secret' => "OwdwX0sgSNoVj5MmptRJywI7PXeFTI8k4db9nxCvy4tdh",
        'consumer_key' => "dPhQ6KBoxCoSSBfLhlpYBzyje",
        'consumer_secret' => "CFfstrXkI0MG59NlHRogeMcpT7IOpFdAJ62zf91mgSHphPNUP5"
    );

    $url = 'https://api.twitter.com/1.1/users/show.json';
    $requestMethod = 'GET';

    $twitter = new TwitterAPIExchange($settings);

    $user_data = json_decode($twitter->setGetField("?screen_name=$user")->buildOauth($url, $requestMethod)->performRequest());
    $url = $user_data->profile_image_url;
    $url = str_replace("_normal", "", $url);
    return $url;
}

$app->get("/index", function ($req, $resp, $args) {
    $team = [
        [
            'name' => 'tterrag',
            'img' => getTwitterIcon('tterrag1098'),
            'bio' => 'Some bio lol',
        ],
        [
            'name' => 'Drullkus',
            'img' => getTwitterIcon('Drullkus'),
        ],
        [
            'name' => 'minecreatr',
            'img' => getTwitterIcon('minecreatr'),
        ]
    ];

    return $this->view->render($resp, "index.twig", ['page_title' => 'Home', 'team' => $team]);
});

$app->get("/ctm", function ($req, $resp, $args) {
    return $this->view->render($resp, 'ctm.twig', ['page_title' => 'Chisel']);
});

$app->get("/chisel", function ($req, $resp, $args) {
    return $this->view->render($resp, 'chisel.twig', ['page_title' => 'Chisel']);
});

$app->get("/about", function ($req, $resp, $args) {
    return $this->view->render($resp, 'about.twig', ['page_title' => 'Chisel']);
});

// Run app
$app->run();

?>

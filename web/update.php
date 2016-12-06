<?php
    include_once(__DIR__ . '/classes/BuildList.php');

    // This file exports plain .json for use in forge's update handler

    $output = array();
    $output['homepage'] = "http://chisel.team/";

    $builds = new BuildList;

    $promos = array();
    foreach ($builds->getVersions() as $version) {
        $promos["$version-latest"]      = $builds->getLatest($version);
        $promos["$version-recommended"] = $builds->getRecommended($version);
    }

    $output['promos'] = $promos;

    echo "<pre>\n";
    echo json_encode($output, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n</pre>\n";
?>

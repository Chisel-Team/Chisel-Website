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
        foreach ($builds->getAllBuilds($version) as $buildurl) {
            $build_data = json_decode(file_get_contents("$buildurl/api/json"));
            $artifact_name = $build_data->artifacts[0]->fileName;
            preg_match("/(MC(\.?[0-9]+)+-)?(\.?[0-9]+)+/", $artifact_name, $matches);
            $fullversion = $matches[0];
            preg_match("/(?:MC((?:\.?[0-9]+)+)-)/", $artifact_name, $matches);
            $mcversion = $matches[1];

            $output[$mcversion][$fullversion] = join("\n", array_map(function($val) {
                return $val->msg;
            }, $build_data->changeSet->items));
        }
    }

    $output['promos'] = $promos;

    echo "<pre>\n";
    echo json_encode($output, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n</pre>\n";
?>

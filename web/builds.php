<?php

    include_once 'includes/twig_loader.php';

    $branch = isset($_GET['branch']) ? $_GET['branch'] : '1.11/dev';
    $proj_url = "http://ci.tterrag.com/job/Chisel/job/" . str_replace('/', '-', $branch);
    $proj_api = $proj_url . "/api/json";

    $proj_json = file_get_contents($proj_api);
    $proj_data = json_decode($proj_json);

    $builds = array();
    foreach ($proj_data->builds as $build) {
        $number = $build->number;
        $build_json = file_get_contents($build->url . '/api/json');
        $build_data = json_decode($build_json);

        // If the build failed, do not display it
        if ($build_data->result == 'FAILURE') continue;

        // Filter out the correct action, we want the one with "parameters" field
        $build_params = array_values(array_filter($build_data->actions, function($v) { return isset($v->parameters); }))[0]->parameters;
        $artifact_name = $build_data->artifacts[0]->displayPath;

        $match_data = array();
        // This horribleness matches for a version in our filename (plus some edge characters to be sure)
        preg_match("/-([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)[-\.]/", $artifact_name, $match_data);
        // Extract the first group match (the actual version string)
        $version = $match_data[1];
        $type = $build_params[0]->value == 'true' ? 'Release' : 'Development';
        $changelog_raw = trim($build_params[1]->value);
        $changelog;
        if ($changelog_raw == 'none' || $changelog_raw == '') {
            $changelog = array_map(function($val) {
                return $val->msg;
            }, $build_data->changeSet->items);
        } else {
            $changelog = preg_split("/[\r\n]+/", str_replace('- ', '', $changelog_raw));
        }

        $builds[$number] = (object) [
            'number' => $number,
            'version' => $version,
            'type' => $type,
            'changelog' => $changelog,
        ];
    }

    $loader->show('builds.twig', array('branch' => $branch, 'builds' => $builds, 'branches' => array('1.9/dev', '1.10/dev', '1.11/dev')));

?>

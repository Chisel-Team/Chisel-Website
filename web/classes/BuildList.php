<?php

class BuildList {

    public function getBranches() {
        $root_api = "http://ci.tterrag.com/job/Chisel/api/json";
        $root_data = json_decode(file_get_contents($root_api));

        return array_map(function($v) {
            return urldecode($v->name);
        }, $root_data->jobs);
    }

    public function getVersions() {
        $versions = array();
        $branches = $this->getBranches();
        var_dump($branches);

        foreach ($branches as $branch) {
            preg_match("/([0-9]+\.?)+/", $branch, $matches);
            $version = $matches[0];
            if (!in_array($version, $versions)) {
                $versions[] = $matches[0];
            }
        }

        return $versions;
    }

    public function getAllBuilds($version) {
        $branches = $this->getBranches();
        $builds = array();
        foreach ($branches as $branch) {
            if (strpos($branch, $version) !== false) {
                $proj_api = "http://ci.tterrag.com/job/Chisel/branch/" . str_replace('/', '%252F', $branch) . "/api/json";
                $proj_data = json_decode(file_get_contents($proj_api));

                $builds = array_merge($builds, array_map(function($v) {
                    return $v->url;
                }, $proj_data->builds));
            }
        }
        return $builds;
    }

    public function getLatest($version, $release = false) {
        $branches = $this->getBranches();
        foreach ($branches as $branch) {
            if (strpos("$version/dev", $branch) !== false) {
                return $release ? $this->getReleaseBuild($branch) : $this->getLatestBuild($branch);
            }
        }
        return null;
    }

    public function getRecommended($version) {
        $branches = $this->getBranches();
        foreach ($branches as $branch) {
            if (strpos("$version/release", $branch) !== false) {
                return $this->getLatestBuild($branch);
            }
        }
        return $this->getLatest($version, true);
    }

    public function getLatestBuild($branch) {
        $proj_api = "http://ci.tterrag.com/job/Chisel/job/" . str_replace('/', '%252F', $branch) . "/api/json";
        $proj_data = json_decode(file_get_contents($proj_api));

        $build_api = $proj_data->lastSuccessfulBuild->url . "/api/json";
        $build_data = json_decode(file_get_contents($build_api));

        $artifact_name = $build_data->artifacts[0]->fileName;
        preg_match("/(MC(\.?[0-9]+)+-)?(\.?[0-9]+)+/", $artifact_name, $matches);

        return $matches[0];
    }

    public function getReleaseBuild($branch) {
        $proj_api = "http://ci.tterrag.com/job/Chisel/job/" . str_replace('/', '%252F', $branch) . "/api/json";
        $proj_data = json_decode(file_get_contents($proj_api));

        foreach ($proj_data->builds as $build) {
            $number = $build->number;
            $build_api = $build->url . '/api/json';
            $build_data = json_decode(file_get_contents($build_api));

            // Filter out the correct action, we want the one with "parameters" field
            $build_params = array_values(array_filter($build_data->actions, function($v) { return isset($v->parameters); }))[0]->parameters;

            if ($build_params[0]->value) {
                $artifact_name = $build_data->artifacts[0]->fileName;
                preg_match("/(MC(\.?[0-9]+)+-)?(\.?[0-9]+)+/", $artifact_name, $matches);
                return $matches[0];
            }
        }

        return null;
    }
}

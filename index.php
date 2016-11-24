<html>
<head>
    <link rel="stylesheet" href="index.css"/>
    <script type="text/javascript" src="jquery-3.1.0.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.build').click(function() {
                var selector = '#' + $(this).attr('id') + ' .changelog';
                $(selector).slideToggle();
            });
        });
    </script>
</head>

<body>
    <div id="main">
        <div id="content">
            <h1>
                Chisel Builds
            </h1>
<?php
            $branch = isset($_GET['branch']) ? $_GET['branch'] : '1.11/dev';
            $proj_url = "http://ci.tterrag.com/job/Chisel/branch/" . str_replace('/', '%252F', $branch);
            $proj_api = $proj_url . "/api/json";

            $proj_json = file_get_contents($proj_api);
            $proj_data = json_decode($proj_json);

            echo '<h3 class="center">' . $branch . '</h3>';
            echo '<div id="builds">';
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

                // Begin printing HTML for this build
                echo '<div class="build ' . strtolower($type) . '" id="' . $number . '">';
                echo $version . '&nbsp';
                echo $type;
                // Default to display:none for jquery toggle
                echo '<div class="changelog" style="display: none;">';
                // Convert \n to <br>
                echo nl2br($build_params[1]->value);
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
?>
        </div>
    </div>
</body>

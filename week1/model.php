<?php
/**
 * Model
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

/* Enable error reporting */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * counts how many series there are in the database
 * @param $db
 * @return int
 */
function count_series($db){
    $sql = 'SELECT count(*) FROM series';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $series_count = $stmt->fetchcolumn();
    return $series_count;
}
/**
 * connects to the database
 * @param $host
 * @param $db
 * @param $user
 * @param $pass
 * @return databse
 */
function connect_db($host, $db, $user, $pass)
{
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        if ($pdo) {
            return $pdo;
        }
    } catch (PDOException $e) {
        echo sprintf("Failed to connect. %s", $e->getMessage());
    }

}

/**
 * returns an array from the database
 * @param $db
 * @return array
 */
function get_series($db){
    $sql = 'SELECT * FROM series';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $series = $stmt->fetchAll();
    $series_exp = Array();
    /* Create array with htmlspecialchars */
    foreach ($series as $key => $value){
        foreach ($value as $user_key => $user_input) {
            $series_exp[$key][$user_key] = htmlspecialchars($user_input);
        }
    }
    return $series_exp;
}

/**
 * returns a table containing all series in the database
 * @param $series
 * @return string
 */
function get_serie_table($series){
    $table_exp =
        '
        <table class="table table-hover">
        <thead
    <tr>
        <th scope="col">Series</th>
        <th scope="col"></th>
        </tr>
        </thead>
        <tbody>';
    foreach($series as $key => $value){
        $table_exp .=
            '
        <tr>
        <th scope="row">'.$value['name'].'</th>
        <td><a href="/DDWT21/week1/series/?serie_id='.$value['id'].'" 
        role="button" class="btn btn-primary">More info</a></td>
        </tr>
    ';
    }
    $table_exp .=
        '
        </tbody>
        </table>
    ';
    return $table_exp;
}

function get_serie_info($db, $serie_id)
{
    $sql = 'SELECT * FROM series WHERE id = ?';
    $stmt = $db->prepare($sql);
    $stmt->execute([$serie_id]);
    $serie_info = $stmt->fetch();
    $series_info_arr = Array();
    /* Create array with htmlspecialchars */
    foreach ($serie_info as $key => $value) {
        $series_info_arr[$key] = htmlspecialchars($value);
    }
    return $series_info_arr;
}
/**
 * Adds a serie from the database
 * @param $serie_info
 * @param $db
 * @return array
 */
function add_series($serie_info, $db){
    /* Check if all fields are set */
    if (
        empty($serie_info['Name']) or
        empty($serie_info['Creator']) or
        empty($serie_info['Seasons']) or
        empty($serie_info['Abstract'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'Error: Serie is not added, because not all fields were filled in.'
        ];
    }
    /* Check data type */
    if (!is_numeric($serie_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'Error: Serie is not added, because seasons should only contain numbers.'
        ];
    }
    /* Check if serie already exists */
    $sql = 'SELECT * FROM series WHERE name = ?';
    $stmt = $db->prepare($sql);
    $stmt->execute([$serie_info['Name']]);
    $serie = $stmt->rowCount();
    if ($serie){
        return [
            'type' => 'danger',
            'message' => 'Error: Serie is not added, because serie already exist.'
        ];
    }
    /* Add Serie */
    $sql1 = "INSERT INTO series (name, creator, seasons, abstract) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($sql1);
    $stmt->execute([
        $serie_info['Name'],
        $serie_info['Creator'],
        $serie_info['Seasons'],
        $serie_info['Abstract']
    ]);
    $inserted = $stmt->rowCount();
    if ($inserted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' is successfully added to Series Overview.", $serie_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'danger',
            'message' => 'Error: Unknown error, the series was not added.'
        ];
    }
}

/**
 * Upddates a serie from the database
 * @param $serie_info
 * @param $db
 * @return array
 */
function updated_series($serie_info, $db){
    /* Check if all fields are set */
    if (
        empty($serie_info['Name']) or
        empty($serie_info['Creator']) or
        empty($serie_info['Seasons']) or
        empty($serie_info['Abstract']) or
        empty($serie_info['serie_id'])
    ) {
        return [
            'type' => 'danger',
            'message' => 'Error: Serie is not added, because not all fields were filled in.'
        ];
    }
    /* Check data type */
    if (!is_numeric($serie_info['Seasons'])) {
        return [
            'type' => 'danger',
            'message' => 'Error: Serie is not added, because seasons should only contain numbers.'
        ];
    }
    /* Get current series name */
    $sql = ('SELECT * FROM series WHERE id = ?');
    $stmt = $db->prepare($sql);
    $stmt->execute([$serie_info['serie_id']]);
    $serie = $stmt->fetch();
    $current_name = $serie['name'];

    /* Check if serie already exists */
    $sql2 = 'SELECT * FROM series WHERE name = ?';
    $stmt = $db->prepare($sql2);
    $stmt->execute([$serie_info['Name']]);
    $serie = $stmt->fetch();
    if ($serie_info['Name'] == ($serie['name'])) {
        if ($serie['name'] != $current_name){
            return [
                'type' => 'danger',
                'message' => sprintf("Error: Serie is not added, because %s already exist.",
                    $serie_info['Name'])
            ];
        }
    }
    /* Update Serie */
    $sql3 = "UPDATE series SET name = ?, creator = ?, seasons = ?, abstract = ? WHERE id = ?";
    $stmt = $db->prepare($sql3);
    $stmt->execute([
        $serie_info['Name'],
        $serie_info['Creator'],
        $serie_info['Seasons'],
        $serie_info['Abstract'],
        $serie_info['serie_id']
    ]);
    $updated = $stmt->rowCount();
    if ($updated == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was edited!", $serie_info['Name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'Error: The series was not edited, because there were no changes detected.'
        ];
    }
}

/**
 * Removes a serie from the database
 * @param $db
 * @param $serie_id
 * @return array
 */
function remove_serie($db, $serie_id){
    $serie_info = get_serie_info($db, $serie_id);
    /* Delete Serie */
    $sql = "DELETE FROM series WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$serie_id]);
    $deleted = $stmt->rowCount();
    if ($deleted == 1) {
        return [
            'type' => 'success',
            'message' => sprintf("Series '%s' was successfully removed!", $serie_info['name'])
        ];
    }
    else {
        return [
            'type' => 'warning',
            'message' => 'Error: An unkown error occurred. The series was not removed.'
        ];
    }
}

/**
 * Check if the route exists
 * @param string $route_uri URI to be matched
 * @param string $request_type Request method
 * @return PDO
 */
function new_route($route_uri, $request_type){
    $route_uri_expl = array_filter(explode('/', $route_uri));
    $current_path_expl = array_filter(explode('/',parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    if ($route_uri_expl == $current_path_expl && $_SERVER['REQUEST_METHOD'] == strtoupper($request_type)) {
        return True;
    } else {
        return False;
    }
}

/**
 * Creates a new navigation array item using URL and active status
 * @param string $url The URL of the navigation item
 * @param bool $active Set the navigation item to active or inactive
 * @return array
 */
function na($url, $active){
    return [$url, $active];
}

/**
 * Creates filename to the template
 * @param string $template Filename of the template without extension
 * @return string
 */
function use_template($template){
    return sprintf("views/%s.php", $template);
}

/**
 * Creates breadcrumbs HTML code using given array
 * @param array $breadcrumbs Array with as Key the page name and as Value the corresponding URL
 * @return string HTML code that represents the breadcrumbs
 */
function get_breadcrumbs($breadcrumbs) {
    $breadcrumbs_exp = '<nav aria-label="breadcrumb">';
    $breadcrumbs_exp .= '<ol class="breadcrumb">';
    foreach ($breadcrumbs as $name => $info) {
        if ($info[1]){
            $breadcrumbs_exp .= '<li class="breadcrumb-item active" aria-current="page">'.$name.'</li>';
        } else {
            $breadcrumbs_exp .= '<li class="breadcrumb-item"><a href="'.$info[0].'">'.$name.'</a></li>';
        }
    }
    $breadcrumbs_exp .= '</ol>';
    $breadcrumbs_exp .= '</nav>';
    return $breadcrumbs_exp;
}

/**
 * Creates navigation bar HTML code using given array
 * @param array $navigation Array with as Key the page name and as Value the corresponding URL
 * @return string HTML code that represents the navigation bar
 */
function get_navigation($navigation){
    $navigation_exp = '<nav class="navbar navbar-expand-lg navbar-light bg-light">';
    $navigation_exp .= '<a class="navbar-brand">Series Overview</a>';
    $navigation_exp .= '<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">';
    $navigation_exp .= '<span class="navbar-toggler-icon"></span>';
    $navigation_exp .= '</button>';
    $navigation_exp .= '<div class="collapse navbar-collapse" id="navbarSupportedContent">';
    $navigation_exp .= '<ul class="navbar-nav mr-auto">';
    foreach ($navigation as $name => $info) {
        if ($info[1]){
            $navigation_exp .= '<li class="nav-item active">';
        } else {
            $navigation_exp .= '<li class="nav-item">';
        }
        $navigation_exp .= '<a class="nav-link" href="'.$info[0].'">'.$name.'</a>';

        $navigation_exp .= '</li>';
    }
    $navigation_exp .= '</ul>';
    $navigation_exp .= '</div>';
    $navigation_exp .= '</nav>';
    return $navigation_exp;
}

/**
 * Pretty Print Array
 * @param $input
 */
function p_print($input){
    echo '<pre>';
    print_r($input);
    echo '</pre>';
}

/**
 * Creates HTML alert code with information about the success or failure
 * @param array $feedback Associative array with keys type and message
 * @return string
 */
function get_error($feedback){
    return '
        <div class="alert alert-'.$feedback['type'].'" role="alert">
            '.$feedback['message'].'
        </div>';
}

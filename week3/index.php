<?php
/**
 * Controller
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

/* Require composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Include model.php */
include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt21_week3', 'ddwt21', 'ddwt21');

/* Create Router instance */
$router = new \Bramus\Router\Router();
$cred = set_cred('ddwt21', 'ddwt21');
// Add routes here
$router->mount('/api', function() use ($router, $db, $cred) {
    http_content_type('application/json');
    /* validates credentials */
    $router->before('GET|POST|PUT|DELETE', '/api/.*', function () use ($cred) {
        // Validate authentication
        if (!check_cred($cred)) {
            return [
                'type' => 'warning',
                'message' => 'You have not editing rights on these page\'s content.'
            ];
        };
        exit();
    });
    /* GET for reading all series */
    $router->get('/series', function() use($db) {
        // Retrieve and output information
        $series = get_series($db);
        echo json_encode($series);
    });
    /* GET for reading individual series */
    $router->get('/series/(\d+)', function($id) use($db) {
        // Retrieve and output information
        $serie_info = get_series_info($db, $id);
        echo json_encode($serie_info);
    });
    /* DELETE for deleting individual series */
    $router->delete('/series/(\d+)', function($id) use($db) {
        // Retrieve and output information
        $delete_serie = remove_series($db, $id);
        echo json_encode($delete_serie);
    });
    /* POST for adding individual series */
    $router->post('/series/add', function() use($db) {
        // Retrieve and output information
        $add_serie = add_series($db, $_POST);
        echo json_encode($add_serie);
    });
    /* PUT for updating individual series */
    $router->put('/series/(\d+)', function($id) use($db) {
        $_PUT = array();
        parse_str(file_get_contents('php://input'), $_PUT);
        $serie_info = $_PUT + ["series_id" => $id];
        // Retrieve and output information
        $update_serie = update_series($db, $serie_info);
        echo json_encode($update_serie);
    });
    /* Error if page does not exist */
    $router->set404(function() {
        header('HTTP/1.1 404 Not Found');
        echo '404: PAGE NOT FOUND!';
    });
});

/* Run the router */
$router->run();

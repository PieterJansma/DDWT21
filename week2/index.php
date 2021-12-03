<?php
/**
 * Controller
 *
 * Database-driven Webtechnology
 * Taught by Stijn Eikelboom
 * Based on code by Reinard van Dalen
 */

include 'model.php';

/* Connect to DB */
$db = connect_db('localhost', 'ddwt21_week2', 'ddwt21','ddwt21');

/* Get Number of Series */
$nbr_series = count_series($db);
/* Set a default for $right_column */
$right_column = use_template('cards');



$template =
    Array(
        1 => Array(
            'name' => 'Home',
            'url' => '/DDWT21/week2/'
        ),
        2 => Array(
            'name' => 'Overview',
            'url' => '/DDWT21/week2/overview/'
        ),
        3 => Array(
            'name' => 'Add series',
            'url' => '/DDWT21/week2/add/'
        ),
        4 => Array(
            'name' => 'My Account',
            'url' => '/DDWT21/week2/myaccount/'
        ),
        5 => Array(
            'name' => 'Register',
            'url' => '/DDWT21/week2/register/'
        ));




/* Landing page */
if (new_route('/DDWT21/week2/', 'get')) {

    /* Page info */
    $page_title = 'Home';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Home' => na('/DDWT21/week2/', True)
    ]);
    $navigation = get_navigation($template, 1);

    /* Page content */
    $page_subtitle = 'The online platform to list your favorite series';
    $page_content = 'On Series Overview you can list your favorite series. You can see the favorite series of all Series Overview users. By sharing your favorite series, you can get inspired by others and explore new series.';

    /* Choose Template */
    include use_template('main');
}

/* Overview page */
elseif (new_route('/DDWT21/week2/overview/', 'get')) {

    /* Page info */
    $page_title = 'Overview';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Overview' => na('/DDWT21/week2/overview', True)
    ]);
    $navigation = get_navigation($template, 2);

    /* Page content */
    $page_subtitle = 'The overview of all series';
    $page_content = 'Here you find all series listed on Series Overview.';
    $left_content = get_serie_table(get_series($db), $db);

    /* Choose Template */
    include use_template('main');
}

/* Single Series */
elseif (new_route('/DDWT21/week2/series/', 'get')) {

    /* Get series from db */
    $series_id = $_GET['series_id'];
    $series_info = get_series_info($db, $series_id);

    /* Page info */
    $page_title = $series_info['name'];
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Overview' => na('/DDWT21/week2/overview/', False),
        $series_info['name'] => na('/DDWT21/week2/series/?series_id='.$series_id, True)
    ]);
    $navigation = get_navigation($template, 1);
    /* Page content */
    $page_subtitle = sprintf("Information about %s", $series_info['name']);
    $page_content = $series_info['abstract'];
    $nbr_seasons = $series_info['seasons'];
    $creators = $series_info['creator'];
    $added_by = get_user_name(get_series_info($db, $series_id)['user_id'], $db);

    /* Choose Template */
    include use_template('series');
}

/* Add series GET */
elseif (new_route('/DDWT21/week2/add/', 'get')) {
    if ( !check_login() ) {
        redirect('/DDWT21/week2/login/');
    }
    /* Page info */
    $page_title = 'Add Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Add Series' => na('/DDWT21/week2/new/', True)
    ]);
    $navigation = get_navigation($template, 3);

    /* Page content */
    $page_subtitle = 'Add your favorite series';
    $page_content = 'Fill in the details of you favorite series.';
    $submit_btn = "Add Series";
    $form_action = '/DDWT21/week2/add/';

    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('new');
}

/* Add series POST */
elseif (new_route('/DDWT21/week2/add/', 'post')) {
    if ( !check_login() ) {
        redirect('/DDWT21/week2/login/');
    }

    /* Add serie to database */
    $feedback = add_series($db, $_POST);

    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT21/week2/add/?error_msg=%s',
        json_encode($feedback)));

    include use_template('new');
}


/* Edit series GET */
elseif (new_route('/DDWT21/week2/edit/', 'get')) {
    /* Checks if user is logged in */
    if ( !check_login() ) {
        redirect('/DDWT21/week2/login/');
    }
    /* Get serie info from db */
    $series_id = $_GET['series_id'];
    $serie_info = get_series_info($db, $series_id);

    /* Page info */
    $page_title = 'Edit Series';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        sprintf("Edit Series %s", $serie_info['name']) => na('/DDWT21/week2/new/', True)
    ]);
    $navigation = get_navigation($template, 0);

    /* Page content */
    $page_subtitle = sprintf("Edit %s", $serie_info['name']);
    $page_content = 'Edit the series below.';
    $submit_btn = "Edit Series";
    $form_action = '/DDWT21/week2/edit/';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('new');
}

/* Edit series POST */
elseif (new_route('/DDWT21/week2/edit/', 'post')) {
    if ( !check_login() ) {
        redirect('/DDWT21/week2/login/');
    }

    /* Update series in database */
    $feedback = update_series($db, $_POST);

    /* Get serie info from db */
    $series_id = $_POST['series_id'];
    $serie_info = get_series_info($db, $series_id);

    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT21/week2/series/?series_id='.$series_id.'&error_msg=%s',
        json_encode($feedback)));
    /* Choose Template */
    include use_template('series');
}

/* Remove series */
elseif (new_route('/DDWT21/week2/remove/', 'post')) {
    if ( !check_login() ) {
        redirect('/DDWT21/week2/login/');
    }

    $series_id = $_POST['series_id'];
    $feedback = remove_series($db, $series_id);

    /* Redirect to serie GET route */
    redirect(sprintf('/DDWT21/week2/overview/?error_msg=%s',
        json_encode($feedback)));

    /* Choose Template */
    /* Choose Template */
    include use_template('main');
}


elseif (new_route('/DDWT21/week2/myaccount/', 'get')) {
    /* Checks if user is logged in */
    if ( !check_login() ) {
        redirect('/DDWT21/week2/login/');
    }

    /* Page info */
    $page_title = 'My Account';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'My Account' => na('/DDWT21/week2/myaccount', True)
    ]);
    $navigation = get_navigation($template, 4);

    /* Page content */
    $page_subtitle = 'The overview of your account';
    $page_content = 'Here you find information about your account';
    $user_id = get_user_id();

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }
    include use_template('account');
}

elseif (new_route('/DDWT21/week2/register/', 'get')) {
    /* Page info */
    $page_title = 'Register';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Register' => na('/DDWT21/week2/register', True)
    ]);
    $navigation = get_navigation($template, 5);

    /* Page content */
    $page_subtitle = 'Register an account';
    $page_content = 'Here you can register an account';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {
        $error_msg = get_error($_GET['error_msg']);
    }

    /* Choose Template */
    include use_template('register');
}

elseif (new_route('/DDWT21/week2/register/', 'post')) {
    /* Register user */
    $feedback = register_user($db, $_POST);

    /* Redirect to homepage */
    redirect(sprintf('/DDWT21/week2/myaccount/?error_msg=%s',
        json_encode($feedback)));

    include use_template('register');
}

elseif (new_route('/DDWT21/week2/login/', 'get')) {
    /* Checks if user is logged in */
    if ( check_login() ) {
        redirect('/DDWT21/week2/myaccount/');
    }

    /* Page info */
    $page_title = 'Login';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Login' => na('/DDWT21/week2/login', True)
    ]);
    $navigation = get_navigation($template, 0);

    /* Page content */
    $page_subtitle = 'Use your username and password to login';

    /* Get error msg from POST route */
    if ( isset($_GET['error_msg']) ) {$error_msg = get_error($_GET['error_msg']);}

    /* Choose Template */
    include use_template('login');
}

elseif (new_route('/DDWT21/week2/login/', 'post')) {
    /* Login user */
    $feedback = login_user($db, $_POST);

    /* Page info */
    $page_title = 'Login';
    $breadcrumbs = get_breadcrumbs([
        'DDWT21' => na('/DDWT21/', False),
        'Week 2' => na('/DDWT21/week2/', False),
        'Login' => na('/DDWT21/week2/login/', True)
    ]);
    $navigation = get_navigation($template, 0);

    /* Redirect to homepage */
    redirect(sprintf('/DDWT21/week2/login/?error_msg=%s',
        json_encode($feedback)));

    include use_template('login');
}

elseif (new_route('/DDWT21/week2/logout/', 'get')) {
    /* Logout user */
    $feedback = logout_user();

    $navigation = get_navigation($template, 0);

    /* Redirect to homepage */
    redirect(sprintf('/DDWT21/week2/?error_msg=%s',
        json_encode($feedback)));
}

else {
    http_response_code(404);
    echo '404 Not Found';
}

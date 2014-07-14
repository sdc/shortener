<?php
/*
Plugin Name: Popular Clicks
Plugin URI: hhttps://github.com/miconda/yourls
Description: Shows an admin page with the top of last clicked links
Version: 1.0
Author: miconda
Author URI: http://miconda.blogspot.com/
*/

yourls_add_action( 'plugins_loaded', 'popularclicks_add_page' );

function popularclicks_add_page() {
    yourls_register_plugin_page( 'popular_clicks', 'Popular Clicks', 'popularclicks_do_page' );
}
// Display popular clicks
function popularclicks_do_page() {
    $nonce = yourls_create_nonce('popular_clicks');
    echo '<h2>Popular Clicks</h2>';
    echo '<p>Legend: Clicks | Short URL | Long URL</p>';

    function show_top($numdays, $numrows) {
        global $ydb;
        $base       = YOURLS_SITE;
        $table_url  = YOURLS_DB_TABLE_URL;
        $table_log  = YOURLS_DB_TABLE_LOG;
        $outdata    = '';

        $query = $ydb->get_results("
            SELECT a.shorturl AS shorturl, COUNT(*) AS clicks, b.url AS longurl 
            FROM `$table_log` a, `$table_url` b 
            WHERE a.shorturl = b.keyword 
                AND DATE_SUB(NOW(), INTERVAL $numdays DAY) < a.click_time 
            GROUP BY a.shorturl 
            ORDER BY COUNT(*) DESC, shorturl ASC
            LIMIT $numrows;");
    
        if ($query) {
            $outdata .= '<ol>';
            foreach( $query as $query_result ) {
                $outdata .= '  <li>'.$query_result->clicks.' | ';
                $outdata .= '  <a href="'.$base.'/'.$query_result->shorturl.'+" target="blank">'.$query_result->shorturl .'</a> | ';
                $outdata .= '  <a href="'.$query_result->longurl.'" target="blank">'.$query_result->longurl.'</a></li>';
            }
            $outdata .= '</ol>';
        }
        $plural = ($numdays == 1) ? '' : 's';
        echo '<h3>Popular clicks in the last '.$numdays.' day'.$plural.':</h3>';
        echo $outdata;
    }

    // Update next lines for adjustments on number of days and number of top links.
    // Example: show_top(1, 5) => print the 5 most popular links clicked in the last 1 day.
    show_top(1, 15);    // last day
    show_top(7, 15);    // last week
    show_top(14, 15);   // last 2 weeks
    show_top(30, 15);   // last month
    show_top(60, 15);   // last 2 months
    show_top(90, 15);   // last 3 months
    show_top(180, 15);  // last 6 months
    show_top(365, 15);  // last year
    show_top(1000, 15); // All time.
}

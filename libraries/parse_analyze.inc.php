<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Parse and analyse a SQL query
 *
 * @package PhpMyAdmin
 */
if (! defined('PHPMYADMIN')) {
    exit;
}

$GLOBALS['unparsed_sql'] = $sql_query;

// Get details about the SQL query.
$analyzed_sql_results = SqlParser\Utils\Query::getAll($sql_query);

// Adding data fetched from the old parser and analyzer.
// TODO: Finish replacing all calls to `PMA_SQP_*`.
$analyzed_sql_results['parsed_sql'] = PMA_SQP_parse($sql_query);
$analyzed_sql_results['analyzed_sql'] = PMA_SQP_analyze($analyzed_sql_results['parsed_sql']);

// TODO: Refactor this as well.
extract($analyzed_sql_results);

// If the targeted table (and database) are different than the ones that is
// currently browsed, edit `$db` and `$table` to match them so other elements
// (page headers, links, navigation panel) can be updated properly.
if (!empty($analyzed_sql_results['select_tables'])) {

    // Previous table and database name is stored to check if it changed.
    $prevDb = $db;
    $prevTable = $table;

    if (count($analyzed_sql_results['select_tables']) > 1) {

        /**
         * @todo if there are more than one table name in the Select:
         * - do not extract the first table name
         * - do not show a table name in the page header
         * - do not display the sub-pages links)
         */
        $table = '';
    } else {
        $table = $analyzed_sql_results['select_tables'][0][0];
        if (!empty($analyzed_sql_results['select_tables'][0][1])) {
            $db = $analyzed_sql_results['select_tables'][0][1];
        }
    }

    // There is no point checking if a reload is required if we already decided
    // to reload. Also, no reload is required for AJAX requests.
    if ((empty($reload)) && (empty($GLOBALS['is_ajax_request']))) {
        // NOTE: Tables are case-insensitive.
        $reload  = ((strcasecmp($db, $prevDb) == 0)
            || (strcasecmp($table, $prevTable)) == 0) ? false : true;
    }

    // Updating the array as well.
    $analyzed_sql_results['reload'] = $reload;
}

return $analyzed_sql_results;

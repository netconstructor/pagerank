#!/usr/bin/php
<?php

/*

    Google PageRank PHP Command Line Interface (CLI) Script

    Requires: pagerank2.class.php

    Install: Rename to pr.php (mv pagerank.cli.php pr.php) and make executable (chmod 755 pr.php)

    Usage: ./pr.php <url>

*/

require('pagerank2.class.php');
$pr = new PageRank();
echo $pr->get($argv[1]);

?>
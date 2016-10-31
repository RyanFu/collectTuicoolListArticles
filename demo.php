<?php
/**
 * Tuicool Article Collect Demo
 */

require ('collectTuicoolListArticles.php');
var_dump((new Tuicool())->collectHotListArticle(0,1));
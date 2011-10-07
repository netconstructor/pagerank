<?php

/**
 * PageRank Lookup (Based on Google Toolbar for Mozilla Firefox)
 *
 * @copyright   2011 HM2K <hm2k@php.net>
 * @link        http://pagerank.phurix.net/
 * @author      James Wade <hm2k@php.net>
 * @version     $Revision: 2.0 $
 * @require     PHP 4.3.0 (file_get_contents)
 * @updated		06/10/11
 */

function GetPageRank($q,$host='toolbarqueries.google.com',$context=NULL) {
	$seed = "Mining PageRank is AGAINST GOOGLE'S TERMS OF SERVICE. Yes, I'm talking to you, scammer.";
	$result = 0x01020345;
	$len = strlen($q);
	for ($i=0; $i<$len; $i++) {
		$result ^= ord($seed{$i%strlen($seed)}) ^ ord($q{$i});
		$result = (($result >> 23) & 0x1ff) | $result << 9;
	}
	$ch=sprintf('8%x', $result);
	$url='http://%s/tbr?client=navclient-auto&ch=%s&features=Rank&q=info:%s';
	$url=sprintf($url,$host,$ch,$q);
	@$pr=file_get_contents($url,false,$context);
	return $pr?substr(strrchr($pr, ':'), 1):false;
}

//Example usage:

if (isset($_GET['q'])) { echo GetPageRank($_GET['q']); }
//eof
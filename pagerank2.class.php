<?php

/**
 * PageRank Lookup (Based on Google Toolbar for Mozilla Firefox)
 *
 * @copyright   2011 HM2K <hm2k@php.net>
 * @link        http://pagerank.phurix.net/
 * @author      James Wade <hm2k@php.net>
 * @version     $Revision: 2.0 $
 * @require     PHP 4.3.0 (file_get_contents)
 * @updated		06/09/11
 */

class PageRank {

	var $hosts=array('www.google.com','toolbarqueries.google.com');

	var $q;
	var $ch;
	var $pr;
	var $pagerank;
	var $host;
	var $url;
	var $context;
	var $stats=array();

	function PageRank ($q=false) {
		if ($q) { return $this->Get($q); }
	}
	function Get ($q) {
		$this->q=$q;
		$this->CheckHash();
		$this->MakeURL();
		$this->Fetch();
		$this->Parse();
		$this->Log();
		return $this->pagerank;
	}
	function GetHost () {
		shuffle($this->hosts);
		$this->host=$this->hosts[0];
		return $this->host;
	}
	function CheckHash () {
		$seed="Mining PageRank is AGAINST GOOGLE'S TERMS OF SERVICE. Yes, I'm talking to you, scammer.";
		$result=0x01020345;
		$len = strlen($this->q);
		for ($i=0; $i<$len; $i++) {
			$result ^= ord($seed{$i%strlen($seed)}) ^ ord($this->q{$i});
			$result = (($result >> 23) & 0x1ff) | $result << 9;
		}
		$this->ch=sprintf('8%x', $result);
		return $this->ch;
	}
	function MakeURL () {
		if (!$this->ch) { $this->CheckHash(); }
		$url='http://%s/tbr?client=navclient-auto&ch=%s&features=Rank&q=info:%s';
		$this->url=sprintf($url,$this->GetHost(),$this->ch,$this->q);
		return $this->url;
	}
	function Fetch() {
		$this->pr=@file_get_contents($this->url,false,$this->context);
		return $this->pr?$this->pr:false;
	}
	function Parse() {
		$this->pagerank=$this->pr?substr(strrchr($this->pr, ':'), 1):false;
		return $this->pagerank;
	}
	function Log() {
		$data=array();
		$data['ip']=$_SERVER['REMOTE_ADDR'];
		$data['useragent']=$_SERVER['HTTP_USER_AGENT'];
		$data['query']=$this->q;
		$data['hash']=$this->ch;
		$data['host']=$this->host;
		$data['result']=$this->pr;
		$data['pagerank']=$this->pagerank;
		include_once('db.php');
		$db=new db();
		//$db->setup();
        $_keys=array();
		$_values=array();
        foreach ($data as $k => $v) {
          $_values[]=$db->quote($v);
          $_keys[]=$k;
        }
        $_values=implode(',',$_values);
        $_keys=implode(',',$_keys);
        //insert
		$sql=sprintf('INSERT OR REPLACE INTO %s (%s) VALUES (%s)',$db->gettables(0),$_keys,$_values);
        //$this->_debug($sql);
        $db->exec($sql);
	}
	function Stat() {
		include_once('db.php');
		$db=new db();
		$sql='SELECT COUNT(*) FROM %s WHERE ip="%s"';
		$now=date('Y-m-d');
		$last24=date('Y-m-d',strtotime('-24 hours'));
		$this->stats['ip']=$_SERVER['REMOTE_ADDR'];
		$sql=sprintf($sql,$db->gettables(0),$this->stats['ip'],$now,$last24);
		$sql.=' AND strftime("%s",timestamp) > strftime("%s","now","-24 hours")';
		$query=$db->query($sql)->fetch();
		$this->stats['count']=$query[0];
		return $this->stats;
	}
}//eof
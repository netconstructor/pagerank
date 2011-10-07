<?php

/**
 * PageRank Lookup (Based on Google Toolbar for Mozilla Firefox)
 *
 * @copyright   2011 HM2K <hm2k@php.net>
 * @link        http://pagerank.phurix.net/
 * @author      James Wade <hm2k@php.net>
 * @version     $Revision: 2.1 $
 * @require     PHP 4.3.0 (file_get_contents)
 * @updated		06/10/11
 */

class PageRank {

    /* Settings */
    protected $url='http://toolbarqueries.google.com/tbr?client=navclient-auto&ch=%s&features=Rank&q=info:%s';

    /* Variables */
	protected $q;
	protected $ch;
	protected $data;
	protected $pagerank;
	protected $context;
    protected $error;

    protected function setQuery($string='') {
        if (empty($string)) {
            $this->setError(
                __METHOD__ . ' does not expect parameter 1 to be empty'
            );
            return false;
        }
        if (!is_string($string)) {
            $this->setError(
                __METHOD__ . ' expects parameter 1 to be string, ' .
                gettype($string) . ' given'
            );
            return false;
        }
        $this->q=$string;
    }
	public function get ($q) {
		$this->setQuery($q);
		$this->checkHash();
		$this->fetch();
		$this->parse();
		$this->log();
		return $this->getPagerank();
	}
    public function hasCheckhash() {
        return !empty($this->ch);
    }
    public function setCheckhash($string='') {
        if (empty($string)) {
            $this->setError(
                __METHOD__ . ' does not expect parameter 1 to be empty'
            );
            return false;
        }
        if (!is_string($string)) {
            $this->setError(
                __METHOD__ . ' expects parameter 1 to be string, ' .
                gettype($string) . ' given'
            );
            return false;
        }
        return $this->ch=$string;
    }
    public function getCheckhash()
    {
		if (!$this->hasCheckhash()) { $this->checkHash(); }
        return $this->ch ? $this->ch : '';
    }
	protected function checkHash () {
		$seed="Mining PageRank is AGAINST GOOGLE'S TERMS OF SERVICE. Yes, I'm talking to you, scammer.";
		$result=0x01020345;
		$len = strlen($this->q);
		for ($i=0; $i<$len; $i++) {
			$result ^= ord($seed{$i%strlen($seed)}) ^ ord($this->q{$i});
			$result = (($result >> 23) & 0x1ff) | $result << 9;
		}
        $ch=sprintf('8%x', $result);
		$this->setCheckhash($ch);
		return $ch;
	}
	protected function getUrl () {
		return sprintf($this->url,$this->getCheckhash(),$this->getQuery());
	}
    public function getQuery()
    {
        return $this->q ? $this->q : '';
    }
    public function setData($string='') {
        return $this->data=$string;
    }
    public function getData()
    {
        return $this->data ? $this->data : '';
    }
    public function setPagerank($string='') {
        return $this->pagerank=$string;
    }
    public function getPagerank()
    {
        return $this->pagerank ? $this->pagerank : '';
    }
	protected function fetch() {
		$data = @file_get_contents($this->getUrl(),false,$this->context);
        $data = $data ? $data : '';
        $this->setData($data);
		return $data;
	}
	protected function parse() {
        $pr = $this->getData();
        if (!$pr) {
            return;
        }
        if ($pr[0] == '<') {
            $this->setError('Parse error, found HTML.');
        } else {
            $pr=substr(strrchr($pr, ':'), 1);
        }
        if ($pr) {
            $this->setPagerank($pr);
            return $pr;
        }
	}
    protected function setError($message)
    {
        return $this->error = $message;
    }
    public function getError()
    {
        return $this->error ? $this->error : '';
    }
    public function hasError()
    {
        return !empty($this->error);
    }
}//eof
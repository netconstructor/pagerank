#!/usr/bin/perl

# pagerank.pl by HM2K (c) 2011 (Updated: 02/08/11)
#		Downloaded from http://pagerank.phurix.net/

# Description: Calculates the Checkhash and returns the Google PageRank
# Usage: ./pagerank.pl <query>

use strict;
use warnings;

sub getpagerank {
	my $q = shift;
	my $host = 'www.google.com';
	my $seed = "Mining PageRank is AGAINST GOOGLE'S TERMS OF SERVICE. Yes, I'm talking to you, scammer.";
	my $result = 0x01020345;
	my $len = length($q);
	for (my $i=0; $i<$len; $i++) {
		$result ^= ord(substr($seed,$i%length($seed))) ^ ord(substr($q,$i));
		$result = (($result >> 23) & 0x1ff) | $result << 9;
	}
	my $ch=sprintf("8%x", $result);
	my $url='http://%s/search?client=navclient-auto&ch=%s&features=Rank&q=info:%s';
	$url=sprintf($url,$host,$ch,$q);
	use LWP::UserAgent;
	my $ua = LWP::UserAgent->new;
    my $res = $ua->get($url);
	$res=$res->decoded_content;
	return substr($res, rindex($res, ':')+1);
}

print getpagerank(shift);
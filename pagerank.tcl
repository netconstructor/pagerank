# pagerank.tcl -- 0.2
#
#   Get the Google PageRank of a URL/Domain
#
# Copyright (c) 2011 HM2K
#
# Name: Google PageRank Lookup
# Author: HM2K <irc@hm2k.org>
# License: http://www.opensource.org/licenses/bsd-license.php The BSD 2-Clause License
# Link: http://pagerank.phurix.net/
# Tags: pagerank, lookup, google, api
# Updated: 01-Aug-2011
#
###Usage
# > .pr hm2k.com
# <Bot> HM2K, * PageRank: 7/10
#
###Revisions
# 0.2	- now calculates it's own "checkhash"
# 0.1   - alpha release
#
#
###Credits
# Web hosting by Phurix <www.phurix.co.uk>
# Shell hosting by Gallush <www.gallush.com>
#
# Please consider a donation. Thanks! http://tinyurl.com/hm2kpaypal

#
# Settings
#

# Namespace
namespace eval ::pr {
	variable version
	set version "0.2"; #current version of this script

	variable pr
	# Default settings
	set pr(ver) "0.2"; #current version of this file
	set pr(cmd) ".pr"; #public command trigger
	set pr(dcccmd) "pr"; #dcc command trigger
	set pr(prefix) "* PageRank:"; #output prefix
	set pr(usage) "Usage: $pr(cmd) <domain|url>";
	set pr(ua) "Mozilla/4.0 (compatible; GoogleToolbar 2.0.111-big; Windows XP 5.1)"; #user agent simulation
	set pr(urlpr) "http://toolbarqueries.google.com/tbr"; #url
	set pr(regex_rank) {Rank_[0-9]+:[0-9]+:([0-9]+)}; #ranking expression
	set pr(output) "%s/10"; #format the output
	set pr(failed) "N/A"; #output for failed
	# Note: Copy the above "Default settings" section into "pagerank.settings.tcl"
	#	to customise your settings. Then they will be used instead.

	# Settings file
	if {[catch {source scripts/pagerank.settings.tcl} err]} {
	  #putlog "Warning: 'pagerank.settings.tcl' was not loaded"
	}
}

#
# Procedures
#

# Initialization
proc ::pr::init {args} {
	variable pr
	variable version

	# Package Definition
	package require eggdrop 1.6;  #see http://geteggdrop.com/
	package require Tcl 8.4;      #see http://tinyurl.com/6kvu2n
	if {[catch {package require http 2.0} err]} {
		putlog "[info script] error: $err"
		putlog "http 2.0 package or above is required, see http://wiki.tcl.tk/1475";
	}
	if {[catch {package require htmlparse} err]} {
		putlog "[info script] error: $err"
		putlog "Tcllib is required, see http://wiki.tcl.tk/12099";
	}

	# User defined channel flag
	setudef flag pagerank

	# Binds
	bind pub - $pr(cmd) [namespace current]::pub
	bind dcc -|- $pr(dcccmd) [namespace current]::dcc
	bind evnt -|- prerehash [namespace current]::deinit

	# Loaded
	putlog "pagerank.tcl $version loaded"
}

# Deinitializaion
proc ::pr::deinit {args} {
	catch {unbind pub -|- {* *} [namespace current]::pub}
	catch {unbind dcc -|- {*} [namespace current]::dcc}
	catch {unbind evnt -|- prerehash [namespace current]::deinit}
	namespace delete [namespace current]
}

# Public command
proc ::pr::pub { nick uhost handle chan arg } {
	variable pr
	if {[channel get $chan pagerank]<1} { return }
	set arg [split $arg]
	if {[llength $arg]==0} {
		putserv "NOTICE $nick :$pr(usage)"
		return
	}
	set result [::pr::get $arg]
	putserv "PRIVMSG $chan :$nick, $pr(prefix) $result"
}

# DCC command
proc ::pr::dcc {ha idx arg} {
	variable pr
	set arg [split $arg]
	if {[llength $arg]==0} {
		putdcc $idx $pr(usage)
		return
	}
	set result [::pr::get $arg]
	putdcc $idx $result
}

# Get CheckHash
proc ::pr::getch { arg } {
	set seed "Mining PageRank is AGAINST GOOGLE'S TERMS OF SERVICE. Yes, I'm talking to you, scammer."
	set seedlen [string length $seed]
	set result 0x01020345
	set len [string length $arg]
	for {set i 0} {$i < $len} {incr i} {
		set a [scan [string index $seed [expr {$i%$seedlen}]] %c]
		set b [scan [string index $arg $i] %c]
		set result [expr {$result ^ ($a ^ $b)}]
		set result [expr {(($result >> 23) & 0x1ff) | $result << 9}]
	}
	return [format 8%x $result]
}

# Fetch PageRank
proc ::pr::getpr { arg } {
  variable pr
  set ch [::pr::getch $arg]
  if {$ch eq {}} { return }
  set query [::http::formatQuery client "navclient-auto" ch $ch features "Rank" q "info:$arg"]
  set http [::http::config -useragent $pr(ua) -urlencoding "utf-8"]
  set http [::http::geturl $pr(urlpr)?$query]
  set data [::http::data $http]
  set data [::htmlparse::mapEscapes $data]
  if {[regexp $pr(regex_rank) $data matched result]} {
    return $result
  }
  return
}

# Return PageRank
proc ::pr::get { arg } {
  variable pr
  set data [::pr::getpr $arg]
  if {$data eq {}} { return $pr(failed) }
  return [format $pr(output) $data]
  
}

::pr::init
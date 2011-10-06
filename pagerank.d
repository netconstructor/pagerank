/*
 *  pagerank.d
 *
 *  Google Pagerank Checksum Algorith
 *	Downloaded from http://pagerank.phurix.net/
 *
 *  Compile with:
 *       gdc -O -inline pagerank.d -o pagerank
 *
 *   Enviroment:
 *        Debian GNU/Linux Lenny
 *        gdc (GCC) 4.1.3 20080623 
 */

import std.stdio;


uint HashURL(char[] url)
{
    char[] seed = "Mining PageRank is AGAINST GOOGLE'S TERMS OF SERVICE. Yes, I'm talking to you, scammer.";
	
    uint key = 0x01020345;
	
    for (uint i=0; i< url.length; i++) {
        key ^= seed[i%seed.length] ^ url[i];
        key = key>>23 | key<<9;		
    }
	
    return key;	
}
 
int main (char[][] args)
{
    if (args.length != 2) {
        writefln("No URL");
        return -1;
    }

    writefln("8%x\n", HashURL(args[1]));
    return 0;
}


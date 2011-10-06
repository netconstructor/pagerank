/*
    pagerank.cpp

	Google Pagerank Checksum Algorithm (Using the Firefox Toolbar)
		Downloaded from http://pagerank.phurix.net/

    Compile With:
        gcc -Wall -O2 pagerank.cpp -o pagerank

	Validating Environment:
      Debian GNU/Linux Lenny(Kernel 2.6.25-7-686)
      gcc version 4.3.1 
*/

#include <stdio.h>
#include <string.h>

unsigned int HashURL(char* url)
{
    char seed[] = "Mining PageRank is AGAINST GOOGLE'S TERMS OF SERVICE. Yes, I'm talking to you, scammer.";
    unsigned int i,  urllen, seedlen;
    unsigned int key = 16909125;

    seedlen = strlen(seed);
    urllen  = strlen(url);

    for(i = 0; i < urllen; i++)
    {
        key ^= seed[i%seedlen] ^ url[i];
        key = key>>23 | key<<9;
    }

    return key;
}


int main (int argc, char ** argv) 
{
    if(argc != 2) {
        printf("Usage: %s [URL]\n",argv[0]);
        return -1;
    }

    printf("Checksum=8%x\n", HashURL(argv[1]));

    return 0;
}

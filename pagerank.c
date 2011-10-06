/******************************************************************************
Filename     : pagerank.c
Description  : Google PageRank Checksum Algorithm 

Log          : Ver 0.1 2005-9-13  first release
               Ver 1.0 2005-10-19  fixed :final character bug
               Ver 1.1 2006-10-05  refine code
               Ver 1.2 2008-8-20   use boolean type
******************************************************************************/

#include <stdio.h>
#include <stdbool.h>

int ConvertStrToInt(char *pStr, int Init, int Factor)
{
    while (*pStr) {
        Init *= Factor;
        Init += *pStr++;
    }
    return Init;
}

int HashURL(char *pStr)
{
    unsigned int C1, C2, T1, T2;

    C1 = ConvertStrToInt(pStr, 0x1505, 0x21);
    C2 = ConvertStrToInt(pStr, 0, 0x1003F);
    C1 >>= 2;
    C1 = ((C1 >> 4) & 0x3FFFFC0) | (C1 & 0x3F);
    C1 = ((C1 >> 4) & 0x3FFC00) | (C1 & 0x3FF);
    C1 = ((C1 >> 4) & 0x3C000) | (C1 & 0x3FFF);

    T1 = (C1 & 0x3C0) << 4;
    T1 |= C1 & 0x3C;
    T1 = (T1 << 2) | (C2 & 0xF0F);

    T2 = (C1 & 0xFFFFC000) << 4;
    T2 |= C1 & 0x3C00;
    T2 = (T2 << 0xA) | (C2 & 0xF0F0000);

    return (T1 | T2);
}

char CheckHash(unsigned int HashInt)
{
    int Check = 0;
    bool Flag = false;
    int Remainder;

    do {
        Remainder = HashInt % 10;
        HashInt /= 10;
        if (Flag){
            Remainder += Remainder;
            Remainder = (Remainder / 10) + (Remainder % 10);
        }
        Check += Remainder;
        Flag = !Flag;
    } while( 0 != HashInt);

    Check %= 10;
    if (0 != Check) {
        Check = 10 - Check;
        if (Flag) {
            if (1 == (Check % 2)) {
                Check += 9;
            }
            Check >>= 1;
        }
    }
    Check += 0x30;
    return Check;
}

int main(int argc, char* argv[])
{
    unsigned int HashInt;

    if (argc != 2) {
        printf("Usage: %s [URL]\n",argv[0]);
        return 1;
    }

    HashInt = HashURL(argv[1]);
    printf("Checksum=7%c%u\n", CheckHash(HashInt), HashInt);
    return 0;
}



/* Written by Tobias Mueller <muelli@cryptobitch.de>
 * for use by Mango
 *
 * compile using: gcc mango-passwd-reset.c -o mango-passwd-reset
 */

#define _GNU_SOURCE
#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <errno.h>

static const char program[] = "/usr/local/bin/mango-passwd-reset";
/* Testscript containing "env" static const char program[] =
"/tmp/env.sh"; */

static const char* environment[] = {"bar=baz", NULL};

int
main (int argc, char* argv[]) {
	uid_t real, effective, saved;
	char buf[1024]; /* Assumed to be sufficient */

	if (getresuid (&real, &effective, &saved) < 0) {
		fprintf (stdout, "getresuid()\n");
		exit (EXIT_FAILURE);
	}
	if (snprintf (buf, sizeof(buf), "%d", real) < 0) {
		fprintf (stdout, "snprintf()\n");
		exit (EXIT_FAILURE);
	}

	execle (program, buf, NULL, environment);
	/* Not reached */
	return errno;
}


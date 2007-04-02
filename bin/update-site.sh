#!/usr/bin/perl -w

while (<>)  {
    if (/^UPDATE\s*([a-zA-Z0-9_+-.]*)\s*$/) {
	#chdir("/var/www/mango");
	chdir($ENV{'HOME'});
	system qq(svn update);
    } elsif (/^UPDATE/) {
        system qq(echo "Bad UPDATE line: $_");
    }
}

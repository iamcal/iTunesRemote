iTunes Remote

A Cal Henderson Projects
May 13th, 2009
https://svn.iamcal.com/public/php/iTunesRemote/

Insipred by http://www.whatsmyip.org/itunesremote/


This simple web app allows you to control iTunes on the machine that hosts it.

I use this for controlling my home music server from all of my other machines.



== INSTALLATION NOTES ==

* This will only run on a Mac (it uses AppleScript)
* You'll need to be running Apache
* You'll need to enable PHP support
* You can google for these two things
* You'll need to modify your /etc/sudoers files in a bad way, adding this line:

	_www ALL = NOPASSWD: /usr/bin/osascript

* This allows _www (the default apache user on OS 10.5.6) to run the apple script commandline app as root


*** This is a work in progress ***
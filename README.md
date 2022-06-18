### weeklyd3's wiki software
This is a tool I created in PHP to create a wiki without having to set up a bunch of
PHP extensions and everything.
#### Requirements
It should work with a installation of `sudo dnf install php` plus DOMDocument.
#### Installing
You need PHP first. If you don't have it, run (on Fedora/Red Hat Enterprise Linux/CentOS/other RPM based systems):
    
    sudo dnf install php

On apt-based systems:

    sudo apt install php

On other systems, there may be installation directions on the [PHP site](http://php.net).

Then, run `php -a` and try doing `new DOMDocument;`. If it says that the class DOMDocument was not found, you need to install it. Run `sudo apt-get install php-xml` on Ubuntu based systems or `sudo dnf install php-xml` on RPM based systems to install it.

Then, you can start the web server (either by testing using `php -S 0.0.0.0:2000` or some other server solution.)

Or, you can...
#### Try this in your browser
[![Run on Repl.it](https://repl.it/badge/github/weeklyd3/wiki)](https://repl.it/github/weeklyd3/wiki)
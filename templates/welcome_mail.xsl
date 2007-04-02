<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE welcomemail [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

  <xsl:template match="welcomemail">Dear <xsl:value-of select="user/cn"/>,

Your GNOME account will be ready for use in about 1 hour.

Please make sure you have read for information and guidelines about how to use your SSH key.

http://sysadmin.gnome.org/users/security.html 

For more information on using GNOME SVN, please see:

http://developer.gnome.org/tools/svn.html
http://live.gnome.org/SubversionFAQ
http://live.gnome.org/Subversion

When working on modules in SVN, please be sure you have read and understood the README.svn or HACKING files if they exist. If they do not exist, send submit patches in Bugzilla first and contact the module maintainer(s) with the relevant ticket numbers to get approval before committing.

An '<xsl:value-of select="user/uid"/>@svn.gnome.org' e-mail alias has been set up to forward e-mail to your '<xsl:value-of select="user/mail"/>' address. Similar aliases exist for everyone with SVN access. We recommend that you use these aliases in ChangeLog entries and/or SVN commit log messages.

Now that you have a SVN account, you might also want to become a member of the GNOME Foundation. For more information on the GNOME Foundation, see:

http://foundation.gnome.org/

You can apply at:

http://foundation.gnome.org/membership/application.php

As a member of the GNOME Foundation, you also qualify for a full '<xsl:value-of select="user/uid"/>@gnome.org' e-mail alias. To apply for your Foundation Member alias to be set up, please see the notes here:

http://live.gnome.org/NewEmailRequest

For any other issues, please contact 'support@gnome.org'.

--
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;
</xsl:template>   
</xsl:stylesheet>

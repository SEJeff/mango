<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE user_instructions [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

  <xsl:template match="user_instructions"><xsl:variable
      name="output"><xsl:if test="boolean(change[@id='keysadded'])"><xsl:call-template 
	  name="keysadded"/></xsl:if><xsl:if
	test="boolean(change[@id='joined-group' and @cn='gnomecvs']) or
	(boolean(change[@id='newuser']) and
	boolean(user/group[@cn='gnomecvs']))"><xsl:call-template
	  name="vcsintro"/></xsl:if><xsl:if
	test="boolean(change[@id='joined-group' and @cn='ftpadmin']) or
	(boolean(change[@id='newuser']) and
	boolean(user/group[@cn='ftpadmin']))"><xsl:call-template
	  name="shellintro" /></xsl:if><xsl:if
	test="boolean(change[@id='joined-group' and @cn='mailusers']) or
	(boolean(change[@id='newuser']) and
	boolean(user/group[@cn='mailusers']))"><xsl:call-template
	  name="mailintro"/></xsl:if></xsl:variable><xsl:if
      test="normalize-space($output) != ''">Dear <xsl:value-of select="user/cn"/>,
<xsl:value-of select="$output"/><xsl:if test="not(boolean(user/group[@cn='mailusers']))"><xsl:call-template name="plug-foundation"/></xsl:if>

For any other issues, please contact 'support@gnome.org'.

--
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;</xsl:if>
</xsl:template><xsl:template name="vcsintro">
Your GNOME Git account will be ready for use in about 1 hour.

Please make sure you have read for information and guidelines about how
to use your SSH key.

http://sysadmin.gnome.org/users/security.html 

For information on using GNOME Git, please see:

http://live.gnome.org/Git

When working on modules in Git, please be sure you have read and
understood the README.git or HACKING files if they exist. If they do not
exist, send submit patches in Bugzilla first and contact the module
maintainer(s) with the relevant ticket numbers to get approval before
committing.

An '<xsl:value-of select="user/uid"/>@src.gnome.org' e-mail alias has been set up to forward e-mail
to your '<xsl:value-of select="user/mail"/>' address. Similar aliases exist for everyone with
Git access. We recommend that you use these aliases in ChangeLog entries
and/or Git commit log messages.
</xsl:template><xsl:template name="plug-foundation">
You might also want to become a member of the GNOME Foundation. For more
information on the GNOME Foundation, see:

http://foundation.gnome.org/

You can apply at:

http://foundation.gnome.org/membership/application.php

As a member of the GNOME Foundation, you also qualify for a full
'<xsl:value-of select="user/uid"/>@gnome.org' e-mail alias. To apply for
your Foundation Member alias to be set up, please see the notes here:

http://live.gnome.org/NewEmailRequest</xsl:template><xsl:template name="shellintro">

Within 1 hour, you can ssh/scp to <xsl:value-of select="user/uid"/>@master.gnome.org. Please 
read http://sysadmin.gnome.org/users/security.html for guidelines about
using your SSH key.

To upload a release, first scp the tarball to your home directory. Then
run the "install-module" script. If you run it without arguments, you
get a nice help message that explains how this tool should be used.</xsl:template><xsl:template name="mailintro">
OK, your <xsl:value-of select="user/uid"/>@gnome.org email alias should be working in about an 
hour from now. Remember to follow the rules outlined on
http://live.gnome.org/MailAliasPolicy when using
your mail alias.
</xsl:template><xsl:template name="keysadded">
One or more SSH keys have been added to your GNOME account. It will take
up to an hour before these changes are reflected on the server.

The following SSH keys are currently set on your account:
<xsl:for-each select="user/authorizedKey">
  <xsl:choose>
    <xsl:when test="boolean(@fingerprint)">
      <xsl:value-of select="@fingerprint"/><xsl:text>
</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="concat(substring(., 0, 20), '...', substring(., string-length(.) - 40))"/><xsl:text>
</xsl:text>
    </xsl:otherwise>
  </xsl:choose>
</xsl:for-each>


To see the fingerprint of your public SSH key, run:
  ssh-keygen -l
or:
  ssh-keygen -l -f $FILE
</xsl:template>
</xsl:stylesheet>

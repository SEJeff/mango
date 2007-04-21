<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE welcomemail [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

  <xsl:template match="shellmail">Dear <xsl:value-of select="user/cn"/>,

Within 1 hour, you can ssh/scp to <xsl:value-of select="user/uid"/>@master.gnome.org.
Please read http://sysadmin.gnome.org/users/security.html for guidelines about
using your SSH key.

To upload a release, first scp the tarball to your home directory. Then run the
"install-module" script. If you run it without arguments, you get a nice help
message that explains how this tool should be used.

For any other issues, please contact 'support@gnome.org'.

--
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;
</xsl:template>   
</xsl:stylesheet>

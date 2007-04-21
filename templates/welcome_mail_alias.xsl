<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE welcomemail [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

  <xsl:template match="aliasmail">Dear <xsl:value-of select="user/cn"/>,

OK, your <xsl:value-of select="user/uid"/>@gnome.org email alias should be working in about an 
hour from now. Remember to follow the rules outlined on
http://developer.gnome.org/doc/policies/accounts/mail.html when using
your mail alias.

For any other issues, please contact 'support@gnome.org'.

--
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;
</xsl:template>   
</xsl:stylesheet>

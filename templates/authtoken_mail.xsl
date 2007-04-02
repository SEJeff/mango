<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE authtokenemail [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="ISO-8859-1" omit-xml-declaration="yes"/>

  <xsl:template match="authtokenmail">Dear <xsl:value-of select="user/cn"/>,

In order to confirm your recent GNOME sysadmin or account request, please reply to this e-mail, leaving the subject intact and ensuring that the following authentication token appears in the body of the mail:

<xsl:value-of select="authtoken"/>

--
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;
  </xsl:template>   
</xsl:stylesheet>

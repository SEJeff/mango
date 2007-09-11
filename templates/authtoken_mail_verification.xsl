<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE authtokenemail [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

  <xsl:template match="authtokenmail">Dear <xsl:value-of select="account/cn"/>,

In order to confirm your recent GNOME sysadmin or account request,
please follow the link below in order to confirm your e-mail address:

<xsl:value-of select="authtokenlink"/>

Please note that your request cannot be processed by the responsible
person unless you validate your e-mail address. 

Note: This is an automated mail. Please do not respond to this mail. You
can send your questions to the accounts team e-mail address.
-- 
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;</xsl:template>   
</xsl:stylesheet>

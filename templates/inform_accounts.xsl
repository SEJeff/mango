<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE informaccounts [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

  <xsl:template match="informaccounts">Dear Accounts Team,
    
A new account has been approved by all required maintainers. Please
log into Mango and to the required stuff.

The person requesting the GNOME account provided the following information:
Name: <xsl:value-of select="account/cn"/>
Email adress: <xsl:value-of select="account/email"/>
Request userid: <xsl:value-of select="account/uid"/>
Comment:
<xsl:value-of select="account/comment"/>



Note: This is an automated mail. Please do not respond to this mail. You
can send your questions to the accounts team e-mail address.
-- 
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;</xsl:template>   
</xsl:stylesheet>

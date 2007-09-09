<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE maintainerapproval [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

  <xsl:template match="maintainerapproval">Dear <xsl:value-of select="maintainername"/>,

Due to your maintainership of <xsl:value-of select="maintainermodule"/>, you need approve or reject an account request. Please login to your mango account through http://mango.gnome.org and check pending requests listed on your account. If you won't see any pending request, beware that this may be due to another maintainer's process of this request. 

Note: This is an automated mail. Please do not respond to this mail. You can send your questions to the accounts team e-mail address. 
--
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;
  </xsl:template>   
</xsl:stylesheet>

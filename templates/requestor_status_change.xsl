<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE statuschange [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

  <xsl:template match="statuschange">Dear <xsl:value-of select="account/cn"/>,
    
<xsl:choose>
      <xsl:when test="status='approved'">Your account request has been approved by all required maintainers.

The request will now be forwarded to the GNOME Accounts Team for a
final check. When this has been completed, the account will be set
up. You will receive an email when that happens.</xsl:when>
      <xsl:otherwise>Your account request has been denied.

Please ask the maintainers of the modules you requested an account for
an explanation (if you have not received an explanation).</xsl:otherwise>
    </xsl:choose>

Note: This is an automated mail. Please do not respond to this mail. You
can send your questions to the accounts team e-mail address.
-- 
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;</xsl:template>   
</xsl:stylesheet>

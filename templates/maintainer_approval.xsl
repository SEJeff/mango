<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE maintainerapproval [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

  <xsl:template match="maintainerapproval">Dear <xsl:value-of select="maintainername"/>,

We've received an account request for <xsl:value-of select="maintainermodule"/>. As
you are a maintainer, please approve or reject this account request.
    
To do so, please login to your mango account through <xsl:value-of select="/page/@baseurl"/> and
check the pending requests. If you do not see any pending request, this
is likely due to one of the other maintainers being faster than you.

A request could need the approval of multiple persons. For example, if an
account is requested for the SVN module gtk+ and for Bugzilla shell access, it
will require two approvals. After all required approvals have been given, the
request will be forwarded to the GNOME Accounts team.

Note: This is an automated mail. Please do not respond to this mail. You can
send your questions to the accounts team e-mail address. 
-- 
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;
  </xsl:template>   
</xsl:stylesheet>

<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE maintainerapproval [
	<!ENTITY gt "&#62;">
	<!ENTITY lt "&#60;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="text" encoding="UTF-8" omit-xml-declaration="yes"/>

  <xsl:template match="maintainerapproval">Dear <xsl:value-of select="maintainername"/>,

We've received an account request for:<xsl:for-each select="maintainermodule">
 * <xsl:value-of select="."/>
</xsl:for-each>
As you are a maintainer, please approve or reject this account request.
    
To do so, please login to your Mango account through <xsl:value-of select="@baseurl"/> and
check the pending requests. If you do not see any pending request, this
is likely due to one of the other maintainers being faster than you.

The person requesting the GNOME account provided the following information:
Name: <xsl:value-of select="account/cn"/>
Email adress: <xsl:value-of select="account/mail"/>
Request userid: <xsl:value-of select="account/uid"/>
Comment:
<xsl:value-of select="account/comment"/>


IMPORTANT NOTE:
1. When rejecting accounts, please email an explanation to the person
requesting the account.
2. Approving above account request means are responsible for whatever
this person does on GNOME SVN. This sounds worse than it is in practice,
but do try to follow the initial commits and guide the person in the
beginning.
3. Although Mango verified that this request came from the email address
listed above, please check if this is the same email address as the
person used before. Otherwise, someone else might have requested an
account in the name of someone else.

ABOUT THE PROCESS:
A request could need the approval of multiple persons. For example, if
an account is requested for the SVN module gtk+ and for Bugzilla shell
access, it will require two approvals. After all required approvals have
been given, the request will be forwarded to the GNOME Accounts team.


Note: This is an automated mail. Please do not respond to this mail. You
can send your questions to the accounts team e-mail address. 
-- 
The GNOME Accounts Team
&lt;accounts@gnome.org&gt;
  </xsl:template>   
</xsl:stylesheet>

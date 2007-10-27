<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:param name="libgo.channel">home</xsl:param>
  <xsl:include href="common.xsl" />

  <xsl:template match="verify_mail">
    <xsl:choose>
      <xsl:when test="boolean(/page/verified)">
	<p>Your e-mail address has been verified as <xsl:value-of
	    select="/page/verified/mail" />. We will send further updates
	  regarding your application via email.</p>
	<p>The next step in your application is to get approval from the
	  selected maintainer(s). These maintainer(s) have been asked via email
	  to vouch for you. Once a maintainer approved your request, your
	  account will be created by a member of the Accounts Team.</p>
      <p>For any further questions, please contact <a
	  href="mailto:{/page/@support}"><xsl:value-of
	    select='/page/@support'/></a>.</p> </xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <xsl:when test="boolean(/page/alreadyverified)">
            <p>This token has already been used to verify your email address.</p>
	    <p>If you believe this is a problem with Mango, please contact <a href="mailto:{/page/@support}"><xsl:value-of select='/page/@support'/></a>.</p>
          </xsl:when>
          <xsl:otherwise>
	    <p>Your e-mail address could not be verified. Please check the link
	      on the mail you received. If it does not work, please resubmit
	      your account request.</p>
	    <p>If you believe this is a problem with Mango, please contact <a href="mailto:{/page/@support}"><xsl:value-of select='/page/@support'/></a>.</p>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template match="mail"></xsl:template><!-- prevent printing of email address again. TODO: Fix properly -->
</xsl:stylesheet>

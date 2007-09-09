<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:include href="common.xsl" />

  <xsl:template match="verify_mail">
    <h1>gnome.org admin</h1>  
  	<xsl:choose>
      <xsl:when test="boolean(/page/verified)">
       <p>Your e-mail address is verified as <xsl:value-of select="/page/verified/email" />. This e-mail will be used as your
       communication e-mail for the rest of the process.</p>
       <p>Now please wait maintainer to approve your account request. Once maintainer approved your request, your account will be
       created by accounts team members. Length of the process will depend on the response time of maintainer which is responsible for the ability you requested.</p>
       <p>For any further questions, please contact <a href="mailto:support@gnome.org">support@gnome.org</a>.</p>
      </xsl:when>
      <xsl:otherwise>
      <xsl:choose>
      	<xsl:when test="boolean(/page/alreadyverified)">
      		<p>This token has already been used.</p>
      		<p>If you believe this is a problem with the mango, please contact <a href="mailto:support@gnome.org">support@gnome.org</a>.</p>
      	</xsl:when>
      	<xsl:otherwise>
       <p>Your e-mail address could not be verified. Please check the link on the mail you received. If it does not work, please resubmit
       your account request.</p>
       <p>If you believe this is a problem with the mango, please contact <a href="mailto:support@gnome.org">support@gnome.org</a>.</p>
      	</xsl:otherwise>
     </xsl:choose>
      	</xsl:otherwise>
     </xsl:choose>
   </xsl:template>
  </xsl:stylesheet>

  
  

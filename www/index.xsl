<?xml version="1.0" encoding="UTF-8" ?>

<!-- $Id: index.xsl,v 1.4 2004/03/11 04:47:07 rossg Exp $ -->

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="common.xsl" />

  <xsl:template name="breadcrumb"/>

  <xsl:template match="request">
  <tr><td width="50%" style="border-bottom: 1px dashed black;">
  	User <a href="mailto:{email}"><xsl:value-of select="cn"/></a> with comment:<br />
  		<i>"<xsl:value-of select="comment"/>"</i></td>
  		<td style="border-bottom: 1px dashed black;">
  		<input type="radio" name="request-{requestid}" value="approve"/><label for="approve-{requestid}">Approve</label>
  		<input type="radio" name="request-{requestid}" value="nochange" checked="true" /><label for="nochange-{requestid}">No change</label>
  		<input type="radio" name="request-{requestid}" value="reject"/><label for="reject">Reject</label>
  	</td>
  	</tr>
  </xsl:template>
  
  <xsl:template match="moduletemplate">
  		<li><xsl:value-of select="actiontext"/></li>
       	<table cellspacing="1" cellpadding="5" width="100%">
       	<xsl:for-each select="request">
       		<xsl:apply-templates match="request" />
       	</xsl:for-each>
       	</table>
  </xsl:template>
  <xsl:template match="homepage">
   <table>
    <tr>
     <td>
      <xsl:choose>
      <xsl:when test="not(boolean(/page/user))">
       <h1>gnome.org admin</h1>
       <p>This site hosts Mango, a system to assist GNOME administrative volunteers to maintain the data held in our LDAP and MySQL database (users, groups, mirrors etc).</p>
       <p>The following public forms have been provided to ease the burden of processing these requests:</p>
       <ul>
        <li><a href="/new_account_request.php">Developer account request form (TODO).</a></li>
        <li><a href="/new_email_request.php">Foundation member '@gnome.org' e-mail request form (TODO).</a></li>
       </ul>
       <p>For any further questions, please contact <a href="mailto:support@gnome.org">support@gnome.org</a>.</p>
      </xsl:when>
      <xsl:otherwise>
       You are now logged in as <xsl:value-of select="/page/user/cn"/>.
       <xsl:if test="boolean(/page/maintainer)">
       <hr/>
       <p>
       
       	Pending actions for modules you maintain:
       </p>
       <ul>
       <form name="module" method="POST">
       <input type="hidden" name="mango_token" value="{/page/@token}"/>
       <xsl:for-each select="/page/homepage/module">
       		<xsl:apply-templates match="moduletemplate"/>
       </xsl:for-each>
       <xsl:if test="boolean(/page/homepage/module/moduletemplate/request)">
        <p style="text-align: right"><input type="submit" value="Submit"/></p>
       </xsl:if>
        </form>
       </ul>
       </xsl:if>
       <xsl:if test="boolean(/page/coordinator)">
       <hr/>
       <p>
       
       	Pending actions for your coordinatorship:
       </p>
       <xsl:for-each select="/page/homepage/translation">
       	<li><xsl:value-of select="/page/homepage/translation/actiontext"/></li>
       </xsl:for-each>
       </xsl:if>
       <xsl:if test="boolean(/page/ftp_access)">
       <hr/>
       <p>
       
       	Pending actions for ftp access:
       </p>
       <ul>
       <xsl:for-each select="/page/homepage/ftp_access">
       	<li><xsl:value-of select="/page/homepage/ftp_access/actiontext"/></li>
       </xsl:for-each>
       </ul>
       </xsl:if>
        <xsl:if test="boolean(/page/bugzilla_access)">
       <hr/>
       <p>
       
       	Pending actions for bugzilla access:
       </p>
       <ul>
       <xsl:for-each select="/page/homepage/bugzilla_access">
       	<li><xsl:value-of select="/page/homepage/bugzilla_access/actiontext"/></li>
       </xsl:for-each>
       </ul>
       </xsl:if>
       <xsl:if test="boolean(/page/art_access)">
       <hr/>
       <p>
       
       	Pending actions for web art access:
       </p>
       <ul>
       <xsl:for-each select="/page/homepage/art_access">
       	<li><xsl:value-of select="/page/homepage/art_access/actiontext"/></li>
       </xsl:for-each>
       </ul>
       </xsl:if>
       </xsl:otherwise>
      </xsl:choose>
     </td>
    </tr>
   </table>
  </xsl:template>
</xsl:stylesheet>

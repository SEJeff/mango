<?xml version="1.0" encoding="UTF-8" ?>

<!-- $Id: index.xsl,v 1.4 2004/03/11 04:47:07 rossg Exp $ -->

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="common.xsl" />

  <xsl:template name="breadcrumb"/>

  <xsl:template match="homepage">
   <table>
    <tr>
     <td>
      <xsl:if test="not(boolean(/page/user))">
       <h1>gnome.org admin</h1>
       <p>This site hosts Mango, a system to assist GNOME administrative volunteers to maintain the data held in our LDAP and MySQL database (users, groups, mirrors etc).</p>
       <p>The following public forms have been provided to ease the burden of processing these requests:</p>
       <ul>
        <li><a href="/new_account_request.php">Developer account request form (TODO).</a></li>
        <li><a href="/new_email_request.php">Foundation member '@gnome.org' e-mail request form (TODO).</a></li>
       </ul>
       <p>For any further questions, please contact <a href="mailto:support@gnome.org">support@gnome.org</a>.</p>
      </xsl:if>
      <xsl:if test="boolean(/page/user)">
       You are now logged in as <xsl:value-of select="/page/user/cn"/>.
      </xsl:if>
     </td>
    </tr>
   </table>
  </xsl:template>

</xsl:stylesheet>

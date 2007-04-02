<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:template match="user">
   <table width="130" border="0" cellspacing="1" cellpadding="0" bgcolor="#FF6600">
    <tr>
     <td bgcolor="#FF6600" align="center">
      <span class="spcr">Logged in</span>
     </td>
    </tr>
    <tr>
     <td align="center" class="nav">
      <xsl:apply-templates select="name"/>
     </td>
    </tr>
    <tr>
     <td align="center" class="nav">
      <xsl:apply-templates select="email"/>
     </td>
    </tr>
    <xsl:call-template name="userbuttons"/>
    <tr>
     <td align="center" class="nav">
      <a href="{/page/@baseuri}?logout">Logout</a>
     </td>
    </tr>
   </table>
  </xsl:template>

  <xsl:template match="loginform">
   <form method="post" action="{/page/@baseuri}/index.php">

    <xsl:choose>
     <xsl:when test="@mode='reminderform'">
      <input type="hidden" name="action" value="sendreminder"/>
     </xsl:when>
     <xsl:otherwise>
      <input type="hidden" name="action" value="login"/>
     </xsl:otherwise>
    </xsl:choose>

    <table class="loginform" width="130" border="0" cellspacing="1" cellpadding="0" bgcolor="#FF6600">
     <tr>
      <td bgcolor="#FF6600" align="center" colspan="2">
       <span class="spcr">
        <xsl:choose>
         <xsl:when test="@mode='form'">Log in</xsl:when>
         <xsl:when test="@mode='failed'">Login failed</xsl:when>
         <xsl:when test="@mode='expired'">Account expired</xsl:when>
         <xsl:when test="@mode='reminderform'">Request login reminder</xsl:when>
         <xsl:when test="@mode='remindersent'">Login reminder sent</xsl:when>
         <xsl:otherwise>Unknown mode - '<xsl:value-of select="@mode"/>'</xsl:otherwise>
        </xsl:choose>
       </span>
      </td>
     </tr>
     <xsl:if test="boolean(exception)">
      <tr>
       <td class="error" colspan="2">
        <xsl:apply-templates select="exception"/>
       </td>
      </tr>
     </xsl:if>
     <xsl:if test="@mode='reminderform'">
      <tr>
       <td align="center" class="nav">
        e-mail
       </td>
       <td align="center" class="nav">
        <input type="text" name="email" size="10"/>
       </td>
      </tr>
      <tr>
       <td align="center" class="nav" colspan="2">
        <input type="submit" value="Send reminder" />
       </td>
      </tr>
     </xsl:if>
     <xsl:if test="@mode='remindersent'">
      <tr>
       <td align="center" class="nav" colspan="2">
        Login reminder sent by e-mail to '<xsl:apply-templates select="email"/>'.
       </td>
      </tr>
     </xsl:if>
     <xsl:if test="@mode='failed'">
      <tr>
       <td class="error" align="center" colspan="2">
        <a href="{/page/@baseuri}?action=loginreminder">Forgotten your login?</a>
       </td>
      </tr>
     </xsl:if>
     <xsl:if test="@mode='expired'">
      <tr>
       <td class="error" colspan="2">
        Please contact <a href="mailto:admin@kohsamui.com">admin@kohsamui.com</a>.
       </td>
      </tr>
     </xsl:if>
     <xsl:if test="@mode!='reminderform'">
      <tr>
       <td align="center" class="nav">
        Login
       </td>
       <td align="center" class="nav">
        <input type="text" name="login" size="10"/>
       </td>
      </tr>
      <tr>
       <td align="center" class="nav">
        Password
       </td>
       <td align="center" class="nav">
        <input type="password" name="password" size="10"/>
       </td>
      </tr>
      <tr>
       <td align="center" class="nav" colspan="2">
        <input type="submit" value="Login" />
       </td>
      </tr>
     </xsl:if>
    </table>
   </form>
  </xsl:template>

</xsl:stylesheet>

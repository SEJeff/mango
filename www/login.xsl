<?xml version="1.0" encoding="UTF-8" ?>

<!-- $Id: login.xsl,v 1.2 2004/03/12 12:50:38 rossg Exp $ -->

<!--
  ** This is just an example template. Still waiting on the final design.
  -->

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">login</xsl:param>

  <xsl:include href="common.xsl" />

   <xsl:variable name="script" value="'login.php'"/>

   <xsl:template match="loginform">
   <xsl:choose>
    <xsl:when test="boolean(exception)">
     <p class="error"><xsl:apply-templates select="exception"/></p>
    </xsl:when>
    <xsl:when test="boolean(@failed)">
     <p class="error">Login failed</p>
    </xsl:when>
    <xsl:otherwise>
      <p>To access the services on this website, please log in to
        identify yourself. If you want to request a new account see
        the <a href="http://live.gnome.org/NewAccounts">instructions on the wiki</a>.</p>
    </xsl:otherwise>
   </xsl:choose>

   <form method="post" action="login.php" name="f">
    <input type="hidden" name="action" value="login"/>
    <input type="hidden" name="mango_token" value="{/page/@token}"/>
    <xsl:if test="boolean(@redirect)">
     <input type="hidden" name="redirect" value="{@redirect}"/>
    </xsl:if>
    <table class="login">
     <tr>
      <th>Login</th>
      <td><input type="text" name="login"/></td>
     </tr>
     <tr>
      <th>Password</th>
      <td><input type="password" name="password"/></td>
     </tr>
    <tr>
      <td colspan="2" align="right"><input type="submit" value="Login" /></td>
    </tr>
    </table>
   </form>
   <script language="JavaScript">
      function setf() { 
        document.forms['f'].login.focus();
      } 
      window.onload = setf;
   </script>

   <!-- TODO: Think of a way of resetting forgotten passwords safely
   <xsl:if test="boolean(@failed)">
    <p>Please click <a href="login.php?action=forgottenloginform">here</a> if you have forgotten your login.</p>
   </xsl:if>
   -->

  </xsl:template>

  <xsl:template match="forgottenloginform">
   <xsl:choose>
    <xsl:when test="boolean(exception)">
     <div class="exception"><xsl:apply-templates select="exception"/></div>
    </xsl:when>
    <xsl:otherwise>
     <p>Please supply your e-mail address. For security, further instructions will be sent to you by e-mail.</p>
    </xsl:otherwise>
   </xsl:choose>

   <form method="post" action="login.php">
    <input type="hidden" name="action" value="forgottenlogin"/>
    <input type="hidden" name="mango_token" value="{/page/@token}"/>
    <table class="login">
     <tr>
      <th>E-mail</th>
      <td><input type="text" name="email" value="{email}"/></td>
     </tr>
    </table>
    <input type="submit" value="I've forgotten my password!" />
   </form>

  </xsl:template>

  <xsl:template match="forgottenloginsent">
   <p>Further instructions have been sent to you via e-mail. Please follow them and try <a href="login.php">logging in</a> again.</p>
  </xsl:template>

  <xsl:template match="loggedinpage">
   <p>
    Welcome, <xsl:value-of select="/page/user/cn"/>.
   </p>
   <p>
    Please select from the following options:
   </p>
   <ul>
    <xsl:if test="boolean(/page/group[@cn='sysadmin']) or boolean(/page/group[@cn='accounts'])">
     <li>
      <a href="{/page/@baseurl}/list_users.php?reload=true">
       LDAP users
      </a>
     </li>
    </xsl:if>
    <xsl:if test="boolean(/page/group[@cn='sysadmin'])">
     <li>
      <a href="{/page/@baseurl}/list_ftpmirrors.php?reload=true">
       FTP mirrors
      </a>
     </li>
     <li>
      <a href="{/page/@baseurl}/list_modules.php?reload=true">
       GNOME modules
      </a>
     </li>
    </xsl:if>
    <xsl:if test="boolean(/page/group[@cn='membctte'])">
     <li>
      <a href="{/page/@baseurl}/list_foundationmembers.php?reload=true">
       Foundation membership
      </a>
     </li>
    </xsl:if>
    <li>
     <a href="{/page/@baseurl}/login.php?logout=true">
      Logout
     </a>
    </li>
   </ul>
  </xsl:template>

  <xsl:template match="loggedoutpage">
   <h1>Logged out</h1>
   <p>Thanks for dropping by. Catch you again next time.</p>
   <p>Please <a href="{/page/@baseurl}/index.php">click here</a> to continue.</p>
  </xsl:template>

<!--
  <xsl:template match="notices">
   <table class="notices">
    <tr>
     <th>
      <b>
       <xsl:value-of select="title"/>
      </b>
     </th>
    </tr>
    <tr>
     <td>
      <table class="noticesitem">
       <xsl:apply-templates select="item"/>
      </table>
     </td>
    </tr>
   </table>
  </xsl:template>

  <xsl:template match="notices/item">
   <tr>
    <th colspan="2">
      <xsl:element name="a">
       <xsl:attribute name="href">
        <xsl:value-of select="link"/>
       </xsl:attribute>
       <xsl:value-of select="title"/>
      </xsl:element>
    </th>
   </tr>
   <tr>
    <td width="30">
     <xsl:text>&#x0d;</xsl:text>
    </td>
    <td>
     <xsl:value-of select="description"/>
    </td>
   </tr>
  </xsl:template>
-->

</xsl:stylesheet>

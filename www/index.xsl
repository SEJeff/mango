<?xml version="1.0" encoding="UTF-8" ?>

<!-- $Id: index.xsl,v 1.4 2004/03/11 04:47:07 rossg Exp $ -->

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">home</xsl:param>

  <xsl:include href="common.xsl" />

  <xsl:template match="group">
    <xsl:param name="requestid" select="'None'" />
    <dd>
      <xsl:value-of select='@cn'/>: 
      <input type="radio" id="a{generate-id(.)}" name="rq:{$requestid}:{@cn}" value="approve"/><label for="a{generate-id(.)}">Approve</label>
      <input type="radio" id="n{generate-id(.)}" name="rq:{$requestid}:{@cn}" value="" checked="true" /><label for="n{generate-id(.)}">No change</label>
      <input type="radio" id="r{generate-id(.)}" name="rq:{$requestid}:{@cn}" value="reject"/><label for="r{generate-id(.)}">Reject</label>
    </dd>
  </xsl:template>

  <xsl:template match="account">
    <dt>
      User <a href="mailto:{@email}"><xsl:value-of select="@cn"/></a> with comment:<br />
      <i>"<xsl:value-of select="@comment"/>"</i>
    </dt>
    <xsl:apply-templates select='groups/group'>
      <xsl:with-param name="requestid" select="@db_id" />
    </xsl:apply-templates>
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
    <xsl:choose>
      <xsl:when test="not(boolean(/page/user))">
        <p>This site hosts Mango, a system to assist GNOME administrative
          volunteers to maintain the data held in our LDAP and MySQL
          database (users, groups, mirrors etc).</p>
        <p>To access protected services on this website, please log in to
          identify yourself. If you want to request a new account click
          on the '<a href="new_account.php">new account</a>' link.</p>
        <form method="post" action="login.php" name="f">
          <input type="hidden" name="action" value="login"/>
          <input type="hidden" name="mango_token" value="{/page/@token}"/>
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
              <th><small>(<a href="new_account.php?reload=true">new account</a>)</small></th>
              <td align="right"><input type="submit" value="Login" /></td>
            </tr>
            </table>
        </form>
      <script language="JavaScript">
        document.forms['f'].login.focus();
      </script>
      </xsl:when>
      <xsl:otherwise>
        You are logged in as <xsl:value-of select="/page/user/cn"/>.
        <xsl:if test="boolean(vouchers)">
          <p>Please approve/reject the following account requests:</p>
          <form name="module" method="POST">
            <input type="hidden" name="mango_token" value="{/page/@token}"/>
            <dl>
            <xsl:for-each select="vouchers">
              <xsl:apply-templates match="account"/>
            </xsl:for-each>
          </dl>
            <p><input type="submit" value="Submit"/></p>
          </form>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>

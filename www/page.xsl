<?xml version="1.0" encoding="UTF-8" ?>

<!-- $Id: page.xsl,v 1.22 2004/03/27 03:35:25 rossg Exp $ -->

<!DOCTYPE html [
 <!ENTITY middot "&#183;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html" encoding="ISO-8859-1" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />

  <xsl:template match="page">
   <html>
    <head>
     <title>GNOME Mango: <xsl:value-of select="@title"/></title>

     <link rel="stylesheet" type="text/css" href="{/page/@baseurl}/default.css"/>
     <link rel="icon" type="image/png" href="http://www.gnome.org/img/logo/foot-16.png"/>

     <meta http-equiv="Content-Type" content="text/html; charset=us-ascii"/>
     <meta name="description" content="The GNOME Project"/>
    </head>
    <body>

    <div id="hdr">
     <div id="logo">
      <a href="{/page/@baseurl}">
       <img src="http://www.gnome.org/img/spacer" alt="HOME"/>
      </a>
     </div>
     <!-- Not logged in... -->
     <div id="hdrNav">
      <xsl:if test="boolean(/page/group)">
       <a href="/">Home</a>
       <xsl:call-template name="breadcrumb"/>
      </xsl:if>
      <xsl:if test="not(boolean(/page/group))">
       <a href="http://www.gnome.org/">GNOME</a> &middot; 
       <a href="http://sysadmin.gnome.org/start/stable/">Sysadmin</a> ::
       <a href="http://developer.gnome.org/">Developers</a> ::
       <a href="http://foundation.gnome.org/">Foundation</a> ::
       <a href="http://cvs.gnome.org/">CVS</a> ::
       <a href="http://mail.gnome.org/">Mail</a> ::
       <a href="http://www.gnome.org/contact/">Contact</a>
      </xsl:if>
     </div>
    </div>

    <div id="body">
     <xsl:apply-templates/>
    </div>

    <div id="sidebar">
     <p class="section">Navigation</p>
     <ul>
      <li><a href="{/page/@baseurl}/login.php">Home</a></li>
      <xsl:if test="boolean(group)">
       <xsl:if test="boolean(group[@cn='accounts']) or boolean(group[@cn='sysadmin'])">
        <li><a href="{/page/@baseurl}/list_users.php">Users</a></li>
       </xsl:if>
       <xsl:if test="boolean(group[@cn='sysadmin'])">
        <li><a href="{/page/@baseurl}/list_ftpmirrors.php">Mirrors</a></li>
       </xsl:if>
       <xsl:if test="boolean(group[@cn='membctte'])">
        <li><a href="{/page/@baseurl}/list_foundationmembers.php">Foundation Members</a></li>
       </xsl:if>
       <li><a href="{/page/@baseurl}/login.php?logout=true">Logout</a></li>
      </xsl:if>
      <xsl:if test="not(boolean(user))">
       <li><a href="{/page/@baseurl}/login.php">Login</a></li>
      </xsl:if>
     </ul>
    </div>

    <div id="copyright">
     <p>
      Copyright &#169; 2003-2006, <a href="http://www.gnome.org/">The GNOME Project</a><br />
      GNOME and the foot logo are trademarks of the GNOME Foundation.<br />
      <a href="http://validator.w3.org/check/referer">Optimized</a> for <a href="http://www.w3.org/">standards</a>. Hosted by <a href="http://redhat.com/">Red Hat</a>.
      </p>
     </div>
    </body>
   </html>

   <xsl:comment>
    Page date: <xsl:value-of select="/page/@date"/>
   </xsl:comment>
  </xsl:template>

  <xsl:template match="/page/notloggedin">
   <p class="error">You must be <a href="{/page/@baseurl}/login.php?redirect={/page/@thisurl}">logged in</a> to use this page.</p>
  </xsl:template>

  <xsl:template match="/page/notauthorised">
   <p class="error">You must be a member of the '<xsl:value-of select="@group"/>' group to use this page.</p>
  </xsl:template>

  <xsl:template match="error">
   <p class="error">
    <xsl:apply-templates/>
   </p>
  </xsl:template>
    
  <xsl:template match="/page/user"/>
  <xsl:template match="/page/group"/>

</xsl:stylesheet>

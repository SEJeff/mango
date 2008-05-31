<?xml version="1.0" encoding="UTF-8" ?>

<!-- $Id: page.xsl,v 1.22 2004/03/27 03:35:25 rossg Exp $ -->

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html" encoding="UTF-8" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
  
  <xsl:template match="page">
    <xsl:param name="channel"><xsl:value-of select="$libgo.channel"/></xsl:param>
   <html>
    <head>
     <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
     <title>GNOME Mango: <xsl:value-of select="@title"/></title>

     <link rel="stylesheet" type="text/css" href="{/page/@baseurl}/skin/default.css"/>
     <link rel="icon" type="image/png" href="http://www.gnome.org/img/logo/foot-16.png"/>
     <link rel="SHORTCUT ICON" type="image/png" href="http://www.gnome.org/img/logo/foot-16.png"></link>

     <meta name="description" content="The GNOME Project"/>
    </head>
    <body>

    <div id="page">
      <ul id="general">
	<li id="siteaction-gnome_home" class="home">
	  <a href="http://www.gnome.org/" accesskey="" title="Home">Home</a>
	</li>
	<li id="siteaction-gnome_news">
	  <a href="http://news.gnome.org" accesskey="" title="News">News</a>
	</li>
	<li id="siteaction-gnome_projects">
	  <a href="http://www.gnome.org/projects/" accesskey="" title="Projects">Projects</a>
	</li>
	<li id="siteaction-gnome_art">
	  <a href="http://art.gnome.org" accesskey="" title="Art">Art</a>
	</li>
	<li id="siteaction-gnome_support">
	  <a href="http://www.gnome.org/support/" accesskey="" title="Support">Support</a>
	</li>
	<li id="siteaction-gnome_development">
	  <a href="http://developer.gnome.org" accesskey="" title="Development">Development</a>
	</li>
	<li id="siteaction-gnome_community">
	  <a href="http://www.gnome.org/community/" accesskey="" title="Community">Community</a>
	</li>
      </ul>
      <div id="header">
	<h1>
	  GNOME Mango
	</h1>   
	<div id="tabs">
	  <ul id="portal-globalnav">
	    <li id="portaltab-root">
	      <xsl:if test="$channel = 'home'">
		<xsl:attribute name="class">selected</xsl:attribute>
	      </xsl:if>
	      <a href="{/page/@baseurl}"><span>Home</span></a>
	    </li>
	    <xsl:if test="boolean(group)">
	      <xsl:if test="boolean(group[@cn='accounts']) or boolean(group[@cn='sysadmin'])">
		<li><xsl:if test="$channel = 'users'"><xsl:attribute name="class">selected</xsl:attribute></xsl:if><a href="{/page/@baseurl}/list_users.php"><span>Users</span></a></li>
		<li><xsl:if test="$channel = 'accounts'"><xsl:attribute name="class">selected</xsl:attribute></xsl:if><a href="{/page/@baseurl}/list_accounts.php"><span>Applications</span></a></li>
	      </xsl:if>
	      <xsl:if test="boolean(group[@cn='sysadmin'])">
	        <li><xsl:if test="$channel = 'mirrors'"><xsl:attribute name="class">selected</xsl:attribute></xsl:if><a href="{/page/@baseurl}/list_ftpmirrors.php"><span>Mirrors</span></a></li>
		<li><xsl:if test="$channel = 'modules'"><xsl:attribute name="class">selected</xsl:attribute></xsl:if><a href="{/page/@baseurl}/list_modules.php"><span>Modules</span></a></li>
             </xsl:if>
	     <xsl:if test="boolean(group[@cn='membctte'])">
	       <li><xsl:if test="$channel = 'foundation'"><xsl:attribute name="class">selected</xsl:attribute></xsl:if><a href="{/page/@baseurl}/list_foundationmembers.php"><span>Foundation Members</span></a></li>
	     </xsl:if>
	     <li><xsl:if test="$channel = 'login'"><xsl:attribute name="class">selected</xsl:attribute></xsl:if><a href="{/page/@baseurl}/login.php?logout=true"><span>Logout</span></a></li>
	   </xsl:if>
	   <xsl:if test="not(boolean(user))">
	    <li><xsl:if test="$channel = 'login'"><xsl:attribute name="class">selected</xsl:attribute></xsl:if><a href="{/page/@baseurl}/login.php"><span>Login</span></a></li>
	   </xsl:if>
	  </ul>
	</div> <!-- end of #tabs -->
      </div> <!-- end of #header -->
    </div>

    <div class="body body-sidebar">
     <xsl:apply-templates/>
    </div>

    <div id="copyright">
     <p>
      Copyright &#169; 2003-2008, <a href="http://www.gnome.org/">The GNOME Project</a><br />
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

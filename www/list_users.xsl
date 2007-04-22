<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'list_users.php'"/>
 
  <xsl:template name="breadcrumb"> 
   · <a href="{$script}">Users</a>
  </xsl:template>

  <xsl:template match="listusers">
   <xsl:apply-templates select="error"/>
   <form method="GET" action="{$script}" id="filterform" name="filterform">
    <table class="navigation">
     <caption>Navigation</caption>
     <tr>
      <td>
       Search: <input type="text" name="filter_keyword" value="{filter/keyword}" onchange="this.form.submit()"/>
       <noscript>
        <input type="submit" value="&gt; &gt;"/>
       </noscript>
      </td>
      <td align="center">
       <a class="button" href="new_user.php?reload=true">New user</a>
      </td>
      <td align="right">
       <xsl:if test="boolean(user)">
        Page <xsl:value-of select="pagedresults/page_num"/> of <xsl:value-of select="pagedresults/total_pages"/>
       </xsl:if>
       <span class="smallprint">(<xsl:value-of select="pagedresults/total_results"/> users found)</span>
       <xsl:if test="pagedresults/page_num &gt; 1">
        <a class="button" href="{$script}?page={pagedresults/page_num - 1}">&lt;&lt; Prev</a>
       </xsl:if>
       <xsl:if test="pagedresults/page_num &lt; pagedresults/total_pages">
        <a class="button" href="{$script}?page={pagedresults/page_num + 1}">Next &gt;&gt;</a>
       </xsl:if>
      </td>
     </tr>
    </table>
   </form>
   <xsl:if test="boolean(user)">
    <table class="results">
     <caption>Results</caption>
     <tr>
      <th>UID</th>
      <th>Name</th>
      <th>Email</th>
     </tr>
     <xsl:for-each select="user">
      <xsl:sort select="uid"/>
      <tr class="row-{position() mod 2}">
       <td>
        <a href="update_user.php?uid={uid}">
         <xsl:apply-templates select="uid"/>
        </a>
       </td>
       <td>
        <xsl:apply-templates select="name"/>
       </td>
       <td>
        <xsl:apply-templates select="email"/>
       </td>
       <td>
       </td>
      </tr>
     </xsl:for-each>
    </table>
    <br/>
   </xsl:if>
  </xsl:template>
   
</xsl:stylesheet>

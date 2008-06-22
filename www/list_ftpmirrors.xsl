<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">mirrors</xsl:param>
   
  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'list_ftpmirrors.php'"/>

  <xsl:template match="listftpmirrors">
   <xsl:apply-templates select="error"/>
   <form method="GET" action="{$script}" name="filterform">
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
      <a class="button" href="add/">New FTP mirror</a>
      </td>
      <td align="right">
       <xsl:if test="boolean(ftpmirror)">
        Page <xsl:value-of select="pagedresults/page_num"/> of <xsl:value-of select="pagedresults/total_pages"/>
       </xsl:if>
       <span class="smallprint">(<xsl:value-of select="pagedresults/total_results"/> mirrors found)</span>
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
   <xsl:if test="boolean(ftpmirror)">
    <table class="results">
     <caption>Results</caption>
     <tr>
      <th>Name</th>
      <th>URL</th>
      <th>Location</th>
     </tr>
     <xsl:for-each select="ftpmirror">
      <xsl:sort select="location"/>
      <tr>
       <xsl:attribute name="class">
        <xsl:choose>
         <xsl:when test="not(boolean(active))">
          <xsl:value-of select="'row-inactive'"/>
         </xsl:when>
         <xsl:otherwise>
          <xsl:value-of select="concat('row-', position() mod 2)"/>
         </xsl:otherwise>
        </xsl:choose>
       </xsl:attribute>
       <td>
        <a href="edit/{id}/">
         <xsl:apply-templates select="name"/>
        </a>
       </td>
       <td>
        <a href="{url}" target="_blank">
         <xsl:apply-templates select="url"/>
        </a>
       </td>
       <td>
        <xsl:apply-templates select="location"/>
       </td>
      </tr>
     </xsl:for-each>
    </table>
    <br/>
   </xsl:if>
  </xsl:template>
   
</xsl:stylesheet>

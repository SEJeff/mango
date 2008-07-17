<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">modules</xsl:param>
 
  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'.'"/>

  <xsl:template match="listmodules">
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
       <a class="button" href="new_module.php?reload=true">New module</a>
      </td>
      <td align="right">
       <xsl:if test="boolean(module)">
        Page <xsl:value-of select="pagedresults/page_num"/> of <xsl:value-of select="pagedresults/total_pages"/>
       </xsl:if>
       <span class="smallprint">(<xsl:value-of select="pagedresults/total_results"/> modules found)</span>
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
   <xsl:if test="boolean(module)">
    <table class="results">
     <caption>Results</caption>
     <tr>
      <th>Name</th>
      <th>Description</th>
      <th>Maintainers</th>
     </tr>
     <xsl:for-each select="module">
      <xsl:sort select="cn"/>
      <tr class="row-{position() mod 2}">
       <td>
        <a href="edit/{cn}/">
         <xsl:apply-templates select="cn"/>
        </a>
       </td>
       <td>
        <xsl:apply-templates select="description"/>
       </td>
       <td>
        <xsl:apply-templates select="maintainer"/>
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

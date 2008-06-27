<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">foundation</xsl:param>
  
  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'.'"/>

  <xsl:template match="listfoundationmembers">
   <xsl:apply-templates select="error"/>
   <xsl:if test="boolean(renewed)">
    Membership of "<xsl:value-of select="@name" />" is renewed.
   </xsl:if>
	 <xsl:if test="boolean(emailsent)">
  	  <p>Renewal notification mail has been send to user.</p>
  	</xsl:if>

    <table class="navigation">
     <caption>Navigation</caption>
     <tr>
      <td>
      <form method="GET" action="{$script}" name="filterform">
       Search: <input type="text" name="filter_keyword" value="{filter/keyword}" onchange="this.form.submit()"/>
       <select name="filter_status" onchange="this.form.submit()">
        <option value=""><xsl:if test="filter/status = ''"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
          All members
        </option>
        <option value="current"><xsl:if test="filter/status = 'current'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
          Current members
        </option>
        <option value="needrenewal"><xsl:if test="filter/status = 'needrenewal'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
          Old members needing renewal
        </option>
       </select>
       <noscript>
        <input type="submit" value="&gt; &gt;"/>
       </noscript>
      </form>
      </td>
      <td align="center">
       <a class="button" href="new_foundationmember.php?reload=true">New Member</a>
      </td>
      <td align="right">
       <xsl:if test="boolean(foundationmember)">
        Page <xsl:value-of select="pagedresults/page_num"/> of <xsl:value-of select="pagedresults/total_pages"/>
       </xsl:if>
       <span class="smallprint">(<xsl:value-of select="pagedresults/total_results"/> members found)</span>
       <xsl:if test="pagedresults/page_num &gt; 1">
        <a class="button" href="?page={pagedresults/page_num - 1}">&lt;&lt; Prev</a>
       </xsl:if>
       <xsl:if test="pagedresults/page_num &lt; pagedresults/total_pages">
        <a class="button" href="?page={pagedresults/page_num + 1}">Next &gt;&gt;</a>
       </xsl:if>
      </td>
     </tr>
    </table>
   <xsl:if test="boolean(foundationmember)">
    <table class="results">
     <caption>Results</caption>
     <tr>
      <th>Last Name</th>
      <th>First Name</th>
      <th>e-mail</th>
      <th>Last renewed on</th>
      <th>Action</th>
     </tr>
     <xsl:for-each select="foundationmember">
      <xsl:sort select="location"/>
      <tr>
       <xsl:attribute name="class">
         <xsl:value-of select="concat('row-', position() mod 2)"/>
       </xsl:attribute>
       <td>
	 <a href="edit/{id}/">
         <xsl:apply-templates select="lastname"/>
        </a>
       </td>
       <td>
        <xsl:apply-templates select="firstname"/>
       </td>
       <td>
        <a href="mailto:{email}">
         <xsl:apply-templates select="email"/>
        </a>
       </td>
       <td>
        <xsl:apply-templates select="last_renewed_on"/>
       </td>
       <td>
        <xsl:choose>
          <xsl:when test="boolean(member)">
          <a class="button" href="edit/{id}/">Update</a>
        	<xsl:if test="boolean(need_to_renew)">
		  <form method="POST" action="{$script}" name="filterform" style="display: inline">
		    <input type="hidden" name="mango_token" value="{/page/@token}"/>
		    <input type="hidden" name="filter_name" value="{filter/keyword}"/>
		    <input type="hidden" name="filter_status" value="{filter/status}"/>
		    <input type="hidden" name="page" value="{page}"/>
		    <input type="hidden" name="renew" value="{id}"/>
		    <input type="submit" value="Renew"/>
		  </form>
        	</xsl:if>
          </xsl:when>
          <xsl:otherwise>
            Resigned
          </xsl:otherwise>
        </xsl:choose>
       </td>
      </tr>
     </xsl:for-each>
    </table>
   </xsl:if>
  </xsl:template>
   
</xsl:stylesheet>

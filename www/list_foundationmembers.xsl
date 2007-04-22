<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'list_foundationmembers.php'"/>
  
  <xsl:template name="breadcrumb">
   · <a href="/list_foundationmembers.php">Foundation Members</a>
  </xsl:template>
 
  <xsl:template match="listfoundationmembers">
   <xsl:apply-templates select="error"/>
   <xsl:if test="boolean(renewed)">
    Membership of "<xsl:value-of select="@name" />" is renewed.
   </xsl:if>
	 <xsl:if test="boolean(emailsent)">
  	  <p>Renewal notification mail has been send to user.</p>
  	</xsl:if>

   <form method="GET" action="{$script}" name="filterform">
    <table class="navigation">
     <caption>Navigation</caption>
     <tr>
      <td>
       Search: <input type="text" name="filter_name" value="{filter_name}" onchange="this.form.submit()"/>
       <select name="filter_old" onchange="this.form.submit()">
        <option value="all">All members</option>
        <option value="current">Current members</option>
        <option value="needrenewal">Old members needing renewal</option>
       </select>
       <noscript>
        <input type="submit" value="&gt; &gt;"/>
       </noscript>
      </td>
      <td align="center">
       <a class="button" href="new_foundationmember.php?reload=true">New Member</a>
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
        <a href="update_foundationmember.php?id={id}">
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
           <a class="button" href="update_foundationmember.php?id={id}">Update</a>
        	<xsl:if test="boolean(need_to_renew)">
         		<a class="button" href="{$script}?filter_name={filter_name}&amp;filter_old={filter_old}&amp;page={page}&amp;renew={id}">Renew</a>
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

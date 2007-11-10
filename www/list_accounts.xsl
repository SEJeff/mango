<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">accounts</xsl:param>

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'list_accounts.php'"/>
 
  <xsl:template match="listaccounts">
   <xsl:apply-templates select="error"/>
  <p> 
   Pending new account request:
   <form method="GET" action="{$script}" id="filterform" name="filterform">
    <table class="navigation">
     <caption>Navigation</caption>
     <tr>
      <td>
       Search: <input type="text" name="filter_keyword" value="{filter/keyword}" onchange="this.form.submit()"/>
       <noscript>
        <input type="submit" value="&gt; &gt;"/>
       </noscript>
       <select name="filter_status">
	 <option value="S">Awaiting setup</option>
	 <option value="V">Awaiting vouchers</option>
	 <option value="M">Awaiting mail verification</option>
	 <option value="A">Created</option>
	 <option value="R">Rejected</option>
       </select>
      </td>
      <td align="right">
       <xsl:if test="boolean(user)">
        Page <xsl:value-of select="pagedresults/page_num"/> of <xsl:value-of select="pagedresults/total_pages"/>
       </xsl:if>
       <span class="smallprint">(<xsl:value-of select="pagedresults/total_results"/> accounts found)</span>
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
     <table class="results" cellspacing="0" cellpadding="5">
       <caption>Results</caption>
       <thead>
	 <th>UID</th>
	 <th>Name</th>
	 <th>Approved For</th>
	 <th>Created On</th>
	 <th>Action</th>
      </thead>
      <tbody>
     <xsl:for-each select="account">
      <xsl:sort select="@uid"/>
      <tr class="row-{position() mod 2}">
       <td valign="top">
        <a href="mailto:{@mail}">
         <xsl:apply-templates select="@uid"/>
        </a>
       </td>
       <td valign="top">
        <xsl:apply-templates select="@cn"/>
       </td>
       <td valign="top">
	 <xsl:for-each select="groups/group">
	   <tt><xsl:value-of select='@cn'/></tt>
	   <xsl:if test="@approvedby != ''"> by <xsl:value-of select="@approvedby"/> for <xsl:value-of select="@module"/>
	   </xsl:if>
	     <br/>
        </xsl:for-each>
       </td>
       <td valign="top">
        <xsl:apply-templates select="@createdon"/>
       </td>
       <td valign="top">
	 <xsl:if test='(@status = "S") or (@status = "V")'><a class="button" href="new_user.php?reload=true&amp;account={@db_id}">New user</a></xsl:if>
	 <xsl:if test='not((@status = "R") or (@status = "A"))'>
	   <form name='f' method='POST' action="{script}" style='display: inline'>
             <input type="hidden" name="mango_token" value="{/page/@token}"/>
	     <input type="hidden" name="reject" value="{@db_id}"/>
	     <input type="submit" value="Reject" />
	   </form>
	 </xsl:if>
       </td>
      </tr>
     </xsl:for-each>
     </tbody>
    </table>
    <br/>
   </p>
  </xsl:template>
   
</xsl:stylesheet>

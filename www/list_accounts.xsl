<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">accounts</xsl:param>

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'list_accounts.php'"/>
 
  <xsl:template match="listaccounts">
   <xsl:apply-templates select="error"/>
    <xsl:choose>
   <xsl:when test="boolean(account)" >
	<p> 
       	Pending new account request:
    <form name='f' method='POST' action="{script}">
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
	 <a class="button" href="new_user.php?reload=true&amp;account={@db_id}">New user</a><input type="submit" value="Reject" />
       </td>
      </tr>
     </xsl:for-each>
     </tbody>
    </table>
    </form>
    <br/>
   </p>
   </xsl:when>
   <xsl:otherwise>
    <p>
      There are no pending account requests.
    </p>
   </xsl:otherwise>
  </xsl:choose>
  </xsl:template>
   
</xsl:stylesheet>

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
			<th>Approved By</th>
			<th>Created On</th>
			<th>Action</th>
			</thead>
			<tbody>
     <xsl:for-each select="account">
      <xsl:sort select="uid"/>
      <tr class="row-{position() mod 2}">
       <td valign="top">
        <a href="mailto:{email}">
         <xsl:apply-templates select="uid"/>
        </a>
       </td>
       <td valign="top">
        <xsl:apply-templates select="name"/>
       </td>
       <td valign="top">
       	<xsl:for-each select="approvedby">
        	<input type="checkbox" checked="on" />
        	 <a href="mailto:{email}"><xsl:value-of select="name"/></a> (<xsl:value-of select="module"/>)<br/>
        </xsl:for-each>
       </td>
       <td valign="top">
        <xsl:apply-templates select="createdon"/>
       </td>
       <td valign="top">
        	<input type="submit" value="Add" /><input type="submit" value="Reject" />
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

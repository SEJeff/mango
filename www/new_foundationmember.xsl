<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'new_foundationmember.php'"/>
  
  <xsl:template name="breadcrumb">
   · <a href="list_foundationmembers.php">Foundation Members</a>
  </xsl:template>

<xsl:template match="newfoundationmember">  
	<xsl:apply-templates select="error"/>
   <xsl:if test="boolean(added)">
    <p>Foundation Member '<xsl:value-of select="added/id"/>' created.</p>
   </xsl:if>
	 <xsl:if test="boolean(emailsent)">
		<p>Accepted notice sent.</p>
	 </xsl:if>
   <form method="POST" action="{$script}" name="form">
    <input type="hidden" name="mango_token" value="{/page/@token}"/>
    <table class="form">
     <caption>New Foundation Member form</caption>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='lastname'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       Last Name
      </th>
      <td>
       <input type="text" name="lastname" value="{lastname}" size="40"/>
      </td>
     </tr>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='firstname'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       First Name
      </th>
      <td>
       <input type="text" name="firstname" value="{firstname}" size="40"/>
      </td>
     </tr>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='email'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       E-mail
      </th>
      <td>
       <input type="text" name="email" value="{email}" size="40"/>
      </td>
     </tr>
     <tr>
      <th>
       Comments
      </th>
      <td>
       <textarea name="comments" rows="5" cols="40"><xsl:value-of select="comments"/></textarea>
      </td>
     </tr>
    </table>
    <p>
     <input type="submit" name="add" value="Add &gt;&gt;"/>
    </p>
   </form>
  </xsl:template>

</xsl:stylesheet>

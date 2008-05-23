<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">foundation</xsl:param>  
 
  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'update_foundationmember.php'"/>

  <xsl:template match="updatefoundationmember">
   <xsl:apply-templates select="error"/>
   <xsl:if test="boolean(updated)">
    <p>Foundation Member updated.</p>
    <xsl:apply-templates select="updated/change"/>
  </xsl:if>
	<xsl:if test="boolean(emailsent)">
		<p>Renewal notification mail has been send to user.</p>
	</xsl:if>
   <form enctype="multipart/form-data" method="POST" action="{$script}" name="form">
   <input type="hidden" name="mango_token" value="{/page/@token}"/>
    <input type="hidden" name="idcheck" value="{id}"/>
    <table class="form">
     <caption>Update member '<xsl:value-of select="id"/>'</caption>
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
       <xsl:if test="boolean(formerror[@type='userid'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       GNOME userid
      </th>
      <td>
       <input type="text" name="userid" value="{userid}" size="15"/>
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
     <xsl:if test="boolean(need_to_renew)">
     <tr>
      <th>
       Renew?
      </th>
      <td>
       <input type="checkbox" name="renew">
       </input>
      </td>
     </tr>
     </xsl:if>
    </table>
    <p>
     <input type="submit" name="update" value="Update &gt;&gt;"/>
    </p>
   </form>
  </xsl:template>

  <xsl:template match="updated/change">
   <xsl:choose>
    <xsl:when test="@id='lastname'">
     <p>Last Name changed</p>
    </xsl:when>
    <xsl:when test="@id='firstname'">
     <p>First Name changed</p>
    </xsl:when>
    <xsl:when test="@id='email'">
     <p>E-mail address updated</p>
    </xsl:when>
    <xsl:when test="@id='userid'">
     <p>GNOME userid updated</p>
    </xsl:when>
    <xsl:when test="@id='last_renewed_on'">
     <p>Last Renewal updated</p>
    </xsl:when>
    <xsl:when test="@id='comments'">
     <p>Comments updated</p>
    </xsl:when>
    <xsl:when test="@id='renewed'">
     <p>Renewed</p>
    </xsl:when>
    <xsl:otherwise>
     <p>Change '<xsl:value-of select="@id"/>'</p>
    </xsl:otherwise>
   </xsl:choose>
  </xsl:template>
  
</xsl:stylesheet>

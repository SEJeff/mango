<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">mirrors</xsl:param>
   
  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'new_ftpmirror.php'"/>
  
  <xsl:template match="newftpmirror">
   <xsl:apply-templates select="error"/>
   <xsl:if test="boolean(added)">
    <p>FTP Mirror '<xsl:value-of select="added/id"/>' created.</p>
   </xsl:if>
   <form method="POST" action="{$script}" name="form">
    <input type="hidden" name="mango_token" value="{/page/@token}"/>
    <table class="form">
     <caption>New FTP mirror form</caption>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='name'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       Name
      </th>
      <td>
       <input type="text" name="name" value="{name}" size="40"/>
      </td>
     </tr>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='location'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       Location
      </th>
      <td>
       <xsl:variable name="location" select="location"/>
       <select name="location">
        <xsl:for-each select="document('lists.xml')/list/mirrorlocation">
         <option>
          <xsl:if test="$location = .">
           <xsl:attribute name="selected"/>
          </xsl:if>
          <xsl:apply-templates/>
         </option>
        </xsl:for-each>
       </select>
      </td>
     </tr>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='url'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       URL
      </th>
      <td>
       <input type="text" name="url" value="{url}" size="40"/>
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
       Description
      </th>
      <td>
       <textarea name="description" rows="5" cols="40"><xsl:value-of select="description"/></textarea>
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

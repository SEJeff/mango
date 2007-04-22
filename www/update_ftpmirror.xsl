<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'update_ftpmirror.php'"/>
  
  <xsl:template name="breadcrumb">
   · <a href="/list_ftpmirrors.php">Mirrors</a>
  </xsl:template>
   
  <xsl:template match="updateftpmirror">
   <xsl:apply-templates select="error"/>
   <xsl:if test="boolean(updated)">
    <p>FTP mirror updated.</p>
    <xsl:apply-templates select="updated/change"/>
   </xsl:if>
   <form enctype="multipart/form-data" method="POST" action="{$script}" name="form">
    <input type="hidden" name="idcheck" value="{id}"/>
    <table class="form">
     <caption>Update mirror '<xsl:value-of select="id"/>'</caption>
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
     <tr>
      <th>
       Active
      </th>
      <td>
       <input type="checkbox" name="active">
        <xsl:if test="boolean(active)">
         <xsl:attribute name="checked"/>
        </xsl:if>
       </input>
      </td>
     </tr>
    </table>
    <p>
     <input type="submit" name="update" value="Update &gt;&gt;"/>
    </p>
   </form>
  </xsl:template>

  <xsl:template match="updated/change">
   <xsl:choose>
    <xsl:when test="@id='name'">
     <p>Name changed</p>
    </xsl:when>
    <xsl:when test="@id='location'">
     <p>Location changed</p>
    </xsl:when>
    <xsl:when test="@id='url'">
     <p>URL changed</p>
    </xsl:when>
    <xsl:when test="@id='email'">
     <p>E-mail address updated</p>
    </xsl:when>
    <xsl:when test="@id='description'">
     <p>Description updated</p>
    </xsl:when>
    <xsl:when test="@id='comments'">
     <p>Comments updated</p>
    </xsl:when>
    <xsl:when test="@id='activated'">
     <p>Activated</p>
    </xsl:when>
    <xsl:when test="@id='deactivated'">
     <p>De-activated</p>
    </xsl:when>
    <xsl:when test="@id='keysadded'">
     <p>SSH keys added</p>
    </xsl:when>
    <xsl:otherwise>
     <p>Change '<xsl:value-of select="@id"/>'</p>
    </xsl:otherwise>
   </xsl:choose>
  </xsl:template>
  
</xsl:stylesheet>

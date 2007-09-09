<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'new_module.php'"/>
  
  <xsl:template name="breadcrumb">
   · <a href="list_modules.php">GNOME Module List</a>
  </xsl:template>

<xsl:template match="newmodule">  
	<xsl:apply-templates select="error"/>
   <xsl:if test="boolean(added)">
    <p>GNOME Module '<xsl:value-of select="added/cn"/>' created.</p>
   </xsl:if>
   <form method="POST" action="{$script}" name="form">
    <table class="form">
     <caption>New GNOME Module form</caption>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='lastname'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       Module Name
      </th>
      <td>
       <input type="text" name="cn" value="{cn}" size="40"/>
      </td>
     </tr>
     <tr>
      <th>
       Maintainer
      </th>
      <td>
      	<select type="multiple" size="5" name="maintainerUids[]">
      		<xsl:for-each select="user">
      		 <option value="{uid}"><xsl:apply-templates select="name"/> &lt;<xsl:apply-templates select="email"/>&gt;</option>
      		</xsl:for-each>
      	</select>
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
      	Localization Module
      </th>
      <td>
      	<input type="checkbox" name="localizationModule" />
      </td>
     </tr>
     <tr>
      <th>
      	Localization Team
      </th>
      <td>
       <input type="text" name="localizationTeam" size="40"/>
      </td>
     </tr>
     <tr>
      <th>
      	Mailing List
      </th>
      <td>
      	<input type="text" name="mailingList" size="40"/>
      </td>
     </tr>
    </table>
    <p>
     <input type="submit" name="add" value="Add &gt;&gt;"/>
    </p>
   </form>
  </xsl:template>

</xsl:stylesheet>

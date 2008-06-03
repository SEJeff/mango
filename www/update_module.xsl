<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">modules</xsl:param>  

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'update_module.php'"/>

<xsl:template match="updatemodule">  
	<xsl:apply-templates select="error"/>
   <xsl:if test="boolean(changed)">
    <p>GNOME Module '<xsl:value-of select="updated/cn"/>' updated.</p>
   </xsl:if>

   <form method="POST" action="{$script}" name="form">
    <input type="hidden" name="mango_token" value="{/page/@token}"/>
    <table class="form">
     <caption>Update GNOME Module form</caption>
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
        <xsl:for-each select="maintainerUid">
          <input type="checkbox" name="maintainerUids[]" id="{generate-id()}" 
                 checked="checked" value="{key}"/>
          <label for="{generate-id()}">
            <xsl:value-of select="value" />
          </label><br/>
        </xsl:for-each>
        New maintainer userid:
        <input type="text" name="maintainerUids[]" />
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
      	<xsl:element name="input">
      		<xsl:attribute name="type">checkbox</xsl:attribute>
      		<xsl:attribute name="name">localizationModule</xsl:attribute>
      		<xsl:if test="boolean(localizationModule)">
      			<xsl:attribute name="checked" />
      		</xsl:if>
      	</xsl:element>
      </td>
     </tr>
     <tr>
      <th>
      	Localization Team
      </th>
      <td>
       <input type="text" name="localizationTeam" value="{localizationTeam}" size="40"/>
      </td>
     </tr>
     <tr>
      <th>
      	Mailing List
      </th>
      <td>
      	<input type="text" name="mailingList" value="{mailingList}" size="40"/>
      </td>
     </tr>
    </table>
    <p>
     <input type="submit" name="add" value="Update &gt;&gt;"/>
    </p>
   </form>
  </xsl:template>

</xsl:stylesheet>

<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="page.xsl" />

  <!--
    ** SECTION: Basic docbook markup language support
    -->
  <xsl:template match="document">
   <span><xsl:apply-templates/></span>
  </xsl:template>

  <xsl:template match="document/title">
   <h2><xsl:apply-templates/></h2>
  </xsl:template>

  <xsl:template match="section/title">
   <h3><xsl:apply-templates/></h3>
  </xsl:template>

  <xsl:template match="section/section/title">
   <h4><xsl:apply-templates/></h4>
  </xsl:template>

  <xsl:template match="itemizedlist">
   <ul><xsl:apply-templates/></ul>
  </xsl:template>

  <xsl:template match="listitem">
   <li><xsl:apply-templates/></li>
  </xsl:template>

  <xsl:template match="imagedata">
   <img>
    <xsl:attribute name="src">
     <xsl:value-of select="@fileref"/>
    </xsl:attribute>
    <xsl:attribute name="title">
     <xsl:value-of select="@title"/>
    </xsl:attribute>
    <xsl:apply-templates/>
   </img>
  </xsl:template>

  <xsl:template match="p|para">
   <p><xsl:apply-templates/></p>
  </xsl:template>

  <xsl:template match="i">
   <i><xsl:apply-templates/></i>
  </xsl:template>

  <xsl:template match="b">
   <b><xsl:apply-templates/></b>
  </xsl:template>

  <!-- Convenience hack! &lt;a&rt; isn't valid docbook -->
  <xsl:template match="a">
   <a>
    <xsl:attribute name="href">
     <xsl:value-of select="@href"/>
    </xsl:attribute>
    <xsl:if test="boolean(target)">
     <xsl:attribute name="target">
      <xsl:value-of select="@target"/>
     </xsl:attribute>
    </xsl:if>
    <xsl:apply-templates/>
   </a>
  </xsl:template>

  <!--
    ** SECTION: Stuff for presenting HTML pulldowns
    -->
  <xsl:template name="pulldown">
   <xsl:param name="type"></xsl:param>
   <xsl:param name="default"></xsl:param>
   <xsl:param name="noopt"></xsl:param>

    <xsl:if test="$noopt='yes'">
     <option value="">N/A</option>
    </xsl:if>
    <xsl:for-each select="/page/pulldowns[@type=$type]/option">
      <xsl:element name="option">
       <xsl:if test="@key=$default">
        <xsl:attribute name="selected"/>
       </xsl:if>
       <xsl:attribute name="value">
        <xsl:value-of select="@key"/>
       </xsl:attribute>
       <xsl:value-of select="@value"/>
      </xsl:element>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="pulldown_date">
   <xsl:param name="name"></xsl:param>
   <xsl:param name="day"></xsl:param>
   <xsl:param name="month"></xsl:param>
   <xsl:param name="year"></xsl:param>
   <xsl:param name="noopt"></xsl:param>
   
   <select name="{$name}-year">
    <xsl:if test="$noopt='yes'">
     <option value="">N/A</option>
    </xsl:if>
    <xsl:for-each select="/page/pulldowns[@type='date']/year">
      <xsl:element name="option">
       <xsl:if test="@key=floor($year)">
        <xsl:attribute name="selected"/>
       </xsl:if>
       <xsl:attribute name="value">
        <xsl:value-of select="@key"/>
       </xsl:attribute>
       <xsl:value-of select="@value"/>
      </xsl:element>
    </xsl:for-each>
   </select>
   <xsl:text>-</xsl:text>
   <select name="{$name}-month">
    <xsl:if test="$noopt='yes'">
     <option value="">N/A</option>
    </xsl:if>
    <xsl:for-each select="/page/pulldowns[@type='date']/month">
      <xsl:element name="option">
       <xsl:if test="@key=floor($month)">
        <xsl:attribute name="selected"/>
       </xsl:if>
       <xsl:attribute name="value">
        <xsl:value-of select="@key"/>
       </xsl:attribute>
       <xsl:value-of select="@value"/>
      </xsl:element>
    </xsl:for-each>
   </select>
   <xsl:text>-</xsl:text>
   <select name="{$name}-day">
    <xsl:if test="$noopt='yes'">
     <option value="">N/A</option>
    </xsl:if>
    <xsl:for-each select="/page/pulldowns[@type='date']/day">
      <xsl:element name="option">
       <xsl:if test="@key=floor($day)">
        <xsl:attribute name="selected"/>
       </xsl:if>
       <xsl:attribute name="value">
        <xsl:value-of select="@key"/>
       </xsl:attribute>
       <xsl:value-of select="@value"/>
      </xsl:element>
    </xsl:for-each>
   </select>

  </xsl:template>

  <xsl:template name="pulldown_time">
   <xsl:param name="name"></xsl:param>
   <xsl:param name="hour"></xsl:param>
   <xsl:param name="minute"></xsl:param>
   <xsl:param name="noopt"></xsl:param>
   
   <select name="{$name}-hour">
    <xsl:if test="$noopt='yes'">
     <option value="">N/A</option>
    </xsl:if>
    <xsl:for-each select="/page/pulldowns[@type='time']/hour">
      <xsl:element name="option">
       <xsl:if test="@key=$hour">
        <xsl:attribute name="selected"/>
       </xsl:if>
       <xsl:attribute name="value">
        <xsl:value-of select="@key"/>
       </xsl:attribute>
       <xsl:value-of select="@value"/>
      </xsl:element>
    </xsl:for-each>
   </select>
   <xsl:text>:</xsl:text>
   <select name="{$name}-minute">
    <xsl:if test="$noopt='yes'">
     <option value="">N/A</option>
    </xsl:if>
    <xsl:for-each select="/page/pulldowns[@type='time']/minute">
      <xsl:element name="option">
       <xsl:if test="@key=$minute">
        <xsl:attribute name="selected"/>
       </xsl:if>
       <xsl:attribute name="value">
        <xsl:value-of select="@key"/>
       </xsl:attribute>
       <xsl:value-of select="@value"/>
      </xsl:element>
    </xsl:for-each>
   </select>

  </xsl:template>

  <!--
    ** Bits for presenting exceptions, warnings and changes
    -->
  <xsl:template match="exception">
    <div class="exception">
      <p><xsl:apply-templates/></p>
    </div>
  </xsl:template>

  <xsl:template match="success">
   <div class="success">
    <xsl:apply-templates/>
   </div>
  </xsl:template>

  <xsl:template match="failure">
   <div class="failure">
    <xsl:apply-templates/>
   </div>
  </xsl:template>

  <xsl:template match="audit">
   <div class="audit">
    <xsl:apply-templates/>
   </div>
  </xsl:template>

  <!--
    ** SECTION: A nifty search results page navigator
    -->
  <xsl:template name="navbar">
   <xsl:param name="script"/>

   <form method="GET" action="{$script}">
    <table>
     <tr>
      <td>
      </td>
      <td>
       <xsl:if test="number(@page_num)&gt;1">
        <a href="{$script}?pagenum={@page_num - 1}"><img border="0" src="{/page/@baseuri}/images/left-arrow.png" alt="Prev" title="Previous page"/></a>
       </xsl:if>
      </td>
      <td>
       <div>Page <input type="text" size="5" name="pagenum" value="{@page_num}"/>/<xsl:value-of select="@page_max"/> <input type="submit" value="Jump"/></div>
      </td>
      <td>
       <xsl:if test="number(@page_num)&lt;number(@page_max)">
        <a href="{$script}?pagenum={@page_num + 1}"><img border="0" src="{/page/@baseuri}/images/right-arrow.png" alt="Next" title="Next page"/></a>
       </xsl:if>
      </td>
      <td>
       <p><xsl:value-of select="@num_results"/> results.</p>
      </td>
     </tr>
    </table>
   </form>
  </xsl:template>

</xsl:stylesheet>

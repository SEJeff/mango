<?xml version="1.0" encoding="UTF-8" ?>

<!-- $Id: forms.xsl,v 1.2 2003/12/15 06:56:45 rossg Exp $ -->

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

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

  <xsl:template match="audit">
   <div class="audit"><xsl:apply-templates/></div>
  </xsl:template>

  <!-- ================================ -->
  <!-- === ************************ === -->
  <!-- === *** SEARCH FUNCTIONS *** === -->
  <!-- === ************************ === -->
  <!-- ================================ -->

  <xsl:template match="searchfield">
   <option value="{@name}">
    <xsl:value-of select="@description"/>
   </option>
  </xsl:template>

  <xsl:template match="searchcriteria">
   <tr>
    <td>(<input type="checkbox" name="searchcriteria_delete_{@id}"/> remove)</td>
    <td>
     <xsl:apply-templates select="@description"/>
    </td>

    <xsl:choose>

     <xsl:when test="@type='text'">
      <td>
       <select name="searchcriteria_update_{@id}_comparison">
        <option value="=">
         <xsl:if test="@comparison='='">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>Equal to</xsl:text>
        </option>
        <option value="like">
         <xsl:if test="@comparison='like'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>Contains</xsl:text>
        </option>
        <option value="startswith">
         <xsl:if test="@comparison='startswith'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>Starts with</xsl:text>
        </option>
        <option value="endswith">
         <xsl:if test="@comparison='endswith'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>Ends with</xsl:text>
        </option>
       </select>
      </td>
      <td>
       <input name="searchcriteria_update_{@id}_value" value="{@value}"/>
      </td>
     </xsl:when>

     <xsl:when test="@type='boolean'">
      <td/>
      <td>
       <select name="searchcriteria_update_{@id}_value">
        <option value="Y">
         <xsl:if test="value='Y'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>Yes</xsl:text>
        </option>
        <option value="N">
         <xsl:if test="value='N'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>No</xsl:text>
        </option>
       </select>
      </td>
     </xsl:when>

     <xsl:when test="@type='pulldown'">
      <td/>
      <td>
       <select name="searchcriteria_update_{@id}_value">
	<xsl:for-each select="option">
	 <option>
          <xsl:attribute name="value">
           <xsl:value-of select="@value"/>
          </xsl:attribute>
          <xsl:if test="../@value=@value">
           <xsl:attribute name="selected"/>
          </xsl:if>
          <xsl:apply-templates/>
         </option>
        </xsl:for-each>
       </select>
      </td>
     </xsl:when>

     <xsl:when test="@type='date'">
      <td>
       <select name="searchcriteria_update_{@id}_comparison">
        <option value="notset">
         <xsl:if test="@comparison='notset'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>Not set</xsl:text>
        </option>
        <option value="after">
         <xsl:if test="@comparison='after'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>After</xsl:text>
        </option>
        <option value="on">
         <xsl:if test="@comparison='on'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>On</xsl:text>
        </option>
        <option value="before">
         <xsl:if test="@comparison='before'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>Before</xsl:text>
        </option>
       </select>
      </td>
      <td>
       <select name="searchcriteria_update_{@id}_year">
        <option value="">N/A</option>
        <xsl:for-each select="year">
         <xsl:element name="option">
          <xsl:if test="@key=floor(../@year)">
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
       <select name="searchcriteria_update_{@id}_month">
        <option value="">N/A</option>
        <xsl:for-each select="month">
         <xsl:element name="option">
          <xsl:if test="@key=floor(../@month)">
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
       <select name="searchcriteria_update_{@id}_day">
       <option value="">N/A</option>
        <xsl:for-each select="day">
         <xsl:element name="option">
          <xsl:if test="@key=floor(../@day)">
           <xsl:attribute name="selected"/>
          </xsl:if>
          <xsl:attribute name="value">
           <xsl:value-of select="@key"/>
          </xsl:attribute>
          <xsl:value-of select="@value"/>
         </xsl:element>
        </xsl:for-each>
       </select>
      </td>
     </xsl:when>

     <xsl:when test="@type='datetime'">
      <td>
       <select name="searchcriteria_update_{@id}_comparison">
        <option value="notset">
         <xsl:if test="@comparison='notset'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>Not set</xsl:text>
        </option>
        <option value="after">
         <xsl:if test="@comparison='after'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>After</xsl:text>
        </option>
        <option value="on">
         <xsl:if test="@comparison='on'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>On</xsl:text>
        </option>
        <option value="before">
         <xsl:if test="@comparison='before'">
          <xsl:attribute name="selected"/>
         </xsl:if>
         <xsl:text>Before</xsl:text>
        </option>
       </select>
      </td>
      <td>
       <select name="searchcriteria_update_{@id}_year">
        <option value="">N/A</option>
        <xsl:for-each select="year">
         <xsl:element name="option">
          <xsl:if test="@key=floor(../@year)">
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
       <select name="searchcriteria_update_{@id}_month">
        <option value="">N/A</option>
        <xsl:for-each select="month">
         <xsl:element name="option">
          <xsl:if test="@key=floor(../@month)">
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
       <select name="searchcriteria_update_{@id}_day">
       <option value="">N/A</option>
        <xsl:for-each select="day">
         <xsl:element name="option">
          <xsl:if test="@key=floor(../@day)">
           <xsl:attribute name="selected"/>
          </xsl:if>
          <xsl:attribute name="value">
           <xsl:value-of select="@key"/>
          </xsl:attribute>
          <xsl:value-of select="@value"/>
         </xsl:element>
        </xsl:for-each>
       </select>
       <xsl:text> </xsl:text>
       <select name="searchcriteria_update_{@id}_hour">
       <option value="">N/A</option>
        <xsl:for-each select="hour">
         <xsl:element name="option">
          <xsl:if test="@key=floor(../@hour)">
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
       <select name="searchcriteria_update_{@id}_minute">
       <option value="">N/A</option>
        <xsl:for-each select="minute">
         <xsl:element name="option">
          <xsl:if test="@key=floor(../@minute)">
           <xsl:attribute name="selected"/>
          </xsl:if>
          <xsl:attribute name="value">
           <xsl:value-of select="@key"/>
          </xsl:attribute>
          <xsl:value-of select="@value"/>
         </xsl:element>
        </xsl:for-each>
       </select>
      </td>
     </xsl:when>

    </xsl:choose>
    <td>
     <input type="submit" name="searchaction" value="Refresh"/>
    </td>
   </tr>
  </xsl:template>

  <xsl:template name="searchcriteriatable">
   <xsl:param name="script"/>

   <!-- criteria toolbar -->
   <form method="post" action="{$script}">
    <table class="form">

     <!-- Any criteria that have been added already -->
     <xsl:apply-templates select="searchcriteria"/>

     <!-- Facility to add a new search criteria from the available fields -->
     <tr>
      <th>New criteria</th>
      <td colspan="3">
       <select name="searchcriteria_new">
        <xsl:apply-templates select="searchfield"/>
       </select>
      </td>
      <td>
       <input type="submit" name="searchaction" value="Add"/>
      </td>
     </tr>

    </table>
   </form>
  </xsl:template>

  <xsl:template name="size_as_bytes">
   <xsl:param name="number"></xsl:param>
   <xsl:param name="display_gb">true</xsl:param>
   <xsl:param name="display_mb">true</xsl:param>
   <xsl:param name="display_kb">true</xsl:param>
   <xsl:param name="display_b">true</xsl:param>
   <xsl:variable name="gigabytes">
    <xsl:value-of select="floor($number div (1024 * 1024 * 1024))"/>
   </xsl:variable>
   <xsl:variable name="leftover1">
    <xsl:value-of select="$number - ($gigabytes * (1024 * 1024 * 1024))"/>
   </xsl:variable>
   <xsl:variable name="megabytes">
    <xsl:value-of select="floor($leftover1 div (1024 * 1024))"/>
   </xsl:variable>
   <xsl:variable name="leftover2">
    <xsl:value-of select="$leftover1 - ($megabytes * (1024 * 1024))"/>
   </xsl:variable>
   <xsl:variable name="kilobytes">
    <xsl:value-of select="floor($leftover2 div 1024)"/>
   </xsl:variable>
   <xsl:variable name="bytes">
    <xsl:value-of select="$leftover2 - ($kilobytes * 1024)"/>
   </xsl:variable>
   <xsl:if test="$display_gb='true'">
    <xsl:if test="$gigabytes &gt; 0">
     <xsl:value-of select="$gigabytes"/>GB
     <xsl:text> </xsl:text> 
    </xsl:if>
   </xsl:if>
   <xsl:if test="$display_mb='true'">
    <xsl:if test="$megabytes &gt; 0">
     <xsl:value-of select="$megabytes"/>MB
     <xsl:text> </xsl:text> 
    </xsl:if>
   </xsl:if>
   <xsl:if test="$display_kb='true'">
    <xsl:if test="$kilobytes &gt; 0">
     <xsl:value-of select="$kilobytes"/>KB
     <xsl:text> </xsl:text> 
    </xsl:if>
   </xsl:if>
   <xsl:if test="$display_b='true'">
    <xsl:value-of select="$bytes"/>B
   </xsl:if>
  </xsl:template>

  <xsl:template name="date_as_string">
   <xsl:param name="year"/>
   <xsl:param name="month"/>
   <xsl:param name="day"/>
   <xsl:value-of select="$day"/>
   <xsl:text>/</xsl:text>
   <xsl:value-of select="$month"/>
   <xsl:text>/</xsl:text>
   <xsl:value-of select="$year"/>
  </xsl:template>

  <xsl:template name="pulldown_value_for_option">
   <xsl:param name="type"/>
   <xsl:param name="key"/>
   <xsl:variable name="return">
    <xsl:value-of select="/page/pulldowns[@type=$type]/option[@key=$key]/@value"/>
   </xsl:variable>
   <xsl:choose>
    <xsl:when test="$return=''">
     <xsl:value-of select="$key"/>
    </xsl:when>
    <xsl:otherwise>
     <xsl:value-of select="$return"/>
    </xsl:otherwise>
   </xsl:choose>
  </xsl:template>

</xsl:stylesheet>

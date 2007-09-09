<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">accounts</xsl:param>

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'new_account.php'"/>

  <xsl:template match="newaccount">
    
  	<xsl:apply-templates select="formwarning">
  	Please fix the problems for the fields expressed in red color.
  	</xsl:apply-templates>
   <xsl:apply-templates select="error"/>
   <script language="javascript"> 
  	function ongnomesvnclick (node) { 
   		document.getElementById("gnomemodule_id").disabled = !node.checked;
   	}
   	function ontranslationsvnclick (node) {
   		document.getElementById("translationmodule_id").disabled = !node.checked;
   	}
   </script>
   <xsl:if test="boolean(alreadyadded)">
   <p>
   <ul>
    <li>Your request has already been received.</li>
   </ul>
   </p>
   </xsl:if>
   <form enctype="multipart/form-data" method="POST" action="{$script}" name="form">
    <input type="hidden" name="mango_token" value="{/page/@token}"/>
    <table class="form">
     <caption>Account request form</caption>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='uid'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       User Name
      </th>
      <td>
       <input type="text" name="uid" value="{uid}" size="40"/>
       <xsl:if test="boolean(formerror[@type='existing_uid'])">
        * This username already exists.
       </xsl:if>
      </td>
     </tr>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='cn'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       Full Name
      </th>
      <td>
       <input type="text" name="cn" value="{cn}" size="40"/>
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
       <xsl:if test="boolean(formerror[@type='comment'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       Why do you request this account
      </th>
      <td>
       <textarea name="comment" rows="5" cols="40"><xsl:value-of select="comment"/>
       	<xsl:value-of select="comment" />
       </textarea>
      </td>
     </tr>
     <tr>
      <th>
      <xsl:if test="boolean(formerror[@type='keys'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       SSH key(s)
      </th>
      <td>
       <div>Upload public key file (e.g. id_dsa.pub):</div>
       <input type="file" name="keyfile"/>
       <div>Or, cut'n'paste here:</div>
       <textarea name="newkeys" rows="5"><xsl:apply-templates select="newkeys"/><xsl:value-of select="authorizationkeys" /></textarea>
      </td>
     </tr>
     <tr>
      <th>
      <xsl:if test="boolean(formerror[@type='abilities'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
		Abilities needed
      </th>
      <td>
       <div>
        <input onclick="javascript: ongnomesvnclick(this);" type="checkbox" name="gnomesvn" id="gnomesvn_id">
         <xsl:if test="boolean(group[@cn='gnomemodule'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="gnomesvn">I need subversion access as coder.</label>
       </div>
       <div><label for="gnomemodule">Work on which module do you need svn account for:</label>
       	 	<select name="gnomemodule" id="gnomemodule_id">
       	 	<xsl:if test="boolean(disabled[@input='gnome'])">
       	 		<xsl:attribute name="disabled" />
       	 	</xsl:if>
      		<xsl:for-each select="gnomemodule">
      			<xsl:element name="option">
      			<xsl:if test="boolean(selected)">
      				<xsl:attribute name="selected"/>
      			</xsl:if>
      			<xsl:attribute name="value">
      				<xsl:value-of select="key" />
      			</xsl:attribute>
      			<xsl:value-of select="value" />
      			</xsl:element>
      		</xsl:for-each>
      	</select>
      </div>
      <div>
       <input onclick="javascript: ontranslationsvnclick(this);" type="checkbox" name="translationsvn" id="translationsvn_id">
         <xsl:if test="boolean(group[@cn='translation'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="translationsvn">I need subversion access as translator.</label>
       </div>
       <div><label for="translation">Which language team are you in:</label>
       	 	<select name="translation" id="translationmodule_id">
       	 	<xsl:if test="boolean(disabled[@input='translation'])">
       	 		<xsl:attribute name="disabled" />
       	 	</xsl:if>
      		<xsl:for-each select="translation">
      			<xsl:element name="option">
      			<xsl:if test="boolean(selected)">
      				<xsl:attribute name="selected"/>
      			</xsl:if>
      			<xsl:attribute name="value">
      				<xsl:value-of select="key" />
      			</xsl:attribute>
      			<xsl:value-of select="value" />
      			</xsl:element>
      		</xsl:for-each>
      	</select>
      </div>
       <div>
       <input type="checkbox" name="ftp_access">
         <xsl:if test="boolean(group[@cn='ftp_access'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="ftp_access">I need to upload stuff to FTP.</label> 
       </div>
       <div>
        <input type="checkbox" name="web_access">
         <xsl:if test="boolean(group[@cn='web_access'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="gnomeweb">I need to manage web content.</label>
       </div>
       <div>
        <input type="checkbox" name="bugzilla_access">
         <xsl:if test="boolean(group[@cn='bugzilla_access'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="bugzilla">I need to manage bugzilla.</label>
       </div>
       <div>
        <input type="checkbox" name="membctte">
         <xsl:if test="boolean(group[@cn='membctte'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="membctte">I need to manage GNOME Foundation membership database.</label>
       </div>
       <div>
        <input type="checkbox" name="art_access">
         <xsl:if test="boolean(group[@cn='art_access'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="artweb">I need to manage web graphics content.</label>
       </div>
       <div>
        <input type="checkbox" name="mail_alias">
         <xsl:if test="boolean(group[@cn='mail_alias'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="gnomealias">I need a 'gnome.org' mail alias. (Only for Foundation Members)</label>
       </div>
      </td>
     </tr>
    </table>
    <p>
     <input type="submit" name="request" value="Request Account &gt;&gt;"/>
    </p>
   </form>
  </xsl:template>

  <xsl:template match="account_added">
  	<p>
  		Your account request has been received. Please check your e-mail, and visit the link posted to you so as to verify your e-mail address. Note that, no further action will be handled before your validating your e-mail address. Once you validate your e-mail address, responsible maintainer for the abilities you've requested will process your application. 
  </p>
  <p>For any further questions, please contact <a href="mailto:support@gnome.org">support@gnome.org</a>.</p>
  </xsl:template>
  
  <xsl:template match="authorizedKey">
   <xsl:apply-templates/>&#x0d;
  </xsl:template>   
</xsl:stylesheet>

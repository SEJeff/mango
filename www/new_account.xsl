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
   		document.getElementById("gnomemodule").disabled = !node.checked;
   	}
   	function ontranslationsvnclick (node) {
   		document.getElementById("translationmodule").disabled = !node.checked;
   	}
   </script>
   <xsl:if test="boolean(alreadyadded)">
   <p>
   <ul>
    <li>Your request has already been received.</li>
   </ul>
   </p>
   </xsl:if>
   <xsl:if test="boolean(account_added)">
	<p>Your account request has been received. Please check your e-mail,
	  and visit the link posted to you so as to verify your e-mail address.
	  Note that, no further action will be handled before your validating
	  your e-mail address. Once you validate your e-mail address,
	  responsible maintainer will process your application.</p>
	<p>For any further questions, please contact <a
	    href="mailto:support@gnome.org">support@gnome.org</a>.</p>
  </xsl:if>
  <xsl:if test="not(boolean(alreadyadded)) and not(boolean(account_added))">
    <p>Note: Please read the following page first: <a href="http://live.gnome.org/NewAccounts">http://live.gnome.org/NewAccounts</a>.</p>
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
       <input type="text" name="uid" value="{uid}" size="15"/>
       <xsl:if test="boolean(formerror[@type='existing_uid'])">
        * This username is taken.
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
       <xsl:if test="boolean(formerror[@type='existing_email'])">
        * This email address has already been used.
       </xsl:if>
      </td>
     </tr>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='comment'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       Comments
      </th>
      <td>
       <textarea name="comment" rows="5" cols="70">
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
       <textarea name="newkeys" rows="3" cols="70"><xsl:apply-templates select="newkeys"/><xsl:value-of select="authorizationkeys" /></textarea>
      </td>
     </tr>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='abilities'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       Abilities
      </th>
      <td>
       <div>
        <input onclick="javascript: ongnomesvnclick(this);" type="checkbox" name="gnomesvn" id="gnomesvn">
         <xsl:if test="boolean(group[@cn='gnomemodule'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="gnomesvn">Developer access to Subversion</label>
       </div>
       <div>&#160;&#160;&#160;&#160;&#160;&#160;<label for="gnomemodule">GNOME module: </label>
       	 	<select name="gnomemodule" id="gnomemodule">
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
      <xml:if test="boolean(translation)">
      <div>
       <input onclick="javascript: ontranslationsvnclick(this);" type="checkbox" name="translationsvn" id="translationsvn">
         <xsl:if test="boolean(group[@cn='translation'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
	<label for="translationsvn">Translator access to Subversion</label><br />
       </div>
       <div>&#160;&#160;&#160;&#160;&#160;&#160;<label for="translation">Translation Team: </label>
       	 	<select name="translation" id="translationmodule">
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
    </div></xml:if>
       <div>
       <input type="checkbox" name="ftp_access" id="ftp_access">
         <xsl:if test="boolean(group[@cn='ftp_access'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="ftp_access">Install new modules on ftp.gnome.org</label> 
       </div>
       <div>
        <input type="checkbox" name="mail_alias" id="mail_alias">
         <xsl:if test="boolean(group[@cn='mail_alias'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="mail_alias">'gnome.org' email alias. (Only for Foundation Members)</label>
       </div>
       <p>Special abilities:
       <div>
        <input type="checkbox" name="web_access" id="web_access">
         <xsl:if test="boolean(group[@cn='web_access'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="web_access">Shell access for GNOME websites</label>
       </div>
       <div>
        <input type="checkbox" name="bugzilla_access" id="bugzilla_access">
         <xsl:if test="boolean(group[@cn='bugzilla_access'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="bugzilla_access">Shell access for GNOME Bugzilla</label>
       </div>
       <div>
        <input type="checkbox" name="art_access" id="art_access">
         <xsl:if test="boolean(group[@cn='art_access'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="art_access">Shell access for GNOME art website</label>
       </div>
       <div>
        <input type="checkbox" name="membctte" id="membctte">
         <xsl:if test="boolean(group[@cn='membctte'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="membctte">GNOME Foundation membership committee</label>
       </div>
       </p>
      </td>
     </tr>
    </table>
    <p>
     <input type="submit" name="request" value="Request Account &gt;&gt;"/>
    </p>
  </form>
  </xsl:if>
  </xsl:template>
  <xsl:template match="authorizedKey">
   <xsl:apply-templates/>&#x0d;
  </xsl:template>   
</xsl:stylesheet>

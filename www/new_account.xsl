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
  	function on_ability_click () { 
	    var new_state = document.getElementById("gnomecvs").checked
                            || document.getElementById("ftpadmin").checked;

   	    document.getElementById("vouch_dev").disabled = !new_state;
	    document.getElementById("vouch_i18n").disabled = !new_state;
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
	<p>Your account request has been received. The next step in your
	  application is to verify your email address. Please check your e-mail
	  and visit the link posted to you. Note that your application cannot
	  be seen until you have verified your email address.<br />
	  To ensure a speedy response, please check your email right away.</p>
	<p>For any further questions, please contact <a
	    href="mailto:{/page/@support}"><xsl:value-of select='/page/@support'/></a>.</p>
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
       <xsl:if test="boolean(formerror[@type='mail'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       E-mail
      </th>
      <td>
       <input type="text" name="mail" value="{mail}" size="40"/>
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
       <div>Upload public key file (e.g. id_rsa.pub):</div>
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
        <input onclick="on_ability_click()" type="checkbox" name="gnomecvs" id="gnomecvs">
         <xsl:if test="boolean(group[@cn='gnomecvs'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="gnomecvs">Access to Subversion</label>
       </div>
       <div>
       <input onclick="on_ability_click()" type="checkbox" name="ftpadmin" id="ftpadmin">
         <xsl:if test="boolean(group[@cn='ftpadmin'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="ftpadmin">Install new modules on ftp.gnome.org</label> 
       </div>
       <div>
        <input type="checkbox" name="mailusers" id="mailusers">
         <xsl:if test="boolean(group[@cn='mailusers'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="mailusers">'gnome.org' email alias. (Only for Foundation Members)</label>
       </div>
       <p>Special abilities:
       <div>
        <input type="checkbox" name="gnomeweb" id="gnomeweb">
         <xsl:if test="boolean(group[@cn='gnomeweb'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="gnomeweb">Shell access for GNOME websites</label>
       </div>
       <div>
        <input type="checkbox" name="bugzilla" id="bugzilla">
         <xsl:if test="boolean(group[@cn='bugzilla'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="bugzilla">Shell access for GNOME Bugzilla</label>
       </div>
       <div>
        <input type="checkbox" name="artweb" id="artweb">
         <xsl:if test="boolean(group[@cn='artweb'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="artweb">Shell access for GNOME art website</label>
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
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='vouchers'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       Vouchers
      </th>
      <td>
       For GNOME SVN and the ability to install new modules, 
       please select who can vouch for you:
       <div><label for="vouch_dev">GNOME module: </label>
       	 	<select name="vouch_dev" id="vouch_dev">
       	 	<xsl:if test="count(gnomemodule) = 0 or not(boolean(group[@cn='gnomecvs']))">
       	 		<xsl:attribute name="disabled" />
       	 	</xsl:if>
		<option value=''>None</option>
      		<xsl:for-each select="gnomemodule">
			<xsl:sort select="@cn"/>
      			<xsl:element name="option">
      			<xsl:if test="boolean(@selected)">
      				<xsl:attribute name="selected"/>
      			</xsl:if>
      			<xsl:attribute name="value">
      				<xsl:value-of select="@cn" />
      			</xsl:attribute>
      			<xsl:value-of select="@cn" />
      			</xsl:element>
      		</xsl:for-each>
      	</select>
       </div>
       <div><label for="vouch_i18n">Translation Team: </label>
       	 	<select name="vouch_i18n" id="vouch_i18n">
       	 	<xsl:if test="count(translation) = 0 or not(boolean(group[cn='gnomecvs']))">
       	 		<xsl:attribute name="disabled" />
       	 	</xsl:if>
		<option value=''>None</option>
		<xsl:for-each select="translation">
			<xsl:sort select="@cn"/>
      			<xsl:element name="option">
      			<xsl:if test="boolean(@selected)">
      				<xsl:attribute name="selected"/>
      			</xsl:if>
      			<xsl:attribute name="value">
      				<xsl:value-of select="@cn" />
      			</xsl:attribute>
      			<xsl:value-of select="@desc" />
      			</xsl:element>
      		</xsl:for-each>
		</select>
        </div>
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

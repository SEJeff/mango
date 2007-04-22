<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'update_user.php'"/>

  <xsl:template name="breadcrumb">
   · <a href="/list_users.php">Users</a>
  </xsl:template>
  
  <xsl:template match="updateuser">
   <xsl:apply-templates select="error"/>
   <xsl:if test="boolean(updated)">
    <p>User updated.</p>
   </xsl:if>
   <xsl:if test="boolean(.//change)">
    <xsl:apply-templates select=".//change"/>
   </xsl:if>
   <table class="formtabs">
    <tr>
     <td>
      <xsl:if test="@tab='general'">
       <xsl:attribute name="class">selected</xsl:attribute>
      </xsl:if>
      <a href="{$script}?tab=general">General</a>
     </td>
     <td>
      <xsl:if test="@tab='sshkeys'">
       <xsl:attribute name="class">selected</xsl:attribute>
      </xsl:if>
      <a href="{$script}?tab=sshkeys">SSH Keys</a>
     </td>
     <td>
      <xsl:if test="@tab='groups'">
       <xsl:attribute name="class">selected</xsl:attribute>
      </xsl:if>
      <a href="{$script}?tab=groups">Groups</a>
     </td>
     <td>
      <xsl:if test="@tab='actions'">
       <xsl:attribute name="class">selected</xsl:attribute>
      </xsl:if>
      <a href="{$script}?tab=actions">Actions</a>
     </td>
    </tr>
   </table>
   <form enctype="multipart/form-data" method="POST" action="{$script}" name="form">
    <input type="hidden" name="uidcheck" value="{uid}"/>
    <xsl:choose>
     <xsl:when test="@tab='general'">
      <xsl:call-template name="updateusergeneraltab"/>
     </xsl:when>
     <xsl:when test="@tab='sshkeys'">
      <xsl:call-template name="updateusersshkeystab"/>
     </xsl:when>
     <xsl:when test="@tab='groups'">
      <xsl:call-template name="updateusergroupstab"/>
     </xsl:when>
     <xsl:when test="@tab='actions'">
      <xsl:call-template name="updateuseractionstab"/>
     </xsl:when>
     <xsl:otherwise>
      <p class="error">Unknown tab '<xsl:value-of select="@tab"/>'.</p>
     </xsl:otherwise>
    </xsl:choose>
    <p>
     <input type="submit" name="update" value="Update &gt;&gt;"/>
    </p>
   </form>
  </xsl:template>

  <xsl:template name="updateusergeneraltab">
    <table class="form">
     <caption>Update user '<xsl:value-of select="uid"/>'</caption>
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
    </table>
  </xsl:template>

  <xsl:template name="updateusersshkeystab">
    <table class="form">
     <caption>Update user '<xsl:value-of select="uid"/>'</caption>
     <tr>
      <th>
       SSH key(s)
      </th>
      <td>
       <xsl:for-each select="savedkeys/key">
        <div>
         <input type="checkbox" name="authorizedKey-{@ref}" value="{.}" checked="true"/>
         <span> <xsl:value-of select="concat(substring(., 0, 20), '...', substring(., string-length(.) - 40))"/></span>
        </div>
       </xsl:for-each>
       <div>Upload additional public keys in a file (e.g. id_dsa.pub):</div>
       <input type="file" name="keyfile"/>
       <div>Or, cut'n'paste here:</div>
       <textarea name="newkeys" rows="5"><xsl:apply-templates select="newkeys"/></textarea>
      </td>
     </tr>
    </table>
  </xsl:template>

  <xsl:template name="updateusergroupstab">
    <table class="form">
     <caption>Update user '<xsl:value-of select="uid"/>'</caption>
     <tr>
      <th>
       Groups/options
      </th>
      <td>
       <div>
        <input type="checkbox" name="group-gnomecvs">
         <xsl:if test="boolean(group[@cn='gnomecvs'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <span> SVN account</span>
       </div>
       <div>
        <input type="checkbox" name="group-ftpadmin">
         <xsl:if test="boolean(group[@cn='ftpadmin'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <span> FTP upload</span>
       </div>
       <div>
        <input type="checkbox" name="group-gnomeweb">
         <xsl:if test="boolean(group[@cn='gnomeweb'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <span> Web admin</span>
       </div>
       <div>
        <input type="checkbox" name="group-bugzilla">
         <xsl:if test="boolean(group[@cn='bugzilla'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <span> Bugzilla dude(/dudess)</span>
       </div>
       <div>
        <input type="checkbox" name="group-accounts">
         <xsl:if test="boolean(group[@cn='accounts'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <span> Accounts team dude(/dudess)</span>
       </div>
       <div>
        <input type="checkbox" name="group-membctte">
         <xsl:if test="boolean(group[@cn='membctte'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <span> Membership committee dude(/dudess)</span>
       </div>
       <div>
        <input type="checkbox" name="group-sysadmin">
         <xsl:if test="boolean(group[@cn='sysadmin'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <span> Sysadmin team dude(/dudess)</span>
       </div>
       <div>
        <input type="checkbox" name="group-artweb">
         <xsl:if test="boolean(group[@cn='artweb'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <span> Artweb admin</span>
       </div>
       <div>
        <input type="checkbox" name="group-mailusers">
         <xsl:if test="boolean(group[@cn='mailusers'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <span> Has a cool 'gnome.org' mail alias</span>
       </div>
      </td>
     </tr>
    </table>
  </xsl:template>

  <xsl:template name="updateuseractionstab">
    <xsl:choose>
     <xsl:when test="boolean(authorisemail)">
      <xsl:call-template name="authorisemail"/>
     </xsl:when>
     <xsl:when test="boolean(emailsent)">
      <xsl:call-template name="emailsent"/>
     </xsl:when>
     <xsl:otherwise>
      <xsl:call-template name="whichaction"/>
     </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="whichaction">
    <table class="form whichaction">
     <tr>
      <th colspan="2">RT3 number</th>
      <td>
       <input type="text" name="rt_number" value="{rt_number}"/>
      </td>
     </tr>
     <tr>
      <td>
       <p>Select an action to perform:</p>
       <ul>
        <li>
         <input type="submit" name="sendauthtoken" value="Send authentication token &gt; &gt;"/>
        </li>
        <li>
         <input type="submit" name="sendwelcome" value="Send welcome (SVN account) &gt; &gt;"/>
        </li>
        <li>
         <input type="submit" name="sendwelcomeshell" value="Send welcome (shell account) &gt; &gt;"/>
        </li>
        <li>
         <input type="submit" name="sendwelcomealias" value="Send welcome (email alias) &gt; &gt;"/>
        </li>
       </ul>
      </td>
     </tr>
    </table>
  </xsl:template>

  <xsl:template name="authorisemail">
    <input type="hidden" name="confirmemail" value="yes"/>
    <table class="form">
     <tr>
      <td colspan="2">
       <p>Please confirm the details of the e-mail to send:</p>
      </td>
     </tr>
     <tr>
      <td>To</td>
      <td>
       <input type="text" name="to" value="{to}" size="40"/>
      </td>
     </tr>
     <tr>
      <td>Cc</td>
      <td>
       <input type="text" name="cc" value="{cc}" size="40"/>
      </td>
     </tr>
     <tr>
      <td>Subject</td>
      <td>
       <input type="text" name="subject" value="{subject}" size="40"/>
      </td>
     </tr>
     <tr>
      <td colspan="2">
       <textarea name="body" cols="80" rows="20"><xsl:value-of select="body"/></textarea>
      </td>
     </tr>
    </table>
  </xsl:template>

  <xsl:template name="emailsent">
    <table class="form">
     <tr>
      <td colspan="2">
       <p>E-mail sent.</p>
      </td>
     </tr>
    </table>
  </xsl:template>

  <xsl:template match="authorizedKey">
   <xsl:apply-templates/>&#x0d;
  </xsl:template>
  
  <xsl:template match="change">
   <xsl:choose>
    <xsl:when test="@id='cn'">
     <p>Name changed</p>
    </xsl:when>
    <xsl:when test="@id='mail'">
     <p>E-mail address updated</p>
    </xsl:when>
    <xsl:when test="@id='description'">
     <p>Description updated</p>
    </xsl:when>
    <xsl:when test="@id='keysremoved'">
     <p>SSH keys removed</p>
    </xsl:when>
    <xsl:when test="@id='keysadded'">
     <p>SSH keys added</p>
    </xsl:when>
    <xsl:when test="@id='authtokensent'">
     <p>Authentication token sent</p>
    </xsl:when>
    <xsl:when test="@id='welcomesent'">
     <p>Welcome message sent</p>
    </xsl:when>
    <xsl:otherwise>
     <p>Change '<xsl:value-of select="@id"/>'</p>
    </xsl:otherwise>
   </xsl:choose>
  </xsl:template>
  
</xsl:stylesheet>

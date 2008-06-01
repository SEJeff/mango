<?xml version="1.0" encoding="UTF-8" ?>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:param name="libgo.channel">users</xsl:param>
    
  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'new_user.php'"/>
  
  <xsl:template match="newuser">
   <xsl:apply-templates select="error"/>
   <xsl:if test="boolean(added)">
    <p>User '<a href="update_user.php?uid={added/uid}"><xsl:value-of select="added/uid"/></a>' created.</p>
   </xsl:if>
   <form enctype="multipart/form-data" method="POST" action="{$script}" name="form">
    <input type="hidden" name="mango_token" value="{/page/@token}"/>
    <table class="form">
     <caption>New user form</caption>
     <tr>
      <th>
       <xsl:if test="boolean(formerror[@type='uid'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       UID
      </th>
      <td>
       <input type="text" name="uid" value="{uid}" size="40"/>
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
       <xsl:if test="boolean(formerror[@type='keys'])">
        <xsl:attribute name="class">formerror</xsl:attribute>
       </xsl:if>
       SSH key(s)
      </th>
      <td>
       <xsl:for-each select="savedkeys/key">
        <div>
         <input type="checkbox" name="authorizedKey-{@ref}" value="{.}" checked="true"/>
         <span> <xsl:value-of select="concat(substring(., 0, 20), '...', substring(., string-length(.) - 40))"/></span>
        </div>
       </xsl:for-each>
       <div>Upload public key file (e.g. id_rsa.pub):</div>
       <input type="file" name="keyfile"/>
       <div>Or, cut'n'paste here:</div>
       <textarea name="newkeys" rows="5"><xsl:apply-templates select="newkeys"/></textarea>
      </td>
     </tr>
     <tr>
      <th>
       Groups/options
      </th>
      <td>
       <div>
        <input type="checkbox" name="group-gnomecvs" id="group-gnomecvs">
         <xsl:if test="boolean(group[@cn='gnomecvs'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="group-gnomecvs">SVN account</label>
       </div>
       <div>
        <input type="checkbox" name="group-ftpadmin" id="group-ftpadmin">
         <xsl:if test="boolean(group[@cn='ftpadmin'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="group-ftpadmin">FTP upload</label>
       </div>
       <div>
        <input type="checkbox" name="group-gnomeweb" id="group-gnomeweb">
         <xsl:if test="boolean(group[@cn='gnomeweb'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="group-gnomeweb">Web admin</label>
       </div>
       <div>
        <input type="checkbox" name="group-bugzilla" id="group-bugzilla">
         <xsl:if test="boolean(group[@cn='bugzilla'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="group-bugzilla">Bugzilla dude(/dudess)</label>
       </div>
       <div>
        <input type="checkbox" name="group-accounts" id="group-accounts">
         <xsl:if test="boolean(group[@cn='accounts'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="group-accounts">Accounts team dude(/dudess)</label>
       </div>
       <div>
        <input type="checkbox" name="group-membctte" id="group-membctte">
         <xsl:if test="boolean(group[@cn='membctte'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="group-membctte">Membership committee dude(/dudess)</label>
       </div>
       <div>
        <input type="checkbox" name="group-sysadmin" id="group-sysadmin">
         <xsl:if test="boolean(group[@cn='sysadmin'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="group-sysadmin">Sysadmin team dude(/dudess)</label>
       </div>
       <div>
        <input type="checkbox" name="group-buildmaster" id="group-buildmaster">
         <xsl:if test="boolean(group[@cn='buildmaster'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="group-buildmaster">Build master account</label>
       </div>
       <div>
        <input type="checkbox" name="group-buildslave" id="group-buildslave">
         <xsl:if test="boolean(group[@cn='buildslave'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
	<label for="group-buildslave">Build slave account</label>
       </div>
       <div>
        <input type="checkbox" name="group-artweb" id="group-artweb">
         <xsl:if test="boolean(group[@cn='artweb'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="group-artweb">Artweb admin</label>
       </div>
       <div>
        <input type="checkbox" name="group-mailusers" id="group-mailusers">
         <xsl:if test="boolean(group[@cn='mailusers'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <label for="group-mailusers">Has a cool 'gnome.org' mail alias</label>
       </div>
      </td>
     </tr>
    </table>
    <p>
     <input type="submit" name="add" value="Add User &gt;&gt;"/>
    </p>
   </form>
  </xsl:template>

  <xsl:template match="authorizedKey">
   <xsl:apply-templates/>&#x0d;
  </xsl:template>   
</xsl:stylesheet>

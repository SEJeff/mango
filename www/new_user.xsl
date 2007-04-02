<?xml version="1.0" encoding="UTF-8" ?>

<!DOCTYPE html [
 <!ENTITY middot "&#183;">
]>

<xsl:stylesheet version="1.1"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="common.xsl" />

  <xsl:variable name="script" select="'new_user.php'"/>
  
  <xsl:template name="breadcrumb">
   &middot; <a href="list_users.php">Users</a>
  </xsl:template>
    
  <xsl:template match="newuser">
   <xsl:apply-templates select="error"/>
   <xsl:if test="boolean(added)">
    <p>User '<a href="update_user.php?uid={added/uid}"><xsl:value-of select="added/uid"/></a>' created.</p>
   </xsl:if>
   <form enctype="multipart/form-data" method="POST" action="{$script}" name="form">
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
       SSH key(s)
      </th>
      <td>
       <xsl:for-each select="savedkeys/key">
        <div>
         <input type="checkbox" name="authorizedKey-{@ref}" value="{.}" checked="true"/>
         <span> <xsl:value-of select="concat(substring(., 0, 20), '...', substring(., string-length(.) - 40))"/></span>
        </div>
       </xsl:for-each>
       <div>Upload public key file (e.g. id_dsa.pub):</div>
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
        <input type="checkbox" name="group-gnomecvs">
         <xsl:if test="boolean(group[@cn='gnomecvs'])">
          <xsl:attribute name="checked"/>
         </xsl:if>
        </input>
        <span> CVS account</span>
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
    <p>
     <input type="submit" name="add" value="Add User &gt;&gt;"/>
    </p>
   </form>
  </xsl:template>

  <xsl:template match="authorizedKey">
   <xsl:apply-templates/>&#x0d;
  </xsl:template>   
</xsl:stylesheet>

from django.db import models
from django.conf import settings
from django.forms import ValidationError

# LDAP orm with a django-like interface
import ldapdb.models

# ldap versions of: IntegerField, CharField, etc
from ldapdb.models.fields import *

LOGIN_SHELLS = (
    ('/bin/bash',     '/bin/bash'),
    ('/bin/sh',       '/bin/sh'),
    # Yes, we have users who prefer tcsh
    ('/bin/tcsh',     '/bin/tcsh'),
    ('/sbin/nologin', '/sbin/nologin'),
)

class LdapUser(ldapdb.models.Model):
    """
    Class for representing an LDAP user entry.
    """
    # LDAP meta-data
    base_dn = settings.LDAPDB_USER_DN
    object_classes = ['posixAccount', 'inetOrgPerson', 'pubkeyAuthenticationUser']

    # inetOrgPerson ldap objectClass
    full_name = CharField(db_column='cn')
    first_name = CharField(db_column='givenName')
    last_name = CharField(db_column='sn')
    email = CharField(db_column='mail', blank=True)

    # posixAccount ldap objectClass
    uid = IntegerField(db_column='uidNumber', unique=True)
    gid = IntegerField(db_column='gidNumber', unique=False)

    username       = CharField(db_column='uid', unique=True, primary_key=True, editable=True)
    login_shell    = CharField(db_column='loginShell', choices=LOGIN_SHELLS, default="/bin/bash")
    description    = CharField(db_column='description')
    home_directory = CharField(db_column='homeDirectory')
    password       = CharField(db_column='userPassword', max_length=100)
    keys = ListField(db_column="authorizedKey")

    def add_ssh_key(self, key):
        """
        Check the key length and type are sufficient before adding it all
        """
        # if is_good(key):
        #    self.keys.append(key.rstrip())
        pass

    # TODO: Convert this to SHA256 if our ldap server supports it
    def set_password(self, password, rand=False):
        """Change a user's password"""
        # Shamelessly stolen from somewhere inside django's core
        try:
            import hashlib
            md5_constructor = hashlib.md5
        except ImportError:
            import md5
            md5_constructor = md5.new

        m = md5_constructor()
        m.update(password)
        hashed = "{MD5}" + base64.b64encode(m.digest())
        self.password = hashed

    def __unicode__(self):
        return self.full_name

class LdapGroup(ldapdb.models.Model):
    """
    Class for representing an LDAP group entry.
    """

    # LDAP meta-data
    base_dn = settings.LDAPDB_GROUP_DN
    object_classes = ['posixGroup']

    # posixGroup attributes
    gid = IntegerField(db_column='gidNumber', unique=True)
    name = CharField(db_column='cn', max_length=200, primary_key=True)
    members = ListField(db_column="memberUid")

    ## TODO: Make each member in "members" have an associated LdapUser objects of freak out and throw a ValueError
    #def save(self, *args, **kwargs):
    #    super(LdapGroup, self).save(*args, **kwargs)

    def __unicode__(self):
        return self.name

import os
import base64
from random import randrange
from django.db import models
from django.conf import settings
from django.forms import ValidationError, Textarea
from django.core.urlresolvers import reverse

# LDAP orm with a django-like interface
import ldapdb.models

# ldap versions of: IntegerField, CharField, etc
from ldapdb.models.fields import *

# Import the right module for sha depending on the python version
try:
    import hashlib
    sha_constructor = hashlib.sha1
except ImportError:
    import sha
    sha_constructor = sha.new

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
    def _groups(self):
        return LdapGroup.objects.filter(members__contains=self.username)

    # LDAP meta-data
    base_dn = settings.LDAPDB_USER_DN
    object_classes = ['posixAccount', 'inetOrgPerson', 'pubkeyAuthenticationUser']
    # Convenience property for working with user management
    groups = property(_groups)

    # inetOrgPerson ldap objectClass
    full_name = CharField(db_column='cn')
    first_name = CharField(db_column='givenName', blank=True)
    last_name = CharField(db_column='sn', blank=True)
    email = CharField(db_column='mail')

    # posixAccount ldap objectClass
    uid = IntegerField(db_column='uidNumber', unique=True)
    gid = IntegerField(db_column='gidNumber', unique=False)

    username       = CharField(db_column='uid', unique=True, primary_key=True, editable=True)
    login_shell    = CharField(db_column='loginShell', choices=LOGIN_SHELLS, default="/bin/bash")
    description    = CharField(db_column='description', blank=True)
    home_directory = CharField(db_column='homeDirectory', blank=True)
    password       = CharField(db_column='userPassword', max_length=100)
    keys           = ListField(db_column="authorizedKey")
    class Meta:
        ordering = ('full_name',)
        verbose_name = 'user'
        verbose_name_plural = 'users'

    def save(self):
        # Add the home directory if one doesn't exist already
        if not self.home_directory:
            self.home_directory = os.path.join(settings.MANGO_USER_HOMEDIR_BASE, self.username)
        super(LdapUser, self).save()

    def add_ssh_key(self, key):
        """
        Check the key length and type are sufficient before adding it all
        """
        # if is_good(key):
        #    self.keys.append(key.rstrip())
        pass

    def set_password(self, password):
        """Change a user's password"""
        hashed = self.hash_password(password)
        self.password = hashed

    def hash_ssha(password):
        """
        Copied from: http://git.gnome.org/browse/sysadmin-bin/tree/handle-ldap-modules#n95
        """
        salt = ''.join([chr(randrange(0,255)) for i in range(4)])
        ctx = sha_constructor(password  + salt)
        hash = "{SSHA}" + base64.b64encode(ctx.digest() + salt)
        return hash

    def __unicode__(self):
        return self.full_name

    def get_absolute_url(self):
        return reverse("users-update", args=(self.username,))

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

    class Meta:
        ordering = ('name',)
        verbose_name = 'ldap group'
        verbose_name_plural = 'ldap groups'

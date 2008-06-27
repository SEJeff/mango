# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#     * Rearrange models' order
#     * Make sure each model has one field with primary_key=True
# Feel free to rename the models, but don't rename db_table values or field names.
#
# Also note: You'll have to insert the output of 'django-admin.py sqlcustom [appname]'
# into your database.

from django.db import models
from django.conf import settings
from django.core import validators
from django.newforms import ModelForm
from django.utils import tree
from django.db.models import Q
import ldap
import ldap.filter

import datetime

class AccountRequest(models.Model):
    id = models.AutoField(primary_key=True)
    uid = models.CharField(max_length=15)
    cn = models.CharField(max_length=255)
    mail = models.EmailField(max_length=255)
    comment = models.TextField()
    timestamp = models.DateTimeField(editable=False)
    authorizationkeys = models.TextField()
    status = models.CharField(max_length=1, default='P', editable=False)
    is_new_account = models.CharField(max_length=1, default='Y', editable=False)
    is_mail_verified = models.CharField(max_length=1, default='N', editable=False)
    mail_token = models.CharField(max_length=40, editable=False)
    class Meta:
        db_table = u'account_request'

class AccountsForm(ModelForm):
    class Meta:
        model = AccountRequest

class AccountGroups(models.Model):
    id = models.AutoField(primary_key=True)
    request = models.ForeignKey(AccountRequest)
    cn = models.CharField(max_length=15,validator_list=[validators.isOnlyLetters,
                                                        validators.isLowerCase])
    voucher_group = models.CharField(max_length=50, blank=True, null=True)
    verdict = models.CharField(max_length=1, default='P', editable=False)
    voucher = models.CharField(max_length=15, blank=True, null=True)
    denial_message = models.CharField(max_length=255, blank=True, null=True)
    class Meta:
        db_table = u'account_groups'

class Foundationmembers(models.Model):
    id = models.AutoField(primary_key=True)
    firstname = models.CharField(max_length=50)
    lastname = models.CharField(max_length=50)
    email = models.EmailField(max_length=255)
    comments = models.TextField(blank=True)
    userid = models.CharField(max_length=15, null=True, blank=True)
    first_added = models.DateField(auto_now_add=True)
    last_renewed_on = models.DateField(null=True, blank=True, editable=False)
    last_update = models.DateTimeField(auto_now=True)
    resigned_on = models.DateField(null=True, blank=True)

    @property
    def is_member(self):
        return (self.resigned_on is None)

    @property
    def need_to_renew(self):
        diff = datetime.date.today() - self.last_renewed_on
        return diff.days >= 700

    class Meta:
        db_table = u'foundationmembers'
        ordering = ['lastname', 'firstname']

class FoundationmembersForm(ModelForm):
    class Meta:
        model = Foundationmembers

LOCATION_CHOICES = (
    ('United States and Canada', 'United States and Canada'),
    ('Australia', 'Australia'),
    ('Europe', 'Europe'),
    ('Asia', 'Asia'),
    ('South America', 'South America'),
    ('Other', 'Other'),
)

class Ftpmirrors(models.Model):
    id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=60)
    url = models.URLField(verify_exists=False)
    location = models.CharField(max_length=72, choices=LOCATION_CHOICES)
    email = models.EmailField()
    comments = models.TextField(blank=True)
    description = models.TextField(blank=True)
    active = models.BooleanField(default=True)
    last_update = models.DateTimeField(auto_now=True)
    class Meta:
        db_table = u'ftpmirrors'

    def add_to_xml(self, ET, node):
        fields = ('id', 'name', 'url', 'location', 'email', 'description', 'comments', 'last_update')
        for field in fields:
            n = ET.SubElement(node, field)
            val = getattr(self, field)
            if val is None: val = ''
            n.text = unicode(val)
        if self.active:
            n = ET.SubElement(node, 'active')

class FtpmirrorsForm(ModelForm):
    class Meta:
        model = Ftpmirrors

class Webmirrors(models.Model):
    name = models.CharField(max_length=60, blank=True)
    url = models.CharField(max_length=300, blank=True)
    location = models.CharField(max_length=72, blank=True)
    email = models.CharField(max_length=120, blank=True)
    comments = models.TextField(blank=True)
    description = models.TextField(blank=True)
    id = models.IntegerField(primary_key=True)
    active = models.IntegerField(null=True, blank=True)
    class Meta:
        db_table = u'webmirrors'

class LdapUtil(object):

    handle = None
    instance = None

    @classmethod
    def singleton(cls):
        if cls.instance is None:
            try:
                cls.instance = cls()
            except:
                return None

        return cls.instance

    def __init__(self):
        l = ldap.initialize(settings.MANGO_CFG['ldap_url'])
        l.protocol_version = ldap.VERSION3
        try:
            l.simple_bind_s(settings.MANGO_CFG['ldap_binddn'], settings.MANGO_CFG['ldap_bindpw'])
        except:
            return None

        self.__class__.handle = l


class LdapObject(object):
    
    BASEDN = None
    MULTI_ATTRS = set(('objectClass'))
    FILTER = None

    def __init__(self, dn, attrs):
        for k, i in attrs.items():
            if k in self.MULTI_ATTRS:
                self.__dict__[k] = i
            else:
                self.__dict__[k] = i[0]
        self.dn = dn

    @classmethod
    def search(cls, filter=None, attrlist=None):
        l = LdapUtil.singleton().handle

        base = cls.BASEDN

        q_object = None
        for f in (cls.FILTER, filter):
            if isinstance(f, tree.Node):
                if q_object:
                    q_object &= f
                else:
                    q_object = f
        if q_object:
            ldapfilter = cls._build_filter(q_object)
        else:
            ldapfilter = '(objectClass=*)'
        results = l.search_s(base, ldap.SCOPE_SUBTREE, ldapfilter, attrlist)

        items = []

        for result in results:
            items.append(cls(result[0], result[1]))

        return items

    @classmethod
    def _build_filter(cls, q_object):
        """Builds a LDAP filter using a Q object"""
        vals = []
        for child in q_object.children:
            if isinstance(child, tree.Node):
                val = cls._build_filter(child)
            else:
                val = ldap.filter.filter_format('(%s=%s)', (child[0], child[1]))
            vals.append(val)

        format = ''
        if len(vals) == 1:
            format = '%s'
        elif q_object.connector == q_object.OR:
            format = '(|%s)'
        else:
            format = '(&%s)'

        if q_object.negated:
            format = '(!%s)' % format

        return format % ''.join(vals)

class UserGroups(LdapObject):

    BASEDN = settings.MANGO_CFG['ldap_groups_basedn']
    MULTI_ATTRS = set(('memberUid', 'objectClass'))
    FILTER = Q(objectClass='posixGroup')

class Users(LdapObject):

    BASEDN = settings.MANGO_CFG['ldap_users_basedn']
    MULTI_ATTRS = set(('authorizedKey','objectClass'))
    FILTER = Q(objectClass='posixAccount')

    def __init__(self, *foo):
        self._groups = None
        super(Users, self).__init__(*foo)

    @property
    def groups(self):
        if self._groups is None:
            self._groups = UserGroups.search(Q(memberUid=self.__dict__['uid']), ('cn',))

        return self._groups

    @property
    def modules(self):
        if self._modules is None:
            self._modules = Modules.search(Q(memberUid=self.__dict__['uid']), ('cn', 'objectClass'))

        return self._modules

    def add_to_xml(self, ET, formnode):
        for item in ('uid', 'cn', 'mail', 'description'):
            node = ET.SubElement(formnode, item)
            node.text = self.__dict__.get(item, '')

        for key in self.__dict__.get('authorizedKey', []):
            # TODO:
            #  - add fingerprint of above keys
            if key:
                node = ET.SubElement(formnode, 'authorizedKey')
                node.text = key

        for group in self.groups:
            node = ET.SubElement(formnode, 'group', {'cn': group.cn})

class Modules(LdapObject):
    """Base class for Module information (maintainer into, etc)"""
    BASEDN = settings.MANGO_CFG['ldap_modules_basedn']
    MULTI_ATTRS = set(('maintainerUid', 'objectClass'))
    FILTER = Q(objectClass='gnomeModule')

    @property
    def maintainer(self):
        return u', '.join(self.__dict__.get('maintainerUid', []))

    def add_to_xml(self, ET, formnode):
        for item in ('cn', 'description', 'maintainer'):
            node = ET.SubElement(formnode, item)
            node.text = getattr(self, item, '')

class L10nModules(Modules):
    """Specific filter to only return localization modules

    Note: within LDAP, it is very easy to morph an l10n module into
    a development one. This class should do as minimal as possible."""
    FILTER = Q(objectClass='localizationModule')

class DevModules(Modules):
    """Specific filter to only return development modules

    Note: within LDAP, it is very easy to morph an l10n module into
    a development one. This class should do as minimal as possible."""
    FILTER = (~ Q(objectClass='localizationModule')) & Q(objectClass='gnomeModule')



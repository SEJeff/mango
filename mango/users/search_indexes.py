from haystack import site, indexes
from mango.users.models import LdapUser


class LdapUserIndex(indexes.SearchIndex):
    text         = indexes.CharField(document=True, use_template=True)
    email        = indexes.CharField(model_attr='email')
    username     = indexes.CharField(model_attr='username')
    full_name    = indexes.CharField(model_attr='full_name')
    description  = indexes.CharField(model_attr='description')
    groups       = indexes.MultiValueField(model_attr='groups')

site.register(LdapUser, LdapUserIndex)

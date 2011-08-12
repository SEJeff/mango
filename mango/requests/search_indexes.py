from haystack import site, indexes
from mango.requests.models import AccountRequest

class AccountRequestIndex(indexes.SearchIndex):
    text        = indexes.CharField(document=True, use_template=True)
    uid         = indexes.CharField(model_attr='uid')
    cn          = indexes.CharField(model_attr='cn')
    comment     = indexes.CharField(model_attr='comment')

site.register(AccountRequest, AccountRequestIndex)

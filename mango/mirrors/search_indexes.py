from haystack import site, indexes
from mango.mirrors.models import FtpMirror

class FtpMirrorIndex(indexes.SearchIndex):
    text        = indexes.CharField(document=True, use_template=True)
    url         = indexes.CharField(model_attr='url')
    name        = indexes.CharField(model_attr='name')
    email       = indexes.CharField(model_attr='email')
    comments    = indexes.CharField(model_attr='comments')
    location    = indexes.CharField(model_attr='location')
    description = indexes.CharField(model_attr='description')

site.register(FtpMirror, FtpMirrorIndex)

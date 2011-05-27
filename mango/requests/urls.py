from django.conf.urls.defaults import *

urlpatterns = patterns('',
    url(r'^$',                          'mango.requests.views.index',  name="requests-index"),
    url(r'^(?P<pk>\d+)/(?P<slug>.*)/$', 'mango.requests.views.update', name="requests-update"),
)

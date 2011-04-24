from django.db import models
from django.conf import settings
from django.core.urlresolvers import reverse
from django.template.defaultfilters import slugify

LOCATION_CHOICES = (
    ('United States and Canada', 'United States and Canada'),
    ('Australia', 'Australia'),
    ('Europe', 'Europe'),
    ('Asia', 'Asia'),
    ('South America', 'South America'),
    ('Other', 'Other'),
)

# TODO: When this goes to production, create a GNOMEMirror model and migrate to it
# TODO: Add a celerybeat daily task to check mirror freshness and set active accordingly
class FtpMirror(models.Model):
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
    def __unicode__(self):
        return "%s FtpMirror (%s)" % (self.name.title(), self.url)

    def is_active(self):
        return self.active == 1

    def get_absolute_url(self):
        return reverse("mirrors-update", args=(self.pk, slugify(self.name)))


class Webmirror(models.Model):
    name = models.CharField(max_length=60, blank=True)
    url = models.CharField(max_length=300, blank=True)
    location = models.CharField(max_length=72, blank=True)
    email = models.CharField(max_length=120, blank=True)
    comments = models.TextField(blank=True)
    description = models.TextField(blank=True)
    active = models.IntegerField(null=True, blank=True)
    class Meta:
        db_table = u'webmirrors'
    def __unicode__(self):
        return "%s Webmirror (%s)" % (self.name.title(), self.url)

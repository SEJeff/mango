
from django.db import models
from django.conf import settings
from django.utils import tree
from django.db.models import Q

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

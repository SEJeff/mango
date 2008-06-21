# This is an auto-generated Django model module.
# You'll have to do the following manually to clean this up:
#     * Rearrange models' order
#     * Make sure each model has one field with primary_key=True
# Feel free to rename the models, but don't rename db_table values or field names.
#
# Also note: You'll have to insert the output of 'django-admin.py sqlcustom [appname]'
# into your database.

from django.db import models

class AccountRequest(models.Model):
    id = models.AutoField(primary_key=True)
    uid = models.CharField(max_length=15)
    cn = models.CharField(max_length=255)
    mail = models.EmailField(max_length=255)
    comment = models.TextField()
    timestamp = models.DateTimeField()
    authorizationkeys = models.TextField()
    status = models.CharField(max_length=1, default='P')
    is_new_account = models.CharField(max_length=1, default='Y')
    is_mail_verified = models.CharField(max_length=1, default='N')
    mail_token = models.CharField(max_length=40)
    class Meta:
        db_table = u'account_request'

class AccountGroups(models.Model):
    id = models.AutoField(primary_key=True)
    request = models.ForeignKey(AccountRequest)
    cn = models.CharField(max_length=15)
    voucher_group = models.CharField(max_length=50, blank=True, null=True)
    verdict = models.CharField(max_length=1, default='P')
    voucher = models.CharField(max_length=15, blank=True, null=True)
    denial_message = models.CharField(max_length=255, blank=True, null=True)
    class Meta:
        db_table = u'account_groups'

class Foundationmembers(models.Model):
    id = models.IntegerField(primary_key=True)
    firstname = models.CharField(max_length=150, blank=True)
    lastname = models.CharField(max_length=150, blank=True)
    email = models.CharField(max_length=300, blank=True)
    comments = models.TextField(blank=True)
    first_added = models.DateField()
    last_renewed_on = models.DateField(null=True, blank=True)
    last_update = models.DateTimeField()
    resigned_on = models.DateField(null=True, blank=True)
    class Meta:
        db_table = u'foundationmembers'

class Ftpmirrors(models.Model):
    name = models.CharField(max_length=60, blank=True)
    url = models.CharField(max_length=300, blank=True)
    location = models.CharField(max_length=72, blank=True)
    email = models.CharField(max_length=120, blank=True)
    comments = models.TextField(blank=True)
    description = models.TextField(blank=True)
    id = models.IntegerField(primary_key=True)
    active = models.IntegerField(null=True, blank=True)
    last_update = models.DateTimeField()
    class Meta:
        db_table = u'ftpmirrors'

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


from django.db import models
from django.conf import settings

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

class AccountGroups(models.Model):
    id = models.AutoField(primary_key=True)
    request = models.ForeignKey(AccountRequest)
    cn = models.CharField(max_length=15)
    voucher_group = models.CharField(max_length=50, blank=True, null=True)
    verdict = models.CharField(max_length=1, default='P', editable=False)
    voucher = models.CharField(max_length=15, blank=True, null=True)
    denial_message = models.CharField(max_length=255, blank=True, null=True)
    class Meta:
        db_table = u'account_groups'

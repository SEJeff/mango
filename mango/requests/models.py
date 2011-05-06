from django.db import models
from django.conf import settings

REQUEST_VERDICTS = (
  ('A', 'approved'),
  ('R', 'rejected'),
  ('M', 'mail_verification'),
  ('V', 'awaiting_vouchers'),
  ('S', 'awaiting_setup'),
  ('P', 'pending'),
)

class AccountRequest(models.Model):
    uid = models.CharField(max_length=15)
    cn = models.CharField(max_length=255)
    mail = models.EmailField(max_length=255)
    comment = models.TextField()
    timestamp = models.DateTimeField(editable=False)
    authorizationkeys = models.TextField()
    status = models.CharField(max_length=1, default='P', choices=REQUEST_VERDICTS, editable=False)
    mail_token = models.CharField(max_length=40, editable=False)
    # FIXME: Original schema designers were on crack. These should be BooleanFields
    is_new_account = models.CharField(max_length=1, default='Y', editable=False)
    is_mail_verified = models.CharField(max_length=1, default='N', editable=False)

    class Meta:
        db_table = u'account_request'

    def __unicode__(self):
        return u"<%s %s from %s>" % (self.get_status_display(), self.__class__.__name__, self.uid)

class AccountGroup(models.Model):
    request = models.ForeignKey(AccountRequest)
    cn = models.CharField(max_length=15)
    voucher_group = models.CharField(max_length=50, blank=True, null=True)
    verdict = models.CharField(max_length=1, default='P', choices=REQUEST_VERDICTS, editable=False)
    voucher = models.CharField(max_length=15, blank=True, null=True)
    denial_message = models.CharField(max_length=255, blank=True, null=True)

    class Meta:
        db_table = u'account_groups'

    def __unicode__(self):
        return u"<%s %s from %s for %s>" % (self.get_verdict_display().title(), self.__class__.__name__, self.request.uid, self.voucher_group)

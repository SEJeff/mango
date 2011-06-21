from django.db import models
from django.conf import settings
from django.utils.translation import ugettext_lazy as _

REQUEST_VERDICTS = (
  ('A', _('approved')),
  ('R', _('rejected')),
  ('M', _('mail verification')),
  ('V', _('awaiting vouchers')),
  ('S', _('awaiting setup')),
  ('P', _('pending')),
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
        return _("%s account request for %s") % (self.get_status_display().title(), self.uid)

class AccountGroup(models.Model):
    request = models.ForeignKey(AccountRequest)
    cn = models.CharField(max_length=15)
    voucher_group = models.CharField(max_length=50, blank=True, null=True)
    verdict = models.CharField(max_length=1, default='P', choices=REQUEST_VERDICTS, editable=False)
    voucher = models.CharField(max_length=15, blank=True, null=True)
    denial_message = models.CharField(max_length=255, blank=True, null=True)

    def for_display(self):
        if self.voucher:
            return "%s on %s by %s" % (self.cn, self.voucher_group, self.voucher)

    class Meta:
        db_table = u'account_groups'

    def __unicode__(self):
        return _("%s %s for %s") % (self.request.uid, self.get_verdict_display(), self.voucher_group or _("unknown"))

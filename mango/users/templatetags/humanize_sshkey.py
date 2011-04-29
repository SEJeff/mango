from django import template
from mango.users.sshkey import SshKey

register = template.Library()

@register.filter
def humanize_sshkey(value):
    return unicode(SshKey(value))

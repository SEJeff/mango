from django.http import HttpResponse
from django.conf import settings
import datetime

import ldap
import models

try:
    import xml.etree.cElementTree as ET
except ImportError:
    import cElementTree as ET


def get_xmldoc(title, request):
    doc = ET.ElementTree(ET.Element('page', {
        'title': title,
        'mode': settings.MANGO_CFG['mode'],
        'baseurl': settings.MANGO_CFG['base_url'],
        'thisurl': request.path,
        'token': "afd0e0d9eab69ab904c7a43f6bd3810156f0afc9", # TODO: generate token
        'support': settings.MANGO_CFG['support_email'],
    }))

    # TODO: 
    #  - determine if user is logged in, if so:
    #    add user details to XML

    return doc, doc.getroot()

def get_xmlresponse(doc, template, response=None):
    if response is None:
        response = HttpResponse(mimetype='text/xml')

    response.write(ET.tostring(ET.ProcessingInstruction('xml-stylesheet', 'href="%s" type="text/xsl"' % template)))
    doc.write(response, 'utf-8')
    return response

def current_datetime(request):
    now = datetime.datetime.now()
    html = "<html><body>It is now %s.</body></html>" % now
    return HttpResponse(html)


def list_users(request):
    l = ldap.initialize(settings.MANGO_CFG['ldap_url'])
#    l.simple_bind("cn=Manager,dc=gnome,dc=org")

    filter = '(objectClass=posixAccount)'
    stuff = l.search_s(settings.MANGO_CFG['ldap_users_basedn'],
               ldap.SCOPE_SUBTREE, filter, None)

    html = '<pre>%s</pre>' % "\n".join(["%s: %s" % (item[0], repr(item[1])) for item in stuff])
    return HttpResponse(html)

def test_index(request):
    doc, root = get_xmldoc('Login Page', request)
    root.append(ET.Element('homepage'))

    return get_xmlresponse(doc, "../www/index.xsl")

def list_accounts(request):
    doc, root = get_xmldoc('List Accounts', request)
    el1 = ET.SubElement(root, 'listaccounts')

    accounts = models.AccountRequest.objects.all()
    for account in accounts:
        el2 = ET.SubElement(el1, 'account', dict([a for a in account.__dict__.iteritems() if a[0] not in ('id', 'timestamp')]))
        el2g = ET.SubElement(el2, 'groups')
        for group in account.accountgroups_set.filter(verdict__exact='A'):
            d = {'cn': group.cn}
            if group.voucher is not None:
                d['approvedby'] = group.voucher
                d['module'] = group.voucher_group

            el3 = ET.SubElement(el2g, 'group', d)

    return get_xmlresponse(doc, "../www/list_accounts.xsl")



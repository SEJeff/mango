from django.http import HttpResponse, Http404, HttpResponseServerError
from django.conf import settings
import datetime

import ldap
import ldap.filter
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

    response.write(ET.tostring(ET.ProcessingInstruction('xml-stylesheet', 'href="%s/%s" type="text/xsl"' % (settings.MANGO_CFG['base_url'], template))))
    doc.write(response, 'utf-8')
    return response

def current_datetime(request):
    now = datetime.datetime.now()
    html = "<html><body>It is now %s.</body></html>" % now
    return HttpResponse(html)


def list_users(request):
    doc, root = get_xmldoc('List Users', request)
    el = ET.SubElement(root, 'listusers')

    l = models.LdapUtil().handle
    if not l:
        return HttpResponseServerError('Cannot connect to LDAP?')

    filter = '(objectClass=posixAccount)'
    users = models.Users.search(filter)
    
    for user in users:
        usernode = ET.SubElement(el, 'user')
        
        node = ET.SubElement(usernode, 'uid')
        node.text = user.uid

        node = ET.SubElement(usernode, 'name')
        node.text = user.cn

        node = ET.SubElement(usernode, 'email')
        node.text = user.mail

    return get_xmlresponse(doc, "list_users.xsl")

def edit_user(request, user):
    doc, root = get_xmldoc('Update user %s' % user, request)
    el = ET.SubElement(root, 'updateuser')

    
    l = models.LdapUtil().handle
    if not l:
        return HttpResponseServerError('Cannot connect to LDAP?')

    filter = ldap.filter.filter_format('(&(objectClass=posixAccount)(uid=%s))', (user,))
    users = models.Users.search(filter)

    if len(users) != 1:
        raise Http404()

    user = users[0]

    for item in ('uid', 'cn', 'mail', 'description'):
        node = ET.SubElement(el, item)
        node.text = user.__dict__[item]

    for key in user.__dict__.get('authorizedKey', []):
        # TODO:
        #  - add fingerprint of above keys
        if key:
            node = ET.SubElement(el, 'authorizedKey')
            node.text = key

    for group in user.groups:
        node = ET.SubElement(el, 'group', {'cn': group.cn})

    return get_xmlresponse(doc, "update_user.xsl")



def test_index(request):
    doc, root = get_xmldoc('Login Page', request)
    root.append(ET.Element('homepage'))

    return get_xmlresponse(doc, "index.xsl")

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

    return get_xmlresponse(doc, "list_accounts.xsl")



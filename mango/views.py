from django.http import HttpResponse, Http404, HttpResponseServerError, HttpResponseRedirect
from django.conf import settings
from django.core.paginator import InvalidPage, QuerySetPaginator, Paginator
from django.db.models import Q
from django.db.models.query import QuerySet
from django.shortcuts import get_object_or_404
import datetime

import ldap
import ldap.filter
import models

try:
    import xml.etree.cElementTree as ET
except ImportError:
    import cElementTree as ET


def get_xmldoc(title, request, subpage=None):
    pagenode = ET.Element('page', {
        'title': title,
        'mode': settings.MANGO_CFG['mode'],
        'baseurl': settings.MANGO_CFG['base_url'],
        'thisurl': request.path,
        'token': "afd0e0d9eab69ab904c7a43f6bd3810156f0afc9", # TODO: generate token
        'support': settings.MANGO_CFG['support_email'],
    })
    doc = ET.ElementTree(pagenode)

    # TODO: 
    #  - determine if user is logged in, if so:
    #    add user details to XML
    users = models.Users.search(Q(uid='ovitters'))
    if len(users) == 1:
        user = users[0]

        usernode = ET.SubElement(pagenode, 'user')
        node = ET.SubElement(usernode, 'cn')
        node.text = user.cn

        for group in user.groups:
            node = ET.SubElement(pagenode, 'group', {'cn': group.cn})

    if subpage is not None:
        pagenode = ET.SubElement(pagenode, subpage)
    return doc, pagenode

def get_xmlresponse(doc, template, response=None):
    if response is None:
        response = HttpResponse(mimetype='text/xml')

    response.write(ET.tostring(ET.ProcessingInstruction('xml-stylesheet', 'href="%s/www/%s" type="text/xsl"' % (settings.MANGO_CFG['base_url'], template))))
    doc.write(response, 'utf-8')
    return response

def setup_xml_paginator(request, root, queryset):
    """Add paginator information to the XML node specified by root

    Note:
     - In the PHP version, the XML also had the elements result_num and page_size"""

    if isinstance(queryset, QuerySet):
        paginator = QuerySetPaginator(queryset, 25)
    else:
        paginator = Paginator(queryset, 25)

    try:
        page = paginator.page(request.GET.get('page', 1))
    except InvalidPage:
        raise Http404('Invalid page')

    pagednode = ET.SubElement(root, 'pagedresults')
    node = ET.SubElement(pagednode, 'total_results')
    node.text = unicode(page.paginator.count)
    node = ET.SubElement(pagednode, 'total_pages')
    node.text = unicode(page.paginator.num_pages)
    node = ET.SubElement(pagednode, 'page_num')
    node.text = unicode(page.number)

    return page

def add_form_errors_to_xml(root, form):
    """Adds form errors to the XML node specified by root"""

    valid = form.is_valid()
    if not valid:
        for field, errors in form.errors.items():
            node = ET.SubElement(root, 'formerror', {'type': field})

    return valid

def current_datetime(request):
    now = datetime.datetime.now()
    html = "<html><body>It is now %s.</body></html>" % now
    return HttpResponse(html)


def list_users(request):
    doc, pagenode = get_xmldoc('List Users', request, 'listusers')

    l = models.LdapUtil().handle
    if not l:
        return HttpResponseServerError('Cannot connect to LDAP?')

    queryset = models.Users.search(attrlist=('uid', 'cn', 'mail'))
    
    page = setup_xml_paginator(request, pagenode, queryset)
    for obj in page.object_list:
        usernode = ET.SubElement(pagenode, 'user')
        
        for item in ('uid', 'cn', 'mail'):
            node = ET.SubElement(usernode, item)
            node.text = getattr(obj, item)

    return get_xmlresponse(doc, "list_users.xsl")

def edit_user(request, user):
    doc, pagenode = get_xmldoc('Update user %s' % user, request, 'updateuser')

    l = models.LdapUtil().handle
    if not l:
        return HttpResponseServerError('Cannot connect to LDAP?')

    users = models.Users.search(Q(uid=user))

    if len(users) != 1:
        raise Http404()

    user = users[0]

    for item in ('uid', 'cn', 'mail', 'description'):
        node = ET.SubElement(pagenode, item)
        node.text = user.__dict__.get(item, '')

    for key in user.__dict__.get('authorizedKey', []):
        # TODO:
        #  - add fingerprint of above keys
        if key:
            node = ET.SubElement(pagenode, 'authorizedKey')
            node.text = key

    for group in user.groups:
        node = ET.SubElement(pagenode, 'group', {'cn': group.cn})

    return get_xmlresponse(doc, "update_user.xsl")



def view_index(request):
    doc, pagenode = get_xmldoc('Login Page', request, 'homepage')

    return get_xmlresponse(doc, "index.xsl")

def list_accounts(request):
    doc, pagenode = get_xmldoc('List Accounts', request, 'listaccounts')

    queryset = models.AccountRequest.objects.filter(status='S')

    page = setup_xml_paginator(request, pagenode, queryset)
    for obj in page.object_list:
        el2 = ET.SubElement(pagenode, 'account', dict([a for a in obj.__dict__.iteritems() if a[0] not in ('id', 'timestamp')]))
        el2g = ET.SubElement(el2, 'groups')
        for group in obj.accountgroups_set.filter(verdict__exact='A'):
            d = {'cn': group.cn}
            if group.voucher is not None:
                d['approvedby'] = group.voucher
                d['module'] = group.voucher_group

            el3 = ET.SubElement(el2g, 'group', d)

    return get_xmlresponse(doc, "list_accounts.xsl")

def add_account(request):
    doc, pagenode = get_xmldoc('Request LDAP account', request, 'newaccount')

    dev_modules = models.DevModules.search()
    trans_modules = models.L10nModules.search()

    for module in dev_modules:
        ET.SubElement(pagenode, 'gnomemodule', {'cn': module.cn})
    for module in trans_modules:
        ET.SubElement(pagenode, 'translation', {'cn': module.cn, 'desc': module.description})

    if request.method == 'POST':
        f = models.AccountsForm(request.POST)
        if add_form_errors_to_xml(pagenode, f):
#            mirror = f.save()
            return HttpResponseRedirect(u'../view/%s' % unicode(mirror.id))

    return get_xmlresponse(doc, "new_account.xsl")

def list_mirrors(request):
    doc, pagenode = get_xmldoc('List Mirrors', request, 'listftpmirrors')

    filter = request.GET.get('filter_keyword', None)
    if filter:
        queryset = models.Ftpmirrors.objects.filter(Q(name__contains=filter) | Q(url__contains=filter))

        filternode = ET.SubElement(pagenode, 'filter')
        keynode = ET.SubElement(filternode, 'keyword')
        keynode.text = filter
    else:
        queryset = models.Ftpmirrors.objects.all()

    page = setup_xml_paginator(request, pagenode, queryset)
    for obj in page.object_list:
        ftpnode = ET.SubElement(pagenode, 'ftpmirror')

        obj.add_to_xml(ET, ftpnode)

    return get_xmlresponse(doc, "list_ftpmirrors.xsl")


def edit_mirror(request, pk):
    doc, pagenode = get_xmldoc('Update mirror', request, 'updateftpmirror')

    mirror = get_object_or_404(models.Ftpmirrors.objects, pk=pk)

    if request.method == 'POST':
        f = models.FtpmirrorsForm(request.POST, instance=mirror)
        if add_form_errors_to_xml(pagenode, f):
            f.save()

    mirror.add_to_xml(ET, el)

    return get_xmlresponse(doc, "update_ftpmirror.xsl")

def add_mirror(request):
    doc, pagenode = get_xmldoc('New mirror', request, 'newftpmirror')

    if request.method == 'POST':
        f = models.FtpmirrorsForm(request.POST)
        if add_form_errors_to_xml(pagenode, f):
            mirror = f.save()
            return HttpResponseRedirect(u'../edit/%s' % unicode(mirror.id))

    return get_xmlresponse(doc, "new_ftpmirror.xsl")

def add_foundationmember_to_xml(root, member=None, form=None):
    if member is None and form is None:
        return

    instance = form and form.instance or member
    form_or_member = form and form.data or member.__dict__

    ET.SubElement(root, 'id').text = unicode(instance.id)
    for field in ('firstname', 'lastname', 'comments', 'email'):
        node = ET.SubElement(root, field)
        node.text = form_or_member[field]
    for field in ('first_added', 'last_renewed_on'):
        node = ET.SubElement(root, field)
        node.text = unicode(getattr(instance, field))
    if instance.is_member:
        ET.SubElement(root, 'member')
    if instance.need_to_renew:
        ET.SubElement(root, 'need_to_renew')

def list_foundationmembers(request):
    doc, pagenode = get_xmldoc('List Foundation Members', request, 'listfoundationmembers')

    queryset = models.Foundationmembers.objects.all()

    page = setup_xml_paginator(request, pagenode, queryset)
    for member in page.object_list:
        membernode = ET.SubElement(pagenode, 'foundationmember')
        membernode.set('id', unicode(member.id))
        add_foundationmember_to_xml(membernode, member)

    return get_xmlresponse(doc, "list_foundationmembers.xsl")

def edit_foundationmember(request, pk):
    doc, pagenode = get_xmldoc('Update Foundation Member', request)
    pagenode = ET.SubElement(root, 'updatefoundationmember')

    obj = get_object_or_404(models.Foundationmembers.objects, pk=pk)

    f = models.FoundationmembersForm(request.POST, instance=obj)
    if request.method == 'POST':
        if add_form_errors_to_xml(pagenode, f):
            f.save()

    add_foundationmember_to_xml(pagenode, obj, f)

    return get_xmlresponse(doc, "update_foundationmember.xsl")

def list_modules(request):
    doc, pagenode = get_xmldoc('List Modules', request, 'listmodules')

    queryset = models.Modules.search()

    page = setup_xml_paginator(request, pagenode, queryset)
    for obj in page.object_list:
        modulenode = ET.SubElement(pagenode, 'module')
        obj.add_to_xml(ET, modulenode)

    return get_xmlresponse(doc, "list_modules.xsl")

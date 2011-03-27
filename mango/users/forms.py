from django import forms
from models import LdapUser, LdapGroup
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User

class UserForm(forms.ModelForm):

    # Developer Options
    gnomecvs = forms.BooleanField(label=_('Git account'), required=False)
    ftpadmin = forms.BooleanField(label=_('FTP upload'), required=False)

    # Foundation Options
    foundation = forms.BooleanField(label=_('Foundation Member'), required=False)
    mailusers = forms.BooleanField(label=_("has a cool '%s' email alias") % "gnome.org", required=False)

    # Shell access
    bugzilla = forms.BooleanField(label=_('Bugzilla dude/dudess'), required=False)
    gnomeweb = forms.BooleanField(label=_('Web admin'), required=False)
    buildmaster = forms.BooleanField(label=_('Build master account'), required=False)
    buildslave = forms.BooleanField(label=_('Build slave account'), required=False)
    artweb = forms.BooleanField(label=_('Artweb Admin'), required=False)
    gitadmin = forms.BooleanField(label=_('Git admin'), required=False)

    # Mango related
    accounts = forms.BooleanField(label=_('Accounts team dude/dudess'), required=False)
    membcte = forms.BooleanField(label=_('Membership Committee dude/dudess'), required=False)
    sysadmin = forms.BooleanField(label=_('Sysadmin team dude/dudess'), required=False)

    def mango_groups(self):
        """
        For this form, all ldap group attributes are BooleanField fields.
        """
        g = []
        for name,type_ in self.fields.items():
            if isinstance(type_, forms.fields.BooleanField):
                g.append(name)
        return g

    def __init__(self, *args, **kwargs):
        # See: http://www.hindsightlabs.com/blog/2010/02/11/adding-extra-fields-to-a-model-form-in-djangos-admin/
        super(UserForm, self).__init__(*args, **kwargs)
        if kwargs.has_key('instance'):
            allowed_mango_groups = self.mango_groups()
            instance = kwargs['instance']

            # Populate the user's groups
            for group in instance.groups:
                if group.name in allowed_mango_groups:
                    self.initial[group.name.encode('ascii')] = True

    def save(self, commit=True):
        user = super(UserForm, self).save(False)
        username = user.username
        if self.has_changed():
            mango_groups = self.mango_groups()
            for field in self.changed_data:
                status = self.cleaned_data.get(field)
                if field in mango_groups:
                    try:
                        group = user.groups.get(name=field)
                    except LdapGroup.DoesNotExist:
                        # When you're adding a user to a new group
                        # TODO: When the group isn't found add error handling
                        group = LdapGroup.objects.get(name=field)
                    if username not in group.members:
                        group.members.append(username)
                    else:
                        index = group.members.index(username)
                        gone = group.members.pop(index)
                    # TODO: Add error handling around this
                    group.save()
        if commit:
            user.save()

    class Meta:
        model = LdapUser
        exclude = ('password', 'home_directory', 'first_name', 'last_name',
                   'uid', 'gid', 'dn', 'username')
        # TODO: Write a custom widget for ListField entries like keys and use it
        #include = ('full_name', 'email', 'description')
        widgets = {
            'description': forms.Textarea(attrs={'rows': 5, 'cols': 50}),
        }

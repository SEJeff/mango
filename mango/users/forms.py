from django import forms
from django.contrib import admin
from models import LdapUser, LdapGroup
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.models import User

from django.template.loader import render_to_string

class SSHKeyWidget(forms.SelectMultiple):
    def __init__(self, attrs=None, environment=None):
        super(SSHKeyWidget, self).__init__(attrs)
        self.environment = environment

    def render(self, name, value, attrs=None):
        i = 0
        keys = {}
        # TODO: Write code to show fingerprints and whatnot
        #for key in value:
        return render_to_string('users/sshkey_widget.html', {
            'name': name,
            'value': value,
        })


# This is very much a W.I.P. to get the ssh key validation working
class MultipleChoiceAnyField(forms.MultipleChoiceField):
    """A MultipleChoiceField with no validation."""

    def valid_value(self, *args, **kwargs):
        return True

class UserForm(forms.ModelForm):
    # TODO: The magic to do the ssh keys is almost certainly using a MultiValueWidget
    # TODO: Use django.contrib.admin.helpers.AdminForm to group these together

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
    membctte = forms.BooleanField(label=_('Membership Committee dude/dudess'), required=False)
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
        keys = MultipleChoiceAnyField(label=_('SSH Keys'), required=True)

        # Populate the group management checkboxes
        if kwargs.has_key('instance'):
            allowed_mango_groups = self.mango_groups()
            instance = kwargs['instance']

            # Populate the user's groups
            for group in instance.groups:
                if group.name in allowed_mango_groups:
                    self.initial[group.name.encode('ascii')] = True

            # Override the default keys widget
            #self.initial['keys'] = [ str(key) for key in self.instance.keys ]

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
        exclude = ('dn', 'first_name', 'last_name', 'uid', 'gid', 'username', 'home_directory', 'password', 'email')
        widgets = {
            'description': forms.Textarea(attrs={'rows': 5, 'cols': 50}),
            'keys': SSHKeyWidget(),
        }

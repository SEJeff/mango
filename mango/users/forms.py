from django import forms
from django.contrib import admin
from models import LdapUser, LdapGroup
# See bug: https://github.com/pydanny/django-uni-form/issues/90
# uni_form blows up with ugettext_lazy + python 2.7 + Fieldset
#from django.utils.translation import ugettext_lazy as _
from django.utils.translation import ugettext as _
from django.contrib.auth.models import User

from uni_form.helpers import FormHelper, Submit, Reset, Layout, Fieldset, Row, HTML

from django.template.loader import render_to_string

class SSHKeyWidget(forms.SelectMultiple):
    def __init__(self, attrs=None, environment=None):
        super(SSHKeyWidget, self).__init__(attrs)
        self.environment = environment

    def render(self, name, value, attrs=None):
        i = 0
        keys = {}
        if isinstance(value, basestring):
            value = [value]

        # TODO: Write code to show fingerprints and whatnot
        #for key in value:
        return render_to_string('users/sshkey_widget.html', {
            'name': name,
            'value': value,
        })


# This is very much a W.I.P. to get the ssh key validation working
# TODO: Fix this shit
class MultipleChoiceAnyField(forms.MultipleChoiceField):
    """A MultipleChoiceField with no validation."""

    def valid_value(self, *args, **kwargs):
        return True

def UserFormFactory(hide_username=True):
    class UserForm(forms.ModelForm):

        # django-uni-form stuff to make the pretty
        helper = FormHelper()

        # Add a pretty layout that mostly mimics the original mango interface
        layout = Layout(Fieldset('',
            'full_name',
            'login_shell',
            'description',
            'keys',
            HTML('<h3>Groups / Options</h3>'),
            Fieldset(_('Developer Options'),
                'gnomecvs',
                'ftpadmin',
            ),
            Fieldset(_('Foundation Options'),
                'foundation',
                'mailusers',
            ),
            Fieldset(_('Shell Access'),
                'bugzilla',
                'gnomeweb',
                'buildmaster',
                'buildslave',
                'artweb',
                'gitadmin',
            ),
            Fieldset(_('Mango Related'),
                'accounts',
                'membctte',
                'sysadmin',
            ),
            css_class="inlineLabels",
        ))

        # Add the username field as the 2nd entry  to  the  only
        # FieldSet. This puts it right after full_name in the ui
        if not hide_username:
            layout.fields[0].fields.insert(1, 'username')

        helper.add_layout(layout)

        submit = Submit('submit', _('Submit Changes'))

        # Add the pretty rounded button style
        submit.field_classes= " action_button"
        helper.add_input(submit)

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
            if self.instance.pk:
                self._meta.exclude= ('username',)

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
            exclude = ('dn', 'first_name', 'last_name', 'uid', 'gid', 'home_directory', 'password', 'email')
            # Changing the username upsets django-ldapdb on existing users
            if hide_username:
                exclude= ('username',)

            widgets = {
                'description': forms.Textarea(attrs={'rows': 5, 'cols': 50}),
                'keys': SSHKeyWidget(),
            }
    return UserForm

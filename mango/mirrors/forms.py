from models import FtpMirror
from django.forms import ModelForm
from uni_form.helpers import FormHelper, Submit
from django.utils.translation import ugettext_lazy as _

class FtpMirrorForm(ModelForm):

    # django-uni-form stuff to make the pretty
    helper = FormHelper()
    submit = Submit('submit', _('Submit Changes'))
    helper.add_input(submit)

    class Meta:
        model = FtpMirror

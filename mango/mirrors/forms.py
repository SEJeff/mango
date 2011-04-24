from models import FtpMirror
from django.forms import ModelForm

class FtpMirrorForm(ModelForm):
    class Meta:
        model = FtpMirror

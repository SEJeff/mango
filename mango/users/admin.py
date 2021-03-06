from django import forms
from django.contrib import admin
from models import LdapUser

class LdapUserAdminForm(forms.ModelForm):
    class Meta:
        model = LdapUser
        widgets = {
            'description': forms.Textarea(attrs={'rows': 5, 'cols': 50}),
        }

class LdapUserAdmin(admin.ModelAdmin):
    fieldsets = (
        ('Imortant', {
            'fields': ('username', 'full_name', 'email', 'description'),
        }),
        ('Advanced options', {
            #'classes': ('collapse',),
            'fields': ('uid', 'gid')
        }),
        ('Extra Stuff', {
            'classes': ('collapse',),
            'fields': ('password', 'home_directory')
        }),
    )

    exclude = ('keys', 'login_shell')
    list_display = ('username', 'full_name', 'email')
    search_fields = ('username', 'full_name', 'email')

    list_editable = ('email', 'full_name')
    ordering = ('username',)

    # For making description a Textarea
    form = LdapUserAdminForm

admin.site.register(LdapUser, LdapUserAdmin)

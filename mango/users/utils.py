def group_to_attrib(group):
    # Group name to ldap model attribute mapping
    lookup = {
        'sysadmin':    'sysadmin_team',
        'membcte':     'membership_committee',
        'accounts':    'accounts_team',
        'gitadmin':    'gitadmin',
        'artweb':      'artweb',
        'buildslave':  'buildslave',
        'buildmaster': 'buildmaster',
        'gnomeweb':    'web_admin',
        'bugzilla':    'bugzilla_admin',
        'mailusers':   'gnome_email',
        'ftpadmin':    'ftp_upload',
        'gnomecvs':    'git_account',
        'foundation':  'foundation_member',

    }
    try:
        field = lookup[group]
    except KeyError:
        return

    return field

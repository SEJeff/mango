class FoundationMemberRouter(object):
    """
    A very simple database router to route all queries for the 'members'
    app to  the  account_requests  database  for  all  pending  accounts.
    """

    def db_for_read(self, model, **hints):
        if model._meta.app_label == 'members':
            return 'foundation'
        return None

    def db_for_write(self, model, **hints):
        if model._meta.app_label == 'members':
            return 'foundation'
        return None

    def allow_relation(self, obj1, obj2, **hints):
        "Allow any relation if a model in 'members' is involved"
        if obj1._meta.app_label == 'members' or obj2._meta.app_label == 'members':
            return True
        return None

    def allow_syncdb(self, db, model):
        "Make sure the 'members' app only appears on the 'foundation' db"
        if db == 'foundation':
            return model._meta.app_label == 'members'
        elif model._meta.app_label == 'members':
            return False
        return None

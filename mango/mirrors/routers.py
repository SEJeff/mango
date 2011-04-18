class MirrorRouter(object):
    """
    A very simple database router to route all queries for the 'mirrors'
    app to the gnome_mirrors database for all GNOME  web / ftp  mirrors.
    """

    def db_for_read(self, model, **hints):
        if model._meta.app_label == 'mirrors':
            return 'gnome_mirrors'
        return None

    def db_for_write(self, model, **hints):
        if model._meta.app_label == 'mirrors':
            return 'gnome_mirrors'
        return None

    def allow_relation(self, obj1, obj2, **hints):
        "Allow any relation if a model in 'mirrors' is involved"
        if obj1._meta.app_label == 'mirrors' or obj2._meta.app_label == 'mirrors':
            return True
        return None

    def allow_syncdb(self, db, model):
        "Make sure the 'mirrors' app only appears on the 'gnome_mirrors' db"
        if db == 'gnome_mirrors':
            return model._meta.app_label == 'mirrors'
        elif model._meta.app_label == 'mirrors':
            return False
        return None

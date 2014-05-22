from configobj import ConfigObj

settings = ConfigObj("emonhub.conf", file_error=True)

print dict(settings)

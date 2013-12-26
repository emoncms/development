class Feed(object):

  def __init__(self,mysql):
    self.mysql = mysql
    pass

  # Create a new feed
  def create(self, name):
    pass

  # List feeds
  def list(self,userid):
    self.mysql.execute("SELECT id,userid,name,datatype,tag,public,size,engine FROM feeds WHERE `userid`='%d'" % userid)
    return self.mysql.fetchall()

  # List public feeds
  def list_public(self,userid):
    self.mysql.execute("SELECT id,userid,name,datatype,tag,public,size,engine FROM feeds WHERE `public`='1' AND `userid`='%d'" % userid)
    return self.mysql.fetchall()
    
  # Delete a feed
  def delete(id):
    pass


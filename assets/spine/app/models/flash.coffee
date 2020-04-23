Spine = require("spine")
$     = Spine.$
Model = Spine.Model
require('spine/lib/local');

class Flash extends Spine.Model

  @configure 'Flash', 'flash', 'record', 'status', 'statusText'
  
  @extend Model.Local

  init: ->

module?.exports = Flash
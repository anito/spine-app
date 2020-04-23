Spine     = require("spine")
$         = Spine.$
Log       = Spine.Log
Model     = Spine.Model
User      = require("models/user")
Flash     = require("models/flash")
Settings  = require("models/settings")
Extender  = require("extensions/model_extender")

require('spine/lib/ajax')

class Mysql extends Spine.Model

  @configure 'Mysql', 'id', 'filename', 'description', 'created', 'human'

  @extend Extender
  @extend Model.Ajax

  @url: '/api/' + @className.toLowerCase() + '/?sort=created&direction=desc&token='

  @trace: true

  @beforeFromJSON: (objects) ->
    objects.data

  @ajaxError: (xhr, status, error) ->
    throw new Error('Failed to fetch Mysql')

  @ajaxSuccess: (record, xhr, statusText, error) ->
  
  init: (instance) ->
    
module?.exports = Model.Mysql = Mysql
Spine     = require("spine")
$         = Spine.$
Log       = Spine.Log
Model     = Spine.Model
Extender  = require("extensions/model_extender")

require('spine/lib/local')

class User extends Spine.Model

  @configure 'User', 'id', 'username', 'token'

  @extend Extender
  @extend Model.Local
  
  @trace: true

  @url: base_url + '/api/users/'

  @logout: ->
    @log 'logout'
    $.ajax
      url: base_url + '/logout'
    .done( @logoutSuccess )
    .fail( @errorHandler )
  
  @logoutSuccess: (json, status, xhr) =>
    @log 'logoutSuccess'
    @log json
    $(window).off()
    @redirect '/users/login'

  @redirect: (url='', hash='') ->
	  location.href = base_url + url + hash

  @getToken: (settings) ->
    @fetch()
    @first().getToken(settings)

  init: (instance) ->
    
  getToken: (settings) ->
    data = JSON.stringify(@)

    $.ajax
      url: base_url + '/api/users/token'
      type: 'POST'
      data: data
      contentType: 'application/json'
      headers:
        Accept : 'application/json'
    .done( @doneRequest( settings ) )
    .fail( @errorHandler )

  doneRequest: (settings) =>
    ( json, state, xhr ) =>
      token = json.data.token
      id = json.data.id
      @updateAttributes
        'token': token #save token
        'id': id
      settings.done?.call()

module?.exports = Model.User = User
Spine     = require('spine')
$         = Spine.$
Model     = Spine.Model
Log       = Spine.Log
Extender  = require("extensions/model_extender")

require('spine/lib/local')

class Settings extends Spine.Model
    @configure 'Settings', 'Refresh', 'Error', 'Overdue'
  
    @extend Model.Local
    @extend Extender
  
    @load: ( options ) =>

        $.ajax
            url: @host + '/api/settings/read?token=' + Model.token
            contentType: 'application/json'
            headers:
                Accept : 'application/json'
                Authorization: 'Bearer ' + Model.token
        .done( @done( options ) )
        .fail( @fail( options ) )

    @done: (options) =>
        (json, status, xhr) =>
            settings = json.data.settings
            s = new @( settings )
            s.save()
            options.done?.call( null, settings )

    @fail: ( options ) =>
        (xhr, status, error) =>
            options.fail?.call( null )

    init: ( instance ) ->

module.exports = Model.Settings = Settings
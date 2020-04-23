require('lib/setup')

Spine           = require('spine')
$               = Spine.$
Model           = Spine.Model
Extender        = require('extensions/controller_extender')
Settings        = require("models/settings")
User            = require("models/user")
Mysql           = require("models/mysql")

class App extends Spine.Controller

    @extend Extender

    elements:
        'form'          : 'form'
        '#opt-download' : 'downloadEl'
        '#opt-restore'  : 'restoreEl'
        '#opt-dump'     : 'dumpEl'
        '#opt-time-info': 'timeInfoEl'
        '#opt-options'  : 'optionsEl'
        '#opt-logout'   : 'logoutEl'
        '#opt-more'     : 'moreEl'
        '#opt-db-saving': 'savingProgressEl'

    events:
        'change #opt-options' : 'change'
        'click .ask'          : 'ask'
        'click #opt-dump'     : 'submit'
        # 'click #opt-logout'   : 'logout'
        'focus #opt-options, #opt-restore'   : 'stopPing'
        'blur #opt-options, #opt-restore'    : 'startPing'

    statusTemplate:  (item) ->
        $('#statusTemplate').tmpl item

    optionsTemplate:  (item) ->
        $('#optionsTemplate').tmpl item

    timeInfoTemplate:  (item) ->
        $('#timeInfoTemplate').tmpl item

    errorTemplate: (error) ->
        $('#errorTemplate').tmpl error

    constructor: ->
        super

        Mysql.bind('ajaxError', Mysql.ajaxError)
        Mysql.bind('ajaxSuccess', Mysql.ajaxSuccess)
        Mysql.bind('refresh', @proxy @mysqlRefreshed)

        User.bind('save', @proxy @authorized)

        @dumpEl.bind('click', @proxy @submit)

        @routes
            '/item/:pid': (params) ->
                @showDetails params.pid
            '/*glob' : (params) ->

    renderInfo: (item) ->
        @timeInfoEl.html @timeInfoTemplate item

    renderError: (item) ->
        @timeInfoEl.html @errorTemplate item

    init: ->
        @localeSettings =
            'mysql-dump':
                processDefault: $(@dumpEl).html()
                processAsk: 'Datensicherung starten?\n\nFortfahren?'
                processBefore:'Sicherung läuft...'
                processDone: 'Gesichert'
                processFail: 'Fehler'
            'mysql-restore':
                processDefault: $(@restoreEl).html()
                processAsk: 'Soll diese Sicherung wiederhergestellt werden?'
                processBefore: 'Wiederherstellung läuft...'
                processDone: 'Wiederhergestellt'
                processFail: 'Fehler'

        Spine.Model.host = Settings.host = @host = @url
        @moreEl.attr('href', @url)
        @disableControl()
        @fetchToken @user

    fetchToken: (user) =>
        User.fetch()
        User.destroyAll()

        $.ajax
            url: @url + '/api/users/token'
            data: user
            type: 'POST'
            # beforeSend: @proxy @disableControl
            headers:
                Accept : 'application/json'
        .done( @doneResponse user )
        .fail( @failResponse user )

    doneResponse: (user) =>
        ( json, status, xhr ) =>
            @enableControl()
            user = $.extend(user, json.data)
            user = new User
                'username': user.username
                'token': json.data.token
            user.save()

    failResponse: ( user ) =>
        ( xhr, responseText, error ) =>
            @disableControl()
            @renderError xhr.responseJSON

    # bound to users save event
    authorized: ->
        token = @getToken()
        Mysql.url += token
        @loadSettings()

    getMysql: ( token = @getToken() ) =>
        Mysql.fetch
            headers:
                Accept: 'application/json'
                Authorization: 'Bearer '+token

    getToken: ->
        User.fetch()
        try
            user = User.first()
        catch e
            throw new Error 'There is no user to retrieve a token from'

        Model.token = user.token

    mysqlRefreshed: (items) ->
        if items.length
            item = items[0]
        else
            item = {}

        item = $.extend( item, {
            url: @url
            overdue: @overdue
        } )
        @renderInfo item
        @refreshElements()

    overdue: (item) =>
        clss = ''
        overdue = @settings.Overdue

        dateNow = new Date( Date.now() )
        dateTimestamp = new Date( parseInt( item.timestamp ))

        diff = Math.floor( dateNow.getTime() / 1000 ) - dateTimestamp.getTime()
        days = Math.round(diff / ( 60 * 60 * 24 ))

        if days >= ( if overdue.alert is true then 1 else  parseInt( overdue.alert ) )
            clss = 'info-alert'
            @el.addClass('notice-error').removeClass('notice-info notice-warning')
        else if days >= ( if overdue.warning is true then 1 else  parseInt( overdue.warning ) )
            clss = 'info-warning'
            @el.addClass('notice-warning').removeClass('notice-info notice-error')
        else
            @el.addClass('notice-info').removeClass('notice-warning notice-error')

        clss

    loadSettings: ->
        Settings.load
            done: @settingsDone
            fail: @settingsFail

    settingsDone: ( settings ) =>
        token = @getToken()
        @settings = settings
        @getMysql( token )
        @pingInterval = settings.Refresh.rate #ping interval in seconds
        @startPing()

    settingsFail: ->
        throw new Error 'Failed loading Settings'

    ask: (e) ->
        el = $(e.currentTarget)
        @dataType = type = el.data('type')

        res = (res = @localeSettings[type].processAsk) ? res : null;

        return unless res

        if (window.confirm(res))
            data = el.data()
            @submit(data);
        else
            alert("Vorgang wurde abgebrochen")

    submit: (e) =>
        el = $(e.currentTarget)
        @data = el.data()

        token = @getToken()

        $.ajax
            url: @host + @data.href + '?token=' + token
            type: 'POST'
            data:
                filename: 'test.sql'
                description: 'test description'
            beforeSend: @proxy @mysqlBeforeSend
            headers:
                Accept: 'application/json'
                Authorization: 'Bearer ' + token
        .done( @mysqlDone() )
        .fail( @mysqlFail() )

    mysqlBeforeSend: =>
        @disableControl @data.type
        buttonTextEl = $('[data-type='+@data.type+']')
        buttonTextEl.html( @localeSettings[@data.type].processBefore )
        @savingProgressEl.removeClass('hide')

    mysqlDone: ( settings ) =>
        (data, state, xhr) =>

            buttonTextEl = $('[data-type='+@data.type+']')
            buttonTextEl.html( @localeSettings[@data.type].processDone )
            @savingProgressEl.addClass('hide')
            func = =>
                @getMysql( @getToken() )
                buttonTextEl = $('[data-type='+@data.type+']').html( @localeSettings[@data.type].processDefault ).attr('disabled', false)
            @delay func, 2000

    mysqlFail: =>
        (xhr, state, responseText) =>
            buttonTextEl = $('[data-type='+@data.type+']')
            buttonTextEl.html( @localeSettings[@data.type].processFail + ': ' + responseText )
            func = ->
                buttonTextEl = $('[data-type='+@data.type+']').html( @localeSettings[@data.type].processDefault )
            @delay func, 2000

    disableControl: ( type = '' ) =>
        if type then type = type.replace /mysql-/, ''
        buttonTextEl = $('[data-type^=mysql-'+type+']').attr('disabled', 'disabled')

    enableControl: ( type = '' ) =>
        if type then type = type.replace /mysql-/, ''
        buttonTextEl = $('[data-type^=mysql-'+type+']').attr('disabled', false)

    logout: (e) ->
        e.preventDefault()
        e.stopPropagation()
        User.logout()

    resetFormControls: ->
        $('.has-success').removeClass('has-success')

    startPing: ->
        return unless @pingInterval

        @stopPing(@pingIntervalId) if @pingIntervalId
        @pingIntervalId = setInterval(@getMysql, @pingInterval*1000)

    stopPing: ->
        clearInterval(@pingIntervalId)

    change: (e) ->
        val = @optionsEl.val()

        @downloadEl.attr('disabled', !val).attr('data-fn', val)
        @restoreEl.attr('disabled', !val).attr('data-fn', val);

        if(val)
            @restoreEl.focus()
        else
            @dumpEl.focus()

    hidemodal: (e) =>
        @navigate '/'

    hiddenmodal: (e) =>
        @log 'hiddenmodal'

    showmodal: (e) =>
        @log 'showmodal'

    shownmodal: (e) =>
        @log 'shownmodal'

module.exports = App

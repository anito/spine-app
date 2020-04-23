Spine             = require("spine")
$                 = Spine.$
LoginView         = require("controllers/login_view")
LoaderView        = require("controllers/loader_view")
Settings          = require("models/settings")
Flash             = require("models/flash")
User              = require("models/user")

require('spine/lib/manager')

class Login extends Spine.Controller

    elements:
        'form'                      : 'form'
        '.flash'                    : 'flashEl'
        '.status'                   : 'statusEl'
        '#password'             : 'passwordEl'
        '#username'             : 'usernameEl'
        '#login .container'         : 'contentEl'
        '#login'                    : 'loginEl'
        '#loader'                   : 'loaderEl'
        '.guest'                    : 'btnGuest'
        '.message'                  : 'messages'
    
    events:
        'keypress'          : 'submitOnEnter'
        'click #guestLogin' : 'guestLogin'
        'click #cancel'     : 'cancel'

    flashTemplate: (item) ->
        $('#flashTemplate').tmpl item
    
    statusTemplate: (item) ->
        $('#statusTemplate').tmpl item
    
    logoTemplate: (item) ->
        $('#logoTemplate').tmpl item
    
    constructor: (form) ->
        super
        
        @loginView = new LoginView
            el: @loginEl
        @loaderView = new LoaderView
            el: @loaderEl
      
        Flash.fetch()
        flash = Flash.first() if Flash.count()
        @flash flash if flash
        Flash.destroyAll()
        
        @appManager = new Spine.Manager(@loginView, @loaderView)
        @loginView.trigger('active')
        
    init: ->
        User.fetch()
        User.destroyAll()
        Settings.fetch()
        Settings.destroyAll()

    render: (el, template) ->  
        el.html template
        @el
    
    submit: =>
        $.ajax
            data: @form.serialize()
            type: 'POST'
        .done( @doneResponse )
        .fail( @failResponse )
        .always( @completeResponse )
      
    completeResponse: (xhr) =>
        json = xhr.responseText
        @passwordEl.val('')
        @usernameEl.val('').focus()
    
    doneResponse: (data, status, xhr) =>
        json = $.parseJSON data

        User.fetch()
        User.destroyAll()
        user = new User @newAttributes(json.data)
        user.save()

        @flash
            message: json.flash
            statusText: xhr.statusText
            status: xhr.status

        fadeFunc = ->
            @contentEl.addClass('fade')
            @delay switchViewFunc, 500
        switchViewFunc = ->
            @loaderView.trigger('active')
            @delay redirectFunc, 2000
        redirectFunc = ->
            User.redirect()
            # User.redirect '/users'

        @delay fadeFunc, 1000

    failResponse: (xhr, status) =>
        json = $.parseJSON(xhr.responseText)

        @flash
            message: json.flash
            statusText: xhr.statusText
            status: xhr.status
    
    flash: (obj) ->
        @oldMessage = @flashEl.html() unless @oldMessage
        delayedFunc = -> @flashEl.html @oldMessage
        @render @flashEl, @flashTemplate obj
        # @render @statusEl, @statusTemplate obj
        # @delay delayedFunc, 2000
    
    newAttributes: (json) ->
        id: json.id
        username: json.username
        token: json.token
    
    cancel: (e) ->
        User.redirect()
        e.preventDefault()
    
    guestLogin: ->
        @usernameEl.val('guest')
        @passwordEl.val('guest')
        @submit()
    
    submitOnEnter: (e) ->
        return unless e.keyCode is 13
        @submit()
        e.preventDefault()
    
module?.exports = Login
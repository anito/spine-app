Spine = require("spine")
$     = Spine.$
User  = require('models/user')

class LoginView extends Spine.Controller

  elements:
    'button'              : 'logoutEl'

  events:
    'click .opt-logout'        : 'logout'
    'click .opt-trace'         : 'toggleTrace'
    
  constructor: ->
    super
    @bind('active', @proxy @active)
    
  active: ->
    
  template: ->
    $('#loginTemplate').tmpl
      user: User.first()
      trace: !Spine.isProduction
    
  logout: ->
    User.logout()
    
  toggleTrace: ->
    Spine.isProduction = localStorage.isProduction = (localStorage.isProduction == 'false')
    @render()
    if confirm('Entwickler Modus: ' + (if Spine.isProduction then 'Aus' else 'An') + '\n\Die Anwendung muss neu gestartet werden.\n\nFortfahren mit OK')
      $(window).off()
      User.redirect('logout')
    
  render: ->
    @html @template()

module?.exports = LoginView
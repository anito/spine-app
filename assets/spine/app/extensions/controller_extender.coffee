Spine       = require("spine")
$           = Spine.$
Controller  = Spine.Controller

Controller.Extender =
  
  extended: ->
    
    Extend = 
    
      empty: ->
        @log 'empty'
        @constructor.apply @, arguments
        
        
    Include = 
    
      init: ->
        
        @trace = !Spine.isProduction
        @logPrefix = '(' + @constructor.name + ')'
        
        @model   = if modelName = @el.data('modelName') then Model[modelName] else @parent?.model
        @models  = if modelsName = @el.data('modelsName') then Model[modelsName] else @parent?.models

      p: -> App.sidebar.products  
      
      humanize: (arr) ->
        arr = [arr] unless Array.isArray arr
        throw 'nothing to humanize' unless arr.length
        record = arr[0]
        plural = arr.length > 1
        
        plural: plural
        length: arr.length
        type: record.constructor['humanName'+ if (p = plural) then 's' else '']()
        name: record.n()
      
      emptyMessage: (name) -> name
      
      followLink: (e) ->
        strWindowFeatures = "menubar=no,location=no,resizable=no,scrollbars=yes,status=no"
        window.open($(e.target).closest('a').attr('href'), 'new')
        e.preventDefault()
        e.stopPropagation()
        
      exposeSelection: (selection=@model.selectionList()) ->
        @log 'exposing'
        @deselect()
        
        for id in selection
          el = $('#'+id, @el)
          el.addClass("active")

        if first = selection.first()
          $('#'+first, @el).addClass("hot")
        
      createImage: (url, onload) ->
        img = new Image()
        img.onload = onload if onload
        img.src = url if url
        img
        
      eql: ->
        c = @current?.model.className
        p = @previous?.model.className
        !!(c is p)
        
      eql_: ->
        rec = @model.record
        prev = @current
        @current = rec
        !!(@current?.eql?(prev) and !!prev)
  
      activated: ->
  
      testEmpty: ->
        if @model.record
          unless @model.record.contains()
            @renderEmpty()
      
      renderEmpty: (s='nichts zu melden', element='el') ->
        info = '<label class="invite"><span class="enlightened">'+@emptyMessage(s)+'</span></label>'
        @[element].html $("#noSelectionTemplate").tmpl({type: info || ''})
        @el
  
      wipe: (item) ->
        if @model.record
          first = @model.record.contains() is 1
        @el.empty() if first
        @el
  
      focusFirstInput: (el=@el) ->
        return unless el
        $('input', el).first().focus().select() if el.is(':visible')
        el

      focus: ->
        @el.focus()

      panelIsActive: (controller) ->
        App[controller].isActive()
        
      openPanel: (controller) ->
        ui = App.vmanager.externalUI(App[controller])
        ui.click()

      closePanel: (controller, target) ->
        App[controller].activate()
        ui = App.vmanager.externalUI(App[controller])
        ui.click()

      isMeta: (e) ->
        e?.metaKey or e?.ctrlKey or e?.altKey

      children: (sel) ->
        @el.children(sel)
        
      find: (sel) ->
        @el.find(sel)

      remove: (item) ->
        els = @el.find('.items')
        el = els.children().forItem(item)
        return unless el.length
        
        el.addClass('out').removeClass('in')
        f = ->
          el.detach()
          @trigger('detached', item)
        @delay f, 400

      deselect: (args...) ->
        @el.deselect(args...)
        
      clearSelection: (e) ->
        @deselect()
        @select e, []
        
      sortable: (type) ->
        @el.sortable type
        
      findModelElement: (item) ->
        @children().forItem(item, true)
        
      noMethod: (e) ->
        e.stopPropagation()
        e.preventDefault()
        
    @extend Extend
    @include Include

module?.exports = Controller.Extender
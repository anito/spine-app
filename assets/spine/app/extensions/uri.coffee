Spine   = require("spine")
$       = Spine.$
Model   = Spine.Model
Category = require('models/category')
Product   = require('models/product')
Photo   = require('models/photo')
ProductsPhoto    = require('models/products_photo')
CategoriesProduct = require('models/categories_product')

Ajax =

  enabled:  true
  cache: true
  pending:  false
  requests: []
  
  requestNext: ->
    next = @requests.shift()
    if next
      @request(next)
    else
      @pending = false
      Spine.trigger('uri:alldone')

  request: (callback) ->
    (do callback).complete(=> do @requestNext)
      
  queue: (callback) ->
    return unless @enabled
    if @pending
      @requests.push(callback)
    else
      @pending = true
      @request(callback)    
    callback
    
class Base
  defaults:
    contentType: 'application/json'
    processData: false
    headers: {'X-Requested-With': 'XMLHttpRequest'}
    dataType: 'json'
  
  ajax: (params, defaults) ->
    $.ajax($.extend({}, @defaults, defaults, params))
  
  ajaxQueue: (callback) ->
    Ajax.queue(callback)
    
  get: ->
    @ajaxQueue =>
      @ajax(
        type: "POST"
        url: base_url + 'photos/uri/' + @atts
        data: JSON.stringify(@data)
      ).done(@recordResponse)
       .fail(@failResponse)
       
  uri: (options) ->
    options.width + '/' + options.height + '/' + options.square + '/' + options.quality
    
class URI extends Base

  constructor: (@model,  params, @callback, @data = []) ->
    super
    options = $.extend({}, @settings, params)
    @atts = @uri options
    
    return unless @data.length
    
  settings:
    square: 1
    quality: 70
    
  init: ->
    @get() unless @cache()
    
  cache: ->
    return unless Ajax.cache #force ajax call for empty data
    res = []
    for data, idx in @data
      raw = (@model.cache @atts, data.id)
      if raw
        res.push raw
      else
        return
      
    @callback res
    return true
      
  recordResponse: (uris) =>
    @model.addToCache @atts, uris
    @callback uris
    
  failResponse: (xhr, statusText, error) =>
    @model.trigger('ajaxError', xhr, statusText, error)

class URICollection extends Base

  constructor: (@record, params, mode, @callback, max) ->
    super
    type = @record.constructor.className
    switch type
      when 'Product'
        # get all photos of the product
        photos = ProductsPhoto.photos(@record.id)
        max = max or photos.length
        @mode = mode
        @photos = photos[0...max]
      when 'Photo'
        @photos = [@record]
        
    options = $.extend({}, @settings, params)
    @atts = @uri options
    
  settings:
    width: 140
    height: 140
    square: 1
    quality: 70
  
  init: ->
    cache = @record.cache @atts
    if cache?.length
      @callback cache, @record
    else
      @get()
      
  all: ->
    @ajaxQueue =>
      @ajax(
        type: "POST"
        url: base_url + 'photos/uri/' + @atts
        data: JSON.stringify(@photos)
      ).done(@recordResponse)
       .fail(@failResponse)

  recordResponse: (uris) =>
    @record.addToCache @atts, uris, @mode
    @callback uris, @record
    
  failResponse: (xhr, statusText, error) =>
    @record.trigger('ajaxError', xhr, statusText, error)
  
Uri =
  
  extended: ->
    
    Include =
      uri: (params, mode, callback, max) -> new URICollection(@, params, mode, callback, max).init()
      
    Extend =
      uri: (params, callback, data) -> new URI(@, params, callback, data).init()
      
    @include Include
    @extend Extend

Uri.Ajax = Ajax
module?.exports = Model.Uri = Uri
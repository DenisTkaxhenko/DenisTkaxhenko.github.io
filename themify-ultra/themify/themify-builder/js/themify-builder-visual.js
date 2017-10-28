var ThemifyLiveStyling;
(function($){

	'use strict';

	var api = themifybuilderapp;

	api.mode = 'visual';
	api.Frontend = {
		slidePanelOpen: true,
		hideModulesControl: function() {
                    $('.themify_builder_module_front_overlay,.themify_builder_dropdown_front').hide();
		},
		hideColumnsBorder: function() {
			//$('.themify_builder_col').css('border', 'none');
		},
		showColumnsBorder: function() {
		   // $('.themify_builder_col').css('border', '');
		},
		responsiveFrame: {
			$el: null,
			contentWindow: null,
			init: function() {
				this.$el = $('#themify_builder_site_canvas_iframe').contents();
				this.contentWindow = document.getElementById('themify_builder_site_canvas_iframe').contentWindow;
				var $frame = this.$el,
					eventToUse = 'true' == themifyBuilder.isTouch ? 'touchend' : 'mouseenter mouseleave';

				$('body').addClass('builder-active-breakpoint--' + api.activeBreakPoint );

				$frame.on(eventToUse, '.themify_builder_module_front', function(event) {
						api.Views.Modules.visual.prototype.modHover(event);
					})
					.on('click', '.themify_builder_option_column,.js--themify_builder_module_styling,.themify_builder_style_row,.themify_builder_style_subrow', function(event) {
						event.preventDefault();
						var cid = $(this).closest('[data-cid]').data('cid'),
							$selector = $('[data-cid="'+ cid +'"]');

						if ( $(this).hasClass('js--themify_builder_module_styling') ) {
							$selector.children('.module_menu_front').find('.js--themify_builder_module_styling').trigger('click');
						} else if ( $(this).hasClass('themify_builder_style_row') ) {
							$selector.children('.themify_builder_row_top').find('.themify_builder_style_row').trigger('click');
						}
						else if ( $(this).hasClass('themify_builder_style_subrow') ) {
							$selector.children('.themify_builder_sub_row_top').find('.themify_builder_style_subrow').trigger('click');
						} else if ( $(this).hasClass('themify_builder_option_column') ) {
							$selector.children('.themify_builder_column_action').find('.themify_builder_option_column').trigger('click');
						}
					})
					.on('dblclick', '.themify_builder .active_module', function(event){
						event.preventDefault();
						var cid = $(this).data('cid'),
							$selector = $('[data-cid="'+ cid +'"]');

						$selector.children('.module_menu_front').find('.js--themify_builder_module_styling').trigger('click');
					})
                                        .on('click', '.themify_builder_grid_list_wrapper .grid_tabs li a', api.Views.Rows.default.prototype._switchGridTabs)
                                        .on('click', '.themify_builder_grid_list li a',api.Views.Rows.default.prototype._gridMenuClicked)
                                        .on('click', '.themify_builder_column_direction li a',api.Views.Rows.default.prototype._columnDirectionMenuClicked);

				$('body').on('builder_toggle_frontend', this.toggleBuilder);
				// Enable iframe scrolling
				this.doIframeScrolling();
			},
			syncTimeout: null,
			doSync: function() {
				if ('desktop' !== api.activeBreakPoint) {
					clearTimeout(this.syncTimeout);
					this.syncTimeout = setTimeout(function(){
						api.Frontend.responsiveFrame.sync();
					}, 300);
				}
			},
			sync: function(reverse) {
				reverse = reverse || false;
				if (reverse) {
                                        var builder = $('.themify_builder_content').not('.not_editable_builder');
					api.Frontend.responsiveFrame.$el.find('.themify_builder_content').not('.not_editable_builder').each(function(i) {
                                            builder.get(i).innerHTML = this.innerHTML;
                                            var builder_el = builder.eq(i);
                                            $(this).find('.themify_builder_row,.themify_builder_col,.themify_builder_module_front,.themify_builder_sub_row').each(function(){
                                                var cid = $(this).data('cid'),
                                                    trigger = 'module',
                                                    model = api.Models.Registry.lookup( cid );
                                                if(model){
                                                    var el = builder_el.find('[data-cid="'+cid+'"]');
                                                    if($(this).hasClass('themify_builder_row')){
                                                        trigger = 'row';
                                                        api.Utils.columnDrag(el,true);
                                                    }
                                                    else if($(this).hasClass('themify_builder_col')){
                                                        trigger = 'column';
                                                    }
                                                    else if($(this).hasClass('themify_builder_sub_row')){
                                                        trigger = 'subrow';
                                                        api.Utils.columnDrag(el,true);
                                                    }
                                                    model.trigger('custom:'+trigger+':update',el);
                                                }
                                            });
                                           
					});
                                        api.vent.trigger('dom:builder:change');
				} else {
					$('.themify_builder_content').not('.not_editable_builder').each(function(i) {
						var $clonedHTML = $(this.outerHTML);
						$clonedHTML.find('.themify_builder_slider').each(function(){
							$(this).data('themify_slider_ready', false);
							if ( $(this).closest('.caroufredsel_wrapper').length>0 ) {
								$(this).unwrap();
							}
							if ( $(this).closest('.carousel-wrap').length>0 ) {
								$(this).unwrap();
							}
							$(this).attr('style', '');
						});
						api.Frontend.responsiveFrame.$el.find('.themify_builder_content').not('.not_editable_builder').get(i).innerHTML = $clonedHTML.html();
					});
				}
				this.syncStyleSheet();
				api.Frontend.responsiveFrame.contentWindow.ThemifyBuilderModuleJs.isResponsiveFrame = !reverse;
                                if(reverse){
                                    ThemifyBuilderModuleJs.bindEvents();
                                }
                                else{
                                    api.Frontend.responsiveFrame.contentWindow.ThemifyBuilderModuleJs.bindEvents();
                                }
			},
			toggleBuilder: function(event, is_edit) {
				if (is_edit) {
					$('.themify_builder_module_panel').removeClass('themify_builder_panel_state_inline');
					$('.themify_builder_responsive_switcher_item .themify_builder_popup_menu_wrapper')
						.find('.breakpoint-desktop').parent().addClass('selected').siblings().removeClass('selected');
					$('.themify_builder_front_panel').removeClass('builder-disable-module-draggable');
					$('.themify_builder_module_panel .themify_builder_module').draggable('enable');
					api.Frontend.responsiveFrame.doIframeScrolling();
				} else {
					$('.themify_builder_site_canvas, .themify_builder_workspace_container').removeAttr('style');
					$(window).off('scroll.themifybuilderresponsive');
				}
			},
			doIframeScrolling: function() {
				// Sync iframe scrolling with parent window.
				var $window = $(window);
				$window.on('scroll.themifybuilderresponsive', function(){ 
					api.Frontend.responsiveFrame.contentWindow.scrollTo(0, $window.scrollTop());
				});
			},
			syncStyleSheet: function() {
				var cssString = api.styleSheet.getCSSText(),
					style = document.createElement("style");
					
				style.type = "text/css";
				style.id = api.styleSheet.name;
				try {
					style.innerHTML = cssString;
				}
				catch (ex) {
					style.styleSheet.cssText = cssString;  // IE8 and earlier
				}

				// remove previous style
				if ( this.contentWindow.document.getElementById( style.id ) ) {
					this.contentWindow.document.getElementById( style.id ).remove();
				}

				this.contentWindow.document.getElementsByTagName("head")[0].appendChild(style);
			}
		}
	};

	api.styleSheet = {
		name: 'themify-builder-component-customize',
		head: document.getElementsByTagName("head")[0],
		styleRegistry: {},
		destroy: function() {
			this.styleRegistry = {};
			if ( document.getElementById( this.name ) ) {
				document.getElementById( this.name ).remove();
			}
		},
		getSheetInstance: function() {
                    return  document.getElementById( this.name )?document.getElementById( this.name ).sheet:null;
		},
		setStyles: function( $selector, styles, device ) {
			device = device || api.activeBreakPoint;
			var media = '';

			if ( 'desktop' !== device ) {
				media = 'screen and (max-width: '+ api.Utils.getBPWidth( device ) +'px)';
			}

			if ( 'undefined' === typeof $selector ) return true;

			var rule = this.getRules( $selector, media );
			if ( ! _.isUndefined( rule ) ) {
				for (var st in styles) {
					
					if (styles[st] && styles[st]!=='#'&& styles[st]!=='url("")') {
						rule.style[ st ] = styles[st];
					} else {
						rule.style[ st ] = '';
					}
				}
			} else {
				var $inline = '';
				for (var st in styles) {
					if (styles[st] && styles[st]!=='#' && styles[st]!=='url("")') {
						$inline += st + ':' + styles[st] + ';';
					}
				}
				var sheet = this.getMediaRules(),
					newRule = $selector + '{'+ $inline +'}',
					length = Math.max( sheet.cssRules.length - 3, 0 );

				if ( '' !== media ) {
					var new_sheet = this.getMediaRules( media );
					if ( _.isUndefined( new_sheet ) ) {
						newRule = '@media ' + media + ' { '+ newRule +' }';
						length = sheet.cssRules.length;

						// desktop, tablet_landscape, tablet, mobile
						if ( sheet.cssRules.length && sheet.cssRules[ sheet.cssRules.length - 1 ].media ) {
							
							if ( 'tablet_landscape' === device ) {
								var check_tablet = this.getMediaRules('screen and (max-width: '+ api.Utils.getBPWidth('tablet') +'px)',true);
								if ( ! _.isUndefined( check_tablet ) ) {
									length = check_tablet;
								} else {
									var check_mobile = this.getMediaRules('screen and (max-width: '+ api.Utils.getBPWidth('mobile') +'px)',true);
									if ( ! _.isUndefined( check_mobile ) ) 
										length = check_mobile;
								}
							}

							if ( 'tablet' === device ) {
								var check_tablet_ls = this.getMediaRules('screen and (max-width: '+ api.Utils.getBPWidth('tablet_landscape') +'px)',true);
								if ( ! _.isUndefined( check_tablet_ls ) ) {
									length = check_tablet_ls + 1;
								} else {
									var check_mobile = this.getMediaRules('screen and (max-width: '+ api.Utils.getBPWidth('mobile') +'px)',true);
									if ( ! _.isUndefined( check_mobile ) ) 
										length = check_mobile;
								}
							}
						}

					} else {
						sheet = new_sheet;
						length = new_sheet.cssRules.length;
					}
				}
				sheet.insertRule( newRule, length );
			}
		},
		getRules: function( searchrule, media ) {
			media = media || '';

			if ( $('#'+ this.name).length === 0 ) {
				$('head').append('<style id="'+ this.name +'"></style>'); // create empty stylesheet
			}

			var mysheet= $('#' + this.name ).get(0).sheet,
				myrules=mysheet.cssRules? mysheet.cssRules: mysheet.rules,
				targetrule;

			if ( '' === media ) {
				for (var i=0; i<myrules.length; i++){
					if( ! _.isUndefined( myrules[i].selectorText ) && myrules[i].selectorText.toLowerCase() === searchrule ){
						targetrule = myrules[i];
						break;
					}
				}
			} else {
                                var rlength = myrules.length;
				for (var i=0; i<rlength; i++){
					if ( myrules[i].media && myrules[i].cssText.indexOf(media) > -1 ) {
						var mediaRules = myrules[i].cssRules ? myrules[i].cssRules : myrules[i].rules,
                                                    mlength = mediaRules.length;
						for (var ii = 0; ii < mlength; ii++) {
							if ( ! _.isUndefined( mediaRules[ii].selectorText ) && mediaRules[ii].selectorText.toLowerCase() === searchrule ) {
								targetrule = mediaRules[ii];
								break;
							}
						};
						break;
					}
				}
			}
			return targetrule;
		},
		getMediaRules: function( media,index ) {
			media = media || '';
			var mysheet= $('#' + this.name).get(0).sheet;

			if ( '' === media ) return mysheet;

			var myrules = mysheet.cssRules? mysheet.cssRules: mysheet.rules, targetrule;

			if ( '' !== media ) {
                                var length = myrules.length;
				for (var i=0; i< length; i++){
					if ( myrules[i].media && myrules[i].cssText.indexOf(media) > -1 ) {
						targetrule = index?i:myrules[i];
						break;
					}
				}
				return targetrule;
			}
		},
		tempRules: null,
		rememberRules: function() {
			var cssString = this.getCSSText();
			if ( '' !== cssString ) this.tempRules = cssString;
		},
		revertRules: function() {
			if ( ! _.isNull( this.tempRules ) ) {
				this.replaceCSSText( this.tempRules );
			}
			this.tempRules = null;
		},
		getCSSText: function() {
			var cssString = "",
				stylesheet = this.getSheetInstance();

			if ( ! _.isNull( stylesheet ) && stylesheet.cssRules) {
				var cssRules = stylesheet.cssRules;
				for (var j = 0, countJ = cssRules.length; j < countJ; ++j)
					cssString += cssRules[j].cssText;
			}
			else if ( ! _.isNull( stylesheet ) )
				cssString += stylesheet.cssText;  // IE8 and earlier

			return cssString;
		},
		replaceCSSText: function( value ) {
			if ( document.getElementById( this.name ) ) {
				document.getElementById( this.name ).innerHTML = value;
			}
		}
	};

	api.Mixins.Frontend = {
		render_visual: function() {
			// collect all jobs
			var constructData = [],
				cid = this.$el.data('cid'),
				model = api.Models.Registry.lookup( cid );

			if ( model ) constructData.push({ jobID: cid, data: model.toJSON() });
			this.$el.find('[data-cid]').each(function(){
				cid = $(this).data('cid');
				model = api.Models.Registry.lookup( cid );
				if ( model ) constructData.push({ jobID: cid, data: model.toJSON() });
			});

			return api.render_element( constructData );
		},
		change_callback: function() {
			console.log('change_callback');
			var that = this,
				$container = this.$el.closest('[data-postid]');
			that.$el.addClass('themify-builder-component-preview-loading');
			this.render_visual().done(function(){
				that.$el.removeClass('themify-builder-component-preview-loading');	
				api.Utils.loadContentJs();
				
				// Hook
				$('body').trigger('builder_load_module_partial', that.$el);
				
				api.vent.trigger('dom:builder:change');

				if ( _.isEmpty( ThemifyBuilderCommon.undoManager.getStartData() ) ) {
					api.vent.trigger('dom:observer:end', $container);
				} else {
					api.vent.trigger('dom:observer:end', $container, { cid: that.model.cid, value: that.model.toJSON() } );
				}
			});
		},
		render_stylesheet: function(cid, breakpoints) {
			var prefix = '.themify_builder .tb_element_cid_' + cid,
				model = api.Models.Registry.lookup( cid ),
				type = model.get('elType'),
				sep = ' ';

				if ( 'module' !== type ) {
					sep = '';
				}

			_.each( breakpoints, function( styles, device ) {

				_.each( styles, function( rules, selector ) {
					api.styleSheet.setStyles( prefix + sep + selector.trim(), rules, device );
				});

			});
		}
	};

	_.extend( api.Views.BaseElement.prototype, api.Mixins.Frontend );

	api.Views.register_row( 'visual', {
		template: wp.template('builder_visual_row_item'),
		initialize: function() {
			this.listenTo(this.model, 'create:element', this.createEl);
			this.listenTo(this.model, 'custom:change', this.change_callback);
			this.listenTo(this, 'component:duplicate', this.change_callback);
			this.listenTo(this.model, 'custom:restorehtml', this.restoreHtml);
		},
		createEl: function( markup, styles ) {
			var $rawHtml = $(markup).filter('.themify_builder_row');
			$rawHtml.children('.builder_row_cover').insertAfter( this.$el.children('.themify_builder_row_top') );
			this.$el.attr('class', $rawHtml.attr('class')).addClass( _.result(this, 'attributes').class );
			if ( this.$el.children('.row-slider').length>0 ) {
				this.$el.children('.row-slider').replaceWith( $rawHtml.children('.row-slider') );
			} else {
				this.$el.prepend($rawHtml.children('.row-slider'));
			}

			if ( _.isObject( styles ) && _.keys(styles).length>0 ) {
				this.render_stylesheet( this.model.cid, styles );
			}
			$(document).trigger('tb:row:create', [this.$el, $rawHtml]);
		},
		restoreHtml: function( rememberedEl ) {
			var $currentEl = $('.current_selected_row');
			if ( $currentEl.length>0 ) {
				var $columns = $currentEl.children('.row_inner_wrapper').children('.row_inner').children('.themify_builder_row_content').detach(),
					$rememberedEl = $(rememberedEl);

				$rememberedEl.find('.themify_builder_row_content').remove(); // don't need this
				$currentEl[0].outerHTML = $rememberedEl[0].outerHTML;
				this.setElement( $('.current_selected_row') );
				$('.current_selected_row').children('.row_inner_wrapper').children('.row_inner').append($columns);
			}
		}
	});

	api.Views.register_subrow( 'visual', {
		template: wp.template('builder_visual_sub_row_item'),
		initialize: function() {
			this.listenTo(this.model, 'create:element', this.createEl);
			this.listenTo(this.model, 'custom:change', this.change_callback);
			this.listenTo(this, 'component:duplicate', this.change_callback);
			this.listenTo(this.model, 'custom:restorehtml', this.restoreHtml);
		},
		createEl: function( markup, styles ) {
			var $rawHtml = $(markup).filter('.themify_builder_sub_row');
			$rawHtml.children('.builder_row_cover').insertAfter( this.$el.children('.themify_builder_sub_row_top') );
			this.$el.attr('class', $rawHtml.attr('class')).addClass( _.result(this, 'attributes').class );
			if ( this.$el.children('.sub_row-slider').length>0 ) {
				this.$el.children('.sub_row-slider').replaceWith( $rawHtml.children('.sub_row-slider') );
			} else {
				this.$el.prepend($rawHtml.children('.sub_row-slider'));
			}

			if ( _.isObject( styles ) && _.keys(styles).length ) {
				this.render_stylesheet( this.model.cid, styles );
			}
		},
		restoreHtml: function( rememberedEl ) {
			var $currentEl = $('.current_selected_sub_row');
			if ( $currentEl.length>0 ) {
				var $columns = $currentEl.children('.sub_row_inner_wrapper').children('.themify_builder_sub_row_content').detach(),
					$rememberedEl = $(rememberedEl);

				$rememberedEl.children('.sub_row_inner_wrapper').children('.themify_builder_sub_row_content').remove(); // don't need this
				$currentEl[0].outerHTML = $rememberedEl[0].outerHTML;
				this.setElement( $('.current_selected_sub_row') );
				$('.current_selected_sub_row').children('.sub_row_inner_wrapper').append($columns);
			}
		}
	});

	api.Views.register_column( 'visual', {
		template: wp.template('builder_visual_column_item'),
		attributes: function() {
			var classes = 'column' === this.model.get('component_name') ? 'module_column module_column_' + this.model.cid : 'sub_column sub_column_' + this.model.cid;
			return {
				'class' : 'themify_builder_col ' + classes + ' ' + this.model.get('grid_class') + ' tb_element_cid_' + this.model.cid,
				'style' : 'width:' + this.model.get('grid_width') + '%',
				'data-cid' : this.model.cid
			};
		},
		initialize: function() {
			this.listenTo(this.model, 'create:element', this.createEl);
			this.listenTo(this.model, 'custom:change', this.change_callback);
			this.listenTo(this.model, 'custom:restorehtml', this.restoreHtml);
		},
		createEl: function( markup, styles ) {
			var $rawHtml = $(markup);
			$rawHtml.find('style').remove(); // remove style

			this.$el.children('.tb-column-inner').prepend( $rawHtml.find('.tb-column-inner').html().replace(/&nbsp;/gi,'') );
			this.$el.addClass( $rawHtml.attr('class').replace('empty-column', '') );
			if ( this.$el.children('.builder_row_cover').length>0 ) {
				this.$el.children('.builder_row_cover').replaceWith( $rawHtml.children('.builder_row_cover') );
			} else {
				this.$el.prepend($rawHtml.children('.builder_row_cover'));
			}
			if ( this.$el.children('.col-slider').length >0) {
				this.$el.children('.col-slider').replaceWith( $rawHtml.children('.col-slider') );
			} else {
				this.$el.prepend($rawHtml.children('.col-slider'));
			}

			if ( _.isObject( styles ) && _.keys(styles).length ) {
				this.render_stylesheet( this.model.cid, styles );
			}
		},
		restoreHtml: function( rememberedEl ) {
			var $currentEl = $('.current_selected_column');
			if ( $currentEl.length>0 ) {
				var $modules = $currentEl.children('.tb-column-inner').children('.themify_module_holder').detach(),
					$rememberedEl = $(rememberedEl);

				$rememberedEl.find('.themify_module_holder').remove(); // don't need this
				$currentEl[0].outerHTML = $rememberedEl[0].outerHTML;
				this.setElement( $('.current_selected_column') );
				$('.current_selected_column').children('.tb-column-inner').append($modules);
			}
		}
	});

	api.Views.register_module( 'visual', {
		draggedNotTapped : false,
		template: wp.template('builder_visual_module_item'),
		templateVisual: function( settings ){
			var tpl = wp.template('builder-' + this.model.get('mod_name') + '-content');
			return tpl( settings );
		},
		attributes: function() {
			return {
				'class' : 'themify_builder_module_front module-' + this.model.get('mod_name') +' active_module clearfix tb_element_cid_' + this.model.cid,
				'data-mod-name' : this.model.get('mod_name'),
				'data-cid' : this.model.cid
			};
		},
		events: {
			'hover' : 'modHover',
		},
		initialize: function() {
			this.listenTo(this.model, 'create:element', this.createEl);
			this.listenTo(this.model, 'custom:change', this.change_callback);
			this.listenTo(this, 'component:duplicate', this.change_callback);
			this.listenTo(this.model, 'custom:restoredata', this.restoreData);
			this.listenTo(this.model, 'custom:restorehtml', this.restoreHtml);
			this.listenTo(this.model, 'custom:preview:live', this.previewLive);
			this.listenTo(this.model, 'custom:preview:sc', _.debounce(this.previewShortcode, 400));
			this.listenTo(this.model, 'custom:preview:live', _.debounce( this.previewLiveCallback, 500 ));
			this.listenTo(this.model, 'custom:preview:reload', this.previewReload);
			this.listenTo(this, 'custom:preview:init', this.previewInit);
		},
		createEl: function( markup, styles ) {
			if( false === markup ) markup = themifyBuilder.i18n.excludedModule;
			if ( this.$('.module').length>0) {
				this.$('.module').replaceWith( markup );
			} else {
				this.el.insertAdjacentHTML('beforeend', markup);
			}
			this.$el.children('style').remove(); // temporary fix unwanted inline style

			if ( _.isObject( styles ) && _.keys(styles).length ) {
				this.render_stylesheet( this.model.cid, styles );
			}
		},
		previewLive: function( data ) {
			if ( this._jqueryXhr && 4 !== this._jqueryXhr ) {
				this._jqueryXhr.abort();
			}

			if ( this._shortcodeXhr && 4 !== this._shortcodeXhr ) {
				this._shortcodeXhr.abort();
			}

			var tmpl = this.templateVisual(data),
				sc_render = api.Utils.shortcodeToHTML(tmpl);

			if ( ! _.isEmpty( sc_render.tags ) ) {
				tmpl = sc_render.content;
			}

			if ( this.$('.module').length>0) {
				this.$('.module').replaceWith( tmpl );
			} else {
				this.el.insertAdjacentHTML('beforeend', tmpl);
			}
			this.$el.children('style').remove(); // temporary fix unwanted inline style
			api.liveStylingInstance.$liveStyledElmt = this.$('.module');

			if ( ! _.isEmpty( sc_render.tags ) ) {
				api.activeModel.trigger('custom:preview:sc', sc_render.tags);
			}
		},
		previewLiveCallback: function() {
			console.log('previewLiveCallback');
			api.Utils.loadContentJs();

			// Hook
			$('body').trigger('builder_load_module_partial', this.$el);
		},
		previewInit: function() {
			api.activeModel.trigger('custom:preview:reload');
		},
		previewReload: function() {
			var that = this;

			if ( this._jqueryXhr && 4 !== this._jqueryXhr ) {
				this._jqueryXhr.abort();
			}

			if ( this._shortcodeXhr && 4 !== this._shortcodeXhr ) {
				this._shortcodeXhr.abort();
			}
			
			this._jqueryXhr = $.ajax({
				type: "POST",
				url: themifyBuilder.ajaxurl,
				dataType: 'json',
				data: {
					action: 'tfb_load_module_partial',
					tfb_post_id: this.$el.closest('.themify_builder_content').data('postid'),
					tfb_cid: this.model.cid,
					tfb_module_slug: this.model.get('mod_name'),
					tfb_module_data: JSON.stringify(this.model.getPreviewSettings()),
					tfb_load_nonce: themifyBuilder.tfb_load_nonce
				},
				beforeSend: function(xhr) {
					that.$el.addClass('themify-builder-component-preview-loading');
				},
				success: function(data) {
					
					that.createEl( data.html );
					api.liveStylingInstance.$liveStyledElmt = that.$('.module');

					// Load google font style
					if ('undefined' !== typeof WebFont && data.gfonts.length > 0) {
						WebFont.load({
							google: {
								families: data.gfonts
							}
						});
					}

					api.Utils.loadContentJs(that.$el);

					api.Frontend.responsiveFrame.doSync();
					that.$el.removeClass('themify-builder-component-preview-loading');

					// Hook
					$('body').trigger('builder_load_module_partial', that.$el);
				},
				error: function() {
					that.$el.removeClass('themify-builder-component-preview-loading');
				}
			});

			return this;
		},

		previewShortcode: function( tags ) {
			var that = this;

			if ( this._shortcodeXhr && 4 !== this._shortcodeXhr ) {
				this._shortcodeXhr.abort();
			}
			
			this._shortcodeXhr = $.ajax({
				type: "POST",
				url: themifyBuilder.ajaxurl,
				dataType: 'json',
				data: {
					action: 'tfb_render_element_shortcode',
					shortcode_data: JSON.stringify(tags),
					tfb_load_nonce: themifyBuilder.tfb_load_nonce
				},
				success: function(data) {
					if ( data.success ) {
						_.each( data.data, function( shortcode ) {
							$('[data-rendered-sc="'+ shortcode.key +'"]').replaceWith( shortcode.rendered_html );

							api.Utils.loadContentJs(that.$el);
							api.Frontend.responsiveFrame.doSync();
							
							// Hook
							$('body').trigger('builder_load_module_partial', that.$el);
						});
					}
				}
			});
		},

		modHover: function(e) {
			var $this = $(e.currentTarget);
			if ('touchend' === e.type) {
				if (!this.draggedNotTapped) {
					this.draggedNotTapped = false;
					var $row = $this.closest('.themify_builder_row'),
						$col = $this.closest('.themify_builder_col'),
						$mod = $this.closest('.themify_builder_module'),
						index = 'row_' + $row.index();
					if ($col.length > 0) {
						index += '_col_' + $col.index();
					}
					if ($mod.length > 0) {
						index += '_mod_' + $mod.index();
					}
					if (this.menuTouched[index]) {
						$this.find('.themify_builder_dropdown_front').stop(false, true).css('z-index', '').hide();
						$this.find('.themify_builder_dropdown_front ul').stop(false, true).hide();
						$this.find('.themify_builder_dropdown').stop(false, true).hide();
						this.menuTouched = [];
					} else {
						var $builderCont = $('.themify_builder_content');
						$builderCont.find('.themify_builder_dropdown_front').stop(false, true).css('z-index', '').hide();
						$builderCont.find('.themify_builder_dropdown_front ul').stop(false, true).hide();
						$builderCont.find('.themify_builder_dropdown').stop(false, true).hide();
						$this.find('.themify_builder_dropdown_front').stop(false, true).css('z-index', '999').show();
						$this.find('.themify_builder_dropdown_front ul').stop(false, true).show();
						this.menuTouched = [];
						this.menuTouched[index] = true;
					}
				}
			} else if (e.type === 'mouseenter') {
				$this.find('.themify_builder_module_front_overlay').stop(false, true).show();
				$this.find('.themify_builder_dropdown_front').stop(false, true).show();
			} else if (e.type === 'mouseleave') {
				$this.find('.themify_builder_module_front_overlay').stop(false, true).hide();
				$this.find('.themify_builder_dropdown_front').stop(false, true).hide();
			}
		},
		tempData: null,
		restoreData: function() {
			this.tempData = this.model.get('mod_settings');
		},
		restoreHtml: function( rememberedEl ) {
			var $currentEl = $('.current_selected_module');
			if ( $currentEl.length ) {
				$currentEl[0].outerHTML = rememberedEl;
				this.setElement( $('.current_selected_module') );
				this.model.set({mod_settings: this.tempData }, {silent: true });
				this.tempData = null;
				this.$el.removeClass('themify-builder-component-preview-loading');
			}
		}
	});

	api.bootstrap = function(callback) {
		// collect all jobs
		var jobs = [];
		_.each( api.Models.Registry.items, function( model, cid ){
			var data = model.toJSON();
			if ( 'column' === data.elType && _.has( data, 'modules' ) ) delete data.modules;
			if ( 'row' === data.elType && _.has( data, 'cols' ) ) delete data.cols;
			if ( 'module' === data.elType && _.has( data, 'cols') ) delete data.cols;
			jobs.push({ jobID: cid, data: data });
		});

		this.batch_rendering(jobs, 0, 180, callback);
	};

	api.batch_rendering = function( jobs, current, size, callback ) {
		if(current >= jobs.length){
			// load callback
			if ($.isFunction(callback)) {
				callback.call(this);
			}
			return;
		}else{
			var smallerJobs = jobs.slice(current, current+size);
			this.render_element(smallerJobs).done(function(){
				api.batch_rendering(jobs, current += size, size, callback);
			});
		}
	};

	api.render_element = function( constructData ) {
		// send json data
		return $.ajax({
			type: "POST",
			url: themifyBuilder.ajaxurl,
			dataType: 'json',
			data: {
				action: 'tfb_render_element',
				batch: JSON.stringify(constructData),
				tfb_load_nonce: themifyBuilder.tfb_load_nonce
			},
			success: function(data) {
				_.each( data, function( obj, cid ) {
					var model = api.Models.Registry.lookup( cid );
					model.trigger('create:element', obj.markup, obj.styles);
				});
			}
		});
	};

	api.toggleFrontEdit = function( event ) {
		var self = this,
                    is_edit = 0;

		// remove lightbox if any
		if ($('#themify_builder_lightbox_parent').is(':visible')) {
			$('.builder_cancel_lightbox').trigger('click');
		}

		var location_url = window.location.pathname + window.location.search;
		// remove hash
		if (window.history && window.history.replaceState) {
			window.history.replaceState('', '', location_url);
		} else {
			window.location.href = window.location.href.replace(/#.*$/, '#');
		}

		var bids = $('.themify_builder_content').not('.not_editable_builder').map(function() {
			return $(this).data('postid') || null;
		}).get();

		// add body class
		if (!$('body').hasClass('themify_builder_active')) {
			is_edit = 1;
			if ( $('#wpadminbar').length ) {
				$('#wpadminbar').slideUp('slow', function(){
					$('#tb_toolbar').slideDown('slow');
				});
			} else {
				$('#tb_toolbar').slideDown('slow');
			}
		} else {
			$('#tb_toolbar').slideUp('slow', function(){
				$('#wpadminbar').slideDown('slow');
			});
			is_edit = 0;
		}

		if (is_edit === 0 && self.editing) {
			// confirm
			var reply = confirm(themifyBuilder.i18n.confirm_on_turn_off);
			if (reply) {
				api.Utils.saveBuilder(true).done(function(){
					self.toggleFrontEditAjax(is_edit, bids);
				});
			} else {
				self.toggleFrontEditAjax(is_edit, bids);
			}
		} else {
			self.toggleFrontEditAjax(is_edit, bids);
			self.editing = false;
		}

		if ('undefined' !== typeof event) {
			event.preventDefault();
		}
	};

	api.toggleFrontEditAjax = function( is_edit, bids ) {
		$.ajax({
			type: "POST",
			url: themifyBuilder.ajaxurl,
			dataType: 'json',
			data: {
				action: 'tfb_toggle_frontend',
				tfb_post_ids: bids,
				tfb_load_nonce: themifyBuilder.tfb_load_nonce,
				state: is_edit
			},
			beforeSend: function(xhr) {
				ThemifyBuilderCommon.showLoader('show');
			},
			success: function(data) {

				if (!is_edit) {
					// Clear undo history
					ThemifyBuilderCommon.undoManager.instance.clear();
					api.Models.Registry.destroy();
					api.Instances.Builder = {};
					api.styleSheet.destroy();
				}

				if (is_edit) {
					$('body').addClass('themify_builder_active themify_builder_front');
					
					if ( data.length ) {
						api.render(data);
					} else {
						ThemifyBuilderCommon.showLoader('spinhide');
					}
					api.Utils.checkUnload();
				} else {
					$('body').removeClass('themify_builder_active themify_builder_front');
						
					if (data.length > 0) {
						$.each(data, function(i, v) {
							var $target = $('#themify_builder_content-' + data[i].builder_id).empty();
							$target.get(0).innerHTML = $(data[i].markup).unwrap().get(0).innerHTML;
						});
					}

					if ( ! $('.js--themify_builder_breakpoint_switcher.breakpoint-desktop').hasClass('tb_selected') ) {
						$('.js--themify_builder_breakpoint_switcher.breakpoint-desktop').trigger('click');
					}

					window.onbeforeunload = null;
					ThemifyBuilderModuleJs.init();

					ThemifyBuilderCommon.showLoader('spinhide');
					$('body').trigger('builder_toggle_frontend', is_edit);
				}
				api.Utils.columnDrag(null,false);
				setTimeout( function(){

					/**
					 * Fix WP 4.8 widgets interface
					 * This needs to be revised when #5687 is fixed
					 */
					wp.mediaWidgets.init();
					wp.textWidgets.init();

					$( 'body' ).trigger( 'builder_finished_loading' );
				}, 2000 );
			}
		});
	};

	api.render = function( data ) {
		if (data.length > 0) {
			$.each(data, function(i, v) {
				var el = '#themify_builder_content-' + data[i].builder_id,
					$el = $(el),
					dataJSON = new api.Collections.Rows(data[i].builder_data);
				$el.wrapInner('<div class="themify_builder_content_remove_wrapper" />');
				api.Instances.Builder[i] = new api.Views.Builder({ el: el, collection: dataJSON, type: 'visual' });
				api.Instances.Builder[i].render();
				$el.find('.themify_builder_content_remove_wrapper').nextAll().addClass('hide-builder-content');
			});

			ThemifyBuilderCommon.showLoader('show');
			api.bootstrap(function(){
				$.each(data, function(i, v){
					var $el = $( '#themify_builder_content-' + data[i].builder_id );
					$el.find('.themify_builder_content_remove_wrapper').remove().end()
					.find('.hide-builder-content').removeClass('hide-builder-content');
				});
				ThemifyBuilderCommon.showLoader('spinhide');
				$('body').trigger('builder_toggle_frontend', true);
				api.Utils.loadContentJs();
				api.vent.trigger('dom:builder:change');
			});
		}

		$( '#themify_builder_switch_backend' ).on( 'click', this.switchBackEnd );
	};

	api.switchBackEnd = function( e ) {
		e.preventDefault();

		var backendURL = $( this ).attr( 'href' );

		if( ThemifyBuilderCommon.undoManager.instance.hasUndo() && backendURL ) {
			api.Utils.saveBuilder( true, function () {
				// Clear undo history
				ThemifyBuilderCommon.undoManager.instance.clear();
				window.location = backendURL;
			} );
		} else {
			window.location = backendURL;
		}
		
		sessionStorage.setItem( 'focusBackendEditor', true );
	};

	api.initFrontend = function() {
		api.Views.bindEvents();

		api.Forms.bindEvents();

		this.toolbar = new api.Views.Toolbar({ el: '#tb_toolbar'});

		/**
		 * New instance created on lightbox open, destroyed on lightbox close
		 * @type ThemifyLiveStyling
		 */
		this.liveStylingInstance = new ThemifyLiveStyling();

		if( tbLocalScript.isAnimationActive ) {
			Themify.LoadCss( tbLocalScript.builder_url + '/css/animate.min.css' ); // load it anyway for animation live preview
		}
	};

	// Initialize Builder
	$('body').on('builderscriptsloaded.themify', function(e) {
		api.initFrontend();
		api.toggleFrontEdit(e);
		$('.toggle_tf_builder a:first, a.js-turn-on-builder').on('click', api.toggleFrontEdit.bind(api));
	})
	.on('builderiframeloaded.themify', function(e) {
		api.Frontend.responsiveFrame.init();
	});

	ThemifyLiveStyling = (function($, jss) {

		function ThemifyLiveStyling() {
			this.$context = $('#themify_builder_lightbox_parent');
			this.elmtSelector = '#builder_live_styled_elmt';
			this.isInit = false;
			this.style_tab = '.themify_builder_style_tab';
			this.selectorQueue = [];
			this.data  = [];
			var self = this;
			$('body').on('builderiframeloaded.themify', function(e) {
				$('.themify_builder_site_canvas').css('width', $(window).width());
				self.iframe = api.Frontend.responsiveFrame.contentWindow;
				self.bindLightboxForm();
				$(document).trigger('tfb.live_styling.after_create', self);
			});
		}

		ThemifyLiveStyling.prototype.init = function($liveStyledElmt, currentStyleObj) {
			this.remove(); // remove previous live styling, if any

			this.$liveStyledElmt = $liveStyledElmt;

			if (typeof currentStyleObj === 'object') {
				this.currentStyleObj = currentStyleObj;
			} else {
				this.currentStyleObj = {};
			}

			//this.setLiveStyledElmtID();
			this.isInit = true;
			$(document).trigger('tfb.live_styling.after_init', this);
		};

		ThemifyLiveStyling.prototype.setLiveStyledElmtID = function() {
			this.$liveStyledElmt.prop('id', this.elmtSelector.substring(1));
		};

		ThemifyLiveStyling.prototype.unsetLiveStyledElmtID = function() {
			this.$liveStyledElmt.prop('id', false);
			$('#'+this.elmtSelector.substring(1)).prop('id',false);
		};
		
		ThemifyLiveStyling.prototype.isModuleExists = function($module) {
			return this.data['module-inner'] && this.data['module-inner'][$module]?true:false;
		};

		ThemifyLiveStyling.prototype.isComponentExists = function($component) {
			return this.data[ $component ] ? true:false;
		};
		
		ThemifyLiveStyling.prototype.addStyleDate = function($component,$rules) {
				   
			if(!this.isComponentExists[$component]){
				this.data[$component] = $rules;
			}
		};

		ThemifyLiveStyling.prototype.convertToPX = function( selector, value ) {
			var pxValue = parseInt( value ),
				unit = value.match( /\D{1,2}$/ );

			if( unit[0] && unit[0] === '%' ) {
				pxValue = $( selector ).outerWidth() * pxValue / 100;
			} else if( unit[0] && unit[0] === 'em' ) {
				pxValue = parseInt( $( selector ).css( 'fontSize' ) ) * pxValue;
			}

			return pxValue;
		};

		/**
		 * Apply CSS rules to the live styled element.
		 *
		 * @param {Object} newStyleObj Object containing CSS rules for the live styled element.
		 * @param {Array} selectors List of selectors to apply the newStyleObj to (e.g., ['', 'h1', 'h2']).
		 */
		ThemifyLiveStyling.prototype.setLiveStyle = function(newStyleObj, selectors) {
			if(!selectors){
				selectors = [''];
			}
			var prefix = '.themify_builder .tb_element_cid_' + api.activeModel.cid,
				type = api.activeModel.get('elType'),
				sep = 'module' === type ? ' ' : '',
				_this = this;
		
			selectors.forEach(function(selector) {
				var fullSelector;
				if ( selector && selector.length > 0 ) {
					fullSelector = prefix + sep + selector.trim();
				} else {
					fullSelector = prefix;
				}

				api.styleSheet.setStyles( fullSelector, newStyleObj );

				if( $( fullSelector ).hasClass( 'fullwidth' ) ) {
					var container = $( tbLocalScript.fullwidth_container );
					var row = $( fullSelector ).closest('.themify_builder_content');
					var left = row.offset().left - container.offset().left;
					var right = container.outerWidth() - left - row.outerWidth();
					var padding = $( fullSelector ).data( 'padding' );
					var margin = $( fullSelector ).data( 'margin' );
					if(padding){
						if( newStyleObj[ 'padding-left' ] ) {
							$( fullSelector ).css( { paddingLeft: '' } );
							padding[0] = _this.convertToPX( fullSelector, newStyleObj[ 'padding-left' ] );
						}

						if( newStyleObj[ 'padding-right' ] ) {
							$( fullSelector ).css( { paddingRight: '' } );
							padding[1] = _this.convertToPX( fullSelector, newStyleObj[ 'padding-right' ] );
						}

						if( newStyleObj[ 'padding-left' ] || newStyleObj[ 'padding-right' ] ) {
							$( fullSelector ).data( 'padding', padding );
						}
					}
					if(margin){
				
						if( newStyleObj[ 'margin-left' ] ) {
							margin[0] = _this.convertToPX( fullSelector, newStyleObj[ 'margin-left' ] );
							$( fullSelector ).css( { marginLeft: -left + margin[0] } );
						}

						if( newStyleObj[ 'margin-right' ] ) {
							margin[1] = _this.convertToPX( fullSelector, newStyleObj[ 'margin-right' ] );
							$( fullSelector ).css( { marginRight: -right + margin[1] } );
						}

						if( newStyleObj[ 'margin-left' ] || newStyleObj[ 'margin-right' ] ) {
							$( fullSelector ).css( { width: container.outerWidth() - ( margin[0] + margin[1] ) + 'px' } );
							$( fullSelector ).data( 'margin', margin );
						}
					}

				}
			});
			api.Frontend.responsiveFrame.doSync();

			function logging(fullSelector) {
			}
		};

		ThemifyLiveStyling.prototype.bindColors = function() {
			var self = this;
			this.$context.on('change', 'input.colordisplay', function() {
				var hexString = $(this).val().length > 0 ? '#' + $(this).val() : '',
					colorType = $(this).parent().find('.builderColorSelectInput').prop('name');

				$('body').trigger('themify_builder_color_picker_change', [colorType, hexString]);
			});
			this.$context.on('change', 'input.color_opacity', function() {
				var opacity = $(this).val(),
					hexString = "#" + $(this).parent().find('.colordisplay').prop('value'),
					colorType = $(this).parent().find('.builderColorSelectInput').prop('name');
					var patt = /^#([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})$/,
                                            matches = patt.exec(hexString),
                                            rgbaString = matches ? "rgba(" + parseInt(matches[1], 16) + ", " + parseInt(matches[2], 16) + ", " + parseInt(matches[3], 16) + ", " + opacity + ")" : '';
				$(this).parent().find('.builderColorSelectInput').prop('value', $(this).parent().find('.colordisplay').prop('value') + "_" + opacity);
				$('body').trigger('themify_builder_color_picker_change', [colorType, rgbaString]);
			});
			$('body').on('themify_builder_color_picker_change', function(e, colorType, rgbaString) {
				if (colorType === 'cover_color') {
					
					var checked = self.$context.find('input[name="'+colorType+'-type"]:checked');
					if(checked.val()!=='color' && checked.val()!=='hover_color'){
						return;
					}
					self.addOrRemoveComponentOverlay(rgbaString,false);
				} else if(self.isInit) {
					
					var $el = $('input[name="'+colorType+'"]');
					if($el.next('.style_border').length>0 && $el.closest('.themify_builder_field').nextAll('.themify_builder_field').last().find('.style_apply_all_border').is(':checked')){
						return false;
					}
					var $data = self.getValue(colorType),
						$selector = $data.selector,
						$prop = {};
					if ($data) {
						$prop[ $data.prop ] = rgbaString;
						self.setLiveStyle($prop, $selector);
					}
				}
			});
		};
				
		ThemifyLiveStyling.prototype.overlayType = function() {
			this.$context.on('change', 'input[name="cover_color-type"]', function() {
				if($(this).val()==='color'){
					$('input[name="cover_color"]',this.$context).prev().trigger('change');
				}
				else{
					$('#'+$(this).val()+'-gradient-type').trigger('change');
				}
			});
		};

		ThemifyLiveStyling.prototype.getSpecialTextSelectors = function() {
			return [' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6'];
		};

		ThemifyLiveStyling.prototype.addOrRemoveComponentOverlay = function(rgbaString,$gradient) {
			var $overlayElmt = ThemifyLiveStyling.getComponentBgOverlay(this.$liveStyledElmt);

			if (!rgbaString.length) {
				$overlayElmt.remove();
				return;
			}
			var $isset = $overlayElmt.length!==0;
			if (!$isset) {
				$overlayElmt = $('<div/>', {
						class: 'builder_row_cover'
				});
			}
			$overlayElmt.data('color', rgbaString);
			$overlayElmt.attr('data-color-val', rgbaString); // fix issue with undo/redo lost data attr
			if(!$gradient){
				 $overlayElmt.css({'background-image': '','background':rgbaString});
			}
			else{
				$overlayElmt.css({'background': rgbaString,'background-color':''});
			}
			if($isset){
				return;
			}
						
			var $elmtToInsertBefore = ThemifyLiveStyling.getComponentBgSlider(this.$liveStyledElmt);

			if (!$elmtToInsertBefore.length) {
				var selector = '';
				var componentType = ThemifyBuilderCommon.getComponentType(this.$liveStyledElmt);

				if (componentType === 'row') {
					selector = '.row_inner_wrapper';
				} else if (componentType === 'col' || componentType === 'sub-col') {
					selector = '.tb-column-inner'
				}

				$elmtToInsertBefore = this.$liveStyledElmt.children(selector);
			}

			$overlayElmt.insertBefore( $elmtToInsertBefore );
		};

		ThemifyLiveStyling.prototype.bindTextInputs = function() {
			var self = this;

			$('body').delegate(self.style_tab + ' input[type="text"]', 'keyup', function() {
				var $id = $(this).prop('id'),
					$data = self.getValue($id),
					$prop = {};
				if ($data) {
					var $val = $.trim($(this).val());
					if ($('#' + $id + '_unit').length > 0 && '' !== $val ) {
						$val += $('#' + $id + '_unit').val() ? $('#' + $id + '_unit').val() : 'px';
					} else if ($(this).hasClass('style_border')) {
						if($(this).closest('.themify_builder_field').nextAll('.themify_builder_field').last().find('.style_apply_all_border').is(':checked')){
							return false;
						}
						$val = parseFloat($val);
						
						if($val){
							$(this).closest('.themify_builder_field').find('select').trigger('change');
							$val = $val.toString();
						}
						else{
							$val = '0';
						}
						$val += 'px';
					} else if( $( this ).hasClass( 'style_field_px' ) ) {
						$val += 'px';
						if( $( this ).closest('.themify_builder_field').find( 'select' ).length ) {
							$( this ).closest( '.themify_builder_field' ).find( 'select' ).trigger( 'change' );
						}
					}
					$prop[ $data.prop ] = $val;
					self.setLiveStyle($prop, $data.selector);
				}
			});
			$('body').delegate(self.style_tab + ' [id$="_unit"]', 'change', function() {
				var $id = $(this).prop('id').replace('_unit', '');
				$('#' + $id).trigger('keyup');
			});
			/*$(document).on('tfb.live_styling.after_init',function(){
				setTimeout(function(){
					var self = api.liveStylingInstance;
					$(self.style_tab +' .style_apply_all_border:checked').each(function(){
						var $field =$(this).closest('.themify_builder_field').prevUntil('h4').last(),
							$input = $field.find('.style_border');
						if(!$input.val()){
							self.setApplyBorder($input.prop('id'),0,'width');
						}
						var $select = $field.find('select');
						self.setApplyBorder($select.prop('id'),$select.val(),'style');
					});
				},900);
				
			});*/			
		};

		ThemifyLiveStyling.prototype.bindRowWidthHeight = function() {
			var self = this;

			this.$context.on('change', 'input[name="row_height"]', function() {
				var rowVal = self.getStylingVal('row_height'),
					val = $(this).val(),
					action = 'remove';
				if (val.length > 0) {
					if (rowVal.length > 0) {
						self.$liveStyledElmt.removeClass(rowVal);
					}
					self.setStylingVal($(this).prop('name'), val);
					self.$liveStyledElmt.addClass(val);
					action = 'apply';
				} else {
					self.$liveStyledElmt.removeClass(rowVal);
				}
			});

			this.$context.on('change', 'input[name="row_width"]', function() {
				var rowVal = self.getStylingVal('row_width'),
					val = $(this).val();
				if( val == 'fullwidth' ) {
					self.$liveStyledElmt.removeClass( 'fullwidth' ).addClass( 'fullwidth_row_container' );
					if (tbLocalScript.fullwidth_support == '') {
						ThemifyBuilderModuleJs.setupFullwidthRows();
					}
				} else if( val == 'fullwidth-content' ) {
					self.$liveStyledElmt.removeClass( 'fullwidth_row_container' ).addClass( 'fullwidth' );
					if (tbLocalScript.fullwidth_support == '') {
						ThemifyBuilderModuleJs.setupFullwidthRows();
					}
				} else {
					self.$liveStyledElmt.removeClass( 'fullwidth fullwidth_row_container' )
					if (tbLocalScript.fullwidth_support == '') {
						self.$liveStyledElmt.css( {
							'margin-left': '',
							'margin-right': '',
							'padding-left': '',
							'padding-right': '',
							'width': ''
						} );
					}
				}

				$('body').trigger('builderfullwidth.themify', [self.$liveStyledElmt] );
			});
		};

		ThemifyLiveStyling.prototype.removeAnimations = function(animationEffect, $elmt) {
			$elmt.removeClass(animationEffect + ' wow animated').css('animation-name', '');
		};

		ThemifyLiveStyling.prototype.addAnimation = function(animationEffect, $elmt) {
			$elmt.addClass(animationEffect + ' animated');
		};

		ThemifyLiveStyling.prototype.bindAnimation = function() {
			var self = this;

			this.$context.on('change', '#animation_effect', function() {
				var animationEffect = self.getStylingVal('animation_effect');

				if ($(this).val().length) {
					if (animationEffect.length) {
						self.removeAnimations(animationEffect, self.$liveStyledElmt);
					}

					animationEffect = $(this).val();

					self.setStylingVal('animation_effect', animationEffect);
					self.addAnimation(animationEffect, self.$liveStyledElmt);
				} else {
					self.removeAnimations(animationEffect, self.$liveStyledElmt);
				}
			});
		};

		ThemifyLiveStyling.prototype.bindAdditionalCSSClass = function() {
			var self = this;

			this.$context.on('keyup', '#custom_css_row, #custom_css_column', function() {
				var id = this.id,
					className = self.getStylingVal(id);

				self.$liveStyledElmt.removeClass(className);

				className = $(this).val();

				self.setStylingVal(id, className);
				self.$liveStyledElmt.addClass(className);
			});
		};

		ThemifyLiveStyling.prototype.bindRowAnchor = function() {
			var self = this;

			this.$context.on('keyup', '#row_anchor', function() {
				var rowAnchor = self.getStylingVal('row_anchor');

				self.$liveStyledElmt.removeClass(self.getRowAnchorClass(rowAnchor));

				rowAnchor = $(this).val();

				self.setStylingVal('row_anchor', rowAnchor);
				self.$liveStyledElmt.addClass(self.getRowAnchorClass(rowAnchor));
			});
		};

		ThemifyLiveStyling.prototype.getRowAnchorClass = function(rowAnchor) {
			return rowAnchor.length > 0 ? 'tb_section-' + rowAnchor : '';
		};

		ThemifyLiveStyling.prototype.getStylingVal = function(stylingKey) {
			return this.currentStyleObj.hasOwnProperty(stylingKey) ? this.currentStyleObj[stylingKey] : '';
		};

		ThemifyLiveStyling.prototype.setStylingVal = function(stylingKey, val) {
			this.currentStyleObj[stylingKey] = val;
		};

		ThemifyLiveStyling.prototype.bindBackgroundMode = function() {
			var self = this;
			this.$context.on('change', '#background_repeat', function() {
				var previousVal = self.getStylingVal('background_repeat');

				var val = $(this).val();
				if (val && val.length > 0) {
					if (previousVal.length > 0) {
						self.$liveStyledElmt.removeClass(previousVal);
					}

					self.setStylingVal('background_repeat', val);
					self.$liveStyledElmt.addClass(val);
				} else {
					self.$liveStyledElmt.removeClass(previousVal);
				}
			});

		};

		ThemifyLiveStyling.prototype.bindBackgroundPosition = function() {
			var self = this;
			this.$context.on('change', '#background_position', function() {
				var previousVal = self.getStylingVal('background_position');

				var val = $(this).val();

				if (val && val.length > 0) {
					if (previousVal.length > 0) {
						self.$liveStyledElmt.removeClass(previousVal);
					}
					val = 'bg-position-' + val;
					self.setStylingVal('background_position', val);
					self.$liveStyledElmt.addClass(val);
				} else {
					self.$liveStyledElmt.removeClass(previousVal);
				}
			});

		};

		ThemifyLiveStyling.prototype.isWebSafeFont = function(fontFamily) {
			/**
			 *  Array containing the web safe fonts from the backend themify_get_web_safe_font_list().
			 *
			 * @type {Array}
			 */
			var webSafeFonts = themifyBuilder.webSafeFonts;

			return webSafeFonts.indexOf(fontFamily) !== -1;
		};

		ThemifyLiveStyling.prototype.bindBackgroundSlider = function() {
			function getBackgroundSlider(options) {
				return $.post(
					themifyBuilder.ajaxurl, {
						nonce: themifyBuilder.tfb_load_nonce,
						action: 'tfb_slider_live_styling',
						tfb_background_slider_data: options
					}
				);
			}

			var getOptions, insertBackgroundSliderToHTML, initBackgroundSlider;

			getOptions = function() {
				return {
					shortcode: encodeURIComponent($('#background_slider').val()),
					mode: $('#background_slider_mode').val(),
					size: $('#background_slider_size').val(),
					order: ThemifyBuilderCommon.getComponentOrder(this.$liveStyledElmt),
					type: ThemifyBuilderCommon.getComponentType(this.$liveStyledElmt)
				};
			}.bind(this);

			insertBackgroundSliderToHTML = function($backgroundSlider) {
				var liveStyledElmtType = ThemifyBuilderCommon.getComponentType(this.$liveStyledElmt);
				var bgCover = ThemifyLiveStyling.getComponentBgOverlay(this.$liveStyledElmt);

				if (bgCover.length) {
					bgCover.after($backgroundSlider);
				} else {
					if (liveStyledElmtType == 'row') {
						this.$liveStyledElmt.children('.themify_builder_row_top').after($backgroundSlider);
					} else {
						this.$liveStyledElmt.prepend($backgroundSlider);
					}
				}
			}.bind(this);

			initBackgroundSlider = function($bgSlider) {
				ThemifyBuilderModuleJs.backgroundSlider($bgSlider);
			};

			var self = this;

			this.$context.on('change', '#background_slider, #background_slider_mode,#background_slider_size', function() {
				ThemifyLiveStyling.removeBgSlider(self.$liveStyledElmt);

				if (!$('#background_slider').val().length) {
					return;
				}

				getBackgroundSlider(getOptions()).done(function(backgroundSlider) {
					if (backgroundSlider.length < 10) {
						return;
					}

					var $bgSlider = $(backgroundSlider);

					insertBackgroundSliderToHTML($bgSlider);
					initBackgroundSlider($($bgSlider.get(0)));
				});
			});
		};
                ThemifyLiveStyling.prototype.VideoOptions = function() {
			var self = this;

			this.$context.on('change', 'input[name="background_video_options"]', function() {
				if (!self.isInit) {
					return;
				}
                                var video = self.$liveStyledElmt.children('.big-video-wrap').first(),
                                    el = '',
                                    type = '';
                                if(video.hasClass('themify_ytb_wrapper')){
                                    el = video.closest('[data-fullwidthvideo]');
                                    if(el.length>0){
                                        type = 'ytb';
                                    }
                                }
                                else if(video.hasClass('themify-video-vmieo')){
                                    el = $f(video.children('iframe')[0]);
                                    if(el){
                                        type = 'vimeo';
                                    }
                                }
                                else{
                                    
                                    el = video.closest('[data-fullwidthvideo]').data('video');
                                    if(el){
                                        el = el.getPlayer();
                                        type = 'local';
                                    }
                                }
                              
                                if($(this).val()==='mute'){
                                    if($(this).is(':checked')){
                                        if(type==='ytb'){
                                           el.ThemifyYTBMute()
                                        }
                                        else if(type === 'vimeo'){
                                            el.api('setVolume', 0);
                                        }
                                        else if(type === 'local'){
                                            el.muted(true);
                                        }
                                    }
                                    else{
                                        if(type==='ytb'){
                                            el.ThemifyYTBUnmute();
                                        }
                                        else if(type === 'vimeo'){
                                            el.api('setVolume', 1);
                                        }
                                        else if(type === 'local'){
                                            el.muted(false);
                                        }
                                    }
                                }
                                else if($(this).val()==='unloop'){
                                    if($(this).is(':checked')){
                                        if(type === 'vimeo'){
                                            el.api('setLoop', 0);
                                        }
                                        else if(type === 'local'){
                                            el.loop(false);
                                        }
                                    }
                                    else{
                                        if(type === 'vimeo'){
                                            el.api('setLoop', 1);
                                        }
                                        else if(type === 'local'){
                                            el.loop(true);
                                        }
                                    }
                                }
			});

		};
		ThemifyLiveStyling.prototype.bindBackgroundTypeRadio = function() {
			var self = this;

			this.$context.on('change', 'input[name="background_type"]', function() {
				if (!self.isInit) {
					return;
				}

				var bgType = ThemifyBuilderCommon.getCheckedRadioInGroup($(this), self.$context).val();
				if (bgType === 'image' || bgType === 'gradient') {
					ThemifyLiveStyling.removeBgSlider(self.$liveStyledElmt);
					ThemifyLiveStyling.removeBgVideo(self.$liveStyledElmt);
					if(bgType === 'image'){
						self.setLiveStyle({
								'background-image': 'none'
						}, ['']);
					}
					else{
						bgType+= '-gradient-angle';
					}
				} 
				else if (bgType === 'video') {
					ThemifyLiveStyling.removeBgSlider(self.$liveStyledElmt);
				} else {
					ThemifyLiveStyling.removeBgVideo(self.$liveStyledElmt);
					// remove bg image
					self.setLiveStyle({
						'background-image': 'none'
					}, ['']);
				}
				self.$context.find('#background_' + bgType).trigger('change');
			});

		};
		ThemifyLiveStyling.prototype.bindVisibilityCheckbox = function() {
			var self = this;
			this.$context.on('change', 'input[name="visibility_all"]', function() {
				$( this ).closest( '.themify_builder_options_tab_content' ).find( 'input[type="radio"]' ).prop( 'disabled', $( this ).is(":checked") );
			});
		};

		// Styling buttons are a mix of radio buttons and checkboxes, only one should
		// be selected, but they can be unselected when clicked
		ThemifyLiveStyling.prototype.bindStylingToggles = function() {
			$("body").on( "mousedown", ".themify_builder_admin input.tfb-icon-radio+label", function( ev ) {
				if( $( this ).prev( "input" ).is( ":checked" ) ){
					$( this ).prev( "input" ).prop( "uncheck", true );
				}
			});
			$("body").on( "click", ".themify_builder_admin input.tfb-icon-radio", function( ev ) {
				if( $( this ).prop( "uncheck" ) ) {
					$( this ).prop( "checked", false );
					$( this ).prop( "uncheck", false );
					$( this ).trigger( "change" );
					console.log( $( this ).val() );
				}
			});
		}
				
		ThemifyLiveStyling.prototype.bindBackgroundGradient = function($id,$val,$return) {
			var self = this;
				if (self.isInit) {
					var $data = self.getValue($id),
						$prop = {};
					if ($data) {
						$val = $val.replace(/background-image:/ig,'').split(";");
						var $vendors = {'moz':0,'webkit':4,'o':2,'ms':3},
							$pref = getVendorPrefix(),
							$val = $vendors[$pref] && $val[$vendors[$pref]]?$val[$vendors[$pref]]:$val[4];
							if(!$return){
								$prop[ $data.prop ] = $val;
								self.setLiveStyle($prop, $data.selector);
							}
							else{
								return $val;
							}
					}
				}
				
				function getVendorPrefix(){
					if ( typeof getVendorPrefix.pre === 'undefined' ) {
						
						var styles = window.getComputedStyle(document.documentElement, '');
						
						getVendorPrefix.pre = (Array.prototype.slice
						  .call(styles)
						  .join('')
						  .match(/-(moz|webkit|ms)-/) || (styles.OLink === '' && ['', 'o'])
						  )[1];
						
					}
					return getVendorPrefix.pre;
				}

		};
		/**
		 * Binds module layout + styling options to produce live styling on change.
		 */
		ThemifyLiveStyling.prototype.bindModuleLayout = function() {
			var self = this;

			var layoutStylingOptions = {
				//'layout_accordion': '> ul.ui.module-accordion',
				//'layout_callout': '',
				'layout_feature': '',
				//'style_image': '',
				'layout_menu': 'ul.ui.nav:first',
				//'layout_post': '> .builder-posts-wrap',
				//'layout_tab': '',
				//'layout_slider' : ''
			};

			// TODO: Optimize for speed by having one .on('click') handler
			Object.keys(layoutStylingOptions).forEach(function(layoutSelectorKey) {
				self.$context.on('click', '#' + layoutSelectorKey + ' > a', function() {
					var selectedLayout = $(this).attr('id');

					var $elmtToApplyTo = self.$liveStyledElmt;

					if (layoutStylingOptions[layoutSelectorKey] !== '') {
						$elmtToApplyTo = self.$liveStyledElmt.find(layoutStylingOptions[layoutSelectorKey]);
					}

					var prevLayout = self.getStylingVal(layoutSelectorKey);

					if (layoutSelectorKey === 'layout_feature') {
						selectedLayout = 'layout-' + selectedLayout;
						prevLayout = 'layout-' + prevLayout;
					}

					$elmtToApplyTo
						.removeClass(prevLayout)
						.addClass(selectedLayout);

					if (layoutSelectorKey === 'layout_feature') {
						selectedLayout = selectedLayout.substr(7);
					}

					self.setStylingVal(layoutSelectorKey, selectedLayout);
				});
			});

		};

		/**
		 * Binds module radio buttons to produce live styling on change.
		 */
		ThemifyLiveStyling.prototype.bindModuleRadio = function() {
			var self = this;

			var RadioStylingOptions = {
				'buttons_style': '> .module-buttons',
				'buttons_size': '> .module-buttons',
				'icon_style': '> .module-icon',
				'icon_size': '> .module-icon',
				'icon_arrangement': '> .module-icon'
			};

			Object.keys(RadioStylingOptions).forEach(function(radioSelectorKey) {
				self.$context.on('change', '#' + radioSelectorKey + ' input[type="radio"]', function() {
									if(self.$liveStyledElmt){
					var selectedRadio = $(this).val(),
						$elmtToApplyTo = RadioStylingOptions[radioSelectorKey] !== '' ?
						self.$liveStyledElmt.find(RadioStylingOptions[radioSelectorKey]) : self.$liveStyledElmt,
						prevLayout = self.getStylingVal(radioSelectorKey);

					$elmtToApplyTo.removeClass(prevLayout).addClass(selectedRadio);
					self.setStylingVal(radioSelectorKey, selectedRadio);
									}
				});
			});

		};

		ThemifyLiveStyling.prototype.bindModuleColor = function() {
			var self = this;

			/**
			 * A key-value pair.
			 * Key represents the ID of the element which should be listened to.
			 * Value represents the selector to the element which the live styling should be applied to.
			 */
			var colorStylingOptions = {
				//'color_accordion': '> ul.ui.module-accordion',
				//'color_box': '> .module-box-content.ui',
				//'color_button': '> .ui.builder_button',
				///'color_callout': '',
				//'action_btn_color_callout': '.callout-button a',
				'color_menu': 'ul.ui.nav:first',
				'mod_color_pricing_table': ['', '> .module-pricing-table-header', '.module-pricing-table-button'],
				//'color_tab': '',
				'icon_color_bg': '> .module-icon i',
				//'button_color_bg': '> .module-buttons a'
			};

			var colorStylingSelector = Object.keys(colorStylingOptions).reduce(function(selectors, selector, index) {
				var result = selectors;

				if (index !== 0) {
					result += ',';
				}

				result += '#' + selector + ' > a';

				return result;
			}, '');

			self.$context.on('click', colorStylingSelector, function() {
				var $this = $(this),
					colorSelectorKey = $this.parent().attr('id'),
					selectedColor = $(this).attr('id'),
					$builder = $this.closest('.themify_builder_row_js_wrapper');

				var elmtToApplyToSelector = colorStylingOptions[colorSelectorKey];

				if (!Array.isArray(elmtToApplyToSelector)) {
					elmtToApplyToSelector = [elmtToApplyToSelector];
				}

				var $elmtsToApplyTo = $([]);

				elmtToApplyToSelector.forEach(function(selector) {
					if (selector === '') {
						$elmtsToApplyTo = $elmtsToApplyTo.add(self.$liveStyledElmt);
					} else {
						$elmtsToApplyTo = $elmtsToApplyTo.add(
							self.$liveStyledElmt.find(selector)
						);
					}

				});

				if ($builder.length > 0) {
					var $index = $this.closest('.themify_builder_row').index(),
						realKey = colorSelectorKey;
					colorSelectorKey += '_' + $index;
					$elmtsToApplyTo = $($elmtsToApplyTo[$index]);
				}

				var prevColor = self.getStylingVal(colorSelectorKey);
				if (!prevColor && $builder.length > 0) {
					var $rows = self.getStylingVal($builder.attr('id'));
					if ($rows[$index] && $rows[$index][realKey]) {
						prevColor = $rows[$index][realKey];
					}
				}

				$elmtsToApplyTo
					.removeClass(prevColor)
					.addClass(selectedColor);

				self.setStylingVal(colorSelectorKey, selectedColor);
			});
		};		
		ThemifyLiveStyling.prototype.setApplyBorder = function(id,value,type) {
			var $data = this.getValue(id),
				$prop = {};

			if ($data) {

				if ( 'color' === type && 'rgba(0, 0, 0, 1)' === value ) {
					value = '';
					if ( '' === $('#' + id.replace('color', 'width') ).val() ) {
						$prop['border-style'] = '';
					}
				}

				$prop[ 'border-'+type ] = type==='width'?(value>0?value+'px': ''):value;
				this.setLiveStyle($prop, $data.selector);
			}
		};
		
		ThemifyLiveStyling.prototype.bindChangesEvent = function() {
			var self = this;
 
			$('body').delegate(self.style_tab + ' select,' + self.style_tab + ' input[type="radio"],' + self.style_tab + ' .themify-builder-uploader-input', 'change', function() {
				if(!self.isInit){
					return;
				}
			   
				var $id = $(this).is('select') || $(this).hasClass('themify-builder-uploader-input') ? $(this).prop('id') : $(this).parent('.tfb_lb_option').prop('id'),
					$data = self.getValue($id),
					$prop = {};
				if ($data) {
					var $val = $(this).val(),
						$selector = $data.selector;
					if( $(this).is("input[type='radio']") && !$(this).is(":checked") ) $val = "";
					if( _.isNull( $val ) ) {
						return;
					}
					if ($(this).hasClass('font-family-select')) {
						if ($val !== 'default' && !self.isWebSafeFont($val)) {
							ThemifyBuilderCommon.loadGoogleFonts([$val.split(' ').join('+') + ':400,700:latin,latin-ext'], self.iframe);
						} else if ($val === 'default') {
							$val = '';
						}
					} else if ($(this).hasClass('themify-builder-uploader-input')) {
						if ($(this).closest('.themify_builder_input').find('.thumb_preview').length > 0) {
							$val = $val ? 'url(' + $val + ')' : 'none';
						} else {
							self.$liveStyledElmt.data('fullwidthvideo', $val);
							if ($val.length > 0) {
								ThemifyBuilderModuleJs.fullwidthVideo(self.$liveStyledElmt);
							} else {
								ThemifyLiveStyling.removeBgVideo(self.$liveStyledElmt);
							}
							return false;
						}
					}
					else if(! $(this).hasClass( 'style_field_select' ) && $(this).is('select') && $(this).find('[value="dashed"]').length>0){
						if(($val!=='none' && !parseInt($(this).closest('.themify_builder_input').find('.style_border').val())) || $(this).closest('.themify_builder_field').nextAll('.themify_builder_field').last().find('.style_apply_all_border').is(':checked')){
							return false;
						}
						
					}
					$prop[ $data.prop ] = $val;
					self.setLiveStyle($prop, $selector);
				}
			});
		};

		ThemifyLiveStyling.prototype.getValue = function($id) {
			if (this.isInit) {
				var type = ThemifyBuilderCommon.getComponentType(this.$liveStyledElmt);

				if ( _.contains( ['sub-col', 'col'], type ) ) type = 'column';

				if (type === 'module-inner') {
					var $module = $(this.style_tab).data('module');
					return $module && this.data[$module] && this.data[$module][$id] ? this.data[$module][$id] : false;
				} else {
					return this.data[type] && this.data[type][$id] ? this.data[type][$id] : false; //raw, column
				}
			}
			return false;
		};

		ThemifyLiveStyling.prototype.bindModuleApppearance = function() {
			var self = this;

			var getSelectedAppearances = function(appearanceSelector) {
				var selectedAppearances = self.$context.find('#' + appearanceSelector + ' > input:checked')
					.map(function(i, checkbox) {
						return $(checkbox).val();
					})
					.toArray();

				return selectedAppearances.join(' ');
			};

			var appearanceStylingOptions = {
				'accordion_appearance_accordion': '> ul.ui.module-accordion',
				//'appearance_box': '> .module-box-content.ui',
				'appearance_button': '> .ui.builder_button',
				'appearance_callout': '',
				'action_btn_appearance_callout': '.callout-button a',
				//'appearance_image': '',
				'according_style_menu': 'ul.ui.nav:first',
				'mod_appearance_pricing_table': ['', '> .module-pricing-table-header', '.module-pricing-table-button'],
				//'tab_appearance_tab': ''
			};

			var appearanceStylingSelector = Object.keys(appearanceStylingOptions).reduce(
				function(selectors, selector, index) {
					var result = selectors;

					if (index !== 0) {
						result += ',';
					}

					result += '#' + selector + ' > input';

					return result;
				},
				'');

			self.$context.on('change', appearanceStylingSelector, function() {
				var $this = $(this);
				var appearanceSelectorKey = $this.parent().attr('id');

				var elmtToApplyToSelector = appearanceStylingOptions[appearanceSelectorKey];

				if (!Array.isArray(elmtToApplyToSelector)) {
					elmtToApplyToSelector = [elmtToApplyToSelector];
				}

				var $elmtsToApplyTo = $([]);

				elmtToApplyToSelector.forEach(function(selector) {
					if (selector === '') {
						$elmtsToApplyTo = $elmtsToApplyTo.add(self.$liveStyledElmt);
					} else {
						$elmtsToApplyTo = $elmtsToApplyTo.add(
							self.$liveStyledElmt.find(selector)
						);
					}

				});

				var prevAppearances = self.getStylingVal(appearanceSelectorKey)
					.split('|')
					.join(' ');

				var selectedAppearances = getSelectedAppearances(appearanceSelectorKey);

				if (appearanceSelectorKey === 'mod_appearance_pricing_table') {
					prevAppearances += ' ' + prevAppearances.split(' ').join('|');
					selectedAppearances += ' ' + selectedAppearances.split(' ').join('|');
				}

				$elmtsToApplyTo
					.removeClass(prevAppearances)
					.addClass(selectedAppearances);

				self.setStylingVal(
					appearanceSelectorKey,
					getSelectedAppearances(appearanceSelectorKey).split(' ').join('|')
				);
			});
		};

		ThemifyLiveStyling.prototype.bindLightboxForm = function() {

			// "Styling" tab live styling
			this.bindChangesEvent();
			this.bindColors();
			this.bindTextInputs();
			this.bindBackgroundMode();
			this.bindBackgroundPosition();
			this.bindAnimation();
			this.bindBackgroundSlider();
			this.bindBackgroundTypeRadio();
                        this.VideoOptions();
			this.overlayType();
			this.bindVisibilityCheckbox();
			this.bindStylingToggles();

			// "Module options tab" live styling
			this.bindModuleLayout();
			this.bindModuleColor();
			this.bindModuleApppearance();
			this.bindModuleRadio();
			this.bindRowAnchor();
			this.bindAdditionalCSSClass();
			this.bindRowWidthHeight();
		};

		/**
		 * Resets or removes all styling (both live and from server).
		 */
		ThemifyLiveStyling.prototype.resetStyling = function() {

			var selectorsWithTriggerRequired = [
				'#background_repeat',
				'#row_anchor',
				'#custom_css_row',
				'#custom_css_column',
				'#add_css_text',
				'#animation_effect',
				'input[name=row_height]',
				'input[name=row_width]'
			];

			$(selectorsWithTriggerRequired.join(','), this.$context).trigger('change');

			var $styleTag = ThemifyLiveStyling.getComponentStyleTag(this.$liveStyledElmt);
			$styleTag.remove();

			// Removes row overlay.
			this.addOrRemoveComponentOverlay('');

			this._removeAllLiveStyles();

			// TODO: removing bg slider needs more testing.
		};

		/**
		 * Returns component's background cover element wrapped in jQuery.
		 *
		 * @param {jQuery} $component
		 * @returns {jQuery}
		 */
		ThemifyLiveStyling.getComponentBgOverlay = function($component) {
			return $component.children('.builder_row_cover');
		};

		/**
		 * Returns component's background slider element wrapped in jQuery.
		 *
		 * @param {jQuery} $component
		 * @returns {jQuery}
		 */
		ThemifyLiveStyling.getComponentBgSlider = function($component) {
			return $component.children('.row-slider, .col-slider, .sub-col-slider, .sub_row-slider');
		};

		/**
		 * Returns component's background video element wrapped in jQuery.
		 *
		 * @param {jQuery} $component
		 * @returns {jQuery}
		 */
		ThemifyLiveStyling.getComponentBgVideo = function($component) {
			return $component.children('.big-video-wrap');
		};

		/**
		 * Returns component's <style> tag.
		 *
		 * @param {jQuery} $component
		 * @returns {jQuery|null}
		 */
		ThemifyLiveStyling.getComponentStyleTag = function($component) {
			var type = ThemifyBuilderCommon.getComponentType($component);

			var $styleTag = null;

			if (type === 'row') {
				$styleTag = $component.find('.row_inner').children('style');
			} else if (type === 'col') {
				$styleTag = $component.find('.tb-column-inner').children('style');
			} else if (type === 'subrow') {
				$styleTag = $component.children('style');
			} else if (type === 'sub-col') {
				$styleTag = $component.children('style');
			} else if (type === 'module-inner') {
				$styleTag = $component.siblings('style');
			}

			return $styleTag;
		};

		/**
		 * Removes background slider if there is any in $component.
		 *
		 * @param {jQuery} $component
		 */
		ThemifyLiveStyling.removeBgSlider = function($component) {
			ThemifyLiveStyling.getComponentBgSlider($component)
				.add($component.children('.backstretch'))
				.remove();

			$component.css({
				'position': '',
				'background': '',
				'z-index': ''
			});
		};

		/**
		 * Removes background video if there is any in $component.
		 *
		 * @param {jQuery} $component
		 */
		ThemifyLiveStyling.removeBgVideo = function($component) {
			ThemifyLiveStyling.getComponentBgVideo($component).remove();
		};

		/**
		 * Removes live styling from the live styled element.
		 *
		 * @private
		 */
		ThemifyLiveStyling.prototype._removeAllLiveStyles = function() {
			var self = this;

			var selectors = this.getSpecialTextSelectors().concat(' a');

			selectors.forEach(function(selector) {
				// Remove styles for special selectors.
				jss.remove(self.elmtSelector + selector);
				if ( self.frameJss ) self.frameJss.remove(self.elmtSelector + selector);
			});

			self.selectorQueue.forEach(function(selector) {
				// Remove styles for special selectors.
				jss.remove(selector);
				if ( self.frameJss ) self.frameJss.remove(selector);
			});
			self.selectorQueue = []; // reset the selectors

			// Remove styles for the selector.
			jss.remove(this.elmtSelector);
			if ( self.frameJss ) self.frameJss.remove(this.elmtSelector);
			api.Frontend.responsiveFrame.doSync();
		};

		ThemifyLiveStyling.prototype.remove = function() {
			if (!this.isInit) {
				return;
			}

			//this._removeAllLiveStyles();

			//this.unsetLiveStyledElmtID();

			this.$liveStyledElmt = null;
			this.currentStyleObj = null;
			this.isInit = false;
		};

		ThemifyLiveStyling.toRGBA = function( color ) {
			var colorArr = color.split( '_' );
			var patt = /^([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})$/;
			if( typeof colorArr[0] !== 'undefined' ) {
				var matches = patt.exec(colorArr[0]);
				var opacity = typeof colorArr[1] !== 'undefined' ? colorArr[1] : 1;
				return matches ? "rgba(" + parseInt(matches[1], 16) + ", " + parseInt(matches[2], 16) + ", " + parseInt(matches[3], 16) + ", " + opacity + ")" : '';
			}
		}

		return ThemifyLiveStyling;
	})(jQuery);
	
})(jQuery);
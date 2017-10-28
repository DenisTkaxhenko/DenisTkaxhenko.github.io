window.themifybuilderapp = window.themifybuilderapp || {};
(function ($) {

    'use strict';

    // extend jquery-ui sortable with beforeStart event
    var oldMouseStart = $.ui.sortable.prototype._mouseStart;
    $.ui.sortable.prototype._mouseStart = function (event, overrideHandle, noActivation) {
        this._trigger('beforeStart', event, this._uiHash());
        oldMouseStart.apply(this, [event, overrideHandle, noActivation]);
    };

    var api = themifybuilderapp = {
        activeModel: null,
        Models: {},
        Collections: {},
        Mixins: {},
        Views: {Modules: {}, Rows: {}, SubRows: {}, Columns: {}, Controls: {}},
        Forms: {},
        Utils: {},
        Instances: {Builder: {}},
        cache: {repeaterElements: {}}
    };

    // Serialize Object Function
    if ('undefined' === typeof $.fn.themifySerializeObject) {
        $.fn.themifySerializeObject = function () {
            var o = {};
            var a = this.serializeArray();
            $.each(a, function () {
                if (o[this.name] !== undefined && this.value) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value);
                } else if (this.value) {

                    /* do not save the value if it's equal to the default */
                    var field = $('#themify_builder_lightbox_container #' + this.name);
                    if (field.length && undefined !== field.data('default') && this.value == field.data('default') && this.value !== 'solid' && !field.hasClass('themify-checkbox')) {
                        return;
                    }

                    o[this.name] = this.value;
                }
            });
            return o;
        };
    }

    api.editing = false;
    api.activeBreakPoint = 'desktop';
    api.Models.Module = Backbone.Model.extend({
        defaults: {
            elType: 'module',
            mod_name: '',
            mod_settings: {}
        },
        initialize: function () {
            api.Models.Registry.register(this.cid, this);
        },
        toRenderData: function () {
            return {
                slug: this.get('mod_name'),
                name: this.get('mod_name'),
                excerpt: this.getExcerpt()
            }
        },
        getExcerpt: function () {
            var excerpt = this.get('mod_settings').content_text || this.get('mod_settings').content_box || '';
            return this.limitString(excerpt, 100);
        },
        limitString: function (str, limit) {
            var new_str;
            str = this.stripHtml(str); // strip html tags

            if (str.toString().length > limit) {
                new_str = str.toString().substr(0, limit);
            }
            else {
                new_str = str.toString();
            }

            return new_str;
        },
        stripHtml: function (html) {
            var tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        },
        setData: function (data) {
            this.set(data, {silent: true});
            this.trigger('custom:change', this);
        },
        // for instant live preview
        getPreviewSettings: function () {
            return _.extend({cid: this.cid}, this.get('mod_settings'), this.get('temp_settings'));
        }
    });

    api.Models.SubRow = Backbone.Model.extend({
        defaults: {
            elType: 'subrow',
            row_order: 0,
            gutter: 'gutter-default',
            column_alignment: '',
            fullwidthvideo: '',
            mutevideo: '',
            unloopvideo: '',
            desktop_dir: 'ltr',
            tablet_dir: 'ltr',
            mobile_dir: 'ltr',
            col_mobile: 'mobile-auto',
            col_tablet: 'tablet-auto',
            cols: {},
            styling: {},
        },
        initialize: function () {
            api.Models.Registry.register(this.cid, this);
        },
        setData: function (data) {
            this.set(data, {silent: true});
            this.trigger('custom:change', this);
        }
    });

    api.Models.Column = Backbone.Model.extend({
        defaults: {
            elType: 'column',
            column_order: '',
            grid_class: '',
            component_name: 'column',
            fullwidthvideo: '',
            mutevideo: '',
            unloopvideo: '',
            modules: {},
            styling: {},
        },
        initialize: function () {
            api.Models.Registry.register(this.cid, this);
        },
        setData: function (data) {
            this.set(data, {silent: true});
            this.trigger('custom:change', this);
        }
    });

    api.Models.Row = Backbone.Model.extend({
        defaults: {
            elType: 'row',
            row_order: 0,
            gutter: 'gutter-default',
            column_alignment: '',
            desktop_dir: 'ltr',
            tablet_dir: 'ltr',
            mobile_dir: 'ltr',
            col_mobile: 'mobile-auto',
            col_tablet: 'tablet-auto',
            fullwidthvideo: '',
            mutevideo: '',
            unloopvideo: '',
            cols: {},
            styling: {},
        },
        initialize: function () {
            api.Models.Registry.register(this.cid, this);
        },
        setData: function (data) {
            this.set(data, {silent: true});
            this.trigger('custom:change', this);
        }
    });

    api.Collections.Rows = Backbone.Collection.extend({
        model: api.Models.Row
    });

    api.Models.Registry = {
        items: {},
        register: function (id, object) {
            this.items[id] = object;
        },
        lookup: function (id) {
            return this.items[id] || null;
        },
        remove: function (id) {
            delete this.items[id];
        },
        destroy: function () {
            _.each(this.items, function (model, cid) {
                model.destroy();
            });
            this.items = {};
            console.log('destroy registry');
        }
    };

    api.Models.setValue = function (cid, data, silent) {
        silent = silent || false;
        var model = api.Models.Registry.lookup(cid);
        model.set(data, {silent: silent});
    };

    api.vent = _.extend({}, Backbone.Events);

    api.Views.register_module = function (type, args) {

        if ('default' !== type)
            this.Modules[ type ] = this.Modules.default.extend(args);
    };

    api.Views.init_module = function (args, type) {
        type = type || 'default';
        if (_.isUndefined(args.mod_settings) && !_.isUndefined(themifyBuilder.modules[ args.mod_name ].defaults)) {
            args.mod_settings = _.extend({}, themifyBuilder.modules[ args.mod_name ].defaults);
        }

        var model = args instanceof api.Models.Module ? args : new api.Models.Module(args),
                callback = this.get_module(type),
                view = new callback({model: model, type: type});

        return {
            model: model,
            view: view
        };
    }

    api.Views.get_module = function (type) {
        type = type || 'default';
        if (this.module_exists(type))
            return this.Modules[ type ];

        return this.Modules.default;
    };

    api.Views.unregister_module = function (type) {

        if ('default' !== type && this.module_exists(type))
            delete this.Modules[ type ];
    };

    api.Views.module_exists = function (type) {

        return this.Modules.hasOwnProperty(type);
    };

    // column
    api.Views.register_column = function (type, args) {

        if ('default' !== type)
            this.Columns[ type ] = this.Columns.default.extend(args);
    };

    api.Views.init_column = function (args, type) {
        type = type || 'default';
        var model = args instanceof api.Models.Column ? args : new api.Models.Column(args),
                callback = this.get_column(type),
                view = new callback({model: model, type: type});

        return {
            model: model,
            view: view
        };
    }

    api.Views.get_column = function (type) {
        type = type || 'default';
        if (this.column_exists(type))
            return this.Columns[ type ];

        return this.Columns.default;
    };

    api.Views.unregister_column = function (type) {

        if ('default' !== type && this.column_exists(type))
            delete this.Columns[ type ];
    };

    api.Views.column_exists = function (type) {

        return this.Columns.hasOwnProperty(type);
    };

    // sub-row
    api.Views.register_subrow = function (type, args) {

        if ('default' !== type)
            this.SubRows[ type ] = this.SubRows.default.extend(args);
    };

    api.Views.init_subrow = function (args, type) {
        type = type || 'default';
        var model = args instanceof api.Models.SubRow ? args : new api.Models.SubRow(args),
                callback = this.get_subrow(type),
                view = new callback({model: model, type: type});

        return {
            model: model,
            view: view
        };
    }

    api.Views.get_subrow = function (type) {
        type = type || 'default';
        if (this.subrow_exists(type))
            return this.SubRows[ type ];

        return this.SubRows.default;
    };

    api.Views.unregister_subrow = function (type) {

        if ('default' !== type && this.subrow_exists(type))
            delete this.SubRows[ type ];
    };

    api.Views.subrow_exists = function (type) {

        return this.SubRows.hasOwnProperty(type);
    };

    // Row
    api.Views.register_row = function (type, args) {

        if ('default' !== type)
            this.Rows[ type ] = this.Rows.default.extend(args);
    };

    api.Views.init_row = function (args, type) {
        type = type || 'default';
        var model = args instanceof api.Models.Row ? args : new api.Models.Row(args),
                callback = this.get_row(type),
                view = new callback({model: model, type: type});

        return {
            model: model,
            view: view
        };
    }

    api.Views.get_row = function (type) {
        type = type || 'default';
        if (this.row_exists(type))
            return this.Rows[ type ];

        return this.Rows.default;
    };

    api.Views.unregister_row = function (type) {

        if ('default' !== type && this.row_exists(type))
            delete this.Rows[ type ];
    };

    api.Views.row_exists = function (type) {

        return this.Rows.hasOwnProperty(type);
    };

    api.Views.BaseElement = Backbone.View.extend({
        type: 'default',
        menuTouched: [],
        events: {
            'click .themify_builder_copy_component': 'copy',
            'click .themify_builder_paste_component': 'paste',
            'click .themify_builder_import_component': 'import',
            'click .themify_builder_export_component': 'export',
            'hover .module_menu': 'actionMenuHover',
            'hover .row_menu': 'actionMenuHover'
        },
        initialize: function (options) {
            _.extend(this, _.pick(options, 'type'));

            this.listenTo(this.model, 'change', this.renderInlineData);
            this.listenTo(this.model, 'custom:change', this.modelChange);
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'custom:dom:update', this.domUpdate);
        },
        modelChange: function (model) {
            this.$el.attr(_.extend({}, _.result(this, 'attributes')));
            this.render();
        },
        remove: function () {
            //api.Models.Registry.remove( this.model.cid );
            this.$el.remove();
        },
        domUpdate: function () {
            this.setElement($('[data-cid="' + this.model.cid + '"]'));
        },
        renderInlineData: function () {
        }, // will be overwrited by sub-view
        copy: function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('copy');

            var $thisElem = $(e.currentTarget),
                    data = {},
                    component = ThemifyBuilderCommon.detectBuilderComponent($thisElem);

            switch (component) {
                case 'row':
                case 'sub-row':
                    var $selectedRow = $thisElem.closest('.themify_builder_' + component.replace('-', '_')),
                            rowOrder = $selectedRow.index(),
                            rowData = 'row' === component ? api.Utils._getRowSettings($selectedRow, rowOrder) : api.Utils._getSubRowSettings($selectedRow, rowOrder),
                            data = JSON.stringify(rowData);
                    if ('row' === component) {
                        $selectedRow.find('.themify_builder_dropdown').hide();
                    }
                    break;

                case 'module':
                    var $selectedModule = $thisElem.closest('.active_module'),
                            moduleName = $selectedModule.data('mod-name'),
                            moduleData = JSON.parse($selectedModule.find('.themify_module_settings').find('script[type="text/json"]').text()),
                            data = JSON.stringify({
                                mod_name: moduleName,
                                mod_settings: moduleData
                            });
                    break;

                case 'column':
                case 'sub-column':
                    var $selectedColumn = $thisElem.closest('.themify_builder_col'),
                            $selectedRow = $thisElem.closest('column' === component ? '.themify_builder_row' : '.themify_builder_sub_row'),
                            rowOrder = $selectedRow.index(),
                            rowData = 'column' === component ? api.Utils._getRowSettings($selectedRow, rowOrder) : api.Utils._getSubRowSettings($selectedRow, rowOrder),
                            columnOrder = $selectedColumn.index(),
                            columnData = rowData.cols[ columnOrder ],
                            data = JSON.stringify(columnData);
                    break;
            }
            ThemifyBuilderCommon.Clipboard.set(component, data);
        },
        paste: function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('paste');

            var $thisElem = $(e.currentTarget),
                    component = ThemifyBuilderCommon.detectBuilderComponent($thisElem),
                    dataInJSON = ThemifyBuilderCommon.Clipboard.get(component);

            if (dataInJSON === false) {
                ThemifyBuilderCommon.alertWrongPaste();
                return;
            }

            if (!ThemifyBuilderCommon.confirmDataPaste()) {
                return;
            }

            var $container = this.$el.closest('[data-postid]'),
                    dataPlainObject = JSON.parse(dataInJSON);

            api.vent.trigger('dom:observer:start', $container, {cid: this.model.cid, value: this.model.toJSON()});

            if (component === 'column' || component === 'sub-column') {

                var $selectedCol = $thisElem.closest('.themify_builder_col'),
                        $selectedRow = $thisElem.closest('column' === component ? '.themify_builder_row' : '.themify_builder_sub_row'),
                        col_index = $selectedCol.index(),
                        row_index = $selectedRow.index();
                dataPlainObject['column_order'] = col_index;
                dataPlainObject['grid_class'] = $selectedCol.prop('class').replace('themify_builder_col', '');

                if ('column' === component) {
                    dataPlainObject['row_order'] = row_index;
                } else {
                    dataPlainObject['sub_row_order'] = row_index;
                    dataPlainObject['row_order'] = $selectedCol.closest('.themify_builder_row').index();
                    dataPlainObject['col_order'] = $selectedCol.parents('.themify_builder_col').index();
                }
                dataPlainObject['component_name'] = component;

            }
            this.model.setData(dataPlainObject);
            api.vent.trigger('dom:builder:change');

            if ('visual' !== this.type) {
                api.vent.trigger('dom:observer:end', $container, {cid: this.model.cid, value: this.model.toJSON()});
            }
        },
        import: function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('import');

            var $thisElem = $(e.currentTarget),
                    component = ThemifyBuilderCommon.detectBuilderComponent($thisElem),
                    options = {
                        data: {
                            action: 'tfb_imp_component_data_lightbox_options'
                        }
                    };

            api.activeModel = api.Models.Registry.lookup($thisElem.closest('[data-cid]').data('cid'));

            switch (component) {
                case 'row':
                case 'sub-row':
                    var $selectedRow = $thisElem.closest('.themify_builder_' + component.replace('-', '_'));
                    ThemifyBuilderCommon.highlightRow($selectedRow);
                    break;
                case 'module':
                    var $selectedModule = $thisElem.closest('.themify_builder_module');
                    $('.themify_builder_module').removeClass('current_selected_module');
                    $selectedModule.addClass('current_selected_module');
                    break;

                case 'column':
                case 'sub-column':
                    var $selectedCol = $thisElem.closest('.themify_builder_col'),
                            $selectedRow = $thisElem.closest('column' === component ? '.themify_builder_row' : '.themify_builder_sub_row');
                    options.data.indexData = {row: $selectedRow.index(), col: $selectedCol.index()};
                    ThemifyBuilderCommon.highlightColumn($selectedCol);
                    break;
            }
            options.data.component = component;
            ThemifyBuilderCommon.Lightbox.open(options, null);
        },
        export: function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('export');

            var self = this,
                    callback = '',
                    $thisElem = $(e.currentTarget),
                    component = ThemifyBuilderCommon.detectBuilderComponent($thisElem),
                    options = {
                        data: {
                            action: 'tfb_exp_component_data_lightbox_options'
                        }
                    };

            switch (component) {
                case 'row':
                case 'sub-row':
                    var $selectedRow = $thisElem.closest('.themify_builder_' + component.replace('-', '_'));
                    callback = function () {
                        var rowOrder = $selectedRow.index(),
                                rowData = component === 'row' ? api.Utils._getRowSettings($selectedRow, rowOrder) : api.Utils._getSubRowSettings($selectedRow, rowOrder);
                        rowData['component_name'] = component;

                        var rowDataInJson = JSON.stringify(rowData),
                                $rowDataTextField = $('#tfb_exp_' + component.replace('-', '_') + '_data_field');
                        $rowDataTextField.val(rowDataInJson);

                        self._autoSelectInputField($rowDataTextField);
                        $rowDataTextField.on('click', function () {
                            self._autoSelectInputField($rowDataTextField)
                        });
                    };
                    break;
                case 'module':
                    var $selectedModule = $thisElem.closest('.active_module');

                    callback = function () {
                        var moduleName = $selectedModule.data('mod-name'),
                                moduleData = JSON.parse($selectedModule.find('.themify_module_settings').find('script[type="text/json"]').text()),
                                moduleDataInJson = JSON.stringify({
                                    mod_name: moduleName,
                                    mod_settings: moduleData,
                                    component_name: 'module'
                                });

                        var $moduleDataTextField = $('#tfb_exp_module_data_field');
                        $moduleDataTextField.val(moduleDataInJson);

                        self._autoSelectInputField($moduleDataTextField);
                        $moduleDataTextField.on('click', function () {
                            self._autoSelectInputField($moduleDataTextField)
                        });
                    };
                    break;

                case 'column':
                case 'sub-column':
                    var $selectedRow = $thisElem.closest('column' === component ? '.themify_builder_row' : '.themify_builder_sub_row'),
                            $selectedCol = $thisElem.closest('.themify_builder_col');

                    callback = function () {
                        var rowOrder = $selectedRow.index(),
                                rowData = 'column' === component ? api.Utils._getRowSettings($selectedRow, rowOrder) : api.Utils._getSubRowSettings($selectedRow, rowOrder),
                                columnOrder = $selectedCol.index(),
                                columnData = rowData.cols[ columnOrder ];
                        columnData['component_name'] = component;

                        var columnDataInJson = JSON.stringify(columnData),
                                $columnDataTextField = $('#tfb_exp_' + component.replace('-', '_') + '_data_field');
                        $columnDataTextField.val(columnDataInJson);

                        self._autoSelectInputField($columnDataTextField);
                        $columnDataTextField.on('click', function () {
                            self._autoSelectInputField($columnDataTextField)
                        });
                    };


                    break;
            }
            options.data.component = component;
            ThemifyBuilderCommon.Lightbox.open(options, callback);
        },
        actionMenuHover: function (e) {
            e.stopPropagation();
            var $this = $(e.currentTarget);

            if ('touchend' === e.type) {
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
                    $this.find('.themify_builder_dropdown').stop(false, true).hide();
                    $row.css('z-index', '');
                    ThemifyPageBuilder.menuTouched = [];
                } else {
                    var $builderCont = this.$el;
                    $builderCont.find('.themify_builder_dropdown').stop(false, true).hide();
                    $builderCont.find('.themify_builder_row').css('z-index', '');
                    $this.find('.themify_builder_dropdown').stop(false, true).show();
                    $row.css('z-index', '998');
                    this.menuTouched = [];
                    this.menuTouched[index] = true;
                }
            } else if (e.type === 'mouseenter') {
                $this.find('.themify_builder_dropdown').stop(false, true).show();
            } else if (e.type === 'mouseleave') {
                $this.find('.themify_builder_dropdown').stop(false, true).hide();
            }
        },
        getBuilderID: function () {
            return this.$el.closest('[data-postid]').data('postid');
        }
    });

    api.Views.BaseElement.extend = function (child) {
        var self = this,
                view = Backbone.View.extend.apply(this, arguments);
        view.prototype.events = _.extend({}, this.prototype.events, child.events);
        view.prototype.initialize = function () {
            if (_.isFunction(self.prototype.initialize))
                self.prototype.initialize.apply(this, arguments);
            if (_.isFunction(child.initialize))
                child.initialize.apply(this, arguments);
        }
        return view;
    };

    api.Views.Modules['default'] = api.Views.BaseElement.extend({
        tagName: 'div',
        attributes: function () {
            return {
                'class': 'themify_builder_module module-' + this.model.get('mod_name') + ' active_module',
                'data-mod-name': this.model.get('mod_name'),
                'data-cid': this.model.cid
            };
        },
        template: wp.template('builder_module_item'),
        events: {
            'dblclick': 'edit',
            'click .themify_module_options': 'edit',
            'click .js--themify_builder_module_styling': 'edit',
            'click .themify_module_delete': 'delete',
            'click .themify_module_duplicate': 'duplicate'
        },
        initialize: function () {
            this.listenTo(this, 'edit', this.edit);
            this.listenTo(this.model, 'custom:element:updatestate', this.removeInitialStateClass);
        },
        render: function () {
            this.$el.html(this.template(this.model.toRenderData()));
            this.renderInlineData();
            return this;
        },
        renderInlineData: function () {
            this.$('.themify_module_settings').find('script[type="text/json"]').text(JSON.stringify(this.model.get('mod_settings')));
        },
        removeInitialStateClass: function () {
            this.$el.removeClass('tb_module_state_unsaved');
        },
        edit: function (e, isNewModule) {
			// editing module

			// Get module name in backend
			var module_name = (!_.isNull(e) && !_.isUndefined(e)) ? $( e.target ).data( 'module-name' ) : '';
			// Get module name in frontend
			if( typeof module_name == 'undefined' || module_name == '' ) module_name = (!_.isNull(e) && !_.isUndefined(e)) ? $( e.target ).closest( '.themify_builder_module_front' ).data( 'mod-name' ) : '';
			// Check if module is excluded
			if( module_name != '' && themifyBuilder.excludedModules.indexOf( module_name ) !== -1 ){
				e.preventDefault();
				alert( themifyBuilder.i18n.editExcludedModule );
				return;
			}

			var triggerGallery = false;
            if (!_.isNull(e) && !_.isUndefined(e)) {
                e.preventDefault();
                this.model.set({styleClicked: $(e.currentTarget).hasClass('themify_builder_module_styling')}, {silent: true});
            } else {
                triggerGallery = true;
            }

            isNewModule = isNewModule || false; // assume that if isNewModule:true = Add module, otherwise Edit Module
            api.activeModel = this.model;

            var self = this,
                    el_settings = this.model.get('mod_settings');

            $('.module_menu .themify_builder_dropdown').hide();

            this.highlightModuleBack(this.$el);

            if (!isNewModule) {
                api.vent.trigger('dom:observer:start', this.$el.closest('[data-postid]'), {cid: api.activeModel.cid, value: {mod_settings: api.activeModel.get('mod_settings')}});
            }

            var callback = function (response) {
                response.setAttribute('data-form-state', (isNewModule ? 'new' : 'edit'));

                if ('desktop' !== api.activeBreakPoint) {
                    var styleFields = $('#themify_builder_options_styling .tfb_lb_option').map(function () {
                        return $(this).attr('id');
                    }).get();
                    el_settings = _.omit(el_settings, styleFields);

                    if (!_.isUndefined(el_settings['breakpoint_' + api.activeBreakPoint]) && _.isObject(el_settings['breakpoint_' + api.activeBreakPoint])) {
                        el_settings = _.extend(el_settings, el_settings['breakpoint_' + api.activeBreakPoint]);
                    }
                }

                if ('visual' === self.type) {
                    api.liveStylingInstance.init(self.$el.children('.module'), el_settings);
                }

                var inputs = response.getElementsByClassName('tfb_lb_option'), iterate,
                        is_settings_exist = !_.isEmpty(el_settings);
                for (iterate = 0; iterate < inputs.length; ++iterate) {
                    var $this_option = $(inputs[iterate]),
                            this_option_id = $this_option.attr('id'),
                            $found_element = el_settings[this_option_id];

                    if ($this_option.hasClass('themify-gradient')) {
                        api.Utils.createGradientPicker($this_option, $found_element);
                    } else if ($this_option.hasClass('tf-radio-input-container')) {
                        //@todo move this
                        $this_option.find(':checked').trigger('change');
                    }
                    if ($found_element) {
                        if ($this_option.hasClass('select_menu_field')) {
                            if (!isNaN($found_element)) {
                                $this_option.find("option[data-termid='" + $found_element + "']").attr('selected', 'selected');
                            } else {
                                $this_option.find("option[value='" + $found_element + "']").attr('selected', 'selected');
                            }
                        } else if ($this_option.is('select')) {
                            $this_option.val($found_element).trigger('change');
                        } else if ($this_option.hasClass('themify-builder-uploader-input')) {
                            var img_field = $found_element,
                                    img_thumb = $('<img/>', {src: img_field, width: 50, height: 50});

                            if (img_field) {
                                $this_option.val(img_field);
                                $this_option.parent().find('.img-placeholder').empty().html(img_thumb).parent().show();
                            }
                            else {
                                $this_option.parent().find('.thumb_preview').hide();
                            }

                        } else if ($this_option.hasClass('themify-option-query-cat')) {
                            var parent = $this_option.parent(),
                                    multiple_cat = parent.find('.query_category_multiple'),
                                    elems = $found_element,
                                    value = elems.split('|'),
                                    cat_val = value[0];

                            parent.find("option[value='" + cat_val + "']").attr('selected', 'selected');
                            multiple_cat.val(cat_val);

                        } else if ($this_option.hasClass('themify_builder_row_js_wrapper')) {
                            var row_append = 0;
                            api.cache.repeaterElements[ $this_option.attr('id') ] = $this_option.find('.tb_repeatable_field').first().clone();
                            if ($found_element.length > 0) {
                                row_append = $found_element.length - 1;
                            }

                            // add new row
                            for (var i = 0; i < row_append; i++) {
                                $this_option.parent().find('.add_new a').first().trigger('click');
                            }

                            $this_option.find('.tb_repeatable_field').each(function (r) {
                                $(this).find('.tfb_lb_option_child').each(function (i) {
                                    var $this_option_child = $(this),
                                            this_option_id_real = $this_option_child.attr('id'),
                                            this_option_id_child = $this_option_child.hasClass('tfb_lb_wp_editor') ? $this_option_child.attr('name') : $this_option_child.data('input-id');
                                    if (!this_option_id_child) {
                                        this_option_id_child = this_option_id_real;
                                    }
                                    var $found_element_child = $found_element[r]['' + this_option_id_child + ''];

                                    if ($this_option_child.hasClass('themify-builder-uploader-input')) {
                                        var img_field = $found_element_child,
                                                img_thumb = $('<img/>', {src: img_field, width: 50, height: 50});

                                        if (img_field) {
                                            $this_option_child.val(img_field);
                                            $this_option_child.parent().find('.img-placeholder').empty().html(img_thumb).parent().show();
                                        }
                                        else {
                                            $this_option_child.parent().find('.thumb_preview').hide();
                                        }

                                    }
                                    else if ($this_option_child.hasClass('tf-radio-choice')) {
                                        $this_option_child.find("input[value='" + $found_element_child + "']").attr('checked', 'checked').trigger('change');
                                    } else if ($this_option_child.hasClass('themify-layout-icon')) {
                                        $this_option_child.find('#' + $found_element_child).addClass('selected');
                                    }
                                    else if ($this_option_child.hasClass('themify-checkbox')) {
                                        for (var $i in $found_element_child) {

                                            $this_option_child.find("input[value='" + $found_element_child[$i] + "']").prop('checked', true);
                                        }
                                    }
                                    else if ($this_option_child.is('input, textarea, select')) {
                                        $this_option_child.val($found_element_child);
                                    }

                                    if ($this_option_child.hasClass('tfb_lb_wp_editor')) {
                                        api.Views.init_control('wp_editor', {el: $this_option_child});
                                    }

                                    if ($this_option_child.data('control-binding') && $this_option_child.data('control-type')) {
                                        api.Views.init_control($this_option_child.data('control-type'), {el: $this_option_child, binding_type: $this_option_child.data('control-binding')});
                                    }

                                });
                            });

                        } else if ($this_option.hasClass('tf-radio-input-container')) {
                            $this_option.find("input[value='" + $found_element + "']").attr('checked', 'checked').trigger('change');
                            var selected_group = $this_option.find('input[name="' + this_option_id + '"]:checked').val();

                            // has group element enable
                            if ($this_option.hasClass('tf-option-checkbox-enable')) {
                                $this_option.find('.tf-group-element').hide();
                                $this_option.find('.tf-group-element-' + selected_group).show();
                            }

                        } else if ($this_option.is('input[type!="checkbox"][type!="radio"], textarea')) {
                            $this_option.val($found_element).trigger('change');
                            if (!isNewModule && $this_option.is('textarea') && $this_option.hasClass('tf-thumbs-preview')) {
                                self.getShortcodePreview($this_option, $found_element);
                            }
                        } else if ($this_option.hasClass('themify-checkbox')) {
                            var cselected = $found_element;
                            cselected = cselected.split('|');

                            $this_option.find('.tf-checkbox').each(function () {
                                $(this).prop('checked', ($.inArray($(this).val(), cselected) !== -1));
                            });

                        } else if ($this_option.hasClass('themify-layout-icon')) {
                            $this_option.find('#' + $found_element).addClass('selected');
                        } else {
                            $this_option.html($found_element);
                        }
                    }
                    else {
                        if ($this_option.hasClass('themify-layout-icon')) {
                            $this_option.children().first().addClass('selected');
                        }
                        else if ($this_option.hasClass('themify-builder-uploader-input')) {
                            $this_option.parent().find('.thumb_preview').hide();
                        }
                        else if ($this_option.hasClass('tf-radio-input-container')) {
                            $this_option.find('input[type="radio"]').first().prop('checked');
                            var selected_group = $this_option.find('input[name="' + this_option_id + '"]:checked').val();

                            // has group element enable
                            if ($this_option.hasClass('tf-option-checkbox-enable')) {
                                $this_option.find('.tf-group-element').hide();
                                $this_option.find('.tf-group-element-' + selected_group).show();
                            }
                        }
                        else if ($this_option.hasClass('themify_builder_row_js_wrapper')) {
                            api.cache.repeaterElements[ $this_option.attr('id') ] = $this_option.find('.tb_repeatable_field').first().clone();
                            $this_option.find('.tb_repeatable_field').each(function (r) {
                                $(this).find('.tfb_lb_option_child').each(function (i) {
                                    var $this_option_child = $(this);

                                    if ($this_option_child.hasClass('tfb_lb_wp_editor')) {
                                        api.Views.init_control('wp_editor', {el: $this_option_child});
                                    }

                                    if ($this_option_child.data('control-binding') && $this_option_child.data('control-type')) {
                                        api.Views.init_control($this_option_child.data('control-type'), {el: $this_option_child, binding_type: $this_option_child.data('control-binding')});
                                    }

                                });
                            });
                        }
                        else if ($this_option.hasClass('themify-checkbox') && is_settings_exist) {
                            $this_option.find('.tf-checkbox').each(function () {
                                $(this).prop('checked', false);
                            });
                        }
                        else if ($this_option.is('input[type!="checkbox"][type!="radio"], textarea') && is_settings_exist) {
                            $this_option.val('');
                        }
                    }

                    if ($this_option.hasClass('tfb_lb_wp_editor')) {
                        api.Views.init_control('wp_editor', {el: $this_option});
                    }

                    if ($this_option.hasClass('themify_builder_row_js_wrapper')) {
                        api.Views.init_control('repeater', {el: $this_option, binding_type: $this_option.data('control-binding')});
                    }

                    if ($this_option.data('control-binding') && $this_option.data('control-type') && 'repeater' !== $this_option.data('control-type')) {
                        api.Views.init_control($this_option.data('control-type'), {el: $this_option, binding_type: $this_option.data('control-binding')});
                    }

                } // iterate

                // Trigger event
                $('body').trigger('editing_module_option', [el_settings]);
                $('.tf-option-checkbox-enable input:checked').trigger('change');

                // shortcut tabs
                if (self.model.get('styleClicked') && $('a[href="#themify_builder_options_styling"]').length) {
                    $('a[href="#themify_builder_options_styling"]').trigger('click');
                }

                // trigger gallery
                if (triggerGallery && 'gallery' === self.model.get('mod_name')) {
                    $('.tf-gallery-btn', response).trigger('click');
                }

                // colorpicker
                api.Utils.setColorPicker(response);

                // plupload init
                api.Utils.builderPlupload('normal');

                // option binding setup
                self.moduleOptionsBinding();

                // tabular options
                $('.themify_builder_tabs').tabs();

                // "Apply all" // apply all init
                self.applyAll_init();
                ThemifyBuilderCommon.fontPreview($('#themify_builder_lightbox_container'), el_settings);

                if ('visual' === self.type) {
                    ThemifyBuilderCommon.Lightbox.rememberRow(self);
                    api.liveStylingInstance.addStyleDate(self.model.get('mod_name'), themifyBuilder.element_style_rules[ self.model.get('mod_name') ]);
                    api.styleSheet.rememberRules();
                }
            };

            ThemifyBuilderCommon.highlightRow(this.$el.closest('.themify_builder_row'));

            ThemifyBuilderCommon.Lightbox.open({loadMethod: 'inline', templateID: 'builder_form_module_' + this.model.get('mod_name')}, function (response) {
                setTimeout(function () {
                    callback(response);
                }, 400);
            });
        },
        delete: function (e) {
            e.preventDefault();

            if (confirm(themifyBuilder.i18n.moduleDeleteConfirm)) {
                var $container = this.$el.closest('[data-postid]');

                api.vent.trigger('dom:observer:start', $container);

                this.model.destroy();

                api.vent.trigger('dom:observer:end', $container);

                api.vent.trigger('dom:builder:change');
            }
        },
        duplicate: function (e) {
            e.preventDefault();

            var moduleView = api.Views.init_module(this.model.toJSON(), this.type),
                    $container = this.$el.closest('[data-postid]');

            api.vent.trigger('dom:observer:start', $container);

            moduleView.view.render().$el.insertAfter(this.$el);

            if ('visual' !== this.type) {
                api.vent.trigger('dom:builder:change');
                api.vent.trigger('dom:observer:end', $container);
            }

            moduleView.view.trigger('component:duplicate');
        }
    });

    api.Views.Columns['default'] = api.Views.BaseElement.extend({
        tagName: 'div',
        attributes: function () {
            return {
                'class': 'themify_builder_col module_column ' + this.model.get('grid_class'),
                'style': this.model.get('grid_width') ? 'width:' + this.model.get('grid_width') + '%' : '',
                'data-cid': this.model.cid,
                'data-fullwidthvideo': this.model.get('fullwidthvideo'),
                'data-mutevideo': this.model.get('mutevideo'),
                'data-unloopvideo': this.model.get('unloopvideo')
            };
        },
        template: wp.template('builder_column_item'),
        events: {
            'click .themify_builder_option_column': 'edit',
            'click .js-column-menu-icon': 'click_menu_icon'
        },
        initialize: function () {
            this.listenTo(this.model, 'change:styling', this.renderInlineData);
            this.listenTo(this.model, 'change:grid_class', this.renderAttrs);
        },
        renderAttrs: function () {
            this.$el.attr(_.extend({}, _.result(this, 'attributes')));
        },
        render: function () {
            this.$el.html(this.template(this.model.toJSON()));
            this.renderInlineData();

            // check if it has module
            if (!_.isEmpty(this.model.get('modules'))) {
                var container = document.createDocumentFragment();
                _.each(this.model.get('modules'), function (value, key) {

                    if (_.isNull(value))
                        return true;
                    if (_.isObject(value.mod_settings)) {
                        value.mod_settings = api.Utils.removeEmptyFields(value.mod_settings);
                    }
                    else if (_.isObject(value.styling)) {
                        value.styling = api.Utils.removeEmptyFields(value.styling);
                    }
                    var moduleView = value && _.isUndefined(value.cols) ? api.Views.init_module(value, this.type) : api.Views.init_subrow(value, this.type);

                    container.appendChild(moduleView.view.render().el);
                }, this);
                this.$el.find('.themify_module_holder').append(container);
            }
            return this;
        },
        renderInlineData: function () {
            var styling = this.model.get('styling'), video = '', mute = '', loop = '';
            ;
            if (_.isObject(styling)) {
                video = !_.isEmpty(styling.background_video) && styling.background_type === 'video' ? styling.background_video : '';
                if (video && !_.isEmpty(styling.background_video_options)) {
                    mute = styling.background_video_options.indexOf('mute') !== -1 ? 'mute' : '';
                    loop = styling.background_video_options.indexOf('unloop') !== -1 ? 'unloop' : '';
                }
            }
            this.$el.attr({'data-fullwidthvideo': video, 'data-mutevideo': mute, 'data-unloopvideo': loop});
            this.$el.children('.column-data-styling').attr('data-styling', JSON.stringify(styling));
        },
        edit: function (e) {
            e.preventDefault();
            e.stopPropagation();

            api.activeModel = this.model;
            var self = this;

            api.vent.trigger('dom:observer:start', this.$el.closest('[data-postid]'), {cid: api.activeModel.cid, value: {styling: api.activeModel.get('styling')}});


            ThemifyBuilderCommon.highlightColumn(this.$el);
            ThemifyBuilderCommon.highlightRow(this.$el.closest('.themify_builder_row'));

            ThemifyBuilderCommon.Lightbox.open({loadMethod: 'inline', templateID: 'builder_form_column'}, function () {
                api.Mixins.Builder.editComponent('column', self);
            });

        },
        click_menu_icon: function (e) {
            e.preventDefault();
        }
    });

    // SubRow view share same model as ModuleView
    api.Views.SubRows['default'] = api.Views.BaseElement.extend({
        tagName: 'div',
        attributes: function () {
            return {
                'class': 'themify_builder_sub_row module_subrow clearfix  module_subrow_' + this.model.cid + ' tb_element_cid_' + this.model.cid,
                'data-column-alignment': this.model.get('column_alignment'),
                'data-desktop_dir': this.model.get('desktop_dir'),
                'data-tablet_dir': this.model.get('tablet_dir'),
                'data-mobile_dir': this.model.get('mobile_dir'),
                'data-mobile': this.model.get('col_mobile'),
                'data-tablet': this.model.get('col_tablet'),
                'data-gutter': this.model.get('gutter'),
                'data-cid': this.model.cid,
                'data-fullwidthvideo': this.model.get('fullwidthvideo'),
                'data-mutevideo': this.model.get('mutevideo'),
                'data-unloopvideo': this.model.get('unloopvideo')
            };
        },
        template: wp.template('builder_sub_row_item'),
        events: {
            'click .themify_builder_style_subrow': 'edit',
            'click .sub_row_delete': 'delete',
            'click .sub_row_duplicate': 'duplicate'
        },
        initialize: function () {
            this.listenTo(this.model, 'change:styling', this.renderInlineData);
        },
        render: function () {
            this.$el.html(this.template(this.model.toJSON()));
            this.renderInlineData();

            if (!_.isUndefined(this.model.get('cols'))) {
                var container = document.createDocumentFragment();
                _.each(this.model.get('cols'), function (value, key) {
                    value.component_name = 'sub-column';
                    if (_.isObject(value.styling)) {
                        value.styling = api.Utils.removeEmptyFields(value.styling);
                    }
                    var columnView = api.Views.init_column(value, this.type);

                    container.appendChild(columnView.view.render().el);
                }, this);
                this.$el.find('.themify_builder_sub_row_content').append(container);
            }

            var directions = {},
                    col_tablet = this.model.get('col_tablet'),
                    col_mobile = this.model.get('col_mobile'),
                    row_content = this.$el.find('.themify_builder_sub_row_content');
            directions['desktop'] = this.model.get('desktop_dir');
            directions['tablet'] = this.model.get('tablet_dir');
            directions['mobile'] = this.model.get('mobile_dir');


            if (!col_tablet) {
                col_tablet = 'tablet-auto';
            }
            if (!col_mobile) {
                col_mobile = 'mobile-auto';
            }
            if (!directions['desktop']) {
                directions['desktop'] = 'ltr';
            }
            if (!directions['tablet']) {
                directions['tablet'] = 'ltr';
            }
            if (!directions['mobile']) {
                directions['mobile'] = 'ltr';
            }
            var $el = this.$el;
            $.each(directions, function (i, d) {
                if (d !== 'ltr') {
                    $el.find('.themify_builder_grid_' + i + ' .themify_builder_column_direction').first().find('li').removeClass('selected').children('.column-dir-' + d).parent().addClass('selected');
                }
            });
            if (api.mode === 'visual') {
                row_content.removeClass('desktop-col-direction-ltr desktop-col-direction-rtl tablet-col-direction-ltr tablet-col-direction-rtl mobile-col-direction-ltr mobile-col-direction-rtl')
                        .addClass('tfb_grid_classes desktop-col-direction-' + directions['desktop'] + ' tablet-col-direction-' + directions['tablet'] + ' mobile-col-direction-' + directions['mobile']);
            }
            else {
                row_content.addClass('direction-' + directions['desktop']);
            }
            var mobile_item = this.$el.find('.themify_builder_grid_mobile .' + col_mobile).first(),
                    tablet_item = this.$el.find('.themify_builder_grid_tablet .' + col_tablet).first(),
                    mcol = parseInt(mobile_item.data('col')),
                    tcol = parseInt(tablet_item.data('col'));
            mobile_item.closest('li').addClass('selected');
            tablet_item.closest('li').addClass('selected');
            if (mcol === 2 || mcol === 3) {
                col_mobile += ' mobile-' + (mcol === 2 ? '2col' : '3col');
            }
            if (tcol === 2 || tcol === 3) {
                col_tablet += ' tablet-' + (tcol === 2 ? '2col' : '3col');
            }
            row_content.addClass(this.model.get('gutter') + ' ' + col_mobile + ' ' + col_tablet);

            return this;
        },
        renderInlineData: function () {

            var styling = this.model.get('styling'), video = '', mute = '', loop = '';
            ;
            if (_.isObject(styling)) {
                video = !_.isEmpty(styling.background_video) && styling.background_type === 'video' ? styling.background_video : '';
                if (video && !_.isEmpty(styling.background_video_options)) {
                    mute = styling.background_video_options.indexOf('mute') !== -1 ? 'mute' : '';
                    loop = styling.background_video_options.indexOf('unloop') !== -1 ? 'unloop' : '';
                }
            }
            this.$el.attr({'data-fullwidthvideo': video, 'data-mutevideo': mute, 'data-unloopvideo': loop});
            this.$el.find('.gutter_select').val(this.model.get('gutter'));
            this.$el.find('[data-alignment="' + this.model.get('column_alignment') + '"]').parent().addClass('selected').siblings().removeClass('selected');
            this.$el.children('.subrow-data-styling').attr('data-styling', JSON.stringify(styling));

            if (this.$el.find('[data-alignment="' + this.model.get('column_alignment') + '"]') !== 'col_align_top') {
                setTimeout(function () {
                    this.$el.find('[data-alignment="' + this.model.get('column_alignment') + '"]').trigger('click');
                }.bind(this), 400);
            }
        },
        edit: function (e) {
            e.preventDefault();
            e.stopPropagation();

            api.activeModel = this.model;
            var self = this;

            api.vent.trigger('dom:observer:start', this.$el.closest('[data-postid]'), {cid: api.activeModel.cid, value: {styling: api.activeModel.get('styling')}});


            ThemifyBuilderCommon.highlightSubRow(this.$el);
            ThemifyBuilderCommon.highlightColumn(this.$el.closest('.themify_builder_column'));
            ThemifyBuilderCommon.highlightRow(this.$el.closest('.themify_builder_row'));

            ThemifyBuilderCommon.Lightbox.open({loadMethod: 'inline', templateID: 'builder_form_subrow'}, function () {
                api.Mixins.Builder.editComponent('sub-row', self);
            });

        },
        delete: function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (confirm(themifyBuilder.i18n.subRowDeleteConfirm)) {

                var $container = this.$el.closest('[data-postid]');

                api.vent.trigger('dom:observer:start', $container);

                this.model.destroy();

                api.vent.trigger('dom:observer:end', $container);

                api.vent.trigger('dom:builder:change');
            }
        },
        duplicate: function (e) {
            e.preventDefault();
            e.stopPropagation();

            var subRowView = api.Views.init_subrow(api.Utils._getSubRowSettings(this.$el, this.$el.index()), this.type),
                    $container = this.$el.closest('[data-postid]');

            api.vent.trigger('dom:observer:start', $container);

            subRowView.view.render().$el.insertAfter(this.$el);

            subRowView.view.trigger('component:duplicate');

            if ('visual' !== this.type) {
                api.vent.trigger('dom:builder:change');
                api.vent.trigger('dom:observer:end', $container);
            }
        }
    });

    api.Views.Rows['default'] = api.Views.BaseElement.extend({
        tagName: 'div',
        attributes: function () {
            return {
                'class': 'themify_builder_row module_row clearfix module_row_' + this.model.cid + ' tb_element_cid_' + this.model.cid,
                'data-column-alignment': this.model.get('column_alignment'),
                'data-desktop_dir': this.model.get('desktop_dir'),
                'data-tablet_dir': this.model.get('tablet_dir'),
                'data-mobile_dir': this.model.get('mobile_dir'),
                'data-mobile': this.model.get('col_mobile'),
                'data-tablet': this.model.get('col_tablet'),
                'data-gutter': this.model.get('gutter'),
                'data-cid': this.model.cid,
                'data-fullwidthvideo': this.model.get('fullwidthvideo'),
                'data-mutevideo': this.model.get('mutevideo'),
                'data-unloopvideo': this.model.get('unloopvideo')
            };
        },
        template: wp.template('builder_row_item'),
        events: {
            'click .themify_builder_option_row': 'edit',
            'click .themify_builder_style_row': 'edit',
            'click .themify_builder_delete_row': 'delete',
            'click .themify_builder_duplicate_row': 'duplicate',
            'click .themify_builder_grid_list li a': '_gridMenuClicked',
            'click .themify_builder_grid_list_wrapper .grid_tabs li a': '_switchGridTabs',
            'click .themify_builder_column_alignment li a': '_columnAlignmentMenuClicked',
            'click .themify_builder_column_direction li a': '_columnDirectionMenuClicked',
            'change .gutter_select': '_gutterChange',
            'click .toggle_row': 'toggleRow',
            'click .themify_builder_toggle_row': 'toggleRow',
            'click .empty_holder_text': 'showModPanel',
			'mouseout': 'showAddBtn',
			'mouseover': 'showAddBtn'
        },
        initialize: function () {
            this.listenTo(this.model, 'change:styling', this.renderInlineData);
        },
        render: function () {
            //this.$el.html( this.defaultRowValues() );
            this.$el.html(this.template(this.model.toJSON()));
            this.renderInlineData();

            if (!_.isEmpty(this.model.get('cols'))) {
                var container = document.createDocumentFragment();
                _.each(this.model.get('cols'), function (value, key) {
                    value.component_name = 'column';
                    if (_.isObject(value.styling)) {
                        value.styling = api.Utils.removeEmptyFields(value.styling);
                    }
                    var columnView = api.Views.init_column(value, this.type);

                    container.appendChild(columnView.view.render().el);
                }, this);
                this.$el.find('.themify_builder_row_content').append(container);
            } else {
                // Add column
                api.Utils._addNewColumn({
                    newclass: 'col-full',
                    component: 'column',
                    type: this.type
                }, this.$el.find('.themify_builder_row_content'));
            }

            setTimeout(function () {
                api.Mixins.Builder._selectedGridMenu(this.$el);
            }.bind(this), 100);

            var directions = {},
                    col_tablet = this.model.get('col_tablet'),
                    col_mobile = this.model.get('col_mobile'),
                    row_content = this.$el.find('.themify_builder_row_content');
            directions['desktop'] = this.model.get('desktop_dir');
            directions['tablet'] = this.model.get('tablet_dir');
            directions['mobile'] = this.model.get('mobile_dir');


            if (!col_tablet) {
                col_tablet = 'tablet-auto';
            }
            if (!col_mobile) {
                col_mobile = 'mobile-auto';
            }
            if (!directions['desktop']) {
                directions['desktop'] = 'ltr';
            }
            if (!directions['tablet']) {
                directions['tablet'] = 'ltr';
            }
            if (!directions['mobile']) {
                directions['mobile'] = 'ltr';
            }
            var $el = this.$el;
            $.each(directions, function (i, d) {
                if (d !== 'ltr') {
                    $el.find('.themify_builder_grid_' + i + ' .themify_builder_column_direction').first().find('li').removeClass('selected').children('.column-dir-' + d).parent().addClass('selected');
                }
            });
            if (api.mode === 'visual') {
                row_content.removeClass('desktop-col-direction-ltr desktop-col-direction-rtl tablet-col-direction-ltr tablet-col-direction-rtl mobile-col-direction-ltr mobile-col-direction-rtl')
                        .addClass('tfb_grid_classes desktop-col-direction-' + directions['desktop'] + ' tablet-col-direction-' + directions['tablet'] + ' mobile-col-direction-' + directions['mobile']);
            }
            else {
                row_content.addClass('direction-' + directions['desktop']);
            }
            var mobile_item = this.$el.find('.themify_builder_grid_mobile .' + col_mobile).first(),
                    tablet_item = this.$el.find('.themify_builder_grid_tablet .' + col_tablet).first(),
                    mcol = parseInt(mobile_item.data('col')),
                    tcol = parseInt(tablet_item.data('col'));
            mobile_item.closest('li').addClass('selected');
            tablet_item.closest('li').addClass('selected');
            if (mcol === 2 || mcol === 3) {
                col_mobile += ' mobile-' + (mcol === 2 ? '2col' : '3col');
            }
            if (tcol === 2 || tcol === 3) {
                col_tablet += ' tablet-' + (tcol === 2 ? '2col' : '3col');
            }
            row_content.addClass(this.model.get('gutter') + ' ' + col_mobile + ' ' + col_tablet);
            return this;
        },
        defaultRowValues: function () {
            var _this = this,
                    $template = $(this.template(this.model.toJSON()));

            // Set default column alignment value
            if (_this.model.get('column_alignment') !== 'col_align_top') {
                $template.find('.themify_builder_column_alignment > li').removeClass('selected').each(function () {
                    if ($(this).find('a').data('alignment') === _this.model.get('column_alignment')) {
                        $(this).addClass('selected');
                    }
                });
            }

            // Set default gutter value
            if (_this.model.get('gutter') !== 'gutter-default') {
                $template.find('.gutter_select option').each(function () {
                    if ($(this).val() === _this.model.get('gutter')) {
                        $(this).attr('selected', '');
                        setTimeout(function () {
                            $template.find('.gutter_select').trigger('change');
                        });
                    }
                });
            }
            return $template[0];
        },
        renderInlineData: function () {
            var anchorname = '', video = '', mute = '', loop = '', styling = this.model.get('styling');
            this.$el.find('.row-data-styling').attr('data-styling', JSON.stringify(styling));
            if (_.isObject(styling)) {
                anchorname = !_.isEmpty(styling.row_anchor) ? '#' + styling.row_anchor : '';
                video = !_.isEmpty(styling.background_video) && styling.background_type === 'video' ? styling.background_video : '';
                if (video && !_.isEmpty(styling.background_video_options)) {
                    mute = styling.background_video_options.indexOf('mute') !== -1 ? 'mute' : '';
                    loop = styling.background_video_options.indexOf('unloop') !== -1 ? 'unloop' : '';
                }
            }
            this.$el.attr({'data-fullwidthvideo': video, 'data-mutevideo': mute, 'data-unloopvideo': loop});
            this.$el.find('.row-anchor-name').first().text(anchorname);
            this.$el.find('.gutter_select').val(this.model.get('gutter'));
            this.$el.find('[data-alignment="' + this.model.get('column_alignment') + '"]').parent().addClass('selected').siblings().removeClass('selected');
        },
        edit: function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.model.set({styleClicked: $(e.currentTarget).hasClass('themify_builder_style_row')}, {silent: true});

            api.activeModel = this.model;

            var self = this;

            api.vent.trigger('dom:observer:start', this.$el.closest('[data-postid]'), {cid: api.activeModel.cid, value: {styling: api.activeModel.get('styling')}});


            ThemifyBuilderCommon.highlightRow(this.$el);

            ThemifyBuilderCommon.Lightbox.open({loadMethod: 'inline', templateID: 'builder_form_row'}, function () {
                api.Mixins.Builder.editComponent('row', self);
            });
        },
        delete: function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (!confirm(themifyBuilder.i18n.rowDeleteConfirm)) {
                return;
            }

            var $container = this.$el.closest('[data-postid]');

            api.vent.trigger('dom:observer:start', $container);

            this.model.destroy();

            api.vent.trigger('dom:observer:end', $container);

            api.vent.trigger('dom:builder:change');
        },
        duplicate: function (e) {
            e.preventDefault();
            e.stopPropagation();

            var rowView = api.Views.init_row(api.Utils._getRowSettings(this.$el, this.$el.index()), this.type),
                    $container = this.$el.closest('[data-postid]');

            api.vent.trigger('dom:observer:start', $container);

            rowView.view.render().$el.insertAfter(this.$el);

            rowView.view.trigger('component:duplicate');

            if ('visual' !== this.type) {
                api.vent.trigger('dom:builder:change');
                api.vent.trigger('dom:observer:end', $container);
            }
        },
        _switchGridTabs: function (e) {
            e.preventDefault();
            e.stopPropagation();
            if(!themifyBuilder.is_premium){
                    return false;
            }
            var $this = $(e.currentTarget),
                    id = $this.attr('href').replace('#', '');
            if ($this.data('handle') === 'module') {
                return false;
            }
            if ('visual' !== api.mode) {
                if ($this.closest('li').hasClass('selected')) {
                    return false;
                }
                $this.closest('ul').children('li.selected').removeClass('selected');
                $this.closest('li').addClass('selected');
                $this.closest('.grid_menu').find('.themify_builder_grid_tab').hide();
                $this.closest('.grid_menu').find('.themify_builder_grid_tab.themify_builder_grid_' + id).show().find('.themify_builder_column_direction li.selected a').trigger('click');

                $('body')
                     .removeClass('builder-active-breakpoint--desktop builder-active-breakpoint--tablet builder-active-breakpoint--tablet_landscape builder-active-breakpoint--mobile')
                    .addClass('builder-active-breakpoint--' + id );
            }
            else {
                if (id === 'tablet') {
                    id += '-landscape';
                }
                $('.js--themify_builder_breakpoint_switcher.breakpoint-' + id).trigger('click');
                setTimeout(function () {
                    var cid = $this.closest('.themify_builder_row').data('cid'),
                            bid = $this.closest('.themify_builder_content').data('postid'),
                            row = id === 'desktop' ? $('.module_row_' + cid, '#themify_builder_content-' + bid) : api.Frontend.responsiveFrame.$el.find('#themify_builder_content-' + bid + ' .module_row_' + cid);
                    $('html,body').animate({
                        scrollTop: row.offset().top - $('#headerwrap.fixed-header').outerHeight(true) - 30
                    }, 'fast');

                }, 1200);
            }
        },
        _gridMenuClicked: function (e) {
            e.preventDefault();
            e.stopPropagation();

            var that = this,
                    $this = $(e.currentTarget),
                    set = $this.data('grid'),
                    handle = $this.data('handle'),
                    $base,
                    is_sub_row = false,
                    $container = $this.closest('[data-postid]'),
                    type = $this.data('type'),
                    is_desktop = type === 'desktop';
            api.vent.trigger('dom:observer:start', $container);

            $this.closest('.themify_builder_grid_list').find('.selected').removeClass('selected');
            $this.closest('li').addClass('selected');
            if (handle === 'module') {
                if (set[0] !== '-full') {
                    var subRowDataPlainObject = {
                        cols: [{grid_class: 'col-full'}]
                    },
                    subRowView = api.Views.init_subrow(subRowDataPlainObject, this.type),
                            $mod_ori = $this.closest('.active_module'),
                            $mod_clone = $mod_ori.clone();
                    $mod_clone.insertAfter($mod_ori);
                    $mod_ori.find('.grid_menu').remove();
                    $base = subRowView.view.render().$el
                            .find('.themify_module_holder')
                            .append($mod_ori)
                            .end()
                            .insertAfter($mod_clone)
                            .find('.' + $this.attr('class').replace(' ', '.'))
                            .closest('li')
                            .addClass('selected')
                            .end().end()
                            .find('.themify_builder_sub_row_content');

                    $base.closest('.themify_builder_sub_row').find('.themify_builder_grid_' + type + ':first .themify_builder_grid_list .' + type + set.join('-')).closest('li').addClass('selected');
                    var grid_items = $base.closest('.themify_builder_sub_row').find('.themify_builder_grid_tablet .themify_builder_grid_list li li,.themify_builder_grid_mobile .themify_builder_grid_list li li');
                    api.Utils.hideResponsiveCols(grid_items, $this.data('col'));
                    $mod_clone.remove();
                }
            }
            else {
                is_sub_row = handle === 'sub_row';
                var row = $this.closest('.themify_builder_' + handle);
                $base = row.find('.themify_builder_' + handle + '_content');
            }
            //sync with desktop
            if ('visual' === api.mode && !is_desktop && !e.isTrigger && handle !== 'module') {
                var bid = $base.closest('.themify_builder_content').data('postid');
                $('#themify_builder_content-' + bid).find('.module_' + handle.replace('_', '') + '_' + row.data('cid') + ' .themify_builder_grid_' + type + ':first .themify_builder_grid_list .' + type + set.join('-')).trigger('click');

            }
            if (handle !== 'module') {
                var grid_list_wrapper = $this.closest('.themify_builder_grid_list_wrapper'),
                        col = is_desktop ? $this.data('col') : grid_list_wrapper.find('.themify_builder_grid_desktop li.selected a').data('col');
                if (col) {
                    if (!is_desktop) {
                        var grid = set,
                                current_col = parseInt($this.data('col')),
                                cl = type + grid.join('-');
                        if ('visual' === api.mode) {
                            var classes = cl;
                            if (current_col === 2 || current_col === 3) {
                                classes += ' ' + type + '-' + (current_col === 2 ? '2col' : '3col');
                            }
                            $base.removeClass(type + '-2col ' + type + '-3col tmp-mobile-auto ' + row.attr('data-' + type)).addClass(classes);
                        }
                        if (handle === 'tablet') {
                            api.Frontend.responsiveFrame.$el.find('.mobile-auto').removeClass('mobile-auto').addClass('tmp-mobile-auto');
                        }
                        row.attr('data-' + type, cl).data(type, cl);
                        return false;
                    }
                    else if (!e.isTrigger) {
                        var grid_items = grid_list_wrapper.find('.themify_builder_grid_tablet .themify_builder_grid_list li li,.themify_builder_grid_mobile .themify_builder_grid_list li li');
                        api.Utils.hideResponsiveCols(grid_items, col);
                        if ('visual' === api.mode) {
                            api.Utils.removeGridClass($base, 'col-count');
                            $base.removeClass('count-odd count-even').addClass('col-count-' + col + ' count-' + ((col % 2 === 0) ? 'even' : 'odd'));
                            $.each(['tablet', 'mobile'], function (i, v) {
                                var selected_tab = grid_list_wrapper.find('.themify_builder_grid_' + v + ' .themify_builder_grid_list li.selected'),
                                        current = selected_tab.length > 0 ? grid_list_wrapper.find('.themify_builder_grid_' + v + ' .themify_builder_grid_list li li').eq(col - 1) : false;

                                if (current && selected_tab.css('display') === 'none' && current.css('display') !== 'none') {
                                    selected_tab.removeClass('selected');
                                    current.addClass('selected');
                                    var tcolumn = current.children('a'),
                                            tcol = parseInt(tcolumn.data('col')),
                                            tdata = tcolumn.data('grid');
                                    cl = v + tdata.join('-');
                                    var classes = cl;
                                    if (tcol === 2 || tcol === 3) {
                                        classes += ' ' + v + '-' + (tcol === 2 ? '2col' : '3col');
                                    }
                                    $base.removeClass(v + '-2col ' + v + '-3col ' + row.attr('data-' + v)).addClass(classes);
                                    row.attr('data-' + v, cl).data(v, cl);
                                }
                            });
                        }
                    }
                }
            }
            $.each(set, function (i, v) {
                if ($base.children('.themify_builder_col').eq(i).length > 0) {
                    $base.children('.themify_builder_col').eq(i).removeClass(api.Utils.clearClass).addClass('module_column col' + v);
                } else {
                    // Add column
                    api.Utils._addNewColumn({
                        newclass: (is_sub_row ? 'sub_column module_column ' : '') + 'col' + v,
                        component: is_sub_row ? 'sub-column' : 'column',
                        type: that.type
                    }, $base);
                }
            });

            // remove unused column
            if (set.length < $base.children().length) {
                $base.children('.themify_builder_col').eq(set.length - 1).nextAll().each(function () {
                    // relocate active_module
                    var modules = $(this).find('.themify_module_holder').first();
                    modules.children().appendTo($(this).prev().find('.themify_module_holder').first());
                    $(this).remove(); // finally remove it
                });
            }
            var $children = $base.children();
            $children.removeClass('first last');
            if (('visual' === api.mode && $base.hasClass(type + '-col-direction-rtl')) || ('visual' !== api.mode && $base.hasClass('direction-rtl'))) {
                $children.last().addClass('first');
                $children.first().addClass('last');
            }
            else {
                $children.first().addClass('first');
                $children.last().addClass('last');
            }
            var $move_modules = false;
            // remove sub_row when fullwidth column
            if (is_sub_row && set[0] === '-full') {
                $move_modules = $base.find('.active_module');
                var $row = $base.closest('.themify_builder_row');
                $move_modules.insertAfter($this.closest('.themify_builder_sub_row'));
                $this.closest('.themify_builder_sub_row').remove();
                if ('visual' === api.mode && api.activeBreakPoint !== 'desktop') {
                    api.Mixins.Builder.initGridMenu($row);
                }
            }

            setTimeout(function () {
                // hide column 'alignment', 'equal column height' and 'gutter' when fullwidth column
                var $grid = is_sub_row && $move_modules ? $move_modules.find('.themify_builder_grid_list') : $this.closest('.themify_builder_grid_list');
                if (set[0] === '-full') {
                    $grid.find('a:first').parent().addClass('selected');
                    $grid.nextAll('.themify_builder_column_alignment').find('a:first').trigger('click');
                    $grid.nextAll('.gutter_select').val('gutter-default').trigger('change');
                    $grid.nextAll().hide();
                }
                else {
                    $grid.nextAll().show();
                }
            }, 100);

            api.Utils.columnDrag($base, true);

            api.vent.trigger('dom:observer:end', $container);

            api.vent.trigger('dom:builder:change');
        },
        _columnAlignmentMenuClicked: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $this = $(e.currentTarget),
                    handle = $this.data('handle'),
                    alignment = $this.data('alignment'),
                    $row = null;

            if (handle === 'module' || (!e.isTrigger && $this.closest('li').hasClass('selected'))) {
                return;
            }

            if (e.which) {
                var $container = $this.closest('[data-postid]');
                api.vent.trigger('dom:observer:start', $container);
            }

            $this.closest('.themify_builder_column_alignment').find('.selected').removeClass('selected');
            $this.closest('li').addClass('selected');

            $row = $this.closest('.themify_builder_' + handle);

            $row.data('column-alignment', alignment);
            if ('visual' === this.type) {
                $row.removeClass(themifyBuilder.columnAlignmentClass).addClass(alignment);
            }

            if (e.which) {
                api.vent.trigger('dom:observer:end', $container);
            }
        },
        _columnDirectionMenuClicked: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $this = $(e.currentTarget),
                    handle = $this.data('handle'),
                    dir = $this.data('dir'),
                    type = $this.data('type'),
                    $row = handle === 'module' ? false : ($this.closest('.themify_builder_' + handle).find('.themify_builder_' + handle + '_content'));

            if (handle === 'module' || (!e.isTrigger && (dir === $row.data(type + '-dir') || $this.closest('li').hasClass('selected')))) {
                return;
            }

            if (e.which) {
                var $container = $this.closest('[data-postid]');
                api.vent.trigger('dom:observer:start', $container);
            }

            var breakpoint = '';
            $this.closest('.themify_builder_column_direction').find('.selected').removeClass('selected');
            $this.closest('li').addClass('selected');
            $row.removeClass('tablet-direction-selected mobile-direction-selected desktop-direction-selected').addClass(type + '-direction-selected').closest('[data-cid]').data(type + '_dir', dir);
            if ('visual' === api.mode) {
                $row.removeClass(type + '-col-direction-ltr ' + type + '-col-direction-rtl').addClass(type + '-col-direction-' + dir);
                var swap = e.isTrigger && api.activeBreakPoint !== 'desktop' && type === 'desktop';
                if (api.activeBreakPoint !== 'desktop' && !e.isTrigger) {
                    var bid = $row.closest('.themify_builder_content').data('postid'),
                            $cid = handle === 'sub_row' ? $row.closest('.themify_builder_sub_row').data('cid') : $row.closest('.themify_builder_row').data('cid');
                    $('#themify_builder_content-' + bid).find('[data-cid="' + $cid + '"]').find('.themify_builder_grid_' + type + ':first .themify_builder_column_direction  .column-dir-' + dir).trigger('click');
                }
                breakpoint = api.activeBreakPoint === 'tablet_landscape' ? 'tablet' : api.activeBreakPoint;
            }
            else {
                $row.removeClass('direction-rtl direction-ltr').addClass('direction-' + dir);
                swap = true;
            }
            if (swap || type === breakpoint) {
                var $cols = $row.children('.themify_builder_col'),
                        $first = $cols.first(),
                        $last = $cols.last();
                $cols.removeClass('first last');
                if (dir === 'rtl') {
                    $first.addClass('last');
                    $last.addClass('first');
                }
                else {
                    $first.addClass('first');
                    $last.addClass('last');
                }
            }

            if (e.which) {
                api.vent.trigger('dom:observer:end', $container);
            }
        },
        _gutterChange: function (e) {
            e.stopPropagation();

            var $this = $(e.currentTarget),
                    handle = $this.data('handle');
            if (handle === 'module')
                return;
            var row = $this.closest('.themify_builder_' + handle);
            api.Utils.columnDrag(row.find('.themify_builder_' + handle + '_content'), false, row.data('gutter'), $this.val());
            row.data('gutter', $this.val()).find('.themify_builder_' + handle + '_content').removeClass(themifyBuilder.gutterClass).addClass($this.val());
        },
        toggleRow: function (e) {
            e.preventDefault();
            e.stopPropagation();
            $(e.currentTarget).parents('.themify_builder_row').toggleClass('collapsed').find('.themify_builder_row_content').slideToggle();
        },
		showModPanel: function (e) {
            e.preventDefault();
			var elm = this.$(e.target);
			if(elm.closest('.tb_plus_btn_popover').length)
				return;

			if(elm.children('.tb_plus_btn_popover').length && $('.tb_plus_btn_popover').is(":visible")) {
				$('.tb_plus_btn_popover').hide();
				elm.css({'zIndex':1});
			} else {
				$('.empty_holder_text').hide();
				elm.append($('.tb_plus_btn_popover'));
				if(this.type == 'visual'){
					elm.show().css({'zIndex':99995});
				}
				else {
					elm.show().css({'zIndex': 'auto'});
				}
				var bottom = window.innerHeight - elm[0].getBoundingClientRect().bottom;
				if(bottom < 270 ){
					 $('html,body').animate({scrollTop: $(document).scrollTop()+(290-bottom)}, 500);
				}
				$('.tb_plus_btn_popover').show();
			}
        },
		showAddBtn: function(e){
			e.preventDefault();
			var elm = $(e.target).closest('.themify_builder_row').children('.empty_holder_text');
			if((!elm.find('.tb_plus_btn_popover').length || !elm.find('.tb_plus_btn_popover').is(":visible")) && e.type == "mouseout"){
				elm.css({'zIndex':1}).hide();
			} else if(e.type == "mouseover"){
				elm.show();
			}
		}
    });

    api.Views.Builder = Backbone.View.extend({
        type: 'default',
        events: {
            'click .tb-import-layout-button': 'importLayoutButton'
        },
        initialize: function (options) {
            _.extend(this, _.pick(options, 'type'));
            api.vent.on('dom:builder:change', this.tempEvents.bind(this));
        },
        render: function () {
            var container = document.createDocumentFragment();
            this.collection.each(function (row) {
                if (_.isObject(row.attributes) && _.isObject(row.attributes.styling)) {
                    row.attributes.styling = api.Utils.removeEmptyFields(row.attributes.styling);
                }

                var rowView = api.Views.init_row(row, this.type);

                container.appendChild(rowView.view.render().el);

            }, this);

            this.el.appendChild(container);
            this.addElementClasses();
            api.Utils.columnDrag(null, false);

            api.vent.trigger('dom:builder:change');

            this.insertLayoutButton();
            return this;
        },
        addElementClasses: function () {
            var builderID = this.$el.data('postid');
            this.$el.find('.themify_builder_row').addClass('themify_builder_' + builderID + '_row');
            this.$el.find('.themify_builder_col:not(.sub_column)').addClass('module_column tb_' + builderID + '_column');
        },
        tempEvents: function () {
            this.deleteEmptyModule();
            this.newRowAvailable();
            this.moduleEvents();
        },
        insertLayoutButton: function () {
            if (this.$('.module_row ').length < 2) {
                var cl = themifyBuilder.is_premium?'':' themify_builder_lite';
                this.$el.append('<a href="#" class="tb-import-layout-button'+cl+'">' + themifyBuilderCommon.text_import_layout_button + '</a>');
            }
        },
        importLayoutButton: function (e) {
            e.preventDefault();
            if(!themifyBuilder.is_premium){
                    return false;
            }
            api.Views.Toolbar.prototype.load_layout(e);
        }
    });

    api.Mixins.Common = {
        _autoSelectInputField: function ($inputField) {
            $inputField.trigger('focus').trigger('select');
        },
        highlightModuleBack: function ($module) {
            $('.active_module').removeClass('current_selected_module');
            $module.addClass('current_selected_module');
        },
        moduleOptionsBinding: function () {
            var doTheBinding = function ($self, binding, val) {
                if (binding) {
                    var logic = false;

                    if (val == '' && typeof binding['empty'] !== 'undefined') {
                        logic = binding['empty'];
                    } else if (val != '' && typeof binding[val] !== 'undefined') {
                        logic = binding[val];
                    } else if (val != '' && typeof binding['not_empty'] !== 'undefined') {
                        logic = binding['not_empty'];
                    }

                    if (logic) {
                        if (typeof logic['show'] !== 'undefined') {
                            $.each(logic['show'], function (i, v) {
                                var optionRow = $self.closest('.themify_builder_row_content');
                                if (optionRow.length) {
                                    optionRow.find('.' + v).removeClass('conditional-input').children().show();
                                } else {
                                    $('.' + v).removeClass('conditional-input').children().show();
                                }
                            });
                        }
                        if (typeof logic['hide'] !== 'undefined') {
                            $.each(logic['hide'], function (i, v) {
                                var optionRow = $self.closest('.themify_builder_row_content');
                                if (optionRow.length > 0) {
                                    optionRow.find('.' + v).addClass('conditional-input').children().hide();
                                } else {
                                    $('.' + v).addClass('conditional-input').children().hide();
                                }
                            });
                        }
                    }
                }
            }

            var form = $('#tfb_module_settings');
            $('body').on('change', 'input[data-binding], textarea[data-binding], select[data-binding]', form, function () {
                doTheBinding($(this), $(this).data('binding'), $(this).val());
            });
            $('input[data-binding], textarea[data-binding], select[data-binding]', form).trigger('change');

            $(form).on('click', '.tfb_lb_option.themify-layout-icon[data-binding] a', function () {
                doTheBinding($(this), $(this).parent().data('binding'), $(this).attr('id'));
            });
            $('.tfb_lb_option.themify-layout-icon a.selected', form).trigger('click');
        },
        // "Apply all" // apply all init
        applyAll_init: function () {
            var that = this;
            $('.style_apply_all').each(function () {
                var $val = $(this).val(),
                        $fields = $(this).closest('.themify_builder_field').prevUntil('h4'),
                        $last = $fields.last(),
                        $inputs = $last.find('input[type="text"]').not('.colordisplay'),
                        $selects = $last.find('select'),
                        $fieldFilter = $val === 'border' ?
                        '[name="border_top_color"], [name="border_top_width"], [name="border_top_style"], [name="border_right_color"], [name="border_right_width"], [name="border_right_style"], [name="border_bottom_color"], [name="border_bottom_width"], [name="border_bottom_style"], [name="border_left_color"], [name="border_left_width"], [name="border_left_style"]' :
                        '[name="' + $val + '_top"], [name="' + $val + '_right"], [name="' + $val + '_bottom"], [name="' + $val + '_left"]',
                        $preSelect = true,
                        $callback = function (e) {
                            if ($fields.first().next('.themify_builder_field').find('.style_apply_all').is(':checked')) {
                                var $v = $(this).val(),
                                        $opt = false,
                                        $select = $(this).is('select');

                                $fields.not(':last').each(function () {
                                    if ($select) {
                                        $opt = $(this).find('select option').prop('selected', false).filter('[value="' + $v + '"]');
                                        $opt.prop('selected', true);
                                        if ($val !== 'border') {
                                            $opt.trigger('change');
                                        }

                                    } else {
                                        $opt = $(this).find('input[type="text"].tfb_lb_option');
                                        $opt.val($v);
                                        if ($val !== 'border') {
                                            $opt.trigger('keyup');
                                        }
                                    }
                                });
                                if ($opt && $val === 'border') {
                                    if ('visual' === that.type) {
                                        api.liveStylingInstance.setApplyBorder($select ? $opt.closest('select').prop('name') : $opt.prop('name'), $v, $select ? 'style' : 'width');

                                        if ($select) {
                                            $last.find('input[type="text"].colordisplay').trigger('blur');
                                        } else {
                                            var checkEmpty = $last.find('.colordisplay, .style_border').map(function () {
                                                return this.value || null;
                                            }).get();
                                            if (_.isEmpty(checkEmpty)) {
                                                api.liveStylingInstance.setApplyBorder($last.find('select').prop('name'), '', 'style');
                                            } else {
                                                $last.find('select').trigger('change');
                                            }
                                        }
                                    }
                                }
                            }
                        };

                if ($(this).is(':checked')) {
                    $fields.not(':last').hide();
                    $last.children('.themify_builder_input').css('color', '#FFF');
                } else {
                    // Pre-select
                    $fields.find($fieldFilter).each(function () {
                        if ($(this).val() && $(this).val() !== 'solid') {
                            $preSelect = false;
                            return false;
                        }
                    });

                    if ($preSelect) {
                        $(this).prop('checked', true);
                        $fields.not(':last').hide();
                        $last.children('.themify_builder_input').css('color', '#FFF');
                    }
                }

                // Events
                $inputs.on('keyup', _.debounce($callback, 300));
                $selects.on('change', $callback);
            });
        },
        getShortcodePreview: function ($input, $value) {

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                data:
                        {
                            action: 'tfb_load_shortcode_preview',
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            shortcode: $value
                        },
                success: function (data) {
                    if (data) {
                        $input.after(data);
                    }
                }
            });
        },
        rowOptionBuilder: function () {
            $(".themify_builder_row_opt_builder_wrap").sortable({
                items: '.themify_builder_row',
                handle: '.themify_builder_row_top',
                axis: 'y',
                placeholder: 'themify_builder_ui_state_highlight',
                start: function (e, ui) {
                    if (typeof tinyMCE !== 'undefined') {
                        $('#tfb_row_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                            var id = $(this).attr('id'),
                                    content = tinymce.get(id).getContent();
                            $(this).data('content', content);
                            tinyMCE.execCommand('mceRemoveEditor', false, id);
                        });
                    }
                },
                stop: function (e, ui) {
                    if (typeof tinyMCE !== 'undefined') {
                        $('#tfb_row_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                            var id = $(this).attr('id');
                            tinyMCE.execCommand('mceAddEditor', false, id);
                            tinymce.get(id).setContent($(this).data('content'));
                        });
                    }
                },
                sort: function (e, ui) {
                    var placeholder_h = ui.item.height();
                    $('.themify_builder_row_opt_builder_wrap .themify_builder_ui_state_highlight').height(placeholder_h);
                }
            });
        },
    };

    api.Mixins.Builder = {
        builderContainer: document.querySelector('.themify_builder_editor_wrapper'),
        moduleEvents: function () {
            var self = this;

            this.$el.find('.row_menu .themify_builder_dropdown, .module_menu .themify_builder_dropdown').hide();

            var toggleCollapseMod = false,
                    moduleHolderArgs = {
                        placeholder: 'themify_builder_ui_state_highlight',
                        items: '.active_module, .themify_builder_sub_row',
                        connectWith: '.themify_module_holder',
                        cursor: 'move',
                        revert: 100,
                        tolerance: 'pointer',
                        cursorAt: {
                            top: 20,
                            left: 110
                        },
                        beforeStart: function (e, ui) {
                            api.vent.trigger('dom:observer:start', ui.item.closest('[data-postid]'));

                            if ('visual' === self.type) {
                                ui.item.css('height', 40);
                                toggleCollapseMod = true;
                                $(this).sortable('refresh');
                            }
                        },
                        sort: function (e, ui) {
                            $('.themify_module_holder .themify_builder_ui_state_highlight').height(35);
                            if ('visual' === self.type) {
                                $('.themify_module_holder .themify_builder_sortable_helper').height(40).width(220);
                                if (!$('#themify_builder_module_panel').hasClass('slide_builder_module_state_down')) {
                                    $('#themify_builder_module_panel').addClass('slide_builder_module_state_down');
                                    api.Frontend.slidePanelOpen = false;
                                    $('#themify_builder_module_panel').find('.slide_builder_module_panel_wrapper').slideUp();
                                }
                            } else {
                                ui.item.css('width', 220);
                            }
                        },
                        stop: function (e, ui) {
                            if (!ui.item.hasClass('active_module') && !ui.item.hasClass('themify_builder_sub_row')) {
                                var moduleView = api.Views.init_module({mod_name: ui.item.data('module-slug')}, self.type),
                                        $newElems = moduleView.view.render().$el;

                                ui.item.replaceWith($newElems);
                                moduleView.view.trigger('edit', null, true);
                                moduleView.view.trigger('custom:preview:init');
                                moduleView.view.$el.addClass('tb_module_state_unsaved');
                            } else {
                                if (toggleCollapseMod) {
                                    ui.item.css('height', '');
                                    toggleCollapseMod = false;
                                }

                                // Make sub_row only can nested one level
                                if (ui.item.hasClass('themify_builder_sub_row') && ui.item.parents('.themify_builder_sub_row').length) {
                                    var $clone_for_move = ui.item.find('.active_module').clone();
                                    $clone_for_move.insertAfter(ui.item);
                                    ui.item.remove();
                                }

                                if ($('.themify_module_holder .themify_builder_sortable_helper').length > 0) {
                                    $('.themify_module_holder .themify_builder_sortable_helper').remove();
                                }
                                api.vent.trigger('dom:builder:change');
                                api.vent.trigger('dom:observer:end', ui.item.closest('[data-postid]'));
                            }

                            // Fix bugs ui state highlight not remove
                            if ($('.themify_module_holder .themify_builder_ui_state_highlight').length > 0) {
                                $('.themify_module_holder .themify_builder_ui_state_highlight').remove();
                            }
                        }
                    };
            if ('visual' === self.type) {
                moduleHolderArgs.helper = function () {
                    return $('<div class="themify_builder_sortable_helper"/>');
                };
                moduleHolderArgs.handle = '.themify_builder_module_front_overlay, .themify_builder_sub_row_top';
                moduleHolderArgs.create = function (e, ui) {
                    $('body').css('overflow-x', 'inherit');
                };
            }
            this.$('.themify_module_holder').sortable(moduleHolderArgs);
			var startIndex, changeIndex, uiHeight;
            var toggleCollapseRow = false,
                    rowSortable = {
                        items: '.themify_builder_row',
                        handle: '.themify_builder_row_top',
                        axis: 'y',
                        placeholder: 'themify_builder_ui_state_highlight',
                        containment: 'parent',
                        tolerance: 'pointer',
                        cursor: 'move',
                        beforeStart: function (e, ui) {
                            api.vent.trigger('dom:observer:start', ui.item.closest('[data-postid]'));
                            if ('visual' === self.type) {
                                if (!ui.item.hasClass('collapsed')) {
                                    ui.item.addClass('collapsed').find('.themify_builder_row_content').first().hide();
                                    toggleCollapseRow = true;
                                    $(this).sortable('refresh');
                                }
                            }
                        },
						start: function(e, ui) {
							startIndex = ui.placeholder.index();
							uiHeight = ui.item.outerHeight(true);//get offset incl margin

							ui.item.nextAll('div:not(.themify_builder_ui_state_highlight)').css({
								transform: 'translateY(' +uiHeight+ 'px)'
							});

							ui.placeholder.css({
								height: 0,
								padding: 0
							});
						},
						change: function(e, ui) {
							changeIndex = ui.placeholder.index();
							if (startIndex > changeIndex) {
								var slice = $('.themify_builder_row').slice(changeIndex, $('.themify_builder_row').length);
								slice.not('.ui-sortable-helper').each(function() {
									var item = $(this);
									item.css({
										transform: 'translateY(' +uiHeight+ 'px)'
									});
								});
							} else if (startIndex < changeIndex) {
								var slice = $('.themify_builder_row').slice(startIndex, changeIndex);
								slice.not('.ui-sortable-helper').each(function() {
									var item = $(this);
									item.css({
										transform: 'translateY(0px)'
									});
								});
							}
							startIndex = changeIndex
						},
                        sort: function (e, ui) {
                            if ('visual' === self.type) {
                                $('.themify_builder_ui_state_highlight').height(35);
                                $('.themify_builder_sortable_helper').height(35);
                            } else {
                                var placeholder_h = ui.item.height();
                                $('.themify_builder_row_panel .themify_builder_ui_state_highlight').height(placeholder_h);
                            }
                        },
                        update: function (e, ui) {
                            if ('visual' === self.type && toggleCollapseRow) {
                                ui.item.removeClass('collapsed').find('.themify_builder_row_content').first().show();
                                toggleCollapseRow = false;
                            }
                            api.vent.trigger('dom:observer:end', ui.item.closest('[data-postid]'));
                        },
                        create: function (e, ui) {
                            if ('visual' === self.type) {
                                $('body').css('overflow-x', 'inherit');
                            }
                        },
						stop: function(e,ui){
							$('.ui-sortable-handle, .themify_builder_row').css({
								transform: ''
							});
							$('.tb_plus_btn_popover').hide().closest('.empty_holder_text').trigger('mouseout');
							var $tb = themifybuilderapp.toolbar;
							if(ui.item.data('type') == 'predesigned-rows'){
								ui.item.empty().removeClass('tb_module_dragging_helper').addClass('predesigned_rows_temp_holder');
								$tb.put_row_template(ui.item);
								
							}
							if ('visual' === self.type && toggleCollapseRow) {
								ui.item.removeClass('collapsed').find('.themify_builder_row_content').first().show();
								toggleCollapseRow = false;
							}
						}
                    };
          
			if ('visual' === self.type) {
                rowSortable.helper = function () {
                    return $('<div class="themify_builder_sortable_helper"/>');
                };
            }
            this.$el.sortable(rowSortable);

            // Column and Sub-Column sortable
            this.$el.find('.themify_builder_row_content, .themify_builder_sub_row_content').each(function () {
                var $wrapper = $(this);
                if ($wrapper.children('.themify_builder_col').length > 1) {
                    $wrapper.sortable({
                        items: '> .themify_builder_col',
                        handle: '> .themify_builder_column_action .themify_builder_column_dragger',
                        axis: 'x',
                        placeholder: 'themify_builder_ui_state_highlight',
                        tolerance: 'pointer',
                        cursorAt: {
                            top: 20,
                            left: 20
                        },
                        beforeStart: function (e, ui) {
                            api.vent.trigger('dom:observer:start', ui.item.closest('[data-postid]'));
                        },
                        sort: function (e, ui) {
                            $('.themify_builder_ui_state_highlight').width(ui.item.width());
                        },
                        stop: function (e, ui) {
                            $wrapper.children().removeClass('first last');
                            var suffix = api.activeBreakPoint === 'tablet_landscape' ? 'tablet' : api.activeBreakPoint;
                            if (('visual' === api.mode && $wrapper.hasClass(suffix + '-col-direction-rtl')) || ('visual' !== api.mode && $wrapper.hasClass('direction-rtl'))) {
                                $wrapper.children().last().addClass('first');
                                $wrapper.children().first().addClass('last');
                            }
                            else {
                                $wrapper.children().first().addClass('first');
                                $wrapper.children().last().addClass('last');
                            }
                            api.Utils.columnDrag($wrapper, false);
                            api.vent.trigger('dom:observer:end', ui.item.closest('[data-postid]'));
                        }
                    });
                }
            });
            this.initGridMenu();
        },
        initGridMenu: function (el) {
            if (!el) {
                el = this.$el;
            }
            var grid_menu_tmpl = wp.template('builder_grid_menu'),
                    grid_menu_render = grid_menu_tmpl({});
            el.find('.themify_builder_row_content').each(function () {
                $(this).children().each(function () {
                    var $holder = $(this).find('.themify_module_holder').first();
                    $holder.children('.active_module').each(function () {
                        if ($(this).find('.grid_menu').length === 0) {
                            var menu = $(grid_menu_render);
                            menu.find('.grid-layout--full').closest('li').addClass('selected');
                            $(this).append(menu);
                        }
                    });
                });
            });
        },
        editComponent: function (component, self) {
			// editing row
            var is_row = component === 'row',
                    $options = self.model.get('styling') || {},
                    elId = is_row ? '#tfb_row_settings' : (component === 'sub-row' ? '#tfb_subrow_settings' : '#tfb_column_settings');
            if ('desktop' !== api.activeBreakPoint) {
                var select = is_row ? '#themify_builder_row_fields_styling' : elId,
                        styleFields = $(select + ' .tfb_lb_option').map(function () {
                    return $(this).attr('id');
                }).get(),
                        temp_background_type = $options.background_type;
                $options = _.omit($options, styleFields);
                if (!_.isUndefined($options['breakpoint_' + api.activeBreakPoint]) && _.isObject($options['breakpoint_' + api.activeBreakPoint])) {
                    $options = _.extend($options, $options['breakpoint_' + api.activeBreakPoint]);
                }
            }

            if ('visual' === self.type) {
                api.liveStylingInstance.init(self.$el, $options);
            }

            if ($options) {
                if ('undefined' !== typeof $options.background_slider) {
                    self.getShortcodePreview($('#background_slider'), $options.background_slider);
                }
                $.each($options, function (id, val) {
                    $(elId).find('#' + id).val(val);
                });

                $(elId).find('.tf-radio-input-container [type="radio"]').each(function () {
                    var id = $(this).prop('name');
                    if ('undefined' !== typeof $options[id]) {
                        if ($(this).val() === $options[id]) {
                            $(this).prop('checked', true);
                        }
                    }
                });
            }
            // image field
            $(elId).find('.themify-builder-uploader-input').each(function () {
                var img_field = $(this).val(),
                        img_thumb = $('<img/>', {src: img_field, width: 50, height: 50});

                if (img_field) {
                    $(this).parent().find('.img-placeholder').empty().html(img_thumb);
                }
                else {
                    $(this).parent().find('.thumb_preview').hide();
                }
            });

            $('.themify-gradient').each(function () {
                var $key = $(this).prop('name');
                $options = $.extend({
                    $key: '',
                }, $options);
                api.Utils.createGradientPicker($(this), $options[$key]);
            });
            if (is_row) {
                // builder
                $(elId).find('.themify_builder_row_js_wrapper').each(function () {
                    var $this_option = $(this),
                            this_option_id = $this_option.attr('id'),
                            $found_element = $options ? $options[this_option_id] : false;

                    api.cache.repeaterElements[ this_option_id ] = $this_option.find('.tb_repeatable_field').first().clone();

                    if ($found_element) {
                        var row_append = 0;
                        if ($found_element.length > 0) {
                            row_append = $found_element.length - 1;
                        }

                        // add new row
                        for (var i = 0; i < row_append; i++) {
                            $this_option.parent().find('.add_new a').first().trigger('click');
                        }

                        $this_option.find('.tb_repeatable_field').each(function (r) {
                            $(this).find('.tfb_lb_option_child').each(function (i) {
                                var $this_option_child = $(this),
                                        this_option_id_child = $this_option_child.hasClass('tfb_lb_wp_editor') ? $this_option_child.attr('name') : $this_option_child.data('input-id');
                                if (!this_option_id_child) {
                                    this_option_id_child = $this_option_child.attr('id');
                                }
                                var $found_element_child = $found_element[r]['' + this_option_id_child + ''];

                                if ($this_option_child.hasClass('themify-builder-uploader-input')) {
                                    var img_field = $found_element_child,
                                            img_thumb = $('<img/>', {src: img_field, width: 50, height: 50});

                                    if (img_field) {
                                        $this_option_child.val(img_field);
                                        $this_option_child.parent().find('.img-placeholder').empty().html(img_thumb).parent().show();
                                    }
                                    else {
                                        $this_option_child.parent().find('.thumb_preview').hide();
                                    }
                                }
                                else if ($this_option_child.is('input, textarea, select')) {
                                    $this_option_child.val($found_element_child);
                                }
                            });
                        });
                    }

                    api.Views.init_control('repeater', {el: $this_option});

                });
            }

            // colorpicker
            api.Utils.setColorPicker();

            // @backward-compatibility
            if ($('#background_video').val() !== '' && $('#background_type input:checked').length === 0) {
                $('#background_type_video').trigger('click');
            } else if ($('#background_type input:checked').length === 0) {
                $('#background_type_image').trigger('click');
            }

            $('.tf-option-checkbox-enable input:checked').trigger('click');

            // plupload init
            api.Utils.builderPlupload('normal');

            /* checkbox field type */
            $('.themify-checkbox').each(function () {
                var id = $(this).attr('id');
                // First unchecked all to fixed checkbox has default value.
                $(this).find('.tf-checkbox').prop('checked', false);
                if ($options && $options[id]) {
                    $options[id] = typeof $options[id] === 'string' ? [$options[id]] : $options[id]; // cast the option value as array
                    // Set the values
                    $.each($options[id], function (i, v) {
                        $('.tf-checkbox[value="' + v + '"]').prop('checked', true);
                    });
                }
            });

            $('body').trigger('editing_' + component.replace('-', '_') + '_option', [$options]);
            if (is_row) {
                // builder drag n drop init
                self.rowOptionBuilder();
            }

            // "Apply all" // apply all init
            self.applyAll_init();
            ThemifyBuilderCommon.fontPreview($('#themify_builder_lightbox_container'), $options);

            if (is_row && self.model.get('styleClicked')) {
                $('a[href="#themify_builder_row_fields_styling"]').trigger('click');
            }
            // Hide non responsive fields
            if ('desktop' !== api.activeBreakPoint) {
                $('.responsive-na').hide();
                if ($.inArray(temp_background_type, ['video', 'slider']) !== -1) {
                    $.each(['background_repeat', 'background_position', 'background_image'], function (i, v) {
                        if ('video' === temp_background_type && 'background_image' === v)
                            return true;
                        $('#' + v).closest('.themify_builder_field').hide();
                    });
                }
            }

            if ('visual' === self.type) {
                ThemifyBuilderCommon.Lightbox.rememberRow(self);
                api.liveStylingInstance.addStyleDate(self.model.get('elType'), themifyBuilder.element_style_rules[ self.model.get('elType') ]);
                api.styleSheet.rememberRules();
            }
        },
        newRowAvailable: function () {
            var $parent = this.$el.children('.themify_builder_row');
            $parent.each(function () {
                if ($(this).find('.active_module').length > 0) {
                    return;
                }

                var removeThis = true,
                        column_data_styling = $(this).find('.column-data-styling'),
                        data_styling = null;

                column_data_styling.each(function () {
                    if (!removeThis) {
                        return;
                    }

                    data_styling = $.parseJSON($(this).attr('data-styling'));

                    if ((typeof data_styling === 'array' && data_styling.length > 0) || !$.isEmptyObject(data_styling)) {
                        removeThis = false;
                    }
                });

                data_styling = $.parseJSON($(this).find('.row-data-styling').attr('data-styling'));
                if (removeThis && (typeof data_styling === 'string' || $.isEmptyObject(data_styling)) && ($parent.length > 1 && ($(this).index() + 1) < $parent.length)) {
                    var removeCids = $(this).find('[data-cid]').map(function () {
                        return $(this).data('cid');
                    }).get();
                    removeCids.push($(this).data('cid'));
                    console.log(removeCids, 'removeCids');
                    _.each(removeCids, function (cid, k) {
                        var model = api.Models.Registry.lookup(cid);
                        if (model)
                            model.destroy();
                    });
                }
            });

            if (this.$el.children('.themify_builder_row').last().find('.active_module').length > 0 || this.$el.children('.themify_builder_row').length === 0) {
                var rowDataPlainObject = {
                    cols: [{grid_class: 'col-full first last'}]
                },
                rowView = api.Views.init_row(rowDataPlainObject, this.type),
                        $template = rowView.view.render().$el;

                $template.appendTo(this.$el);
            }
        },
		refreshRows: function(){
			ThemifyBuilderCommon.showLoader('show');
			api.bootstrap(function(){
				ThemifyBuilderCommon.showLoader('spinhide');
				//api.Utils.loadContentJs();
				api.vent.trigger('dom:builder:change');
			});
		},
        _selectedGridMenu: function (context) {
            context = context || document;
            $('.grid_menu', context).each(function () {
                var handle = $(this).data('handle'),
                        grid_base = [],
                        $base;
                if (handle === 'module')
                    return;

                $base = $(this).closest('.themify_builder_' + handle).find('.themify_builder_' + handle + '_content');
                $base.children().each(function () {
                    grid_base.push(api.Utils._getColClass($(this).prop('class').split(' ')));
                });

                var $selected = $(this).find('.themify_builder_grid_desktop .grid-layout-' + grid_base.join('-')),
                        $col = $selected.data('col');
                $selected.closest('li').addClass('selected');

                // hide column 'alignment', 'equal column height' and 'gutter' when fullwidth column
                var $grid = $(this).find('.themify_builder_grid_desktop .themify_builder_grid_list'),
                        grid = $selected.data('grid');
                if (grid && grid[0] === '-full') {
                    $grid.nextAll('.themify_builder_column_alignment').find('a:first').trigger('click');
                    $grid.nextAll('.gutter_select').val('gutter-default').trigger('change');
                    $grid.nextAll().hide();
                }
                else {
                    $grid.nextAll().show();
                }
                var grid_items = $(this).find('.themify_builder_grid_tablet .themify_builder_grid_list li li,.themify_builder_grid_mobile .themify_builder_grid_list li li');
                api.Utils.hideResponsiveCols(grid_items, $col);
                if ($selected.data('col')) {
                    $base.addClass('col-count-' + $col + ' count-' + (($col % 2 === 0) ? 'even' : 'odd'));
                }
            });
        },
        makeEqual: function ($obj, target) {
            $obj.each(function () {
                var t = 0;
                $(this).find(target).children().each(function () {
                    var $holder = $(this).find('.themify_module_holder').first();
                    $holder.css('min-height', '');
                    if ($holder.height() > t) {
                        t = $holder.height();
                    }
                });
                $(this).find(target).children().each(function () {
                    $(this).find('.themify_module_holder').first().css('min-height', t + 'px');
                });
            });
        },
        deleteEmptyModule: function () {
            this.$el.find('.active_module.tb_module_state_unsaved').each(function () {
                var model = api.Models.Registry.lookup($(this).data('cid'));
                if (model) {
                    model.destroy();
                }
            });
        },
        toJSON: function () {
            var option_data = {};

            // rows
            this.$el.children('.themify_builder_row').each(function (r) {
                option_data[r] = api.Utils._getRowSettings($(this), r);
            });
            return option_data;
        }
    };

    api.Forms = {
        Validators: {},
        bindEvents: function () {
            var $body = $('body');

            if (_.isNull(api.Utils.tfb_hidden_editor_object)) {
                api.Utils.tfb_hidden_editor_object = tinyMCEPreInit.mceInit['tfb_lb_hidden_editor'];
            }

            if ($.fn.ThemifyGradient) {
                $body.on('change', '.tf-image-gradient-field .tf-radio-input-container input', function () {
                    var value = $(this).val(),
                            input = $(this).closest('.tf-image-gradient-field'),
                            inputGradient = input.find('.themify-gradient-field').hide(),
                            inputImage = input.find('.themify-image-field').hide();

                    if (value === 'image')
                        inputImage.show();
                    if (value === 'gradient')
                        inputGradient.show();
                });
            }

            $body
					.on('click', this.clickCheck)
                    // used for both column and sub-column options
                    .on('click', '#tfb_row_settings .add_new a', this.rowOptAddRow)

                    /* save module option */
                    .on('click', '#tfb_module_settings .add_new a', this.moduleOptAddRow)

                    .on('click', '.js-builder-restore-revision-btn', ThemifyBuilderCommon.restoreRevision)
                    .on('click', '.js-builder-delete-revision-btn', ThemifyBuilderCommon.deleteRevision)

                    .on('click', '#builder_submit_import_form', this.builderImportSubmit)
                    /* Layout Action */
                    .on('click', '.layout_preview img', this.templateSelected)
                    .on('click', '#builder_submit_layout_form', this.saveAsLayout)

                    // Apply All checkbox
                    .on('click', '.style_apply_all', this.applyAll_events)

                    /* On component import form save */
                    .on('click', '#builder_submit_import_component_form', this.importRowModBuilderFormSave);

            this.moduleActions();
        },
		clickCheck: function(e){
			if(!$(e.target).closest('.tb_plus_btn_popover').length && !$(e.target).closest('.empty_holder_text').length) {
				if($('.tb_plus_btn_popover').is(":visible")) {
					$('.tb_plus_btn_popover').hide().appendTo('body');
				}
			}
		},
        moduleSave: function (e) {
            e.preventDefault();

            if (!api.Forms.isValidate($('#tfb_module_settings')))
                return;

            var temp_appended_data = {},
                    entire_appended_data = api.activeModel.get('mod_settings'),
                    $container = $('.current_selected_module').closest('[data-postid]');

            $('#tfb_module_settings .tfb_lb_option').each(function () {
                var option_value, option_class,
                        this_option_id = $(this).attr('id');
                option_class = this_option_id + ' tfb_module_setting';

                if ($(this).hasClass('tfb_lb_wp_editor') && !$(this).hasClass('builder-field')) {
                    if (typeof tinyMCE !== 'undefined') {
                        option_value = tinyMCE.get(this_option_id).hidden === false ? tinyMCE.get(this_option_id).getContent() : switchEditors.wpautop(tinymce.DOM.get(this_option_id).value);
                    } else {
                        option_value = $(this).val();
                    }
                }
                else if ($(this).hasClass('themify-checkbox')) {
                    var cselected = [];
                    $(this).find('.tf-checkbox:checked').each(function (i) {
                        cselected.push($(this).val());
                    });
                    option_value = cselected.length > 0 ? cselected.join('|') : null;
                }
                else if ($(this).hasClass('themify-layout-icon')) {
                    option_value = $(this).find('.selected').length > 0 ? $(this).find('.selected').attr('id') : $(this).children().first().attr('id');
                }
                else if ($(this).hasClass('themify-option-query-cat')) {
                    var parent = $(this).parent(),
                            single_cat = parent.find('.query_category_single'),
                            multiple_cat = parent.find('.query_category_multiple');
                    option_value = multiple_cat.val() ? multiple_cat.val() + '|multiple' : single_cat.val() + '|single';
                }
                else if ($(this).hasClass('themify_builder_row_js_wrapper')) {
                    option_value = api.Utils.getRepeaterValues($(this));
                }
                else if ($(this).hasClass('tf-radio-input-container')) {
                    option_value = $(this).find('input[name="' + this_option_id + '"]:checked').val();
                }
                else if ($(this).hasClass('module-widget-form-container')) {
                    option_value = $(this).find(':input').themifySerializeObject();
                }
                else if ($(this).is('select, input, textarea')) {
                    option_value = $(this).val();
                }

                if (option_value) {

                    /* do not save the value if it's equal to the default */
                    if (option_value == $(this).data('default') && !$(this).hasClass('themify-checkbox') && option_value !== 'solid') {
                        return;
                    }

                    temp_appended_data[this_option_id] = option_value;
                }
            });

            if ('desktop' !== api.activeBreakPoint) {
                var styleFields = $('#themify_builder_options_styling .tfb_lb_option').map(function () {
                    return $(this).attr('id');
                }).get();

                // get current styling data
                var temp_style_data = _.pick(temp_appended_data, styleFields);

                // revert desktop styling data
                temp_appended_data = _.omit(temp_appended_data, styleFields);
                temp_appended_data = _.extend(temp_appended_data, _.pick(entire_appended_data, styleFields));

                // append breakpoint data
                temp_appended_data['breakpoint_' + api.activeBreakPoint] = temp_style_data;

                // Check for another breakpoint
                _.each(_.omit(themifyBuilder.breakpoints, api.activeBreakPoint), function (value, key) {
                    if (!_.isUndefined(entire_appended_data['breakpoint_' + key])) {
                        temp_appended_data['breakpoint_' + key] = entire_appended_data['breakpoint_' + key];
                    }
                });
            } else {
                // Check for another breakpoint
                _.each(themifyBuilder.breakpoints, function (value, key) {
                    if (!_.isUndefined(entire_appended_data['breakpoint_' + key])) {
                        temp_appended_data['breakpoint_' + key] = entire_appended_data['breakpoint_' + key];
                    }
                });
            }

            api.activeModel.set({mod_settings: {}}, {silent: true}); // Fix bug backbone change doesn't triggered
            api.activeModel.set({mod_settings: api.Utils.removeEmptyFields(temp_appended_data)});
            api.activeModel.trigger('custom:element:updatestate');

            ThemifyBuilderCommon.Lightbox.close();

            api.vent.trigger('dom:observer:end', $container, {cid: api.activeModel.cid, value: {mod_settings: api.activeModel.get('mod_settings')}});

            // hack: hide tinymce inline toolbar
            if ($('.mce-inline-toolbar-grp:visible').length > 0) {
                $('.mce-inline-toolbar-grp:visible').hide();
            }

            if ($('.tb-import-layout-button').length > 0) {
                $('.tb-import-layout-button').remove();
            }
        },
        rowSaving: function (e) {
            e.preventDefault();

            var temp_appended_data = $('#tfb_row_settings').themifySerializeObject(),
                    entire_appended_data = api.activeModel.get('styling') || {},
                    temp_style_data = {}, $container = $('.current_selected_row').closest('[data-postid]');

            $('#tfb_row_settings').find('.themify_builder_row_js_wrapper').each(function () {
                var this_option_id = $(this).attr('id'),
                        row_items = api.Utils.getRepeaterValues($(this));

                if (row_items) {
                    temp_appended_data[this_option_id] = row_items;
                }
            });

            if ('desktop' !== api.activeBreakPoint) {
                var styleFields = $('#themify_builder_row_fields_styling .tfb_lb_option').map(function () {
                    return $(this).attr('id');
                }).get();

                // get current styling data
                temp_style_data = _.pick(temp_appended_data, styleFields);

                // revert desktop styling data
                temp_appended_data = _.omit(temp_appended_data, styleFields);
                temp_appended_data = _.extend(temp_appended_data, _.pick(entire_appended_data, styleFields));

                // append breakpoint data
                temp_appended_data['breakpoint_' + api.activeBreakPoint] = temp_style_data;

                // Check for another breakpoint
                _.each(_.omit(themifyBuilder.breakpoints, api.activeBreakPoint), function (value, key) {
                    if (!_.isUndefined(entire_appended_data['breakpoint_' + key])) {
                        temp_appended_data['breakpoint_' + key] = entire_appended_data['breakpoint_' + key];
                    }
                });
            } else {
                // Check for another breakpoint
                _.each(themifyBuilder.breakpoints, function (value, key) {
                    if (!_.isUndefined(entire_appended_data['breakpoint_' + key])) {
                        temp_appended_data['breakpoint_' + key] = entire_appended_data['breakpoint_' + key];
                    }
                });
            }

            api.activeModel.set({styling: {}}, {silent: true}); // fix backbone model doesn't trigger change
            api.activeModel.set('styling', api.Utils.removeEmptyFields(temp_appended_data));

            ThemifyBuilderCommon.Lightbox.close();

            api.vent.trigger('dom:observer:end', $container, {cid: api.activeModel.cid, value: {styling: api.activeModel.get('styling')}});

        },
        subRowSaving: function (e) {
            e.preventDefault();

            var entire_appended_data = api.activeModel.get('styling') || {},
                    temp_appended_data = $('#tfb_subrow_settings').themifySerializeObject(),
                    temp_style_data = {}, $container = $('.current_selected_sub_row').closest('[data-postid]');

            if ('desktop' !== api.activeBreakPoint) {
                var styleFields = $('#tfb_subrow_settings .tfb_lb_option').map(function () {
                    return $(this).attr('id');
                }).get();

                // get current styling data
                temp_style_data = temp_appended_data;

                // revert desktop styling data
                temp_appended_data = _.omit(temp_appended_data, styleFields);
                temp_appended_data = _.extend(temp_appended_data, _.pick(entire_appended_data, styleFields));

                // append breakpoint data
                temp_appended_data['breakpoint_' + api.activeBreakPoint] = temp_style_data;

                // Check for another breakpoint
                _.each(_.omit(themifyBuilder.breakpoints, api.activeBreakPoint), function (value, key) {
                    if (!_.isUndefined(entire_appended_data['breakpoint_' + key])) {
                        temp_appended_data['breakpoint_' + key] = entire_appended_data['breakpoint_' + key];
                    }
                });
            } else {
                // Check for another breakpoint
                _.each(themifyBuilder.breakpoints, function (value, key) {
                    if (!_.isUndefined(entire_appended_data['breakpoint_' + key])) {
                        temp_appended_data['breakpoint_' + key] = entire_appended_data['breakpoint_' + key];
                    }
                });
            }

            api.activeModel.set({styling: {}}, {silent: true}); // fix backbone model doesn't trigger change
            api.activeModel.set('styling', api.Utils.removeEmptyFields(temp_appended_data));

            ThemifyBuilderCommon.Lightbox.close();

            api.vent.trigger('dom:observer:end', $container, {cid: api.activeModel.cid, value: {styling: api.activeModel.get('styling')}});

        },
        columnSaving: function (e) {
            e.preventDefault();

            var entire_appended_data = api.activeModel.get('styling') || {},
                    temp_appended_data = $('#tfb_column_settings').themifySerializeObject(),
                    temp_style_data = {}, $container = $('.current_selected_column').closest('[data-postid]');

            if ('desktop' !== api.activeBreakPoint) {
                var styleFields = $('#tfb_column_settings .tfb_lb_option').map(function () {
                    return $(this).attr('id');
                }).get();

                // get current styling data
                temp_style_data = temp_appended_data;

                // revert desktop styling data
                temp_appended_data = _.omit(temp_appended_data, styleFields);
                temp_appended_data = _.extend(temp_appended_data, _.pick(entire_appended_data, styleFields));

                // append breakpoint data
                temp_appended_data['breakpoint_' + api.activeBreakPoint] = temp_style_data;

                // Check for another breakpoint
                _.each(_.omit(themifyBuilder.breakpoints, api.activeBreakPoint), function (value, key) {
                    if (!_.isUndefined(entire_appended_data['breakpoint_' + key])) {
                        temp_appended_data['breakpoint_' + key] = entire_appended_data['breakpoint_' + key];
                    }
                });
            } else {
                // Check for another breakpoint
                _.each(themifyBuilder.breakpoints, function (value, key) {
                    if (!_.isUndefined(entire_appended_data['breakpoint_' + key])) {
                        temp_appended_data['breakpoint_' + key] = entire_appended_data['breakpoint_' + key];
                    }
                });
            }
            api.activeModel.set('styling', api.Utils.removeEmptyFields(temp_appended_data));

            ThemifyBuilderCommon.Lightbox.close();

            api.vent.trigger('dom:observer:end', $container, {cid: api.activeModel.cid, value: {styling: api.activeModel.get('styling')}});
        },
        rowOptAddRow: function (e) {
            var parent = $(this).parent().prev(),
                    template = api.cache.repeaterElements[ parent.attr('id') ].clone(),
                    row_count = $('.themify_builder_row_js_wrapper').find('.themify_builder_row:visible').length + 1,
                    number = row_count + Math.floor(Math.random() * 9);

            // clear form data
            template.removeClass('collapsed').find('.themify_builder_row_content').show();
            template.find('.themify-builder-radio-dnd').each(function (i) {
                var oriname = $(this).attr('name');
                $(this).attr({'name': oriname + '_' + row_count, 'id': oriname + '_' + row_count + '_' + i}).not(':checked').prop('checked', false)
                        .next('label').attr('for', oriname + '_' + row_count + '_' + i);
            });

            template.find('.themify-layout-icon a').removeClass('selected');

            template.find('.thumb_preview').each(function () {
                $(this).find('.img-placeholder').html('').parent().hide();
            });
            template.find('input[type="text"], textarea').each(function () {
                $(this).val('');
            });
            template.find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                var $parent = $(this).parents('.wp-editor-wrap').parent(),
                        ori_id = $(this).prop('id'),
                        name = $(this).prop('name'),
                        new_id = ori_id + '_' + ThemifyBuilderCommon.randNumber(),
                        dom_changes = $parent.html().replace(new RegExp(ori_id, 'g'), new_id),
                        newClass = e.which ? 'newEditor' : '';

                $parent.html(dom_changes).find('.tfb_lb_wp_editor').prop('name', name).addClass(newClass);
            });
            template.find('.themify-builder-plupload-upload-uic').each(function (i) {
                $(this).attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-upload-ui').addClass('plupload-clone')
                        .find('input[type="button"]').attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-browse-button');
            });

            // Fix color picker input
            template.find('.builderColorSelectInput').each(function () {
                var thiz = $(this),
                        input = thiz.clone().val(''),
                        parent = thiz.closest('.themify_builder_field');
                thiz.prev().minicolors('destroy').removeAttr('maxlength');
                parent.find('.colordisplay').wrap('<div class="themify_builder_input" />').before('<span class="builderColorSelect"><span></span></span>').after(input);
                api.Utils.setColorPicker(parent);
            });

            $(template).appendTo(parent).show();

            if (e.which) {
                $('#tfb_row_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child.newEditor').each(function (i) {
                    api.Views.init_control('wp_editor', {el: $(this)});
                    $(this).removeClass('newEditor');

                });
                api.Utils.builderPlupload('new_elemn');
            }

            e.preventDefault();
        },
        moduleOptAddRow: function (e) {
            var parent = $(this).parent().prev(),
                    template = api.cache.repeaterElements[ parent.attr('id') ].clone(),
                    row_count = $('.themify_builder_row_js_wrapper').find('.tb_repeatable_field').length + 1,
                    number = row_count + Math.floor(Math.random() * 9);

            // clear form data
            template.removeClass('collapsed').find('.themify_builder_row_content').show();
            template.find('.themify-builder-radio-dnd').each(function (i) {
                var oriname = $(this).attr('name');
                $(this).prop({'name': oriname + '_' + row_count, 'id': oriname + '_' + row_count + '_' + i, 'checked': false})
                        .next('label').attr('for', oriname + '_' + row_count + '_' + i);
                if ($(this).is('[data-checked]')) {
                    var $self = $(this);
                    $(this).prop('checked', true);
                    setTimeout(function () {
                        $self.trigger('change')
                    }, 100);
                }
            });

            // Hide conditional inputs
            template.find('[data-binding]').each(function () {
                var bindingData = $(this).data('binding');
                try {
                    var hideEl = '.' + bindingData.empty.hide.join(', .');
                    template.find(hideEl).children().hide();
                } catch (e) {
                }
            });

            template.find('.themify-layout-icon a').removeClass('selected');

            template.find('.thumb_preview').each(function () {
                $(this).find('.img-placeholder').html('').parent().hide();
            });
            template.find('input[type=text], textarea').each(function () {
                $(this).val('');
            });
            template.find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                var $parent = $(this).parents('.wp-editor-wrap').parent(),
                        ori_id = $(this).prop('id'),
                        name = $(this).prop('name'),
                        new_id = ori_id + '_' + ThemifyBuilderCommon.randNumber(),
                        dom_changes = $parent.html().replace(new RegExp(ori_id, 'g'), new_id),
                        newClass = e.which ? 'newEditor' : '';

                $parent.html(dom_changes).find('.tfb_lb_wp_editor').prop('name', name).addClass(newClass);
            });
            template.find('.themify-builder-plupload-upload-uic').each(function (i) {
                $(this).attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-upload-ui').addClass('plupload-clone')
                        .find('input[type=button]').attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-browse-button');
            });

            // Fix color picker input
            template.find('.builderColorSelectInput').each(function () {
                var thiz = $(this),
                        input = thiz.clone().val(''),
                        parent = thiz.closest('.themify_builder_field');
                thiz.prev().minicolors('destroy').removeAttr('maxlength');
                parent.find('.colordisplay').wrap('<div class="themify_builder_input" />').before('<span class="builderColorSelect"><span></span></span>').after(input);
                api.Utils.setColorPicker(parent);
            });

            template.find('.tfb_lb_option_child').each(function () {
                if ($(this).data('control-binding') && $(this).data('control-type')) {
                    api.Views.init_control($(this).data('control-type'), {el: $(this), binding_type: $(this).data('control-binding')});
                }
            });

            $(template).appendTo(parent).show();
            if (e.which) {
                $('#tfb_module_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child.newEditor').each(function (i) {
                    api.Views.init_control('wp_editor', {el: $(this)});
                    $(this).removeClass('newEditor');

                });
                api.Utils.builderPlupload('new_elemn');
            }

            e.preventDefault();
        },
        builderImportSubmit: function (e) {
            e.preventDefault();

            var $this = $(this),
                    options = {
                        buttons: {
                            no: {
                                label: 'Replace Existing Builder'
                            },
                            yes: {
                                label: 'Append Existing Builder'
                            }
                        }
                    };

            ThemifyBuilderCommon.LiteLightbox.confirm(themifyBuilder.i18n.dialog_import_page_post, function (response) {
                $.ajax({
                    type: "POST",
                    url: themifyBuilder.ajaxurl,
                    dataType: 'json',
                    data:
                            {
                                action: 'builder_import_submit',
                                nonce: themifyBuilder.tfb_load_nonce,
                                data: $this.closest('form').serialize(),
                                importType: 'no' === response ? 'replace' : 'append',
                                importTo: themifyBuilder.post_ID
                            },
                    beforeSend: function (xhr) {
                        ThemifyBuilderCommon.showLoader('show');
                    },
                    success: function (data) {
                        ThemifyBuilderCommon.Lightbox.close();
                        ThemifyBuilderCommon.showLoader('spinhide');
                        window.location.reload();
                    }
                });

            }, options);
        },
        templateSelected: function (e) {
            e.preventDefault();

            var $this = $(this).closest('.layout_preview'),
                    options = {
                        buttons: {
                            no: {
                                label: 'Replace Existing Layout'
                            },
                            yes: {
                                label: 'Append Existing Layout'
                            }
                        }
                    };

            ThemifyBuilderCommon.LiteLightbox.confirm(themifyBuilder.i18n.confirm_template_selected, function (response) {
                var args = {
                    type: "POST",
                    url: themifyBuilder.ajaxurl,
                    dataType: 'json',
                    data: {
                        action: 'no' === response ? 'tfb_set_layout' : 'tfb_append_layout',
                        nonce: themifyBuilder.tfb_load_nonce,
                        layout_slug: $this.data('slug'),
                        current_builder_id: themifyBuilder.post_ID,
                        layout_group: $this.data('group')
                    },
                    success: function (data) {
                        ThemifyBuilderCommon.Lightbox.close();
                        if (data.status == 'success') {
                            if ('visual' === api.mode) {
                                window.location.hash = '#builder_active';
                                window.location.reload();
                            } else {
                                var postData = wp.autosave.getPostData();
                                api.isPostSave = true;
                                if (!_.isUndefined(postData.auto_draft) && postData.auto_draft) {
                                    var collection = new api.Collections.Rows(data.builder_data);
                                    api.Instances.Builder[0] = new api.Views.Builder({el: '#themify_builder_row_wrapper', collection: collection});
                                    api.Instances.Builder[0].render();
                                }

                                $($('#save-post').length ? '#save-post' : '#publish').trigger('click');
                            }
                        } else {
                            alert(data.msg);
                        }
                    }
                };
                if ($this.data('group') == 'pre-designed') {
                    ThemifyBuilderCommon.showLoader('show');
                    var file = 'https://themify.me/themify-layouts/' + $this.data('slug') + '.txt';
                    $.get(file, null, null, 'text')
                            .done(function (builder_data) {
                                args.data.builder_data = builder_data;
                                $.ajax(args);
                            })
                            .fail(function (jqxhr, textStatus, error) {
                                ThemifyBuilderCommon.LiteLightbox.alert('There was an error in loading layout, please try again later, or you can download this file: (' + file + ') and then import manually (http://themify.me/docs/builder#import-export).');
                            })
                            .always(function () {
                                ThemifyBuilderCommon.showLoader();
                            })
                } else {
                    $.ajax(args);
                }
            }, options);
        },
        saveAsLayout: function (e) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data: {
                    action: 'tfb_save_custom_layout',
                    nonce: themifyBuilder.tfb_load_nonce,
                    form_data: $('#tfb_save_layout_form').serialize()
                },
                success: function (data) {
                    if (data.status === 'success') {
                        ThemifyBuilderCommon.Lightbox.close();
                    } else {
                        alert(data.msg);
                    }
                }
            });
        },
        // "Apply all" // apply all events
        applyAll_events: function ($selector) {
            var $this = $(this),
                    $fields = $this.closest('.themify_builder_field').prevUntil('h4');

            if ($this.prop('checked')) {
                var $fire = true;
                $fields.not(':last').slideUp(function () {
                    if ($fire) {

                        $fields.last().find('input[type="text"], select').each(function () {
                            var ev = ($(this).prop('tagName') === 'SELECT') ? 'change' : 'keyup';
                            $(this).trigger(ev);
                        });
                        $fire = false;
                    }
                });
                $fields.last().children('.themify_builder_input').css('color', '#FFF');
            } else {
                $fields.slideDown();
                $fields.last().children('.themify_builder_input').css('color', '');
            }
        },
        importRowModBuilderFormSave: function (e) {
            e.preventDefault();

            var $form = $("#tfb_imp_component_form"),
                    component = $form.find("input[name='component']").val(),
                    $container = $('[data-cid="' + api.activeModel.cid + '"]').closest('[data-postid]');

            api.vent.trigger('dom:observer:start', $container, {cid: api.activeModel.cid, value: api.activeModel.toJSON()});
            var $dataField = $form.find('#tfb_imp_' + component.replace('-', '_') + '_data_field'),
                    dataPlainObject = JSON.parse($dataField.val());
            if (!dataPlainObject.hasOwnProperty('component_name') ||
                    dataPlainObject['component_name'] !== component) {
                ThemifyBuilderCommon.alertWrongPaste();
                return;
            }
            if (component === 'column' || component === 'sub-column') {
                var $column = $('.current_selected_column'),
                        $row = $column.closest('column' === component ? '.themify_builder_row' : '.themify_builder_sub_row'),
                        row_index = $row.index(),
                        col_index = $column.index();

                dataPlainObject['column_order'] = col_index;
                dataPlainObject['grid_class'] = $column.prop('class').replace('themify_builder_col', '');

                if ('column' === component) {
                    dataPlainObject['row_order'] = row_index;
                } else {
                    dataPlainObject['sub_row_order'] = row_index;
                    dataPlainObject['row_order'] = $column.closest('.themify_builder_row').index();
                    dataPlainObject['col_order'] = $column.parents('.themify_builder_col').index();
                }
            }
            ThemifyBuilderCommon.Lightbox.close();

            api.activeModel.setData(dataPlainObject);

            api.vent.trigger('dom:builder:change');

            if ('visual' !== api.mode) {
                api.vent.trigger('dom:observer:end', $container, {cid: api.activeModel.cid, value: api.activeModel.toJSON()});
            }

        },
		moduleActions: function () {
			var $body = $('body');
			$body.on('editing_module_option', function (e, settings) {
				var $field = $('#tfb_module_settings .tfb_lb_option.module-widget-select-field');
				function populateWidgetField() {
					if ($field.length === 0)
						return;

					var $seclass = $field.val(),
							id_base = $field.find(':selected').data('idbase'),
							$instance = settings.instance_widget;

					$.ajax({
						type: "POST",
						url: themifyBuilder.ajaxurl,
						dataType: 'html',
						data: {
							action: 'module_widget_get_form',
							tfb_load_nonce: themifyBuilder.tfb_load_nonce,
							load_class: $seclass,
							id_base: id_base,
							widget_instance: $instance
						},
						success: function (data) {
							var $newElems = $(data);
							$('.module-widget-form-placeholder').html($newElems);

							// fix display of <select> fields
							$('#themify_builder_lightbox_container').each(function () {
								var $this = $(this).find('#instance_widget');
								$this.find('select').wrap('<div class="selectwrapper"></div>');
							});
							$('.selectwrapper').click(function () {
								$(this).toggleClass('clicked');
							});

							$( document ).trigger( 'widget-added', [ $('.module-widget-form-placeholder') ] );
						}
					});
				}
				$body.find('.module-widget-select-field').on('change', function () {
					$('.module-widget-form-placeholder').empty();
					populateWidgetField();
				});
				populateWidgetField();
			});
		},
        isValidate: function ($form) {
            if ($form.find('[data-validation]').length === 0)
                return true;
            var that = this,
                    errors = {};
            $form.find('[data-validation]').each(function () {
                var $this = $(this),
                        rule = $(this).data('validation'), value = '';

                if ($this.is('select, input, textarea')) {
                    value = $this.val();
                }
                if (!that.checkValidate(rule, value))
                    errors[ $this.prop('id') ] = $this.data('error-msg');
            });

            $form.find('.tb_field_error').removeClass('tb_field_error')
                    .end().find('.tb_field_error_msg').remove();

            if (!_.isEmpty(errors)) {
                _.each(errors, function (msg, div_id) {
                    var $field = $('#' + div_id);
                    $field.addClass('tb_field_error');
                    $('<span/>', {class: 'tb_field_error_msg', 'data-error-key': div_id})
                            .text(msg).insertAfter($field);
                });
                return false;
            } else {
                return true;
            }
        },
        checkValidate: function (rule, value) {
            var validator = api.Forms.get_validator(rule);
            return validator(value);
        },
    };

    // Validators
    api.Forms.register_validator = function (type, fn) {
        this.Validators[ type ] = fn;
    };
    api.Forms.get_validator = function (type) {
        if (this.Validators.hasOwnProperty(type))
            return this.Validators[ type ];

        return this.Validators.not_empty; // default
    };

    api.Forms.register_validator('email', function (value) {
        var pattern = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,
                arr = value.split(','),
                errors = $.map(arr, function (v, i) {
                    return pattern.test(v) ? null : '1';
                });
        if (errors.length) {
            return false;
        } else {
            return true;
        }
    });

    api.Forms.register_validator('not_empty', function (value) {
        if (!value || '' === value.trim())
            return false;
        return true;
    });

    api.Views.Toolbar = Backbone.View.extend({
        events: {
            'click .tb_toolbar_add_modules': 'toogle_module_panel',
            'hover .tb_toolbar_add_modules': 'toggle_module_panel_hover',
            'click .add_module_btn': 'add_module',
            // Undo/Redo
            'click .js-themify-builder-undo-btn': 'action_undo',
            'click .js-themify-builder-redo-btn': 'action_redo',
            // Import
            'click .themify_builder_import_file': 'import_file',
            'click .themify_builder_import_page': 'import_page',
            'click .themify_builder_import_post': 'import_post',
            // Layout
            'click .themify_builder_load_layout': 'load_layout',
            'click .themify_builder_save_layout': 'save_layout',
            // Duplicate
            'click .themify_builder_dup_link': 'duplicate',
			// Pre-designed
			//'change .tb_module_panel_rows_wrap' : 'load_row_templates',
			//'click .tb_row_cat_filter a' : 'category_sort_row_tmpl',

            // Revisions
            'click .themify_builder_load_revision': 'load_revision',
            'click .themify_builder_save_revision': 'save_revision',
            'click .tb_toolbar_save': 'save',
            'click .tb_toolbar_close_btn': 'panel_close',
            //'keyup .tb_module_panel_search_text': 'search',
            'click .tb_module_panel_lock': 'lock',
            'click .tb_toolbar_revision_btn': 'toggle_rev_dropdown',
            'click .js--themify_builder_breakpoint_switcher': 'breakpoint_switcher',
        },
        initialize: function () {
            var that = this,
                    $body = $('body'),
                    $htmlbody = $('html,body');

            var moduleItems = '',
				popoverItems = '',
                    moduleItemTmpl = wp.template('builder_module_item_draggable'),
                    rowPopover = wp.template('builder_addon_popover');
			$('body').append(rowPopover({}));
            _.each(_.sortBy(themifyBuilder.modules, 'name'), function (item, key) {
                moduleItems += moduleItemTmpl({slug: item.slug, name: item.name});
			});
            this.$('.tb_module_panel_modules_wrap').html(moduleItems);
            $('.tb_plus_btn_popover .tb_pc_module_holder').html(moduleItems);
			new SimpleBar($('.tb_plus_btn_popover .tb_pc_module_holder')[0]);
			
			var dragOpt = {
					appendTo: "body",
					helper: "clone",
					revert: 'invalid',
					connectToSortable: '.themify_module_holder',
					cursorAt: {
						top: 10,
						left: 40
					},
					drag: function (e, ui) {
						ui.helper.addClass('tb_module_dragging_helper').removeClass('themify_builder_module');
						that._hide_module_panel();
						if (!$body.hasClass('tb_module_on_dragging')) {
							$body.addClass('tb_module_on_dragging');
						}
						if (!$htmlbody.hasClass('tb_preview_fullheight')) {
							$htmlbody.addClass('tb_preview_fullheight');
						}
					},
					stop: function (e, ui) {
						if ($body.hasClass('tb_module_on_dragging')) {
							$body.removeClass('tb_module_on_dragging');
						}
						if ($htmlbody.hasClass('tb_preview_fullheight')) {
							$htmlbody.removeClass('tb_preview_fullheight');
						}
					}
				};
            this.$('.themify_builder_module').draggable(dragOpt);
            $('.tb_plus_btn_popover .themify_builder_module').draggable(dragOpt);

            // Listen to any changes of undo/redo
            if (document.getElementsByClassName('tb_toolbar_menu').length > 0) {
                ThemifyBuilderCommon.undoManager.instance.setCallback(this.undoManagerCallback.bind(this));
                this.updateUndoBtns();
                api.vent.on('dom:observer:start', function ($container, data) {
                    data = data || {};
                    ThemifyBuilderCommon.undoManager.setStartValue($container[0].innerHTML);
                    ThemifyBuilderCommon.undoManager.setStartData(data);
                    if ('visual' === api.mode)
                        ThemifyBuilderCommon.undoManager.setStartStyleValue(api.styleSheet.getCSSText());
                    console.log('dom:observer:start');
                })
                        .on('dom:observer:end', function ($container, newData) {
                            newData = newData || {};
                            var startValue = ThemifyBuilderCommon.undoManager.getStartValue(),
                                    startData = ThemifyBuilderCommon.undoManager.getStartData(),
                                    newValue = $container[0].innerHTML,
                                    styleData = null;

                            if ('visual' === api.mode) {
                                styleData = {
                                    startValue: ThemifyBuilderCommon.undoManager.getStartStyleValue(),
                                    newValue: api.styleSheet.getCSSText()
                                };
                            }

                            if (startValue !== newValue) {
                                ThemifyBuilderCommon.undoManager.set($container, startValue, newValue, startData, newData, styleData);
                                api.editing = true;
                                console.log('dom:observer:end');
                            }
                        });
            }

            $body.on('builder_toggle_frontend', function (e, is_edit) {
                if (is_edit && that.is_locked) {
                    $body.addClass('tb_module_panel_locked');
                } else if (!is_edit && that.is_locked) {
                    $body.removeClass('tb_module_panel_locked');
                }
            }).on('click', function (e) {
				/* verify that "click" is user generated, not triggered by external script (eg. $.fn.trigger) */
				if ( ! e.hasOwnProperty('originalEvent') ) {
					return;
				}
                if (that.is_locked || 'tb_toolbar' === e.target.id || $(e.target).closest('#tb_toolbar').length || $(e.target).hasClass('js-tb_empty_row_btn'))
                    return;
                that._hide_module_panel();
            }).on('click', '.tb_module_types a',function (e){
				if($(e.target).closest('.tb_plus_btn_popover').length){
					that.change_tab(e,'.tb_plus_btn_popover');
				} else {
					that.change_tab(e,'#tb_module_panel');
				}
			}).on('change', '.tb_module_panel_rows_wrap',function (e){
					that.load_row_templates(e);
			}).on('click', '.tb_row_cat_filter a',function (e){
				if($(e.target).closest('.tb_plus_btn_popover').length){
					that.category_sort_row_tmpl(e,'.tb_plus_btn_popover');
				} else {
					that.category_sort_row_tmpl(e,'#tb_module_panel');
				}
			}).on('keyup', '.tb_module_panel_search_text',function (e){
				if($(e.target).closest('.tb_plus_btn_popover').length){
					that.search(e,'.tb_plus_btn_popover');
				} else {
					that.search(e,'#tb_module_panel');
				}
			});

            this.$('.tb_toolbar_save').on({
                focusout: function () {
                    $(this).data('timer', setTimeout(function () {
                        that.$('.tb_toolbar_revision_btn').removeClass('tb_toolbar_rev_hover');
                    }.bind(this), 300));
                },
                focusin: function () {
                    clearTimeout($(this).data('timer'));
                }
            });

            new SimpleBar(this.$('.tb_module_panel_modules_wrap')[0]);

            this.on('hide-distraction-panel', function () {
                this._hide_distraction_module_panel();
            }).on('show-distraction-panel', function () {
                this._show_distraction_module_panel();
            });

            this.$('.tb_export_link').prop('href', this.$('.tb_export_link').prop('href').replace('data.post_ID', themifyBuilder.post_ID));

            if ('visual' === api.mode) {
                this._setupModulePanelState();
            }
        },
        toogle_module_panel: function (e) {
            e.preventDefault();

            if ($(e.currentTarget).hasClass('tb_disabled'))
                return;

            if (this.is_locked) {
                this.is_locked = false;
                $('.tb_module_panel_lock').removeClass('tb_locked');

                if ('visual' === api.mode) {
                    $('body').removeClass('tb_module_panel_locked');
                }
            }
            this.$('#tb_module_panel').toggleClass('tb_module_panel_show');
        },
        toggle_module_panel_hover: function (e) {
            e.stopPropagation();
            if ($(e.currentTarget).hasClass('tb_disabled'))
                return;

            if (e.type === 'mouseenter') {
                this.$('#tb_module_panel').addClass('tb_module_panel_show');
            }
        },
        add_module: function (e) {
            e.preventDefault();

            this._hide_module_panel();

            var moduleView = api.Views.init_module({mod_name: $(e.currentTarget).closest('.themify_builder_module').data('module-slug')}, api.Instances.Builder[0].type),
                    dest = api.Instances.Builder[0].$el.find('.themify_builder_row:visible').first().find('.themify_module_holder').first(),
                    $newElems = moduleView.view.render().$el,
                    position = $newElems.appendTo(dest);

            $('html,body').animate({scrollTop: position.offset().top - 300}, 500);
            moduleView.view.trigger('edit');
            moduleView.view.trigger('custom:preview:init');
        },
        btnUndo: document.getElementsByClassName('js-themify-builder-undo-btn')[0],
        btnRedo: document.getElementsByClassName('js-themify-builder-redo-btn')[0],
        action_undo: function (e) {
            e.preventDefault();
            if (e.currentTarget.classList.contains('tb_disabled'))
                return;
            ThemifyBuilderCommon.undoManager.instance.undo();
            this.updateUndoBtns();
            this.undoUpdateCallback();
        },
        action_redo: function (e) {
            e.preventDefault();
            if (e.currentTarget.classList.contains('tb_disabled'))
                return;
            ThemifyBuilderCommon.undoManager.instance.redo();
            this.updateUndoBtns();
            this.undoUpdateCallback();
        },
        updateUndoBtns: function () {
            if (ThemifyBuilderCommon.undoManager.instance.hasUndo()) {
                this.btnUndo.classList.remove('tb_disabled');
            } else {
                this.btnUndo.classList.add('tb_disabled');
            }

            if (ThemifyBuilderCommon.undoManager.instance.hasRedo()) {
                this.btnRedo.classList.remove('tb_disabled');
            } else {
                this.btnRedo.classList.add('tb_disabled');
            }
        },
        undoManagerCallback: function () {
            console.log('undo callback');
            this.updateUndoBtns();
            ThemifyBuilderCommon.undoManager.startValue = null; // clear temp
        },
        undoUpdateCallback: function () {
            api.Utils.columnDrag(null, false);
            $('.themify_builder_module_front_overlay').hide();
            $('.themify_builder_dropdown_front').removeAttr('style');
            $('.themify_builder_col').css({height: '', position: '', zIndex: ''});
            $('.themify_grid_drag_placeholder,.themify_builder_sortable_helper').remove();
            $('.tb_module_dragging_helper').remove();

            if ('visual' === api.mode) {
                api.Utils.loadContentJs();
            }

            api.vent.trigger('dom:builder:change');

            if ('visual' === api.mode) {
                api.Frontend.responsiveFrame.doSync(); // sync responsive frame
            }
        },
        import_file: function (e) {
            e.preventDefault();
            var options = {
                dataType: 'html',
                data: {
                    action: 'builder_import_file'
                }
            },
            callback = this.builderImportPlupload;

            if (confirm(themifyBuilder.i18n.importFileConfirm)) {
                ThemifyBuilderCommon.Lightbox.open(options, callback);
            }
        },
        import_page: function (e) {
            e.preventDefault();
            this.builderImport('page');
        },
        import_post: function (e) {
            e.preventDefault();
            this.builderImport('post');
        },
        builderImport: function (imType) {
            var options = {
                dataType: 'html',
                data: {
                    action: 'builder_import',
                    type: imType
                }
            };
            ThemifyBuilderCommon.Lightbox.open(options, null);
        },
        builderImportPlupload: function () {
            var $builderPluploadUpload = $(".themify-builder-plupload-upload-uic");

            if ($builderPluploadUpload.length > 0) {
                var pconfig = false;
                $builderPluploadUpload.each(function () {
                    var $this = $(this),
                            id1 = $this.attr("id"),
                            imgId = id1.replace("themify-builder-plupload-upload-ui", "");

                    pconfig = JSON.parse(JSON.stringify(themify_builder_plupload_init));

                    pconfig["browse_button"] = imgId + pconfig["browse_button"];
                    pconfig["container"] = imgId + pconfig["container"];
                    pconfig["drop_element"] = imgId + pconfig["drop_element"];
                    pconfig["file_data_name"] = imgId + pconfig["file_data_name"];
                    pconfig["multipart_params"]["imgid"] = imgId;
                    pconfig["multipart_params"]["_ajax_nonce"] = themifyBuilder.tfb_load_nonce;
                    ;
                    pconfig["multipart_params"]['topost'] = themifyBuilder.post_ID;

                    var uploader = new plupload.Uploader(pconfig);

                    uploader.bind('Init', function (up) {
                    });

                    uploader.init();

                    // a file was added in the queue
                    uploader.bind('FilesAdded', function (up, files) {
                        up.refresh();
                        up.start();
                        ThemifyBuilderCommon.showLoader('show');
                    });

                    uploader.bind('Error', function (up, error) {
                        var $promptError = $('.prompt-box .show-error');
                        $('.prompt-box .show-login').hide();
                        $promptError.show();

                        if ($promptError.length > 0) {
                            $promptError.html('<p class="prompt-error">' + error.message + '</p>');
                        }
                        $(".overlay, .prompt-box").fadeIn(500);
                    });

                    // a file was uploaded
                    uploader.bind('FileUploaded', function (up, file, response) {
                        var json = JSON.parse(response['response']), status;
                        status = '200' == response['status'] && !json.error ? 'done' : 'error';
                        $("#themify_builder_alert").removeClass("busy").addClass(status).delay(800).fadeOut(800, function () {
                            $(this).removeClass(status);
                        });

                        if (json.error) {
                            alert(json.error);
                            return;
                        }

                        $('#themify_builder_alert').promise().done(function () {
                            ThemifyBuilderCommon.Lightbox.close();
                            window.location.reload();
                        });

                    });

                });
            }
        },
        // Layout actions
        load_layout: function (e) {
            e.preventDefault();
            var api = this,
                    options = {
                        dataType: 'html',
                        data: {
                            action: 'tfb_load_layout'
                        }
                    };

            ThemifyBuilderCommon.Lightbox.open(options, function () {
                var container = $('#themify_builder_tabs_pre-designed'),
                        loader = $('<div class="themify-builder-alert busy"></div>').appendTo(container);
                $.getJSON('https://themify.me/themify-layouts/index.json')
                        .done(function (data) {
                            var template = wp.template('themify-builder-layout-item'),
                                    categories = [];

                            container.append(template(data));
                            api.layoutLayoutsList();
                            container.find('li.layout_preview_list').each(function () {
                                var cat = $(this).data('category').split(',');
                                $.each(cat, function (i, v) {
                                    if ('' !== v && $.inArray(v, categories) === -1) {
                                        categories.push(v);
                                        $('#themify_builder_pre-designed-filter').append('<li><a href="#">' + v + '</a></li>');
                                    }
                                });
                            });
                            $('#themify_builder_pre-designed-filter').show().find('a').click(function (e) {
                                e.preventDefault();
                                if (!$(this).hasClass('selected')) {
                                    if ($(this).hasClass('all')) {
                                        container.find('.layout_preview_list').css('display', 'block');
                                    } else {
                                        var cat = $(this).text();
                                        container.find('.layout_preview_list').css('display', 'none').filter('[data-category*="' + cat + '"]').css('display', 'block');
                                    }
                                    $(this).addClass('selected').parent().siblings().find('a').removeClass('selected');
                                    api.layoutLayoutsList();
                                }
                            });
                            $('#themify_builder_layout_search').on('keyup', function () {
                                var s = $(this).val();
                                if (!s) {
                                    container.find('.layout_preview_list').css('display', 'block');
                                } else {
                                    if (!$('#themify_builder_pre-designed-filter a.all.selected').length > 0) {
                                        $('#themify_builder_pre-designed-filter a.all').click();
                                    }
                                    container.find('.layout_preview_list').hide();
                                    container.find('.layout_title:contains(' + s + ')').each(function () {
                                        $(this).closest('.layout_preview_list').show();
                                    });
                                }
                                api.layoutLayoutsList();
                            });

                            // when switching between different tabs, redo the layout
                            $('#themify_builder_load_template_form .themify_builder_tabs').on('tabsactivate', function (e, ui) {
                                api.layoutLayoutsList();
                            });
                        }).fail(function (jqxhr, textStatus, error) {
                    ThemifyBuilderCommon.LiteLightbox.alert($('#themify_builder_load_layout_error').show().text());
                }).always(function () {
                    loader.fadeOut();
                });
            });
        },
        layoutLayoutsList: function () {
            $('#themify_builder_load_template_form .themify_builder_layout_lists').each(function () {
                $('.layout-column-break', this).removeClass('layout-column-break');
                var i = 0;
                $('.layout_preview_list:visible', this).each(function () {
                        if (i++ / 4 == 1 ) {
                                $(this).addClass('layout-column-break');
                        i = 1;
                    }
                }).filter(':eq(0)').addClass('layout-column-break');
            });
        },
        save_layout: function (e) {
            e.preventDefault();
            var options = {
                data: {
                    action: 'tfb_custom_layout_form',
                    postid: themifyBuilder.post_ID
                }
            },
            callback = function () {
                // plupload init
                api.Utils.builderPlupload('normal');
            };
            ThemifyBuilderCommon.Lightbox.open(options, callback);
        },
        // Duplicate actions
        duplicate: function (e) {
            e.preventDefault();

            var self = this,
                    reply = confirm(themifyBuilder.i18n.confirm_on_duplicate_page);
            if (reply) {
                api.Utils.saveBuilder(true).done(self.duplicatePageAjax);
            } else {
                this.duplicatePageAjax();
            }
        },
        duplicatePageAjax: function () {
            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data: {
                    action: 'tfb_duplicate_page',
                    tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                    tfb_post_id: themifyBuilder.post_ID,
                    tfb_is_admin: 'visual' === api.mode ? 0 : 1
                },
                beforeSend: function (xhr) {
                    ThemifyBuilderCommon.showLoader('show');
                },
                success: function (data) {
                    ThemifyBuilderCommon.showLoader('hide');
                    var new_url = data.new_url.replace(/\&amp;/g, '&');
                    window.location.href = new_url;
                }
            });
        },

		//Pre-designed Rows actions
		preDesigned_rows: false,
		load_row_templates: function (event,$parent) {
			event.preventDefault();
			if(!this.preDesigned_rows){
				var main = this;
				ThemifyBuilderCommon.showLoader('show');
				$.getJSON('https://themify.me/public-api/predesigned-rows/index.json')
						.done(function (data) {
							ThemifyBuilderCommon.showLoader('hide');
							main.init_row_template(data);
						}).fail(function (jqxhr, textStatus, error) {
							ThemifyBuilderCommon.showLoader('error');
							main.preDesigned_rows = false;
							$('.tb_predesigned_rows_list').html('<h3>Failed to load Pre-Designed Rows from server.</h3>');
				});
			}
		},
		init_row_template:function(data,$parent){
			this.preDesigned_rows = {'tmpl':data, 'cat': new Array()};
			var temp = "";
			for(var i=0; i < data.length ; i++){
				temp = data[i].category.split(',');
				if(Array.isArray(temp)){
					for(var j=0; j< temp.length; j++){
						if(this.preDesigned_rows.cat.indexOf(temp[j]) == -1){
							this.preDesigned_rows.cat.push(temp[j]);
						}
					}
				} else if(this.preDesigned_rows.cat.indexOf(temp) == -1){
					this.preDesigned_rows.cat.push(temp);
				}
				if(data[i].thumbnail == '' || _.isUndefined(data[i].thumbnail) || !data[i].thumbnail){
					this.preDesigned_rows.tmpl[i].thumbnail = 'https://placeholdit.imgix.net/~text?txtsize=24&txt='+(encodeURI(data[i].title))+'&w=181&h=77';
				}
			}
			this.preDesigned_rows.cat.sort();
			this.category_list();
			this.show_row_templates('all');
		},
		get_row_template:function(slug,callback){
			$.getJSON('https://themify.me/public-api/predesigned-rows/'+slug+'.txt')
						.done(function (data) {
							if(!_.isUndefined(callback))
								callback(data);
						}).fail(function (jqxhr, textStatus, error) {
							ThemifyBuilderCommon.showLoader('error');
							alert('Failed to fetch row template');
				});
		},
		put_row_template: function(elem){
			var id = elem.data('id'),
				row_tmpl = this.preDesigned_rows.tmpl[id];
			ThemifyBuilderCommon.showLoader('show');
			
			this.get_row_template(row_tmpl.slug,function(data){
				data = data[0];
				var rowView = api.Views.init_row(data, themifybuilderapp.mode),
					rowT = rowView.view.render().el;
				$('.predesigned_rows_temp_holder').replaceWith(rowT);
				if ('visual' === themifybuilderapp.mode) {
					themifybuilderapp.Mixins.Builder.refreshRows();
				}
				ThemifyBuilderCommon.showLoader('hide');
			});
			
		},
		show_row_templates: function(cat, $parent){
			if(_.isUndefined($parent)){
				$parent = '';
			} else {
				$parent += ' ';
			}
			$($parent + '.tb_module_panel_rows_wrap .tb_predesigned_rows_list').empty();
			var rows = this.preDesigned_rows.tmpl;
			if(_.isUndefined(cat) || cat.toLowerCase() == 'all' || !cat){
				for(var i=0; i< rows.length; i++){
					$($parent + '.tb_module_panel_rows_wrap .tb_predesigned_rows_list').append('<div class="predesigned_row search_able_item" data-type="predesigned-rows" data-id="'+i+'"><figure class="tb_predesigned_rows_list_image"><img src="'+(rows[i].thumbnail)+'"></figure><span class="tb_predesigned_rows_list_title search_able_title">'+(rows[i].title)+'</span></div>');
				}
			} else {
				for(var i=0; i< rows.length; i++){
					if(rows[i].category.split(',').indexOf(cat) != -1){
						$($parent + '.tb_module_panel_rows_wrap .tb_predesigned_rows_list').append('<div class="predesigned_row search_able_item" data-type="predesigned-rows" data-id="'+i+'"><figure class="tb_predesigned_rows_list_image"><img src="'+(rows[i].thumbnail)+'"></figure><span class="tb_predesigned_rows_list_title search_able_title">'+(rows[i].title)+'</span></div>');
					}
				}
			}
			if($parent){
				new SimpleBar($($parent + '.tb_module_panel_rows_wrap .tb_predesigned_rows_list')[0]);
			} else {
				$('.tb_module_panel_rows_wrap .tb_predesigned_rows_list').each(function( index, element ){
					new SimpleBar(element);
				});
			}

			// jQuery Drag
			var that = this, $body = $('body');
            $('.predesigned_row').draggable({
                appendTo: "body",
                helper: "clone",
                revert: 'invalid',
                connectToSortable: '#themify_builder_row_wrapper,.themify_builder_content',
                cursorAt: {
                    top: 10,
                    left: 40
                },
				start: function(event, ui){
					ui.helper.addClass('tb_module_dragging_helper').removeClass('predesigned_row');
					ui.helper.find('.tb_predesigned_rows_list_image').remove();
                    that._hide_module_panel();
                    if (!$body.hasClass('tb_predesigned_on_dragging'))
                        $body.addClass('tb_predesigned_on_dragging');
				},
                stop: function (event, ui) {
                    if ($body.hasClass('tb_predesigned_on_dragging'))
                        $body.removeClass('tb_predesigned_on_dragging');
                }
            });
		},
		category_list:function(){
			$('.tb_row_cat_filter_wrap .tb_row_cat_filter').empty();
			var cat = this.preDesigned_rows.cat;
			for(var i=0; i < cat.length ; i++){
				$('.tb_row_cat_filter_wrap .tb_row_cat_filter').append('<li><a href="#">'+cat[i]+'</a></li>');
			}
			$('.tb_row_cat_filter_wrap .tb_row_cat_filter').prepend('<li><a href="#">All</a></li>');
		},
		category_sort_row_tmpl:function(event, $parent){
			event.preventDefault();
			var elem = $(event.target);
			if(this.preDesigned_rows){
				this.show_row_templates(elem.text(), $parent);
				elem.closest('.tb_row_cat_filter_wrap').children('.tb_row_cat_filter_active').text(elem.text());
			}
		},
		// Revision actions
        load_revision: function (e) {
            e.preventDefault();
            e.stopPropagation();
            var options = {
                data: {
                    action: 'tfb_load_revision_lists',
                    postid: themifyBuilder.post_ID,
                    tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                }
            };
            ThemifyBuilderCommon.Lightbox.open(options, function () {
                $('.themify_builder_options_tab li:first-child').addClass('current');
            });
        },
        save_revision: function (e) {
            e.preventDefault();
            e.stopPropagation();
            ThemifyBuilderCommon._saveRevision();
        },
        save: function (e) {
            e.preventDefault();
            api.Utils.saveBuilder(true).fail(function () {
                alert(themifyBuilder.i18n.errorSaveBuilder);
            });
        },
        panel_close: function (e) {
            api.toggleFrontEdit(e);
        },
        search: function (e,$parent) {
            var s = $(e.currentTarget).val(),
				tab = this.current_tab($parent);
				console.log(tab);
			if (!s) {
				tab.find('.search_able_item').show();
			} else {
				tab.find('.search_able_item').hide();
				$($parent + ' .search_able_title:contains(' + s + ')').each(function () {
					$(this).closest('.search_able_item').show();
				});
			}
        },
        clear_search: function(){
			$('.tb_module_panel_search_text').val('');
			$('#tb_module_panel .search_able_item, .tb_plus_btn_popover .search_able_item').show();
		},
        is_locked: false,
        lock: function (e) {
            e.preventDefault();
            this._set_panel_lock(!this.is_locked);
            $(e.currentTarget).toggleClass('tb_locked');
            this.$('.tb_toolbar_add_modules').toggleClass('tb_disabled');
            this._hide_module_panel();

            if ('visual' === api.mode) {
                $('body').toggleClass('tb_module_panel_locked');
            }
        },
        _set_panel_lock: function (state) {
            this.is_locked = state;
            localStorage.setItem('tb_module_panel_locked', this.is_locked); // remember state
        },
		change_tab: function(event,$parent){
			event.preventDefault();
			var elm = $(event.target);
			var oldElm = this.current_tab($parent);
			if(oldElm){
				oldElm.hide();
			}
			$($parent +' ul.tb_module_types li').removeClass('active');
			elm.closest('li').addClass('active');
			$($parent + ' .'+elm.data('target')).show().trigger('change');
			this.clear_search();
		},
		current_tab: function($parent){
			var elm = $($parent + ' .tb_module_types li.active a');
			
			if(elm.length > 0){
				return $($parent + ' .'+elm.data('target'));
			}
			
			return false;
		},
		tab_loader_pic: function(tab,action){
			var $elm = $(tab);
			if($elm.length>0){
				if(action == "remove")
					$elm.find(".tb_tab_loader_pic").remove();
				else
					$elm.append('<span class="tb_tab_loader_pic">Spin</span>'); // Default 
			}
			return false
		},
        _setupModulePanelState: function () {
            var is_locked = localStorage.getItem('tb_module_panel_locked');

            if (!_.isNull(is_locked) && 'true' === is_locked) {
                this.$('#tb_module_panel').addClass('tb_module_panel_show');
                this.$('.tb_module_panel_lock').addClass('tb_locked');
                this.$('.tb_toolbar_add_modules').toggleClass('tb_disabled');
                $('body').addClass('tb_module_panel_locked');
                this.is_locked = true;
            }
        },
        toggle_rev_dropdown: function (e) {
            e.preventDefault();
            $(e.currentTarget).toggleClass('tb_toolbar_rev_hover')
            this.$('.tb_toolbar_save').focus();
        },
        breakpoint_switcher: function (e) {
            e.preventDefault();
            if(!themifyBuilder.is_premium){
                    return false;
            }
            var $this = $(e.currentTarget),
                    breakpoint = 'desktop',
                    prevBreakPoint = api.activeBreakPoint;
            if ($this.hasClass('breakpoint-tablet')) {
                breakpoint = 'tablet';
            } else if ($this.hasClass('breakpoint-tablet-landscape')) {
                breakpoint = 'tablet_landscape';
            } else if ($this.hasClass('breakpoint-mobile')) {
                breakpoint = 'mobile';
            }
            if (prevBreakPoint === breakpoint) {
                return false;
            }
            if ('visual' === api.mode && 'desktop' === breakpoint) {
                $('.themify_builder_grid_' + breakpoint + ' .themify_builder_column_direction li.selected a').trigger('click');
            }
            api.activeBreakPoint = breakpoint;
            $this.addClass('tb_selected').parent().siblings().find('a').removeClass('tb_selected');

            if ('visual' !== api.mode) {
                var suffix = breakpoint === 'tablet_landscape' ? 'tablet' : breakpoint;
                $('.themify_builder_row_panel').removeClass(prevBreakPoint + '-responsive-selected').addClass(breakpoint + '-responsive-selected')
                        .find('.grid_tabs a[href="#' + suffix + '"]').trigger('click');
                return; // on backend enough till here.
            }

            $('body').add(api.Frontend.responsiveFrame.$el.find('body'))
                    .removeClass('builder-active-breakpoint--desktop builder-active-breakpoint--tablet builder-active-breakpoint--tablet_landscape builder-active-breakpoint--mobile')
                    .addClass('builder-active-breakpoint--' + breakpoint)
                    .removeClass('tb_tablet tb_mobile tb_desktop').addClass('tb_' + breakpoint.replace('_landscape', '')); // needed for styling

            if ('desktop' === breakpoint) {
                $('.themify_builder_site_canvas').animate({width: $(window).width()}, 800, function () {
                    $('.themify_builder_workspace_container').removeClass('onshow');
                });
                document.body.style.height = ''; // reset the body height
                document.body.style.paddingBottom = ''; // reset padding bottom
                this.$('.tb_toolbar_add_modules').removeClass('tb_disabled');
            } else {
                if ('desktop' === prevBreakPoint) {
                    api.Frontend.responsiveFrame.sync();
                }

                if (breakpoint === 'mobile' || 'mobile' === prevBreakPoint || 'desktop' === prevBreakPoint) {
                    api.Frontend.responsiveFrame.$el.find('.themify_builder_grid_' + (breakpoint === 'tablet_landscape' ? 'tablet' : breakpoint) + ' .themify_builder_column_direction li.selected a').trigger('click');
                }

                api.Frontend.responsiveFrame.$el.find('.module_column').each( function( i, el ) {
                    if( el.style.width ) el.style.width = '';
                } );

                $('.themify_builder_workspace_container').addClass('onshow');
                $('.themify_builder_site_canvas').animate({
                    width: api.Utils.getBPWidth(breakpoint) - 1//sometimes the browsers doesn't set correct width(e.g 1024.44px instead of 1024px) and the media quireis don't
                }, 800, function () {
                    if (breakpoint !== 'mobile') {
                        api.Frontend.responsiveFrame.$el.find('.mobile-auto').removeClass('mobile-auto').addClass('tmp-mobile-auto');
                    }
                    else {
                        api.Frontend.responsiveFrame.$el.find('.tmp-mobile-auto').removeClass('tmp-mobile-auto').addClass('mobile-auto');
                    }

                    setTimeout(function () {
                        document.body.style.height = api.Frontend.responsiveFrame.contentWindow.document.body.scrollHeight + 'px'; // Set the same height as iframe content height
                    }, 500);
                    api.Frontend.responsiveFrame.contentWindow.scrollTo(0, $(window).scrollTop());
                });
                this._disable_module_panel();
            }

			// wait for animation to finish, then call a custom trigger
			setTimeout(function(){
				var body = api.Frontend.responsiveFrame.$el.find( 'body' );
				body.trigger( 'builderSwitchBreakpoint.themify', [ body ] );
			}, 1000);
        },
        _hide_module_panel: function () {
            if (this.is_locked)
                return;

            if (this.$('#tb_module_panel').hasClass('tb_module_panel_show')) {
                this.$('#tb_module_panel').removeClass('tb_module_panel_show');
            }
        },
        _show_module_panel: function () {
            if (this.is_locked)
                return;

            if (!this.$('#tb_module_panel').hasClass('tb_module_panel_show')) {
                this.$('#tb_module_panel').addClass('tb_module_panel_show');
            }
        },
        _disable_module_panel: function () {
            this._set_panel_lock(false);
            this.$('.tb_toolbar_add_modules').addClass('tb_disabled');
            this.$('.tb_module_panel_lock').removeClass('tb_locked');
            if ('visual' === api.mode) {
                $('body').removeClass('tb_module_panel_locked');
            }
            this._hide_module_panel();
        },
        reopen_module_panel: false,
        _hide_distraction_module_panel: function () {
            if (this.$('#tb_module_panel').hasClass('tb_module_panel_show')) {
                this.$('#tb_module_panel').removeClass('tb_module_panel_show');
                if (this.is_locked) {
                    $('body').removeClass('tb_module_panel_locked');
                }
                this.reopen_module_panel = true;
            } else {
                this.reopen_module_panel = false;
            }
        },
        _show_distraction_module_panel: function () {
            if (!this.$('#tb_module_panel').hasClass('tb_module_panel_show') && this.reopen_module_panel) {
                this.$('#tb_module_panel').addClass('tb_module_panel_show');
                if (this.is_locked) {
                    $('body').addClass('tb_module_panel_locked');
                }
                this.reopen_module_panel = false;
            }
        }
    });

    api.Views.bindEvents = function () {
        ThemifyBuilderCommon.setupLoader();
        ThemifyBuilderCommon.Lightbox.setup();
        ThemifyBuilderCommon.LiteLightbox.modal.on('attach', function () {
            this.$el.addClass('themify_builder_lite_lightbox_modal');
        });

        api.Utils.setupTooltips();
        api.Utils.mediaUploader();
        api.Utils.openGallery();
        var setCompactModeIfNeeded = function(col){
            if( $(col).hasClass( 'themify_builder_module_front' ) ) {
                $(col).find('.themify_builder_dropdown_front:first').toggleClass('compact-mode', $(col).width() < 370);
            } else {
                $(col).find('.themify_builder_column_action:first').toggleClass('compact-mode', $(col).width() < 184);
            }
        };
        $('body').on('mouseenter', '.themify_builder_col, .themify_builder_module_front', function(ev) {
            ev.stopPropagation();
            setCompactModeIfNeeded(this);
        });
        setTimeout(function(){
            $('.themify_builder_col, .themify_builder_module_front').each(function(){
                setCompactModeIfNeeded(this); 
            });
        },500);
    };

    api.Utils = {
        clearClass: 'col6-1 col5-1 col4-1 col4-2 col4-3 col3-1 col3-2 col2-1 col-full',
        gridClass: ['col-full', 'col4-1', 'col4-2', 'col4-3', 'col3-1', 'col3-2', 'col6-1', 'col5-1'],
        tfb_hidden_editor_object: null,
        _addNewColumn: function (params, $context) {
            var columnView = api.Views.init_column({grid_class: params.newclass, component_name: params.component}, params.type);
            $context.append(columnView.view.render().$el);
        },
        removeGridClass: function (base, cls) {
            cls = cls.split(',');
            var classes = base.prop('class').replace(/ +(?= )/g, '').split(' ');
            for (var i = 0; i < classes.length; i++) {
                for (var j = 0; j < cls.length; j++) {
                    if (classes[i].indexOf(cls[j]) !== -1 && classes[i].indexOf('col-direction') === -1) {
                        base.removeClass(classes[i]);
                    }
                }
            }
        },
        removeEmptyFields: function (data) {
            return data;
            //for (var i in data) {
                // if (!data[i] || data[i]==='solid' || data[i]==='px' || data[i]==='default' || data[i]==='|' || (data[i]==='show' && i.indexOf('visibility_')!==-1)) {
                // delete data[i];
                // }
           // }

            if ((typeof data['background_image-type'] !== 'undefined' && data['background_image-type'] !== 'gradient') || (typeof data['background_type'] !== 'undefined' && data['background_type'] !== 'gradient')) {
                var gradient = ['background_image-type_gradient',
                    'background_gradient-gradient-type',
                    'background_image-gradient-angle',
                    'background_image-gradient-type',
                    'background_image-gradient',
                    'background_gradient-gradient-angle',
                    'background_gradient-gradient',
                    'background_gradient-css',
                    'background_image-css'
                ];
                for (var i = 0; i < gradient.length; ++i) {
                    if (typeof data[gradient[i]] !== 'undefined') {
                        delete data[gradient[i]];
                    }
                }
            }
            if (typeof data['background_type'] !== 'undefined') {
                var covers = ['', '_hover'];
                for (var j = 0; j < covers.length; ++j) {

                    if ((typeof data['cover_color' + covers[j] + '-type'] && data['cover_color' + covers[j] + '-type'] !== 'gradient')) {
                        var gradient = [
                            'cover_gradient' + covers[j] + '-gradient-type',
                            'cover_gradient' + covers[j] + '-gradient-angle',
                            'cover_gradient' + covers[j] + '-gradient',
                            'cover_gradient' + covers[j] + '-css'
                        ];
                        for (var i = 0; i < gradient.length; ++i) {
                            if (typeof data[gradient[i]] !== 'undefined') {
                                delete data[gradient[i]];
                            }
                        }
                    }
                }
            }
            return data;
        },
        hideResponsiveCols: function (grid_items, col) {
            if (col) {
                col = parseInt(col);
                var is_even = (col % 2) === 0;
                grid_items.hide().each(function () {
                    var c = parseInt($(this).children('a').data('col'));
                    if (c === col || c === 1 || (is_even && ((col % c) === 0))) {
                        $(this).show();
                    }
                    else if (!is_even && col > c && ((col === 5 && (c === 2 || c === 3)) || (col === 3 && c === 2))) {
                        $(this).show();
                    }
                });
                var dir = grid_items.first().closest('.themify_builder_grid_list_wrapper').find('.themify_builder_column_direction');
                if (col === 1) {
                    dir.hide();
                }
                else {
                    dir.show();
                }
            }
        },
        filterClass: function (str) {
            var grid = this.gridClass.concat(['first', 'last']),
                    n = str.split(' '),
                    new_arr = [];

            for (var i = 0; i < n.length; i++) {
                if ($.inArray(n[i], grid) > -1) {
                    new_arr.push(n[i]);
                }
            }

            return new_arr.join(' ');
        },
        _getRowSettings: function ($base, index) {
            var self = this,
                    option_data = {},
                    cols = {},
                    row_content = $base.find('.themify_builder_row_content');

            // cols
            row_content.children('.themify_builder_col').each(function (c) {
                var grid_class = self.filterClass($(this).attr('class')),
                        modules = {};
                // mods
                $(this).find('.themify_module_holder').first().children().each(function (m) {
                    if ($(this).hasClass('active_module')) {
                        var mod_cid = $(this).data('cid'),
                                mod_model = api.Models.Registry.lookup(mod_cid);
                        modules[m] = {'mod_name': mod_model.get('mod_name'), 'mod_settings': mod_model.get('mod_settings')};
                    }

                    // Sub Rows
                    if ($(this).hasClass('themify_builder_sub_row')) {
                        modules[m] = self._getSubRowSettings($(this), m);
                    }
                });

                cols[c] = {
                    'column_order': c,
                    'grid_class': grid_class,
                    'modules': modules
                };
                if ($(this).prop('style').width) {
                    cols[c]['grid_width'] = parseFloat($(this).prop('style').width);
                }
                // get column styling
                if ($(this).children('.column-data-styling').length > 0) {
                    var $data_styling = $.parseJSON($(this).children('.column-data-styling').attr('data-styling'));
                    if ('object' === typeof $data_styling)
                        cols[ c ].styling = $data_styling;
                }
            });

            option_data = {
                row_order: index,
                cols: cols
            };
            if ($base.data('gutter') !== 'gutter-default') {
                option_data['gutter'] = $base.data('gutter');
            }
            if ($base.data('column-alignment') !== 'col_align_top') {
                option_data['column_alignment'] = $base.data('column-alignment');
            }
            if ($base.data('desktop_dir') !== 'ltr') {
                option_data['desktop_dir'] = $base.data('desktop_dir');
            }
            if ($base.data('tablet_dir') !== 'ltr') {
                option_data['tablet_dir'] = $base.data('tablet_dir');
            }
            if ($base.data('mobile_dir') !== 'ltr') {
                option_data['mobile_dir'] = $base.data('mobile_dir');
            }
            if ($.trim($base.data('tablet')) !== 'tablet-auto') {
                option_data['col_tablet'] = $.trim($base.data('tablet'));
            }
            if ($.trim($base.data('mobile')) !== 'mobile-auto') {
                option_data['col_mobile'] = $.trim($base.data('mobile'));
            }
            // get row styling
            if ($base.find('.row-data-styling').length > 0) {
                var $data_styling = $.parseJSON($base.find('.row-data-styling').attr('data-styling'));
                if ('object' === typeof $data_styling)
                    option_data.styling = $data_styling;
            }
            return option_data;
        },
        _getSubRowSettings: function ($subRow, subRowOrder) {
            var self = this,
                    option_data = {},
                    sub_cols = {};
            $subRow.find('.themify_builder_col').each(function (sub_col) {
                var sub_grid_class = self.filterClass($(this).attr('class')),
                        sub_modules = {};

                $(this).find('.active_module').each(function (sub_m) {
                    var sub_mod_cid = $(this).data('cid'),
                            sub_mod_model = api.Models.Registry.lookup(sub_mod_cid);
                    sub_modules[sub_m] = {'mod_name': sub_mod_model.get('mod_name'), 'mod_settings': sub_mod_model.get('mod_settings')};
                });

                sub_cols[ sub_col ] = {
                    column_order: sub_col,
                    grid_class: sub_grid_class,
                    modules: sub_modules
                };
                if ($(this).prop('style').width) {
                    sub_cols[sub_col]['grid_width'] = parseFloat($(this).prop('style').width);
                }
                // get sub-column styling
                if ($(this).children('.column-data-styling').length > 0) {
                    var $data_styling = $.parseJSON($(this).children('.column-data-styling').attr('data-styling'));
                    if ('object' === typeof $data_styling)
                        sub_cols[ sub_col ].styling = $data_styling;
                }
            });
            option_data = {
                row_order: subRowOrder,
                cols: sub_cols
            };
            if ($subRow.data('gutter') !== 'gutter-default') {
                option_data['gutter'] = $subRow.data('gutter');
            }
            if ($subRow.data('column-alignment') !== 'col_align_top') {
                option_data['column_alignment'] = $subRow.data('column-alignment');
            }
            if ($subRow.data('desktop_dir') !== 'ltr') {
                option_data['desktop_dir'] = $subRow.data('desktop_dir');
            }
            if ($subRow.data('tablet_dir') !== 'ltr') {
                option_data['tablet_dir'] = $subRow.data('tablet_dir');
            }
            if ($subRow.data('mobile_dir') !== 'ltr') {
                option_data['mobile_dir'] = $subRow.data('mobile_dir');
            }
            if ($.trim($subRow.data('tablet')) !== 'tablet-auto') {
                option_data['col_tablet'] = $.trim($subRow.data('tablet'));
            }
            if ($.trim($subRow.data('mobile')) !== 'mobile-auto') {
                option_data['col_mobile'] = $.trim($subRow.data('mobile'));
            }
            // get sub-row styling
            if ($subRow.find('.subrow-data-styling').length > 0) {
                var $data_styling = $.parseJSON($subRow.find('.subrow-data-styling').attr('data-styling'));
                if ('object' === typeof $data_styling)
                    option_data.styling = $data_styling;
            }
            return option_data;
        },
        builderPlupload: function (action_text) {
            var class_new = action_text === 'new_elemn' ? '.plupload-clone' : '',
                    $builderPlupoadUpload = $(".themify-builder-plupload-upload-uic" + class_new);

            if ($builderPlupoadUpload.length > 0) {
                var pconfig = false;
                $builderPlupoadUpload.each(function () {
                    var $this = $(this),
                            id1 = $this.attr("id"),
                            imgId = id1.replace("themify-builder-plupload-upload-ui", "");

                    pconfig = JSON.parse(JSON.stringify(themify_builder_plupload_init));
                    pconfig["browse_button"] = imgId + pconfig["browse_button"];
                    pconfig["container"] = imgId + pconfig["container"];
                    pconfig["drop_element"] = imgId + pconfig["drop_element"];
                    pconfig["file_data_name"] = imgId + pconfig["file_data_name"];
                    pconfig["multipart_params"]["imgid"] = imgId;
                    //pconfig["multipart_params"]["_ajax_nonce"] = $this.find(".ajaxnonceplu").attr("id").replace("ajaxnonceplu", "");
                    pconfig["multipart_params"]["_ajax_nonce"] = themifyBuilder.tfb_load_nonce;
                    pconfig["multipart_params"]['topost'] = themifyBuilder.post_ID;
                    if ($this.data('extensions')) {
                        pconfig['filters'][0]['extensions'] = $this.data('extensions');
                    }
                    var uploader = new plupload.Uploader(pconfig);

                    uploader.bind('Init', function (up) {
                    });
                    uploader.init();

                    // a file was added in the queue
                    uploader.bind('FilesAdded', function (up, files) {
                        up.refresh();
                        up.start();
                        $('#themify_builder_alert').addClass('busy').show();
                    });

                    uploader.bind('Error', function (up, error) {
                        var $promptError = $('.prompt-box .show-error');
                        $('.prompt-box .show-login').hide();
                        $promptError.show();

                        if ($promptError.length > 0) {
                            $promptError.html('<p class="prompt-error">' + error.message + '</p>');
                        }
                        $(".overlay, .prompt-box").fadeIn(500);
                    });

                    // a file was uploaded
                    uploader.bind('FileUploaded', function (up, file, response) {
                        var json = JSON.parse(response['response']), status;
                        status = '200' == response['status'] && !json.error ? 'done' : 'error';

                        $("#themify_builder_alert").removeClass("busy").addClass(status).delay(800).fadeOut(800, function () {
                            $(this).removeClass(status);
                        });

                        if (json.error) {
                            alert(json.error);
                            return;
                        }

                        var response_url = json.large_url ? json.large_url : json.url,
                                response_id = json.id,
                                thumb_url = json.thumb;

                        $this.closest('.themify_builder_input').find('.themify-builder-uploader-input').val(response_url).trigger('change')
                                .parent().find('.img-placeholder').empty()
                                .html($('<img/>', {src: thumb_url, width: 50, height: 50}))
                                .parent().show();
                        // Attach image id to the input
                        $this.closest('.themify_builder_input').find('.themify-builder-uploader-input-attach-id').val(response_id);
                    });

                    $this.removeClass('plupload-clone');
                });
            }
        },
        columnDrag: function ($container, $remove, old_gutter, new_gutter) {
            if ($remove) {
                if ($container) {
                    $container.children('.themify_builder_col').css('width', '');
                }
                else {
                    $('.themify_builder_col').css('width', '');
                }
            }
            var self = this,
                    $cdrags = $container ? $container.children('.themify_builder_col').find('.themify_grid_drag') : $('.themify_grid_drag'),
                    $min = 5,
                    post_id = $cdrags.first().closest('[data-postid]'),
                    _cols = {
                        default: {'col6-1': 14, 'col5-1': 17.44, 'col4-1': 22.6, 'col4-2': 48.4, 'col2-1': 48.4, 'col4-3': 74.2, 'col3-1': 31.2, 'col3-2': 65.6},
                        narrow: {'col6-1': 15.33, 'col5-1': 18.72, 'col4-1': 23.8, 'col4-2': 49.2, 'col2-1': 49.2, 'col4-3': 74.539, 'col3-1': 32.266, 'col3-2': 66.05},
                        none: {'col6-1': 16.666, 'col5-1': 20, 'col4-1': 25, 'col4-2': 50, 'col2-1': 50, 'col4-3': 75, 'col3-1': 33.333, 'col3-2': 66.666}
                    },
            _margin = {
                default: 3.2,
                narrow: 1.6,
                none: 0
            };
            if (old_gutter && new_gutter) {
                var cols = $container.children('.themify_builder_col'),
                        new_margin = new_gutter === 'gutter-narrow' ? _margin.narrow : (new_gutter === 'gutter-none' ? _margin.none : _margin.default),
                        old_margin = old_gutter === 'gutter-narrow' ? _margin.narrow : (old_gutter === 'gutter-none' ? _margin.none : _margin.default),
                        margin = old_margin - new_margin;
                margin = parseFloat((margin * (cols.length - 1)) / cols.length);
                cols.each(function (i) {
                    if ($(this).prop('style').width) {
                        var w = parseFloat($(this).prop('style').width) + margin;
                        $(this).css('width', w + '%');
                    }
                });
                return;
            }
            $cdrags.each(function () {

                var $el = $(this),
                        $row = $el.closest('.themify_builder_sub_row_content'),
                        $row = $row.length === 0 ? $el.closest('.themify_builder_row_content') : $row,
                        $columns = $row.children('.themify_builder_col'),
                        $current = $el.closest('.themify_builder_col'),
                        $el_width = 0,
                        dir = $el.hasClass('themify_drag_right') ? 'w' : 'e',
                        cell = false,
                        cell_w = 0,
                        $helperClass = dir === 'w' ? 'themify_grid_drag_placeholder_right' : 'themify_grid_drag_placeholder_left',
                        row_w,
                        dir_rtl,
                        origpos;
                if ($el.hasClass('themify_drag_right')) {
                    dir = 'w';
                }
                else {
                    dir = 'e';
                }

                $el.draggable({
                    axis: "x",
                    cursor: 'col-resize',
                    distance: 0,
                    containment: '.themify_builder_row_content',
                    helper: function (event) {
                        return $('<div class="ui-widget-header themify_grid_drag_placeholder ' + $helperClass + '"></div><div class="ui-widget-header themify_grid_drag_placeholder"></div>');
                    },
                    start: function (event, ui) {
                        api.vent.trigger('dom:observer:start', post_id);
                        dir_rtl = $row.closest('[data-cid]').data(api.activeBreakPoint + '_dir') === 'rtl';
                        if (dir === 'w') {
                            cell = dir_rtl ? $current.prev('.themify_builder_col') : $current.next('.themify_builder_col');
                            $el_width = $el.outerWidth();
                        }
                        else {
                            cell = dir_rtl ? $current.next('.themify_builder_col') : $current.prev('.themify_builder_col');
                            $el_width = $current.outerWidth();
                        }
                        cell_w = cell.outerWidth() - 2;
                        origpos = ui.position.left;
                        row_w = $row.outerWidth();
                    },
                    stop: function (event, ui) {
                        $('.themify_grid_drag_placeholder').remove();
                        api.vent.trigger('dom:observer:end', post_id);
                        var percent = Math.ceil(100 * ($current.outerWidth() / row_w));
                        $current.css('width', percent + '%');
                        var cols = _cols.default,
                                margin = _margin.default;
                        if ($row.hasClass('gutter-narrow')) {
                            cols = _cols.narrow;
                            margin = _margin.narrow;
                        }
                        else if ($row.hasClass('gutter-none')) {
                            cols = _cols.none;
                            margin = _margin.none;
                        }
                        var cellW = margin * ($columns.length - 1);
                        $columns.each(function (i) {
                            if (i !== cell.index()) {
                                var w;
                                if ($(this).prop('style').width) {
                                    w = parseFloat($(this).prop('style').width);
                                }
                                else {
                                    var col = $.trim(self.filterClass($(this).attr('class')).replace('first', '').replace('last', ''));
                                    w = cols[col];
                                }
                                cellW += w;
                            }
                        });
                        cell.css('width', (100 - cellW) + '%');

                    },
                    drag: function (event, ui) {

                        if (cell && cell.length > 0) {
                            var px = $el_width + (dir === 'e' ? -(ui.position.left) : ui.position.left),
                                    $width = parseFloat((100 * px) / row_w);
                            if ($width >= $min && $width < 100) {
                                var $max = cell_w + origpos + (dir === 'w' ? -(ui.position.left) : ui.position.left),
                                        $max_percent = parseFloat((100 * $max) / row_w);

                                if ($max_percent > $min && $max_percent < 100) {
                                    cell.css('width', $max + 'px');
                                    $current.css('width', px + 'px').children('.' + $helperClass).html($width.toFixed(2) + '%');
                                    $current.children('.themify_grid_drag_placeholder').last().html($max_percent.toFixed(2) + '%');
                                }
                            }
                        }

                    }

                });
            });
        },
        initNewEditor: function (editor_id) {
            if (typeof tinyMCEPreInit.mceInit[editor_id] !== "undefined") {
                return this.initMCEv4(editor_id, tinyMCEPreInit.mceInit[editor_id]);
            }
            var tfb_new_editor_object = this.tfb_hidden_editor_object;

            tfb_new_editor_object['elements'] = editor_id;
            tfb_new_editor_object['selector'] = '#' + editor_id;
            tfb_new_editor_object['wp_autoresize_on'] = false;
            tinyMCEPreInit.mceInit[editor_id] = tfb_new_editor_object;

            // v4 compatibility
            return this.initMCEv4(editor_id, tinyMCEPreInit.mceInit[editor_id]);
        },
        initMCEv4: function (editor_id, $settings) {
            // v4 compatibility
            if (parseInt(tinyMCE.majorVersion) > 3) {
                // Creates a new editor instance
                var ed = new tinyMCE.Editor(editor_id, $settings, tinyMCE.EditorManager);
                ed.render();
                return ed;
            }
        },
        initQuickTags: function (editor_id) {
            // add quicktags
            if (typeof (QTags) === 'function') {
                quicktags({id: editor_id});
                QTags._buttonsInit();
            }
        },
        setColorPicker: function (context) {
            $('.builderColorSelectInput', context).each(function () {
                if ($(this).data('control-type'))
                    return true;
                api.Views.init_control('color', {el: $(this)});
            });
        },
        _getColClass: function (classes) {
            var matches = this.clearClass.split(' '),
                    spanClass = null;

            for (var i = 0; i < classes.length; i++) {
                if ($.inArray(classes[i], matches) > -1) {
                    spanClass = classes[i].replace('col', '');
                }
            }
            return spanClass;
        },
        saveBuilder: function (loader, callback, saveto) {
            saveto = saveto || 'main';
            var ids = _.map(api.Instances.Builder, function (view, key) {
                var temp_id = view.$el.data('postid') || null,
                        temp_data = view.toJSON() || null;
                return {
                    id: temp_id,
                    data: temp_data
                };
            });
            console.log(ids, 'ids');

            return $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                data: {
                    action: 'tfb_save_data',
                    tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                    ids: JSON.stringify(ids),
                    tfb_saveto: saveto
                },
                cache: false,
                beforeSend: function (xhr) {
                    if (loader) {
                        ThemifyBuilderCommon.showLoader('show');
                    }
                },
                success: function (data) {
                    if (loader) {
                        ThemifyBuilderCommon.showLoader('hide');
                    }

                    // load callback
                    if ($.isFunction(callback)) {
                        callback.call(this, data);
                    }
                    api.editing = false;
                    $('body').trigger('themify_builder_save_data');

                },
                error: function () {
                    if (loader) {
                        ThemifyBuilderCommon.showLoader('error');
                    }
                }
            });
        },
        loadContentJs: function (el) {
            if (typeof ThemifyBuilderModuleJs !== 'undefined') {
                ThemifyBuilderModuleJs.loadOnAjax(el); // load module js ajax
            }
            // hook
            $('body').trigger('builder_load_on_ajax');
        },
        setupTooltips: function () {
            var setupBottomTooltips = function () {
                $('body').on('mouseover mouseout', '[rel^="themify-tooltip-"]', function (e) {
                    if (e.type === 'mouseover') {
                        // append custom tooltip
                        var $title = $(this).data('title') ? $(this).data('title') : $(this).prop('title');
                        $(this).append('<span class="themify_tooltip">' + $title + '</span>');
                    }
                    else {
                        // remove custom tooltip
                        $(this).children('.themify_tooltip').remove();
                    }
                });
            };
            setupBottomTooltips();
            ThemifyBuilderCommon.setUpTooltip();
        },
        mediaUploader: function () {

            // Uploading files
            var $body = $('body'); // Set this

            // Field Uploader
            $body.on('click', '.themify-builder-media-uploader', function (e) {
                var $el = $(this),
                        $builderInput = $el.closest('.themify_builder_input'),
                        isRowBgImage = $builderInput.children('#background_image').length === 1,
                        isRowBgVideo = $builderInput.children('#background_video').length === 1;

                var file_frame = wp.media.frames.file_frame = wp.media({
                    title: $el.data('uploader-title'),
                    library: {
                        type: $el.data('library-type') ? $el.data('library-type') : 'image'
                    },
                    button: {
                        text: $el.data('uploader-button-text')
                    },
                    multiple: false // Set to true to allow multiple files to be selected
                });

                // When an image is selected, run a callback.
                file_frame.on('select', function () {
                    // We set multiple to false so only get one image from the uploader
                    var attachment = file_frame.state().get('selection').first().toJSON();

                    // Do something with attachment.id and/or attachment.url here
                    $el.closest('.themify_builder_input').find('.themify-builder-uploader-input').val(attachment.url).trigger('change')
                            .parent().find('.img-placeholder').empty()
                            .html($('<img/>', {
                                src: attachment.url,
                                width: 50,
                                height: 50
                            }))
                            .parent().show();

                    // Attached id to input
                    $el.closest('.themify_builder_input').find('.themify-builder-uploader-input-attach-id').val(attachment.id);
                });

                // Hide ATTACHMENT DISPLAY SETTINGS
                if (isRowBgImage || isRowBgVideo) {
                    if ($('#hide_attachment_display_settings').length === 0) {
                        $('body').append('<style id="hide_attachment_display_settings">.media-modal .attachment-display-settings { display:none }</style>');
                    }

                    file_frame.on('close', function (selection) {
                        $('#hide_attachment_display_settings').remove();
                    });
                }

                // Finally, open the modal
                file_frame.open();
                e.preventDefault();
            });

            // delete button
            $body.on('click', '.themify-builder-delete-thumb', function (e) {
                $(this).prev().empty().parent().hide();
                $(this).closest('.themify_builder_input').find('.themify-builder-uploader-input').val('').trigger('change');
                e.preventDefault();
            });

            // Media Buttons
            $body.on('click', '.insert-media', function (e) {
                window.wpActiveEditor = $(this).data('editor');
            });
        },
        openGallery: function () {

            var clone = wp.media.gallery.shortcode,
                    $self = this,
                    file_frame;

            $('body').on('click', '.tf-gallery-btn', function (e) {
                var shortcode_val = $(this).closest('.themify_builder_input').find('.tf-shortcode-input');

                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    frame: 'post',
                    state: 'gallery-edit',
                    title: wp.media.view.l10n.editGalleryTitle,
                    editing: true,
                    multiple: true,
                    selection: false
                });

                wp.media.gallery.shortcode = function (attachments) {
                    var props = attachments.props.toJSON(),
                            attrs = _.pick(props, 'orderby', 'order');

                    if (attachments.gallery)
                        _.extend(attrs, attachments.gallery.toJSON());

                    attrs.ids = attachments.pluck('id');

                    // Copy the `uploadedTo` post ID.
                    if (props.uploadedTo)
                        attrs.id = props.uploadedTo;

                    // Check if the gallery is randomly ordered.
                    if (attrs._orderbyRandom)
                        attrs.orderby = 'rand';
                    delete attrs._orderbyRandom;

                    // If the `ids` attribute is set and `orderby` attribute
                    // is the default value, clear it for cleaner output.
                    if (attrs.ids && 'post__in' === attrs.orderby)
                        delete attrs.orderby;

                    // Remove default attributes from the shortcode.
                    _.each(wp.media.gallery.defaults, function (value, key) {
                        if (value === attrs[key])
                            delete attrs[key];
                    });

                    var shortcode = new wp.shortcode({
                        tag: 'gallery',
                        attrs: attrs,
                        type: 'single'
                    });

                    shortcode_val.val(shortcode.string()).trigger('change');

                    wp.media.gallery.shortcode = clone;
                    return shortcode;
                };

                // Hide GALLERY SETTINGS
                if ($('#hide_gallery_settings').length === 0) {
                    $('body').append('<style id="hide_gallery_settings">.media-modal .gallery-settings { display:none }</style>');
                }

                file_frame.on('close', function (selection) {
                    $('#hide_gallery_settings').remove();
                });

                file_frame.on('update', function (selection) {
                    var shortcode = wp.media.gallery.shortcode(selection).string().slice(1, -1);
                    shortcode_val.val('[' + shortcode + ']');
                    $self.setShortcodePreview(selection.models, shortcode_val);
                });

                if ($.trim(shortcode_val.val()).length > 0) {
                    file_frame = wp.media.gallery.edit($.trim(shortcode_val.val()));

                    file_frame.on('close', function (selection) {
                        $('#hide_gallery_settings').remove();
                    });

                    file_frame.state('gallery-edit').on('update', function (selection) {
                        var shortcode = wp.media.gallery.shortcode(selection).string().slice(1, -1);
                        shortcode_val.val('[' + shortcode + ']');
                        $self.setShortcodePreview(selection.models, shortcode_val);
                    });
                } else {
                    file_frame.open();
                    $('.media-menu').find('.media-menu-item').last().trigger('click');
                }
                e.preventDefault();
            });

        },
        setShortcodePreview: function ($images, $input) {
            var $preview = $input.next('.themify_builder_shortcode_preview'),
                    $html = '';
            if ($preview.length === 0) {
                $input.after('<div class="themify_builder_shortcode_preview"></div>');
                $preview = $input.next('.themify_builder_shortcode_preview');
            }
            for (var $i in $images) {
                var attachment = $images[$i].attributes,
                        $url = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                $html += '<img src="' + $url + '" width="50" height="50" />';
            }
            $preview.html($html);
        },
        createGradientPicker: function ($input, value) {
            if (typeof $.fn.ThemifyGradient === 'undefined') {
                return;
            }
            var $field = $input.closest('.themify-gradient-field'),
                    instance = null, // the ThemifyGradient object instance
                    isTrigger = false,
                    args = {
                        onChange: function (stringGradient, cssGradient, asArray) {
                            $input.val(stringGradient);
                            // $field.find('.themify-gradient-css').val(cssGradient);
                            if (isTrigger && 'visual' === api.mode) {
                                var $is_cover = $input.prop('name') === 'cover_gradient-gradient',
                                        rgbaString = api.liveStylingInstance.bindBackgroundGradient($input.data('id'), cssGradient, $is_cover);
                                if ($is_cover && rgbaString) {
                                    api.liveStylingInstance.addOrRemoveComponentOverlay(rgbaString, true);
                                }
                            }
                        }
                    };
            args.gradient = value ? value : ($input.attr('data-default-gradient') ? $input.attr('data-default-gradient') : []);
            $input.prev().ThemifyGradient(args);
            instance = $input.prev().data('ThemifyGradient');
            $field.find('.themify-clear-gradient').on('click', function (e) {
                e.preventDefault();
                instance.settings.gradient = '0% rgba(255,255,255, 1)|100% rgba(255,255,255,1)';
                instance.update();
                // $input.add($field.find('.themify-gradient-css')).val('').trigger('change');
                $input.val('').trigger('change');
                if ($input.prop('name') === 'cover_gradient-gradient' && 'visual' === api.mode) {
                    api.liveStylingInstance.addOrRemoveComponentOverlay('');
                }
            });

            // $( 'body' ).on( 'themify_builder_lightbox_resize', function(){
            // instance.settings.width = $field.width();
            // instance.settings.gradient = $input.val();
            // instance.update();
            // } );
            // angle input
            var $angleInput = $field.find('.themify-gradient-angle');

            // Linear or Radial select field
            $field.find('.themify-gradient-type').on('change', function () {
                instance.setType($(this).val());
                var $angelparent = $angleInput.closest('.gradient-angle-knob'),
                        $radial_circle = $field.find('.themify-radial-circle');
                if ($(this).val() === 'radial') {
                    $angelparent.hide();
                    $angelparent.next('span').hide();
                    $radial_circle.show();
                }
                else {
                    $angelparent.show();
                    $angelparent.next('span').show();
                    $radial_circle.hide();
                }
            })
                    .trigger('change'); // required: the option's value is set before the event handler is registered, trigger change manually to patch this

            $field.find('.themify-radial-circle').on('change', function () {
                instance.setRadialCircle($(this).val() == 'circle');
            }).trigger('change');

            $angleInput.on('change', function () {
                var $val = parseInt($(this).val());
                if (!$val) {
                    $val = 0;
                }
                instance.setAngle($val);
            }).knob({
                change: function (v) {
                    instance.setAngle(Math.round(v));
                }
            });
            $angleInput.trigger('change'); // required

            // angle input popup style
            $angleInput.removeAttr('style')
                    .focus(function () {
                        $(this).parent().find('canvas').show();
                    })
                    .parent().addClass('gradient-angle-knob')
                    .hover(function () {
                        $(this).addClass('gradient-angle-hover-state');
                    }, function () {
                        $(this).removeClass('gradient-angle-hover-state');
                    })
                    .find('canvas')
                    .insertAfter($angleInput);
            $(document).bind('click', function () {
                if (!$angleInput.parent().is('.gradient-angle-hover-state')) {
                    $angleInput.parent().find('canvas').hide('fast');
                }
            });
            //for image_and_gradient
            setTimeout(function () {
                isTrigger = true;
                $field.closest('.tf-image-gradient-field').find('.tf-option-checkbox-js:checked').trigger('change');
            }, 900);
        },
        checkUnload: function () {
            /* unload event */
            if ($('body').hasClass('themify_builder_active')) {
                window.onbeforeunload = function () {
                    return ThemifyBuilderCommon.undoManager.instance.hasUndo() && api.editing ? themifyBuilder.i18n.confirm_on_unload : null;
                };
            }
        },
        getRepeaterValues: function ($repeater) {
            var row_items = [];
            $repeater.find('.tb_repeatable_field').each(function () {
                var temp_rows = {};
                $(this).find('.tfb_lb_option_child').each(function () {
                    var option_value_child,
                            this_option_id_child = $(this).data('input-id');
                    if (!this_option_id_child) {
                        this_option_id_child = $(this).attr('id');
                    }

                    if ($(this).hasClass('tf-radio-choice')) {
                        option_value_child = ($(this).find(':checked').length > 0) ? $(this).find(':checked').val() : '';
                    } else if ($(this).hasClass('themify-layout-icon')) {
                        if (!this_option_id_child) {
                            this_option_id_child = $(this).attr('id');
                        }
                        option_value_child = $(this).find('.selected').length > 0 ? $(this).find('.selected').attr('id') : $(this).children().first().attr('id');
                    }
                    else if ($(this).hasClass('themify-checkbox')) {
                        option_value_child = [];
                        $(this).find(':checked').each(function (i) {
                            option_value_child[i] = $(this).val();
                        });
                    }
                    else if ($(this).hasClass('tfb_lb_wp_editor')) {
                        var text_id = $(this).attr('id');
                        this_option_id_child = $(this).attr('name');
                        if (typeof tinyMCE !== 'undefined' && !_.isNull(tinyMCE.get(text_id))) {
                            option_value_child = tinyMCE.get(text_id).hidden === false ? tinyMCE.get(text_id).getContent() : switchEditors.wpautop(tinymce.DOM.get(text_id).value);
                        } else {
                            option_value_child = $(this).val();
                        }
                    }
                    else {
                        option_value_child = $(this).val();
                    }

                    if (option_value_child) {
                        temp_rows[this_option_id_child] = option_value_child;
                    }
                });
                row_items.push(temp_rows);
            });
            return row_items;
        },
        toRGBA: function (color) {
            var colorArr = color.split('_'),
                    patt = /^([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})$/;
            if (typeof colorArr[0] !== 'undefined') {
                var matches = patt.exec(colorArr[0]),
                        opacity = typeof colorArr[1] !== 'undefined' ? colorArr[1] : 1;
                return matches ? "rgba(" + parseInt(matches[1], 16) + ", " + parseInt(matches[2], 16) + ", " + parseInt(matches[3], 16) + ", " + opacity + ")" : '';
            }
        },
        // get breakpoint width
        getBPWidth: function (device) {
            var breakpoints = _.isArray(themifyBuilder.breakpoints[ device ]) ? themifyBuilder.breakpoints[ device ] : themifyBuilder.breakpoints[ device ].toString().split('-');
            return breakpoints[ breakpoints.length - 1 ];
        },
        shortcodeToHTML: function (content) {
            var tags = [],
                    index = 1;

            _.each(themifyBuilder.available_shortcodes.split('|'), function (sc, i) {
                content = wp.shortcode.replace(sc, content, function (shortcode) {
                    tags.push({key: index, sc_render: shortcode.string()});
                    var return_content = '<span data-rendered-sc="' + index + '">[loading shortcode...]</span>';
                    index++;
                    return return_content;
                });
            });
            return {tags: tags, content: content};
        }
    };

    _.extend(api.Views.BaseElement.prototype, api.Mixins.Common);
    _.extend(api.Views.Builder.prototype, api.Mixins.Builder);

    /**
     * Form control views.
     */

    api.Views.ControlRegistry = {
        items: {},
        register: function (id, object) {
            this.items[id] = object;
        },
        lookup: function (id) {
            return this.items[id] || null;
        },
        remove: function (id) {
            delete this.items[id];
        },
        destroy: function () {
            _.each(this.items, function (view, cid) {
                view.remove();
            });
            this.items = {};
            console.log('destroy controls');
        }
    };

    api.Views.Controls[ 'default' ] = Backbone.View.extend({
        initialize: function (args) {
            api.Views.ControlRegistry.register(this.$el.attr('id'), this);
            if (args.binding_type) {
                this.binding_type = args.binding_type;
            }
        },
        preview_element: function (key, value, $repeater) {
            if (_.isUndefined(this.binding_type))
                return;

            var settings = api.activeModel.getPreviewSettings();

            if (!_.isUndefined($repeater) && $repeater.length) {
                settings[ $repeater.prop('id') ] = api.Utils.getRepeaterValues($repeater);
            } else {
                settings[ key ] = value;
            }

            api.activeModel.set('temp_settings', settings);
            if ('live' === this.binding_type) {
                api.activeModel.trigger('custom:preview:live', settings);
            } else if ('refresh' === this.binding_type) {
                api.activeModel.trigger('custom:preview:reload');
            }
        }
    });

    api.Views.Controls.default.extend = function (child) {
        var self = this,
                view = Backbone.View.extend.apply(this, arguments);
        view.prototype.events = _.extend({}, this.prototype.events, child.events);
        view.prototype.initialize = function () {
            if (_.isFunction(self.prototype.initialize))
                self.prototype.initialize.apply(this, arguments);
            if (_.isFunction(child.initialize))
                child.initialize.apply(this, arguments);
        }
        return view;
    };

    api.Views.register_control = function (type, args) {

        if ('default' !== type)
            this.Controls[ type ] = this.Controls.default.extend(args);
    };

    api.Views.get_control = function (type) {
        if (this.control_exists(type))
            return this.Controls[ type ];

        return this.Controls.default;
    };

    api.Views.control_exists = function (type) {

        return this.Controls.hasOwnProperty(type);
    };

    api.Views.init_control = function (type, args) {
        args = args || {};

        if ('wp_editor' === type) {
            if (args.el.hasClass('data_control_binding_refresh'))
                args['binding_type'] = 'refresh';
            if (args.el.hasClass('data_control_binding_live'))
                args['binding_type'] = 'live';
        }

        var id = args.el.attr('data-input-id') ? args.el.attr('data-input-id') : args.el.attr('id'),
                exist = this.ControlRegistry.lookup(id);
        if (exist) {
            exist.setElement(args.el).render();
            return exist;
        } else {
            var control = api.Views.get_control(type);
            return new control(args);
        }
    };

    api.vent.on('dom:observer:update', function ($container) {
        console.log('dom:observer:update');
        $container.find('[data-cid]').each(function () {
            var cid = $(this).data('cid'),
                    model = api.Models.Registry.lookup(cid);
            if (model)
                model.trigger('custom:dom:update');
        });
        $container.find('.themify_builder_ui_state_highlight').remove();
    });

    // Register core controls
    api.Views.register_control('wp_editor', {
        mceEditor: null,
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('id');
            api.Utils.initQuickTags(this_option_id);
            if (typeof tinyMCE !== 'undefined') {
                var ed = _.isNull(this.mceEditor) ? api.Utils.initNewEditor(this_option_id) : this.mceEditor,
                        getClass = $.grep(this.el.className.split(" "), function (v, i) {
                            return v.indexOf('data_control_repeater_') === 0;
                        }).join(),
                        $repeater = $('#' + getClass.replace('data_control_repeater_', '')),
                        previewText = function (e) {
                            that.preview_element(this_option_id, this.getContent(), $repeater);
                        };
                if (!_.isUndefined(this.binding_type)) {
                    ed.on('keyup change', previewText);
                    $('#' + this_option_id).on('keyup change', function () {
                        that.preview_element(this_option_id, this.value, $repeater);
                    });
                }
            }
            return this;
        },
        reBind: function () {
            this.mceEditor = tinyMCE.get(this.$el.attr('id'));
            this.render();
        },
        resetEditor: function () {
            this.mceEditor = null;
        }
    });

    api.Views.register_control('text', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id'),
                    repeater_id = this.$el.attr('data-control-repeater'),
                    $repeater = $('#' + repeater_id),
                    callback = function () {
                        that.preview_element(this_option_id, this.value, $repeater);
                    };

            if (!_.isUndefined(this.binding_type) && 'refresh' === this.binding_type) {
                callback = _.throttle(function () {
                    that.preview_element(this_option_id, this.value, $repeater);
                }, 1000);
            }
            this.$el.on('keyup', callback);
            return this;
        }
    });

    api.Views.register_control('textonchange', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id'),
                    repeater_id = this.$el.attr('data-control-repeater'),
                    $repeater = $('#' + repeater_id),
                    callback = function () {
                        that.preview_element(this_option_id, this.value, $repeater);
                    };

            if (!_.isUndefined(this.binding_type) && 'refresh' === this.binding_type) {
                callback = _.throttle(function () {
                    that.preview_element(this_option_id, this.value, $repeater);
                }, 1000);
            }
            this.$el.on('change', callback);
            return this;
        }
    });

    api.Views.register_control('image', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id'),
                    repeater_id = this.$el.attr('data-control-repeater'),
                    $repeater = $('#' + repeater_id),
                    callback = function () {
                        that.preview_element(this_option_id, this.value, $repeater);
                    };

            if (!_.isUndefined(this.binding_type) && 'refresh' === this.binding_type) {
                callback = _.throttle(function () {
                    that.preview_element(this_option_id, this.value, $repeater);
                }, 1000);
            }
            this.$el.on('change', callback);
            return this;
        }
    });

    api.Views.register_control('textarea', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id'),
                    repeater_id = this.$el.attr('data-control-repeater'),
                    $repeater = $('#' + repeater_id),
                    callback = function () {
                        that.preview_element(this_option_id, this.value, $repeater);
                    };

            if (!_.isUndefined(this.binding_type) && 'refresh' === this.binding_type) {
                callback = _.throttle(function () {
                    that.preview_element(this_option_id, this.value, $repeater);
                }, 1000);
            }
            this.$el.on('keyup', callback);
            return this;
        }
    });

    api.Views.register_control('icon', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id'),
                    repeater_id = this.$el.attr('data-control-repeater'),
                    $repeater = $('#' + repeater_id),
                    callback = function () {
                        that.preview_element(this_option_id, this.value, $repeater);
                    };

            if (!_.isUndefined(this.binding_type) && 'refresh' === this.binding_type) {
                callback = _.throttle(function () {
                    that.preview_element(this_option_id, this.value, $repeater);
                }, 1000);
            }
            this.$el.on('change', callback);
            return this;
        }
    });

    api.Views.register_control('query_category', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = that.$el.attr('id'),
                    parent = that.$el.parent(),
                    single_cat = parent.find('.query_category_single'),
                    multiple_cat = parent.find('.query_category_multiple'),
                    option_value;

            single_cat.add(multiple_cat).on('change', function (e) {
                option_value = multiple_cat.val() ? (multiple_cat.val() + '|multiple') : (single_cat.val() + '|single');
                that.preview_element(this_option_id, option_value);
            });
            return this;
        }
    });

    api.Views.register_control('select', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id'),
                    repeater_id = this.$el.attr('data-control-repeater'),
                    $repeater = $('#' + repeater_id);

            this.$el.on('change', function () {
                that.preview_element(this_option_id, this.value, $repeater);
            });
            return this;
        }
    });

    api.Views.register_control('layout', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id'),
                    repeater_id = this.$el.attr('data-control-repeater'),
                    $repeater = $('#' + repeater_id);
            this.$('.tfl-icon').on('click', function (e) {
                e.preventDefault();

                $(this).addClass('selected').siblings().removeClass('selected');

                if (!_.isUndefined(that.binding_type) && 'visual' === api.mode) {
                    var selectedLayout = $(this).prop('id');

                    if ('live' === that.binding_type) {

                        if (!_.isUndefined(that.$el.data('control-selector'))) {
                            var $elmtToApplyTo = api.liveStylingInstance.$liveStyledElmt,
                                    settings = api.activeModel.getPreviewSettings();

                            settings[ this_option_id ] = selectedLayout;
                            api.activeModel.set({temp_settings: settings}, {silent: true});

                            if (that.$el.data('control-selector') !== '') {
                                $elmtToApplyTo = api.liveStylingInstance.$liveStyledElmt.find(that.$el.data('control-selector'));
                            }

                            var prevLayout = api.liveStylingInstance.getStylingVal(this_option_id);

                            if (this_option_id === 'layout_feature') {
                                selectedLayout = 'layout-' + selectedLayout;
                                prevLayout = 'layout-' + prevLayout;
                            }
                            else if (this_option_id === 'columns') {
                                selectedLayout = this_option_id + '-' + selectedLayout;
                                prevLayout = this_option_id + '-' + prevLayout;
                            }

                            console.log(selectedLayout, prevLayout)

                            $elmtToApplyTo
                                    .removeClass(prevLayout)
                                    .addClass(selectedLayout);

                            if (this_option_id === 'layout_feature') {
                                selectedLayout = selectedLayout.substr(7);
                            }
                            else if (this_option_id === 'columns') {
                                selectedLayout = selectedLayout.substr(8);
                            }

                            api.liveStylingInstance.setStylingVal(this_option_id, selectedLayout);
                        } else {
                            that.preview_element(this_option_id, selectedLayout, $repeater);
                        }

                    } else {
                        that.preview_element(this_option_id, selectedLayout, $repeater);
                    }
                }
            });
            return this;
        }
    });

    api.Views.register_control('radio', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id'),
                    repeater_id = this.$el.attr('data-control-repeater'),
                    $repeater = $('#' + repeater_id);

            this.$('input[type="radio"]').on('click', function () {
                that.preview_element(this_option_id, this.value, $repeater);
            });
            return this;
        }
    });

    api.Views.register_control('checkbox', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id');

            this.$('input[type="checkbox"]').on('click', function () {
                if (!_.isUndefined(that.binding_type)) {
                    var checked = that.$('input[type="checkbox"]:checked').map(function () {
                        return this.value;
                    }).get();

                    that.preview_element(this_option_id, checked.join('|'));
                }
            });
            return this;
        }
    });

    api.Views.register_control('color', {
        is_typing: false,
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    $minicolors = this.$el.parent().find('.builderColorSelect'),
                    // Hidden field used to save the value
                    $input = this.$el,
                    // Visible field used to show the color only
                    $colorDisplay = $minicolors.parent().parent().find('.colordisplay'),
                    setColor = '',
                    setOpacity = 1.0,
                    sep = '_',
                    $colorOpacity = $minicolors.parent().parent().find('.color_opacity');

            if ($input.val()) {
                // Get saved value from hidden field
                var colorOpacity = $input.val();
                if (colorOpacity.indexOf(sep) !== -1) {
                    // If it's a color + opacity, split and assign the elements
                    colorOpacity = colorOpacity.split(sep);
                    setColor = colorOpacity[0];
                    setOpacity = colorOpacity[1] ? colorOpacity[1] : 1;
                } else {
                    // If it's a simple color, assign solid to opacity
                    setColor = colorOpacity;
                    setOpacity = 1.0;
                }
                // If there was a color set, show in the dummy visible field
                $colorDisplay.val(setColor);
                $colorOpacity.val(setOpacity);
            }

            $minicolors.minicolors({
                opacity: 1,
                textfield: false,
                change: _.debounce(function (hex, opacity) {
                    var $cssRuleInput = $(this).parent().parent().find('.builderColorSelectInput');

                    if (hex) {
                        if (opacity && '0.99' == opacity) {
                            opacity = '1';
                        }
                        var value = hex.replace('#', '') + sep + opacity;

                        $cssRuleInput.val(value);

                        if (!_.isUndefined(that.binding_type)) {
                            that.preview_element($cssRuleInput.prop('id'), value, $('#' + $input.attr('data-control-repeater')));
                        }

                        if (!that.is_typing) {
                            $colorDisplay.val(hex.replace('#', ''));
                            if (!$colorOpacity.is(':focus')) {
                                $colorOpacity.val(opacity);
                            }
                        }

                        // "Apply all" // verify is "apply all" is enabled to propagate the border color
                        that.applyAll_verifyBorderColor($cssRuleInput, value, hex.replace('#', ''), hex.replace('#', ''), 'change');

                        $('body').trigger(
                                'themify_builder_color_picker_change', [$cssRuleInput.attr('name'), $minicolors.minicolors('rgbaString')]
                                );
                    } else {
                        if (!_.isUndefined(that.binding_type)) {
                            that.preview_element($cssRuleInput.prop('id'), '', $('#' + $input.attr('data-control-repeater')));
                        }
                    }
                }, 200)
            });
            // After initialization, set initial swatch, either defaults or saved ones
            $minicolors.minicolors('value', setColor);
            $minicolors.minicolors('opacity', setOpacity);

            $colorDisplay.on('blur keyup', function (e) {
                var $input = $(this),
                        tempColor = '',
                        $minicolors = $input.parent().find('.builderColorSelect'),
                        $field = $input.parent().find('.builderColorSelectInput');
                if ($input.val()) {
                    tempColor = $input.val();
                }
                that.is_typing = 'keyup' === e.type;
                $input.val(tempColor.replace('#', ''));
                $field.val($input.val().replace(/[abcdef0123456789]{3,6}/i, tempColor.replace('#', '')));
                if ('keyup' === e.type) {
                    $minicolors.minicolors('value', tempColor);
                } else {
                    $minicolors.minicolors('value', '').minicolors('value', tempColor); // fix change doesn't trigger
                }

                // "Apply all" // verify is "apply all" is enabled to propagate the border color
                that.applyAll_verifyBorderColor($field, $field.val(), $input.val(), tempColor, e.type);
            });

            $colorOpacity.on('blur keyup', function (e) {
                var $input = $(this),
                        tempOpacity = '',
                        $minicolors = $input.parent().find('.builderColorSelect');
                if ($input.val()) {
                    tempOpacity = $input.val();
                }
                that.is_typing = 'keyup' === e.type;
                $input.val(tempOpacity);
                $minicolors.minicolors('opacity', tempOpacity);
                //that.applyAll_verifyBorderColor($field, $field.val(), $input.val(), tempColor,e.type);
            });
        },
        // "Apply all" // apply all color change
        applyAll_verifyBorderColor: function (element, hiddenInputValue, colorDisplayInputValue, minicolorsObjValue, type) {
            var $checkbox = false,
                    element = $(element);
            if (element.prop('name').indexOf('border_top_color') !== -1) {
                var $fields = element.closest('.themify_builder_field').nextAll('.themify_builder_field');
                $fields.each(function () {
                    $checkbox = $(this).find('.style_apply_all_border');
                    if ($checkbox.length > 0) {
                        return false;
                    }
                });

                if ($checkbox && $checkbox.is(':checked')) {
                    var minicolorsObj = null;
                    if (type !== 'keyup') {
                        $('.builderColorSelectInput', $fields).each(function () {
                            var $parent = $(this).closest('.themify_builder_input');
                            minicolorsObj = $parent.find('.builderColorSelect');
                            $(this).val(hiddenInputValue);
                            $parent.children('.colordisplay').val(colorDisplayInputValue);
                            minicolorsObj.minicolors('value', minicolorsObjValue);
                        });
                    }
                    else {
                        minicolorsObj = element.closest('.themify_builder_input').find('.builderColorSelect');
                    }

                    if ('visual' === api.mode) {
                        api.liveStylingInstance.setApplyBorder(element.prop('name'), minicolorsObj.minicolors('rgbaString'), 'color');
                    }
                }
            }
        },
    });

    api.Views.register_control('repeater', {
        events: {
            'click .toggle_row': 'toggleField',
            'hover .row_menu': api.Views.BaseElement.prototype.actionMenuHover,
            'click .themify_builder_duplicate_row': 'duplicateRowField',
            'click .themify_builder_delete_row': 'deleteRowField'
        },
        initialize: function () {
            this.render();
            this.on('duplicate delete', this.updateElement);
        },
        updateElement: function () {
            this.preview_element(this.$el.attr('id'), null, this.$el);
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('id'),
                    toggleCollapse = false;

            // sortable accordion builder
            this.$el.sortable({
                items: '.tb_repeatable_field',
                handle: '.tb_repeatable_field_top',
                axis: 'y',
                placeholder: 'themify_builder_ui_state_highlight',
                tolerance: 'pointer',
                cursor: 'move',
                start: _.debounce(function (e, ui) {
                    if (typeof tinyMCE !== 'undefined') {
                        that.$el.find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                            var id = $(this).attr('id');
                            tinyMCE.execCommand('mceRemoveEditor', false, id);
                        });
                    }
                }, 400),
                stop: _.debounce(function (e, ui) {
                    if (typeof tinyMCE !== 'undefined') {
                        that.$el.find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                            var id = $(this).attr('id');
                            tinyMCE.execCommand('mceAddEditor', true, id);
                            api.Views.ControlRegistry.lookup(id).reBind(); // re-bind event
                        });
                    }

                    if (toggleCollapse) {
                        ui.item.removeClass('collapsed').find('.tb_repeatable_field_content').show();
                        toggleCollapse = false;
                    }

                    that.preview_element(this_option_id, null, that.$el);
                    that.$el.find('.themify_builder_ui_state_highlight').remove();
                }, 400),
                sort: function (e, ui) {
                    that.$el.find('.themify_builder_ui_state_highlight').height(30);
                },
                beforeStart: function (event, ui) {
                    if (!ui.item.hasClass('collapsed')) {
                        ui.item.addClass('collapsed').find('.tb_repeatable_field_content').hide();
                        toggleCollapse = true;
                        that.$el.sortable('refresh');
                    }
                }
            });

            return this;
        },
        toggleField: function (e) {
            e.preventDefault();
            $(e.currentTarget).parents('.tb_repeatable_field').toggleClass('collapsed').find('.tb_repeatable_field_content').slideToggle();
        },
        duplicateRowField: function (e) {
            e.preventDefault();
            var $parentWrapper = this.$el,
                    oriElems = $(e.currentTarget).closest('.tb_repeatable_field'),
                    newElems = oriElems.clone(),
                    row_count = $parentWrapper.find('.tb_repeatable_field:visible').length + 1,
                    number = row_count + Math.floor(Math.random() * 9);

            // fix wpeditor empty textarea
            newElems.find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                var $parent = $(this).parents('.wp-editor-wrap').parent(),
                        $tmpl = api.cache.repeaterElements[ $parentWrapper.attr('id') ].find('.wp-editor-wrap').parent().clone(),
                        ori_tmpl_id = $tmpl.find('.tfb_lb_wp_editor').prop('id'),
                        ori_id = $(this).prop('id'),
                        name = $(this).prop('name'),
                        new_id = ori_id + '_' + ThemifyBuilderCommon.randNumber(),
                        dom_changes = $tmpl.html().replace(new RegExp(ori_tmpl_id, 'g'), new_id),
                        element_val;

                if (typeof tinyMCE !== 'undefined') {
                    element_val = tinyMCE.get(ori_id).hidden === false ? tinyMCE.get(ori_id).getContent() : switchEditors.wpautop(tinymce.DOM.get(ori_id).value);
                } else {
                    element_val = $('#' + ori_id).val();
                }
                $parent.html(dom_changes).find('.tfb_lb_wp_editor').prop('name', name).val(element_val).addClass('newEditor');
            });

            // fix textarea field clone
            newElems.find('textarea:not(.tfb_lb_wp_editor)').each(function (i) {
                var insertTo = oriElems.find('textarea').eq(i).val();
                if (insertTo) {
                    $(this).val(insertTo);
                }
            });

            // fix radio button clone
            newElems.find('.themify-builder-radio-dnd').each(function (i) {
                var oriname = $(this).attr('name');
                $(this).attr({'name': oriname + '_' + row_count, 'id': oriname + '_' + row_count + '_' + i})
                        .next('label').attr('for', oriname + '_' + row_count + '_' + i);
            });

            newElems.find('.themify-builder-plupload-upload-uic').each(function (i) {
                $(this)
                        .attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-upload-ui').addClass('plupload-clone')
                        .find('input[type="button"]').attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-browse-button');

            });
            newElems.find('select').each(function (i) {
                var orival = oriElems.find('select').eq(i).find('option:selected').val();
                $(this).find('option[value="' + orival + '"]').prop('selected', true);
            });
            newElems.find('.builderColorSelectInput').each(function () {
                var thiz = $(this),
                        input = thiz.clone(),
                        parent = thiz.closest('.themify_builder_field');
                thiz.prev().minicolors('destroy').removeAttr('maxlength');
                parent.find('.colordisplay').wrap('<div class="themify_builder_input" />').before('<span class="builderColorSelect"><span></span></span>').after(input);
            });
            newElems.find('.tfb_lb_option_child').each(function () {
                if ($(this).data('control-binding') && $(this).data('control-type')) {
                    api.Views.init_control($(this).data('control-type'), {el: $(this), binding_type: $(this).data('control-binding')});
                }
            });

            newElems.insertAfter(oriElems).find('.themify_builder_dropdown').hide();

            $('#tfb_module_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child.newEditor').each(function (i) {
                api.Views.init_control('wp_editor', {el: $(this)});
                $(this).removeClass('newEditor');
            });

            api.Utils.builderPlupload('new_elemn');

            this.trigger('duplicate');
        },
        deleteRowField: function (e) {
            e.preventDefault();
            if (!confirm(themifyBuilder.i18n.rowDeleteConfirm)) {
                return;
            }
            var $row = $(e.currentTarget).closest('.tb_repeatable_field');
            if ($row.closest('.themify_builder_row_js_wrapper').find('.tb_repeatable_field:visible').length > 1) {
                $row.remove();
            }
            else {
                $row.hide();
            }
            this.trigger('delete');
        }
    });

    api.Views.register_control('widget_select', {
        initialize: function () {
            this.render();
        },
        render: function () {
            var that = this,
                    this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id'),
                    repeater_id = this.$el.attr('data-control-repeater'),
                    $repeater = $('#' + repeater_id);

            this.$el.on('change', function () {
                that.preview_element(this_option_id, this.value, $repeater);
            });
            return this;
        }
    });

    api.Views.register_control('widget_form', {
        initialize: function () {
            this.render();
        },
        render: function () {
            this.this_option_id = this.$el.attr('data-input-id') ? this.$el.attr('data-input-id') : this.$el.attr('id');
            this.$el.on('change', ':input', this._updateWidgetPreview.bind(this));
            return this;
        },
        _updateWidgetPreview: function () {
            var option_value = this.$el.find(':input').themifySerializeObject();
            this.preview_element(this.this_option_id, option_value);
        }
    });

})(jQuery);
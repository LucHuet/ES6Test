'use strict';

(function(window, $, Routing, swal) {
    window.RepLogApp = function ($wrapper) {
        this.$wrapper = $wrapper;
        this.helper = new Helper(this.$wrapper);

        //initialisation des données
        this.loadRepLogs();

        this.$wrapper.on(
            'click',
            '.js-delete-rep-log',
            this.handleRepLogDelete.bind(this)
        );
        this.$wrapper.on(
            'click',
            'tbody tr',
            this.handleRowClick.bind(this)
        );
        this.$wrapper.on(
            'submit',
            this._selectors.newRepForm,
            this.handleNewFormSubmit.bind(this)
        );
    };

    $.extend(window.RepLogApp.prototype, {
        _selectors: {
            newRepForm: '.js-new-rep-log-form'
        },

        loadRepLogs: function() {
            var self = this;
            $.ajax({
                url: Routing.generate('rep_log_list'),
            }).then(function(data) {
                $.each(data.items, function(key, repLog) {
                    self._addRow(repLog);
                });
            })
        },

        updateTotalLitersDrinked: function () {
            this.$wrapper.find('.js-total-drink').html(
                this.helper.calculateTotalDrink()
            );
        },

        handleRepLogDelete: function (e) {
            e.preventDefault();

            var $link = $(e.currentTarget);

            var self = this;
            swal({
                title: 'Delete this log?',
                text: 'What? Did you not actually drink this?',
                showCancelButton: true,
                showLoaderOnConfirm: true,
                preConfirm: function() {
                    return self._deleteRepLog($link);
                }
            }).catch(function(arg) {
                // canceling is cool!
            });
        },

        _deleteRepLog: function($link) {
            $link.addClass('text-danger');
            $link.find('.fa')
                .removeClass('fa-trash')
                .addClass('fa-spinner')
                .addClass('fa-spin');

            var deleteUrl = $link.data('url');
            var $row = $link.closest('tr');
            var self = this;

            return $.ajax({
                url: deleteUrl,
                method: 'DELETE'
            }).then(function() {
                $row.fadeOut('normal', function () {
                    $(this).remove();
                    self.updateTotalLitersDrinked();
                });
            })
        },

        handleRowClick: function () {
            console.log('row clicked!');
        },

        handleNewFormSubmit: function(e) {
            e.preventDefault();

            var $form = $(e.currentTarget);
            var formData = {};
            $.each($form.serializeArray(), function(key, fieldData) {
                formData[fieldData.name] = fieldData.value
            });
            var self = this;
            console.log(formData);
            this._saveRepLog(formData)
            .then(function(data) {
                console.log("handleNewFormSubmit then");
                self._clearForm();
                self._addRow(data);
            }).catch(function(errorData) {
                console.log("handleNewFormSubmit catch");
                self._mapErrorsToForm(errorData.errors);
            });
        },

        _saveRepLog: function(data) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: Routing.generate('rep_log_new'),
                    method: 'POST',
                    data: JSON.stringify(data)
                }).then(function(data, textStatus, jqXHR) {
                                    console.log("_saveRepLog then");
                    $.ajax({
                        url: jqXHR.getResponseHeader('Location')
                    }).then(function(data) {
                        // we're finally done!
                        resolve(data);
                    });
                }).catch(function(jqXHR) {
                  console.log("_saveRepLog catch");
                    var errorData = JSON.parse(jqXHR.responseText);

                    reject(errorData);
                });
            });
        },

        _mapErrorsToForm: function(errorData) {
            this._removeFormErrors();
            var $form = this.$wrapper.find(this._selectors.newRepForm);
            $form.find(':input').each(function() {
                var fieldName = $(this).attr('name');
                var $wrapper = $(this).closest('.form-group');
                if (!errorData[fieldName]) {
                    // no error!
                    //continue;
                }

                var $error = $('<span class="js-field-error help-block"></span>');
                $error.html(errorData[fieldName]);
                $wrapper.append($error);
                $wrapper.addClass('has-error');
            });
        },

        _removeFormErrors: function() {
            var $form = this.$wrapper.find(this._selectors.newRepForm);
            $form.find('.js-field-error').remove();
            $form.find('.form-group').removeClass('has-error');
        },

        _clearForm: function() {
            this._removeFormErrors();

            var $form = this.$wrapper.find(this._selectors.newRepForm);
            $form[0].reset();
        },

        _addRow: function(repLog) {

            var tplText = $('#js-rep-log-row-template').html();
            var tpl = _.template(tplText);
            var html = tpl(repLog);
            this.$wrapper.find('tbody').append($.parseHTML(html));

            this.updateTotalLitersDrinked();
        }
    });

    /**
     * A "private" object
     */
    var Helper = function ($wrapper) {
        this.$wrapper = $wrapper;
    };
    $.extend(Helper.prototype, {
        calculateTotalDrink: function() {
            var totalDrink = 0;
            this.$wrapper.find('tbody tr').each(function () {
                totalDrink += $(this).data('drink');
            });

            return totalDrink;
        }
    });
})(window, jQuery, Routing, swal);

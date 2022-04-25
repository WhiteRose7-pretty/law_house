import * as mdc from 'material-components-web';

window.mdc = mdc;

$(document).ready(function() {
    mdc.autoInit();
});

// pass show/hide enabler
$(document).ready(function() {
    $('.toggle-pass').click(function() {
        var id = $(this).data('for');
        if ($(this).hasClass('toggle-on')) {
            $(this).removeClass('toggle-on');
            document.getElementById(id).type = "password";
            return;
        }
        $(this).addClass('toggle-on');
        document.getElementById(id).type = "text";
    });
});

// forms helper functions
window.MDCFormHelper = {
    _auto: {},
    _button: '',
    _continueErrorsProcess: {},
    _continueSubmit: false,
    _fields: {},
    _required: {},
    _rules: {},
    _rulesAsync: {},
    _rulesAsyncProcess: {},
    _rulesAsyncProcessLocks: {},
    _rulesAsyncProcessLocksCount: 0,
    _errors: {},
    _regexp: {
        email: /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))){2,}$/i,
        pass: /.*/,
        phone: /^[0-9\s\+]+$/,
    },

    asyncProcessNew: function(id, msg) {
        var proc = new Date().valueOf();
        this._rulesAsyncProcessLocks[id]++;
        this._rulesAsyncProcessLocksCount++;
        this._rulesAsyncProcess[proc] = {
            id: id,
            msg: msg
        };
        return proc;
    },

    asyncProcessKill: function(proc) {
        var data = this._rulesAsyncProcess[proc];
        delete this._rulesAsyncProcess[proc];
        return data;
    },

    asyncProcessLockDrop: function(id) {
        this._rulesAsyncProcessLocks[id]--;
        this._rulesAsyncProcessLocksCount--;
        if (this._rulesAsyncProcessLocks[id] == 0) {
            if (this._continueErrorsProcess.hasOwnProperty(id) && this._continueErrorsProcess[id] == true) {
                this._continueErrorsProcess[id] = false;
                this.errorsProcessOne(id);
            }
        }

        if (this._rulesAsyncProcessLocksCount == 0 && this._continueSubmit == true) {
            if (this.errorsFound()) {
                this._continueSubmit = false;
                return;
            }
            $('#' + this._button).trigger('click');
        }
    },

    auto: function() {
        for (let id in this._required) {
            if (this._auto.hasOwnProperty(id)) {
                continue;
            }
            $('#' + id).change(function() {
                if ($(this).is(':checkbox')) {
                    OneForm.validateCheckbox(id);
                    return;
                }
                OneForm.validate(id);
            });
            this._auto[id] = true;
        }
        for (let id in this._rules) {
            if (this._auto.hasOwnProperty(id)) {
                continue;
            }
            $('#' + id).change(function() {
                OneForm.validate(id);
            });
            this._auto[id] = true;
        }
    },

    button: function(id) {
        this._button = id;
    },

    error: function(id, msg) {
        if (!this._errors.hasOwnProperty(id)) {
            this._errors[id] = [];
        }
        this._errors[id].push(msg);
    },

    errorsClearOne: function(id) {
        if (!this.parentContainer(id).hasClass('mark-error')) {
            return;
        }
        $('#form-errors-for-' + id).remove();
        this.parentContainer(id).removeClass('mark-error');
    },

    errorsFound: function() {
        return Object.getOwnPropertyNames(this._errors).length > 0 ? true : false;
    },

    errorsMarkOne: function(id) {
        this.parentContainer(id).addClass('mark-error');
    },

    errorsProcessOne: function(id) {
        if (this._rulesAsyncProcessLocks.hasOwnProperty(id) && this._rulesAsyncProcessLocks[id] > 0) {
            this._continueErrorsProcess[id] = true;
            return;
        }
        if (!this._errors.hasOwnProperty(id)) {
            this.errorsClearOne(id);
            return;
        }
        this.errorsShowOne(id);
    },

    errorsShowOne: function(id) {

        if (!this._errors.hasOwnProperty(id)) {
            return;
        }

        this.errorsMarkOne(id);

        var html = '<div id="form-errors-for-' + id + '" class="form-errors"><ul>';
        this._errors[id].forEach(function(value) {
            html = html + '<li>' + value + '</li>';
        });
        html = html + '</ul></div>';
        $('#form-errors-for-' + id).remove();
        this.parentContainer(id).after(html);
    },

    field: function(id) {
        if (this._fields.hasOwnProperty(id)) {
            return;
        }
        this._fields[id] = true;
        this._continueErrorsProcess[id] = false;
    },

    parentContainer: function(id) {
        if ($('#' + id).is(':checkbox')) {
            return $('#' + id).closest('.mdc-form-field');
        }
        return $('#' + id).closest('.mdc-text-field');
    },

    required: function(id, msg) {
        this.field(id);
        this._required[id] = msg;
    },

    rule: function(id, msg, call) {
        this.field(id);
        if (!this._rules.hasOwnProperty(id)) {
            this._rules[id] = [];
        }
        this._rules[id].push({
            msg: msg,
            call: call
        });
    },

    ruleAsync: function(id, msg, call) {
        this.field(id);
        if (!this._rulesAsync.hasOwnProperty(id)) {
            this._rulesAsync[id] = [];
            this._rulesAsyncProcessLocks[id] = 0;
        }
        this._rulesAsync[id].push({
            msg: msg,
            call: call
        });
    },

    submit: function() {

        if (this._continueSubmit) {
            return true;
        }

        this._errors = {};

        for (let id in this._fields) {

            if ($('#' + id).is(':checkbox')) {
                this.validateCheckbox(id);
                continue;
            }
            this.validate(id);
        }

        if (this.errorsFound()) {
            return false;
        }

        if (this._rulesAsyncProcessLocksCount > 0) {
            this._continueSubmit = true;
            return false;
        }

        return true;
    },

    validate: function(id) {
        if (this._errors.hasOwnProperty(id)) {
            delete this._errors[id];
        }
        if (!$('#' + id).val() && !this._required.hasOwnProperty(id)) {
            this.errorsClearOne(id);
            return;
        }
        if (!$('#' + id).val()) {
            this.error(id, this._required[id]);
        }
        var _this = this;
        if (this._rules.hasOwnProperty(id)) {
            this._rules[id].forEach(function(r) {
                if (!r.call($('#' + id).val())) {
                    _this.error(id, r.msg);
                }
            });
        }
        if (this._rulesAsync.hasOwnProperty(id)) {
            this._rulesAsync[id].forEach(function(r) {
                var proc = _this.asyncProcessNew(id, r.msg);
                r.call(proc, $('#' + id).val());
            });
        }
        this.errorsProcessOne(id);
    },

    validateCheckbox: function(id) {
        if (this._errors.hasOwnProperty(id)) {
            delete this._errors[id];
        }
        if (!$('#' + id).is(':checked')) {
            this.error(id, this._required[id]);
        }
        this.errorsProcessOne(id);
    },

    validateRegexp: function(val, regexp, lenmin, lenmax) {
        if ((lenmin > 0 && val.length < lenmin) || (lenmax > 0 && val.length > lenmax)) {
            return false;
        }
        if (!(regexp.test(val))) {
            return false;
        }
        return true;
    }

};

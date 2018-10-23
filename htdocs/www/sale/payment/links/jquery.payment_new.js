/**
 * Payment page universal handler
 */
(function($) {
  jQuery.ajaxSettings.traditional = true;
  var settings = {
    // name for orderId parameter
    orderIdParam: "mdOrder",
    language: "ru",

    // orderDetails
    orderId: "orderNumber",
    amount: "amount",
    amountCurrency: 'amountCurrency',
    rawAmount: "rawAmount",
    currency: 'currency',
    feeAmount: "feeAmount",
    bonusAmount: "bonusAmount",
    bonusBlock: "bonusBlock",
    description: "description",
    merchantFullName: "merchantFullName",
    merchantLogin: "merchantLogin",

    paymentFormId: "formPayment",
    acsFormId: "acs",

    pan: "iPAN_sub",
    panInputId: "iPAN",
    pan1InputId: "pan1",
    pan2InputId: "pan2",
    pan3InputId: "pan3",
    pan4InputId: "pan4",

    paramPrefix: "param.",

    yearSelectId: "year",
    currentYear: (new Date).getFullYear(),
    monthSelectId: "month",
    cardholderInputId: "iTEXT",
    cvcInputId: "iCVC",
    emailInputId: "email",
    bindingCheckBoxId: "createBinding",
    deactiveBindingId: "deactiveBinding",
    agreementCheckboxId: "agreeCheckbox",
    emailId: "email",
    emailContainer: "email-container",
    emailDescription: "email-description",
    emailDescriptionOfd: "email-description-ofd",
    phoneId: "phone",
    phoneInputId: "phone",
    phoneContainer: "phone-container",
    phoneDescription: "phone-description",
    phoneDescriptionOfd: "phone-description-ofd",

    paymentAction: "../../rest/processform.do",
    paymentBindingAction: "../../rest/processBindingForm.do",
    getSessionStatusAction: "../../rest/getSessionStatus.do",
    isMaestroCardAction: "../../rest/isMaestroCard.do",
    showErrorAction: "../../rest/showErrors.do",
    getFeeAction: "../../rest/getFee.do",
    unbindCard: "../../rest/unbindcardanon.do",

    messageAjaxError: "Сервис временно недоступен. Попробуйте позднее.",
    messageTimeRemaining: "До окончания сессии осталось #MIN#:#SEC#",
    messageRedirecting: "Переадресация...",
    messageValidationInvalid: "Проверьте введённые данные",

    visualValidationEnabled: false,
    getFeeEnabled: false,
    bindingCheckboxEnabled: false,
    agreementCheckboxEnabled: false,
    paramNames: [],
    merchantOptions: [],

    onReady: function() {
    },

    updatePage: function(data) {
      var rawAmount = (data[settings.amount]).replace(/[a-zA-Z ]/g, ""),
          currency  = (data[settings.amount]).substr(-3);
      $("#" + settings.orderId).text(data[settings.orderId]);
      $("#" + settings.amountCurrency).text(data[settings.amount]);
      properties.rawAmount = rawAmount;
      properties.currency = currency;

      $("#" + settings.amount).text(data[settings.amount]);
      $("#" + settings.rawAmount).text(rawAmount);
      $("." + settings.currency).addClass(currency);

      $("#" + settings.description).text(data[settings.description]);
      if (data[settings.bonusAmount] > 0) {
        $("#" + settings.bonusBlock).show();
        $("#" + settings.bonusAmount).text(data[settings.bonusAmount] / 100);
      } else {
        $("#" + settings.bonusBlock).hide();
      }
      if (data['queriedParams']) {
        $.each(data['queriedParams'], function(name, value) {
          var el = $('#' + name);
          if (el && el.is('a')) {
            el.attr('href', value);
          } else if (el) {
            if (el.val) el.val(value);
            if (el.text) el.text(value);
          }
        });
      }
      if ( 'merchantInfo' in data ) {
        $("#" + settings.merchantFullName).text(data.merchantInfo[settings.merchantFullName]);
        $("#" + settings.merchantLogin).text(data.merchantInfo[settings.merchantLogin]);
      }
    }
  };

  var properties = {
    rawAmount: null,
    currency: null,
    orderId: null,
    expired: false,
    validatePan: false,
    validateExpiry: false,
    validateCardholderName: false,
    validateCvc: false,
    isMaestro: false,
    fee: 0,
    feeChecked: false,
    cvcValidationRequired: true,
    validateAgreementCheckbox: false,
    validateEmail: false,
    validatePhone: false,
    validateOfdCustomFields: false,
    isBindingEnabled: false,
    isAgreementEnabled: true,
    ofdEnabled: false,
    emailEnabled: false,
    phoneEnabled: false
  };

  var methods = {
    maestroCheck: {
      pan: "",
      result: false
    },

    init: function(options) {
      if (options) {
        $.extend(settings, options);
      }
      if (properties.ofdEnabled) {
        methods.switchEmailEnabled(true);
        methods.switchPhoneEnabled(true);
      }
      return this.each(function() {
        $(this).ready(methods.fillControls);
        var orderId = $.url.param(settings.orderIdParam);
        if (!orderId) {
          orderId = $.url.param(settings.orderIdParamUpperCase);
          if (!orderId) {
            $(this).log("Unknown order", "error");
            return;
          }
        }
        properties.orderId = orderId;
        properties.expired = false;
        methods.getSessionStatus(true);
      });
    },
    updateLogos: function(merchantOption) {
      $("ul#logo-list").children().remove();

      var systemList = ['VISA', 'MASTERCARD', 'MIR'];
      var securityList = ['SSL', 'VISA_TDS', 'MASTERCARD_TDS', 'MIR_TDS'];

      for (var i in merchantOption) {
        var value = merchantOption[i];
        if (systemList.indexOf(value) > -1) {
          $("ul#logo-list").append('<li><img class="bg" src="../../img/' + value.toLowerCase() + '.png" alt=""></li>');
        } else if (securityList.indexOf(value) > -1) {
          if (value === 'SSL') {
            $("ul.ico").append('<li><img class="bg" src="../../img/' + value.toLowerCase() + '.gif" width="113" height="19" alt="">' +
              '<img class="bg-640" src="../../img/' + value.toLowerCase() + '-640.gif" width="136" height="24" alt=""></li>');
          } else {
            $("ul.ico").append('<li><img class="bg" src="../../img/' + value.toLowerCase() + '.gif" width="54" height="28" alt="">' +
              '<img class="bg-640" src="../../img/' + value.toLowerCase() + '-640.gif" width="68" height="35" alt=""></li>');
          }
        }
      }
    },
    checkControl: function(name) {
      if ($(name).length == 0) {
        alert('Absent ' + name);
      }
    },
    checkControls: function() {
      methods.checkControl('#' + settings.paymentFormId);
      methods.checkControl("#" + settings.panInputId);
      methods.checkControl("#" + settings.cardholderInputId);
      methods.checkControl("#" + settings.cvcInputId);

      methods.checkControl("#" + settings.yearSelectId);
      methods.checkControl("#" + settings.monthSelectId);

      methods.checkControl('#' + settings.orderId);

      if (settings.bindingCheckboxEnabled) methods.checkControl('#' + settings.bindingCheckBoxId);
      if (settings.agreementCheckboxEnabled) methods.checkControl('#' + settings.agreementCheckboxId);
      if (properties.emailEnabled || properties.ofdEnabled) methods.checkControl('#' + settings.emailId);
      if (properties.phoneEnabled || properties.ofdEnabled) methods.checkControl('#' + settings.phoneId);

      methods.checkControl('#buttonPayment');
      methods.checkControl('#mdOrder');
      methods.checkControl('#location');
      methods.checkControl('#expiry');
      methods.checkControl('#language');
      methods.checkControl('#errorBlock');
      methods.checkControl('#numberCountdown');
      methods.checkControl('#infoBlock');
    },
    checkFee: function() {
      $.ajax({
        url: settings.getFeeAction,
        type: 'POST',
        cache: false,
        data: ({
          mdOrder: $.url.param("mdOrder"),
          pan: '0'
        }),
        dataType: 'json',
        error: function() {
          methods.showError(settings.messageAjaxError);
        },
        success: function(data) {
          if (data['errorCode'] == 0) {
            $("#feeBlock").show();
            if (properties.isAgreementEnabled) {
              $("#agreeBlock").show();
              settings.agreementCheckboxEnabled = true;
            }
          }
        }
      });
    },
    bindControls: function() {
      methods.checkControls();
      $('#' + settings.paymentFormId).bind('submit.payment', methods.onSubmit);
      if (settings.visualValidationEnabled) {
        $("#" + settings.panInputId).bind('keyup.payment', methods.validatePan);
        $("#" + settings.panInputId).bind('keyup.payment', methods.getFee);
        $("#" + settings.cardholderInputId).bind('keyup.payment', methods.validateCardholderName);
        $("#" + settings.cvcInputId).bind('keyup.payment', methods.validateCvc);
        $("#" + settings.emailId).bind('keyup.payment', methods.validateEmail);
        $("#" + settings.phoneId).bind('keyup.payment', methods.validatePhone);

        $("#" + settings.emailId).bind('keyup.payment', methods.validateOfdCustomFields);
        $("#" + settings.phoneId).bind('keyup.payment', methods.validateOfdCustomFields);

        $("#" + settings.yearSelectId).bind('change.payment', methods.validateExpiry);
        $("#" + settings.monthSelectId).bind('change.payment', methods.validateExpiry);
      } else {
        $("#" + settings.panInputId).bind('keyup.payment', methods.validate);
        $("#" + settings.pan).bind('keyup.payment', methods.validate);
        $("#" + settings.cardholderInputId).bind('keyup.payment', methods.validate);
        $("#" + settings.cvcInputId).bind('keyup.payment', methods.validate);
        $("#" + settings.emailId).bind('keyup.payment', methods.validate);
        $("#" + settings.phoneId).bind('keyup.payment', methods.validate);

        $("#" + settings.yearSelectId).bind('change.payment', methods.validate);
        $("#" + settings.monthSelectId).bind('change.payment', methods.validate);
      }
      $('#' + settings.panInputId).bind('keypress.payment', methods.checkNumberInput);
      $('#' + settings.pan1InputId).bind('keypress.payment', methods.checkNumberInput);
      $('#' + settings.pan1InputId).bind('paste.payment', methods.checkNumberInput);
      $('#' + settings.pan2InputId).bind('keypress.payment', methods.checkNumberInput);
      $('#' + settings.pan2InputId).bind('paste.payment', methods.checkNumberInput);
      $('#' + settings.pan3InputId).bind('keypress.payment', methods.checkNumberInput);
      $('#' + settings.pan3InputId).bind('paste.payment', methods.checkNumberInput);
      $('#' + settings.pan4InputId).bind('keypress.payment', methods.checkNumberInput);
      $('#' + settings.pan4InputId).bind('paste.payment', methods.checkNumberInput);
      $('#' + settings.cvcInputId).bind('keyup.payment', methods.checkNumberInput);
      $('#' + settings.cvcInputId).bind('paste.payment', methods.checkNumberInput);
      $('#' + settings.cardholderInputId).bind('keyup.payment', methods.checkNameInput);
      $('#' + settings.cardholderInputId).bind('paste.payment', methods.checkNameInput);

      $('#buttonPayment').bind('click.payment', methods.doSubmitForm);
      $('#buttonPaymentAlfa').bind('click.payment', methods.doSubmitFormAlfa);
      $('#buttonPaymentUPOP').bind('click.payment', methods.doSubmitFormUpop);

      $('#' + settings.deactiveBindingId).bind('click', methods.deactiveBinding);

      if (settings.agreementCheckboxEnabled) {
        $('#' + settings.agreementCheckboxId).change(methods.validate);
      }
    },
    showCustomFields: function() {
      $('#' + settings.emailContainer).show();
      $('#' + settings.phoneContainer).show();

      if (properties.emailEnabled) {
        $('#' + settings.emailDescription).show();
      }
      if (properties.phoneEnabled) {
        $('#' + settings.phoneDescription).show();
      }
      if (properties.ofdEnabled) {
        $('#' + settings.emailDescription + ', #' + settings.phoneDescription).hide();
        $('#' + settings.emailDescriptionOfd + ', #' + settings.phoneDescriptionOfd).show();
      }
    },
    customerDetails: function(data) {
      if ($('#' + settings.emailInputId).length && data['customerDetails'].email != null) {
        $('#' + settings.emailInputId).val(data['customerDetails'].email);
      }
      if ($('#' + settings.phoneInputId).length && data['customerDetails'].phone != null) {
        if (/^[78]/.test(data['customerDetails'].phone)) {
          data['customerDetails'].phone = data['customerDetails'].phone.replace(/^[78]/, '+7');
        }
        $('#' + settings.phoneInputId).val(data['customerDetails'].phone);
      }

      if (data['customerDetails'].email != null) {
        $('#' + settings.emailContainer).hide();
      }
      if (data['customerDetails'].phone != null) {
        $('#' + settings.phoneContainer).hide();
      }

      // показываем сообщение, если:
      // - включено ОФД;
      // - нет данных о клиенте из сессии (customerDetails);
      // - есть хотя бы одно поле для ввода email/phone.
      if (properties.ofdEnabled &&
        data['customerDetails'].phone == null &&
        data['customerDetails'].email == null && (
          methods.getExistField(settings.emailInputId) ||
          methods.getExistField(settings.phoneInputId) ||
          methods.getExistField('phoneInput')
        ) ) {
        $('#' + settings.phoneContainer + ', #' + settings.emailContainer).addClass('error').find('input').addClass('invalid');
        $('#errorBlock').append('<p class = "errorField">' + getLocalizedText('err_ofd') + '</p>');
        methods.switchActions(false);
      }
    },
    fillControls: function() {
      methods.bindControls();
      // fill years
      $('#' + settings.yearSelectId).empty();
      var year = settings.currentYear;
      while (year < settings.currentYear + 20) {
        var option = "<option value=" + year + ">" + year + "</option>";
        $('#' + settings.yearSelectId).append($(option));
        year++;
      }
      // show custom fields
      methods.showCustomFields();
    },
    checkNumberInput: function(event) {
      var elem = $(event.target);
      elem.val(elem.val().replace(/\D/g, ""));
    },
    checkNameInput: function(event) {
      var target   = event.target,
          position = target.selectionEnd,
          length   = target.value.length;

      var elem = $(event.target);
      elem.val(transliterate(elem.val()).replace(/[^a-zA-Z ' \-`.]/g, "").toUpperCase());

      target.selectionEnd = position += ((target.value.charAt(position - 1) === ' ' &&
        target.value.charAt(length - 1) === ' ' &&
        length !== target.value.length) ? 1 : 0);
    },
    onSubmit: function(event) {
      event.preventDefault();
      methods.sendPayment();
    },
    switchActions: function(isEnabled) {
      $('#buttonPayment').attr('disabled', !isEnabled);
      $('#buttonBindingPayment2').attr('disabled', !isEnabled);
    },
    switchBindingsActions: function(isEnabled) {
      $('#buttonBindingPayment').attr('disabled', !isEnabled);
    },
    switchEmailEnabled: function(status) {
      if (!methods.getExistField(settings.emailInputId)) {
        status = false;
      }
      properties.emailEnabled = status;
    },
    switchPhoneEnabled: function(status) {
      if (!methods.getExistField(settings.phoneId) && !methods.getExistField('phoneInput')) {
        status = false;
      }
      properties.phoneEnabled = status;
    },
    getExistField: function(fieldName) {
      return $('#' + fieldName).length && $('#' + fieldName).is(':visible');
    },
    doSubmitForm: function() {
      if (!methods.validate()) {
        return;
      }
      if (settings.getFeeEnabled && properties.fee > 0 && !properties.feeChecked) {
        return;
      }
      $('#expiry').val($("#" + settings.yearSelectId).val() + $("#" + settings.monthSelectId).val());
      methods.switchActions(false);
      $('#formPayment').submit();
    },
    doSubmitFormAlfa: function() {
      methods.sendPaymentOtherWays('ALFACLICK');
    },
    doSubmitFormUpop: function() {
      methods.sendPaymentOtherWays('UPOP');
    },
    validateCardholderName: function() {
      if (!/(\s*\w+\s*((\.|'|-)|\s+|$)){1,}/.test($('#' + settings.cardholderInputId).val())) {
        properties.validateCardholderName = false;
        if (settings.visualValidationEnabled) {
          $("#" + settings.cardholderInputId).removeClass("valid").addClass("invalid");
        }
      } else {
        properties.validateCardholderName = true;
        if (settings.visualValidationEnabled) {
          $("#" + settings.cardholderInputId).removeClass("invalid").addClass("valid");
        }
      }
    },
    validateAgreementCheckbox: function() {
      if (!settings.agreementCheckboxEnabled || $('#' + settings.agreementCheckboxId).attr("checked")) {
        properties.validateAgreementCheckbox = true;
        $("#" + settings.agreementCheckboxId).removeClass("invalid").addClass("valid");
      } else if (!properties.isAgreementEnabled) {
        properties.validateAgreementCheckbox = true;
      } else {
        properties.validateAgreementCheckbox = false;
        $("#" + settings.agreementCheckboxId).removeClass("valid").addClass("invalid");
      }
    },
    validateExpiry: function() {
      // check if card expiration date
      var dateNow = new Date();
      var cardDate = new Date();
      cardDate.setYear($('#' + settings.yearSelectId).val());
      cardDate.setMonth($('#' + settings.monthSelectId).val() - 1);
      if (dateNow.getTime() > cardDate.getTime() || this.expired) {
        properties.validateExpiry = false;
        if (settings.visualValidationEnabled) {
          $("#" + settings.yearSelectId + ", #" + settings.monthSelectId).removeClass("valid").addClass("invalid");
        }
      } else {
        properties.validateExpiry = true;
        if (settings.visualValidationEnabled) {
          $("#" + settings.yearSelectId + ", #" + settings.monthSelectId).removeClass("invalid").addClass("valid");
        }
      }
    },
    validatePan: function() {
      function switchPanValidClasses(valid) {
        if (settings.visualValidationEnabled) {
          var classToRemove = valid ? "invalid" : "valid",
              classToAdd    = valid ? "valid" : "invalid";
          $("#" + settings.panInputId +
          ", #" + settings.pan1InputId +
          ", #" + settings.pan2InputId +
          ", #" + settings.pan3InputId +
          ", #" + settings.pan4InputId).removeClass(classToRemove).addClass(classToAdd);

        }
      }

      if ($(document).saveEvent) {
        $(document).saveEvent("change", "iPAN", $('#' + settings.panInputId).val());
      }
      if (!/^\d{12,19}$/.test($('#' + settings.panInputId).val())) {
        properties.validatePan = false;
        switchPanValidClasses(false);
      } else if (!luhn($('#' + settings.panInputId).val())) {
        properties.validatePan = false;
        switchPanValidClasses(false);
      } else {
        if (properties.validatePan === false && $(document).saveEvent) {
          $(document).saveEvent("pan_valid", "iPAN")
        }
        properties.validatePan = true;
        switchPanValidClasses(true);
      }


      if (properties.validatePan) {
        if (/^(50|5[6-8]|6[0-9]).*$/.test($('#' + settings.panInputId).val())) {
          properties.isMaestro = false;
          properties.isMaestro = methods.isMaestroCard();
        }
        methods.validateCvc();
      } else {
        properties.isMaestro = false;
        methods.validateCvc();
      }

    },
    validateCvc: function() {
      $("#cvcMessage").hide();
      if ((properties.isMaestro && $('#' + settings.cvcInputId).val() == "") ||
        (properties.cvcValidationRequired === false &&
          ($('#' + settings.cvcInputId).val() == "" || /^\d{3,4}$/.test($('#' + settings.cvcInputId).val())))) {
        $("#cvcMessage").show();
        properties.validateCvc = true;
        if (settings.visualValidationEnabled) {
          $("#" + settings.cvcInputId).removeClass("invalid").addClass("valid");
        }
      } else if (!/^\d{3,4}$/.test($('#' + settings.cvcInputId).val())) {
        properties.validateCvc = false;
        if (settings.visualValidationEnabled) {
          $("#" + settings.cvcInputId).removeClass("valid").addClass("invalid");
        }
      } else {
        properties.validateCvc = true;
        if (settings.visualValidationEnabled) {
          $("#" + settings.cvcInputId).removeClass("invalid").addClass("valid");
        }
      }

    },
    validateEmail: function() {
      if (!properties.emailEnabled || !methods.getExistField(settings.emailId)) {
        properties.validateEmail = true;
        return;
      }
      if ($('#' + settings.emailId).val().split(" ").join("") == "" ||
        /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/.test($('#' + settings.emailId).val())
      ) {
        properties.validateEmail = true;
        $("#" + settings.emailId).removeClass("invalid").addClass("valid");
      } else {
        properties.validateEmail = false;
        $("#" + settings.emailId).removeClass("valid").addClass("invalid");
      }
    },
    validatePhone: function() {
      if (!properties.phoneEnabled ||
        (!methods.getExistField(settings.phoneId) && !methods.getExistField('phoneInput'))) {
        properties.validatePhone = true;
        return;
      }
      var patternDefault = /^(\+|)[0-9]{0,12}$/,
          valuePattern   = $('#' + settings.phoneId).attr('pattern') != undefined ? new RegExp('^' + $('#' + settings.phoneId).attr('pattern') + '$') : null,
          pattern        = valuePattern ? valuePattern : patternDefault;
      if ($('#' + settings.phoneId).val().trim() === "" ||
        pattern.test($('#' + settings.phoneId).val())) {
        properties.validatePhone = true;
        $("#" + settings.phoneId).removeClass("invalid").addClass("valid");
      } else {
        properties.validatePhone = false;
        $("#" + settings.phoneId).removeClass("valid").addClass("invalid");
      }
    },
    validateOfdCustomFields: function() {
      if (properties.ofdEnabled) {
        var validate;

        methods.validateEmail();
        methods.validatePhone();

        if ( (methods.getExistField(settings.emailId) &&
              properties.validateEmail &&
              $('#' + settings.emailId).val().split(" ").join("") !== "") ||
             ((methods.getExistField(settings.phoneId) || methods.getExistField('phoneInput') ) &&
               properties.validatePhone &&
               $('#' + settings.phoneId).val().trim() !== "")
        ) {
          validate = true;
          properties.validateOfdCustomFields = true;
          $('#' + settings.emailContainer + ', #' + settings.phoneContainer).removeClass("error-ofd");
        } else {
          validate = false;
          properties.validateOfdCustomFields = false;
          $('#' + settings.phoneContainer + ', #' + settings.emailContainer).addClass("error-ofd");


          // У мерчента может не быть полей емейл и телефон, а настройки есть
          if (!methods.getExistField(settings.emailId) &&
              !methods.getExistField(settings.phoneId)
          ) {
            validate = true;
            properties.validateOfdCustomFields = true;
          }
        }
        methods.switchActions(validate);
      } else {
        properties.validateOfdCustomFields = true;
      }
    },
    validate: function() {
      methods.validateCardholderName();
      methods.validatePan();
      methods.validateCvc();
      methods.validateExpiry();
      methods.validateAgreementCheckbox();
      methods.validateEmail();
      methods.validatePhone();
      methods.validateOfdCustomFields();
      var isValid = properties.validateCardholderName &&
        properties.validatePan &&
        properties.validateExpiry &&
        properties.validateCvc &&
        properties.validateAgreementCheckbox &&
        properties.validateEmail &&
        properties.validatePhone &&
        properties.validateOfdCustomFields;

      // Валидируем другие поля если выбрана связка
      if (window.bindingsEnabled) {
        isValid = methods.validateBindingForm();
      }

      if (!settings.visualValidationEnabled) {
        methods.switchActions(isValid);
      }
      return isValid;
    },
    showProgress: function() {
      $('#errorBlock').empty();
      $('#indicator').show();
    },
    hideProgress: function() {
      $('#indicator').hide();
    },
    showError: function(message) {
      methods.hideProgress();
      $('#errorBlock').empty().prepend('<p class="errorField" id="loginError">' + message + "</p>");
    },
    redirect: function(destination, message) {
      if (message) {
        $('#infoBlock').empty().prepend('<p>' + message + "</p>");
      }
      $('#numberCountdown').hide();
      $('#errorBlock').empty();
      $('#formPayment').attr('expired', '1');
      methods.switchActions(false);

      if (!/[;<>,]|javascript/g.test(destination)) {
        document.location = destination;
      } else {
        console.warn("Некорректный backUrl");
        return false;
      }
    },
    startCountdown: function(remainingSecs) {
      $(document).oneTime(remainingSecs * 1000, function() {
        $('#formPayment').attr('expired', '1');
      });

      $('#numberCountdown').everyTime(1000, function(i) {
        if (settings.messageTimeRemaining.indexOf("#DAY#") + 1) {
          var secondsLeft = remainingSecs - i,
              seconds     = secondsLeft % 60,
              days        = Math.floor((secondsLeft / 3600) / 24),
              hours       = Math.floor((secondsLeft - days * 86400) / 3600),
              minutes     = Math.floor((secondsLeft - days * 86400 - hours * 3600) / 60);

        } else if ((settings.messageTimeRemaining.indexOf("#HOU#") + 1) &&
          !(settings.messageTimeRemaining.indexOf("#DAY#") + 1)) {
          var secondsLeft = remainingSecs - i,
              seconds     = secondsLeft % 60,
              hours       = Math.floor(secondsLeft / 3600),
              minutes     = Math.floor((secondsLeft - hours * 3600) / 60);
        } else {
          var secondsLeft = remainingSecs - i,
              seconds     = secondsLeft % 60,
              minutes     = Math.floor(secondsLeft / 60),
              hours       = "";
        }
        if (hours < 10) {
          hours = "0" + hours;
        }
        if (minutes < 10) {
          minutes = "0" + minutes;
        }
        if (seconds < 10) {
          seconds = "0" + seconds;
        }
        if (days == 0) {
          days = "";
        }
        if (hours == 0) {
          hours = "";
        }
        if (minutes == 0) {
          minutes = "";
        }
        $(this).text(settings.messageTimeRemaining
        .replace("#DAY#", new String(days))
        .replace("#HOU#", new String(hours))
        .replace("#MIN#", new String(minutes))
        .replace("#SEC#", new String(seconds)));
        if (secondsLeft <= 0) {
          methods.getSessionStatus(false);
        }
        if (settings.messageTimeRemaining.indexOf("<sec>") + 1) {
          $(this).text($(this).text()
          .replace("<sec>", getLocalizedText("sec"))
          .replace("<min>", isUnitTime("minutes", minutes))
          .replace("<hou>", isUnitTime("hours", hours))
          .replace("<day>", isUnitTime("days", days)));
        }
      }, remainingSecs);
    },
    setupBindingForm: function(data) {
      if (data['bindingEnabled'] === true) {
        properties.isBindingEnabled = true;

        if (settings.bindingCheckboxEnabled === true) {
          $('#bindingBlock').show();  // show checkbox 'save card'
        } else {
          $('#bindingBlock').hide();  // hide checkbox 'save card'
        }

      } else {
        properties.isBindingEnabled = false;
        $('#bindingBlock').hide();
      }

      var bindingForm = $('#formBinding');
      var bindingItems = data['bindingItems'];
      if (bindingForm.length === 0) {
        // Page template does not support bindings
        return;
      }
      if (typeof bindingItems === 'undefined') {
        // No bindings for this order
        bindingForm.hide();
        return;
      }
      methods.checkControl('#buttonBindingPayment');

      // Build binding select control
      var bindingSelect = bindingForm.find('select[name=bindingId]');
      if (bindingSelect.length !== 1) {
        alert('Binding selector not found');
      }
      for (var i = 0; i < bindingItems.length; i++) {
        var o = $('<option value="' + bindingItems[i].id + '">' + bindingItems[i].label + '</option>');
        bindingSelect.append(o);
      }

      var hiddenNodes = bindingForm.find('.rbs_hidden');
      bindingSelect.change(function() {
        hiddenNodes.toggle($(this).val() !== '');
      });
      $('#buttonBindingPayment').bind('click', function() {
        methods.switchBindingsActions(false);
        if (methods.validateBindingForm() === true) {
          bindingForm.submit();
        } else {
          methods.switchBindingsActions(true);
        }
        return false;
      });
      bindingForm.bind('submit', methods.sendBindingPayment);

      bindingForm.show();
      hiddenNodes.hide();
    },
    validateBindingForm: function() {
      methods.validateCvc();
      methods.validateAgreementCheckbox();
      methods.validateEmail();
      methods.validatePhone();
      methods.validateOfdCustomFields();
      var isValid = properties.validateAgreementCheckbox &&
        properties.validateCvc &&
        properties.validateEmail &&
        properties.validatePhone &&
        properties.validateOfdCustomFields;

      if (!settings.visualValidationEnabled) {
        methods.switchBindingsActions(isValid);
      }
      return isValid;
    },
    setupAgreementBlock: function(data) {
      if (data['agreementUrl'] !== null && data['agreementUrl'] !== "null" &&
        data['agreementUrl'] !== "" && data['agreementUrl'] !== "#") {
        $('#agreeHref').attr("href", data['agreementUrl']);
        properties.validateAgreementCheckbox = true;
      } else {
        $('#agreeHref').closest('.agreeBox').hide();
        properties.isAgreementEnabled = false;
        properties.validateAgreementCheckbox = true;
      }
    },
    sendBindingPayment: function() {
      methods.showProgress();
      var orderId = properties.orderId;
      var bindingForm = $('#formBinding');
      var addParams = methods.getAdditionalParams(settings.paymentFormId);
      $.ajax({
        url: settings.paymentBindingAction,
        type: 'POST',
        cache: false,
        data: {
          'orderId': orderId,
          'bindingId': bindingForm.find('select[name=bindingId]').val(),
          'cvc': bindingForm.find('input[name=cvc]').val(),
          'email': $("#" + settings.emailInputId).val(),
          'jsonParams': addParams
        },
        dataType: 'json',
        error: function() {
          methods.showError(settings.messageAjaxError);
          return true;
        },
        success: function(data) {
          methods.hideProgress();
          if ('acsUrl' in data && data['acsUrl'] !== null) {
            methods.redirectToAcs(data);
          } else if ('error' in data) {
            methods.showError(data['error']);
            methods.switchBindingsActions(true);
          } else if ('redirect' in data) {
            methods.redirect(data['redirect'], data['info'], settings.messageRedirecting);
          }
          return true;
        }
      });
      return false;
    },
    getSessionStatus: function(informRbsOnLoad) {
      methods.showProgress();
      var orderId = properties.orderId;
      $.ajax({
        url: settings.getSessionStatusAction,
        type: 'POST',
        cache: false,
        data: ({
          MDORDER: orderId,
          language: settings.language,
          informRbsOnLoad: informRbsOnLoad,
          paramNames: settings.paramNames
        }),
        dataType: 'json',
        error: function() {
          methods.showError(settings.messageAjaxError);
        },
        success: function(data) {
          methods.hideProgress();
          if ('acsUrl' in data && data['acsUrl'] !== null) {
            methods.redirectToAcs(data);
            return true;
          }
          if ('cvcNotRequired' in data && data['cvcNotRequired'] === true) {
            properties.cvcValidationRequired = false;
          }
          if ('otherWayEnabled' in data && data['otherWayEnabled'] === true) {
            if ('paymentWays' in data) {
              var dataPaymentWays = data['paymentWays'];
              for (var i = 0; i < dataPaymentWays.length; i++) {
                var item = dataPaymentWays[i];
                if (item === 'UPOP') {
                  $('#buttonPaymentUPOP').toggle(true);
                }
                if (item === 'ALFA_ALFACLICK') {
                  $('#buttonPaymentAlfa').toggle(true);
                }
              }
            }
          }
          if ('sslOnly' in data && data['sslOnly'] === true) {
            if ($('#visa_3dsecure') !== null) {
              $('#visa_3dsecure').hide();
            }
            if ($('#mc_3dsecure') !== null) {
              $('#mc_3dsecure').hide();
            }
          }
          if ('feeEnabled' in data) {
            settings.getFeeEnabled = data['feeEnabled'];
            if (settings.getFeeEnabled) {
              methods.checkFee();
            }
          }
          if ('payerNotificationEnabled' in data) {
            if (data['payerNotificationEnabled'] && properties.emailEnabled) {
              $('#' + settings.emailDescription).show();
            }
          }
          if ('sessionParams' in data) {
            var sessionParams = data['sessionParams'];
            for (var i in sessionParams) {
              switch (sessionParams[i]) {
                case "OFD_ENABLED"  :
                  properties.ofdEnabled = true;
                  break;
                case "EMAIL_ENABLED":
                  methods.switchEmailEnabled(true);
                  break;
                case "PHONE_ENABLED":
                  methods.switchPhoneEnabled(true);
                  break;
                default:
              }
            }
            methods.showCustomFields();
          }

          if ('bindingDeactivationEnabled' in data) {
            if (data['bindingDeactivationEnabled'] === false) {
              $('#delete-binding').remove();
            }
          }
          if ('merchantOptions' in data) {
            methods.updateLogos(data.merchantOptions)
          }
          if ('customerDetails' in data) {
            methods.customerDetails(data);
          }
          if (('error' in data) && ('amount' in data) && ('remainingSecs' in data)) {
            methods.showError(data['error']);
            settings.updatePage(data);
            var remainingSecs = data['remainingSecs'];
            if (remainingSecs > 0) {
              methods.startCountdown(remainingSecs);
              methods.setupBindingForm(data);
              methods.setupAgreementBlock(data);
            } else {
              methods.redirect(settings.showErrorAction, settings.messageRedirecting);
            }
          } else if ('error' in data) {
            methods.showError(data['error']);
          } else if ('redirect' in data) {
            methods.redirect(data['redirect'], settings.messageRedirecting);
          } else if ('paymentWay' in data) {
            $('#paymentForm').attr('display', 'none');
          } else {
            settings.updatePage(data);
            var remainingSecs = data['remainingSecs'];
            if (remainingSecs > 0) {
              methods.startCountdown(remainingSecs);
              methods.setupBindingForm(data);
              methods.setupAgreementBlock(data);
            } else {
              methods.redirect(settings.showErrorAction, settings.messageRedirecting);
            }
          }
          settings.onReady();
          return true;
        }
      });
    },
    getFee: function() {
      if (!properties.validatePan) {
        return;
      }
      if (!settings.getFeeEnabled) {
        return;
      }
      properties.feeChecked = false;
      methods.showProgress();
      var orderId = properties.orderId;
      $.ajax({
        url: settings.getFeeAction,
        type: 'POST',
        cache: false,
        data: ({
          mdOrder: orderId,
          pan: $("#" + settings.panInputId).val()
        }),
        dataType: 'json',
        error: function() {
          methods.showError(settings.messageAjaxError);
        },
        success: function(data) {
          methods.hideProgress();
          if ('errorCode' in data && data['errorCode'] == 0) {
            properties.fee = data['fee'];
            $("#" + settings.feeAmount).text(properties.fee);
            properties.feeChecked = true;
          }
          return true;
        }
      });
    },
    isMaestroCard: function() {
      if (methods.maestroCheck.pan != $("#" + settings.panInputId).val()) { // Вызов из-за изменения PAN-а, не из-за submit-а.
        $.ajax({
          url: settings.isMaestroCardAction,
          type: 'POST',
          cache: false,
          data: ({
            pan: $("#" + settings.panInputId).val()
          }),
          dataType: 'json',
          error: function() {
            methods.showError(settings.messageAjaxError);
          },
          success: function(data) {
            data = JSON.parse(data);
            if ('error' in data) {
              methods.showError(data['error']);
            } else {
              methods.maestroCheck.pan = $("#" + settings.panInputId).val(); // Запомним результат вызова для заданного PAN-а, чтобы не
                                                                             // дёргать AJAX зря.
              methods.maestroCheck.result = data["isMaestro"];
              properties.isMaestro = data["isMaestro"];
              methods.validateCvc();
            }
          }
        });
        return; // Т.к. methods.maestroCheck.result всё равно не успеет обновиться, а результат вызова функции validate() сейчас не нужен,
                // т.к. это не submit.
      }
      return methods.maestroCheck.result; // Вызов не из-за изменения PAN-а - submit или изменение другого поля. Если это submit результат
                                          // пригодится.
    },
    sendPayment: function() {
      methods.showProgress();
      var orderId = properties.orderId;
      var bindingNotNeeded = settings.bindingCheckboxEnabled && !$("#" + settings.bindingCheckBoxId).attr("checked");
      var addParams = methods.getAdditionalParams(settings.paymentFormId);
      $.ajax({
        url: settings.paymentAction,
        type: 'POST',
        cache: false,
        data: ({
          MDORDER: orderId,
          $EXPIRY: $("#expiry").attr("value"),
          $PAN: $("#" + settings.panInputId).val(),
          MM: $("#" + settings.monthSelectId).val(),
          YYYY: $("#" + settings.yearSelectId).val(),
          TEXT: $("#" + settings.cardholderInputId).val(),
          $CVC: $("#" + settings.cvcInputId).val(),
          language: settings.language,
          email: $("#" + settings.emailInputId).val(),
          bindingNotNeeded: bindingNotNeeded,
          'jsonParams': addParams
        }),
        dataType: 'json',
        error: function() {
          methods.showError(settings.messageAjaxError);
          methods.switchActions(true);
          return true;
        },
        success: function(data) {
          methods.hideProgress();
          methods.switchActions(true);
          if ('acsUrl' in data && data['acsUrl'] !== null) {
            methods.redirectToAcs(data);
          } else if ('error' in data) {
            methods.showError(data['error']);
          } else if ('redirect' in data) {
            methods.redirect(data['redirect'], data['info'], settings.messageRedirecting);
          }
          return true;
        }

      });
    },
    getAdditionalParams: function(paymentFormId) {
      var jsonParams = '{';
      $("#" + paymentFormId + " input[name*='" + settings.paramPrefix + "']").each(function(index, element) {
        jsonParams += '"' + element.name.substring(settings.paramPrefix.length) + '":"' + element.value + '",'
      });
      if (jsonParams.length > 1) {
        jsonParams = jsonParams.substr(0, jsonParams.length - 1);
      }
      jsonParams += "}";
      return jsonParams;
    },
    sendPaymentOtherWays: function(paymentType) {
      methods.showProgress();
      var orderId     = properties.orderId,
          paymentData = {
            MDORDER: orderId
          };
      if (paymentType === 'UPOP') {
        paymentData.paymentWay = 'UPOP';
      } else if (paymentType === 'ALFACLICK') {
        paymentData.paymentWay = 'ALFA_ALFACLICK';
      }
      $.ajax({
        url: settings.paymentAction,
        type: 'POST',
        cache: false,
        data: paymentData,
        dataType: 'json',
        error: function() {
          methods.showError(settings.messageAjaxError);
          methods.switchActions(true);
          return true;
        },
        success: function(data) {
          methods.hideProgress();
          methods.switchActions(true);
          if ('acsUrl' in data && data['acsUrl'] !== null) {
            methods.redirectToAcs(data);
          } else if ('error' in data) {
            methods.showError(data['error']);
          } else if ('redirect' in data) {
            methods.redirect(data['redirect'], data['info'], settings.messageRedirecting);
          }
          return true;
        }

      });
    },
    deactiveBinding: function() {
      methods.showProgress();
      var orderId     = properties.orderId,
          bindingForm = $('#formBinding');
      $.ajax({
        url: settings.unbindCard,
        type: 'POST',
        cache: false,
        data: {
          'mdOrder': orderId,
          'bindingId': bindingForm.find('select[name=bindingId]').val(),
        },
        dataType: 'json',
        error: function() {
          methods.hideProgress();
          methods.showError(settings.messageAjaxError);
          return true;
        },
        success: function(data) {
          methods.hideProgress();
          if ('errorCode' in data && data['errorCode'] == 0) {
            $("#bindingIdSelect option:selected").remove();
            $("#combobox option:selected").remove();
            $("#combobox").val('other').change();
            $("#delete-binding").hide();
            $(document).trigger('deactivedBinding');
          } else {
            methods.showError(settings.messageAjaxError);
          }
          return true;
        }
      });
    },
    redirectToAcs: function(data) {
      $('#acs').attr('action', data['acsUrl']);
      $('#PaReq').val(data['paReq']);
      $('#MD').val(properties.orderId);
      $('#TermUrl').val(data['termUrl']);
      $('#acs').submit();
    },
    checkUrl: function(url) {
      if (!/[;<>,]|javascript/g.test(url)) {
        return url;
      } else {
        $(".back-btn").hide();
        console.warn("Некорректный backUrl");
        return false;
      }
    }
  };

  $.fn.getPaymentProperty = function(name) {
    return properties[name];
  }

  $.fn.payment = function(method) {
    // Method calling logic
    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } else if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments);
    } else {
      return $.error('Method ' + method + ' does not exist on jQuery.payment');
    }
  };
})(jQuery);

jQuery.fn.log = function(msg, type) {
  if (typeof lastSelector === 'undefined') {
    lastSelector = '--';
  }

  if (lastSelector !== this.selector.slice(0, lastSelector.length)) {
    if (lastSelector !== '--') {
      console.groupEnd();
      lastSelector = '--';
    }
    else {
      lastSelector = this.selector;
    }
    console.group("%s (%s)", msg, this.selector);
  }

  if (type === undefined) {
    type = "log";
  }
  switch (type) {
    case "log":
      console.log(this);
      break;
    case "warn":
      console.warn(this);
      break;
    case "info":
      console.info(this);
      break;
    case "error":
      console.error(this);
      break;
    case "time":
      console.time(msg);
      break;
    case "timestop":
      console.timeEnd(msg);
      break;
    case "profile":
      console.profile(msg);
      break;
    case "profilestop":
      console.profileEnd(msg);
      break;
  }
  return this;
};

function luhn(num) {
  num = (num + '').replace(/\D+/g, '').split('').reverse();
  if (!num.length) {
    return false;
  }
  var total = 0, i;
  for (i = 0; i < num.length; i++) {
    num[i] = parseInt(num[i]);
    total += i % 2 ? 2 * num[i] - (num[i] > 4 ? 9 : 0) : num[i];
  }
  return (total % 10) === 0;
}

// Clear empty units time
function isUnitTime(unit, value) {
  if (unit === 'days' && value === "") {
    return "";
  } else if (unit === 'days') {
    return getLocalizedText("day") || ":";
  }
  if (unit === 'hours' && value === "") {
    return "";
  } else if (unit === 'hours') {
    return getLocalizedText("hou") || ":";
  }
  if (unit === 'minutes' && value === "") {
    return "";
  } else if (unit === 'minutes') {
    return getLocalizedText("min") || ":";
  }
}

/**
 * Keyboard specific transliteration
 */
var keys = {
  "Й": "Q", "Ц": "W", "У": "E", "К": "R", "Е": "T", "Н": "Y", "Г": "U", "Ш": "I", "Щ": "O", "З": "P",
  "Ф": "A", "Ы": "S", "В": "D", "А": "F", "П": "G", "Р": "H", "О": "J", "Л": "K", "Д": "L", "Я": "Z",
  "Ч": "X", "С": "C", "М": "V", "И": "B", "Т": "N", "Ь": "M", "й": "q", "ц": "w", "у": "e", "к": "r",
  "е": "t", "н": "y", "г": "u", "ш": "i", "щ": "o", "з": "p", "ф": "a", "ы": "s", "в": "d", "а": "f",
  "п": "g", "р": "h", "о": "j", "л": "k", "д": "l", "я": "z", "ч": "x", "с": "c", "м": "v", "и": "b",
  "т": "n", "ь": "m"
};

function transliterate(word) {
  return word.split('').map(function(char) {
    return keys[char] || char;
  }).join("");
}

var bindings = [];
var bindingsEnabled = false;
var OTHER_TEXT = "Другая...";

function togglePasswordField(id) {
    if ($(id).get(0).type == 'password') {
        $(id).get(0).type = 'text'
    } else {
        $(id).get(0).type = 'password'
    }
}

function initVisualPan(){
    var visualPan = $('.visual-pan-js');
    function getRealCardNum(){
        var re = /[^0-9]/g;
        return visualPan.val().replace(re,'');
    }

    visualPan.mask("?9999 9999 9999 9999 999",{placeholder:" "});
    visualPan.keyup(function(){
        $('#iPAN').val(getRealCardNum());
    });
}

function initBindings(){
    $("#iCVC").keyup(function(value){
        $("#bindingCvc").val($("#iCVC").val());
    });
    $("#formBinding").hide();
    $("#buttonBindingPayment2").hide();
    $("#buttonBindingPayment2").click(function(){
        $("#buttonBindingPayment").click();
    });
}
function setEnableBinding(enable){
    // $("#pan_visible").val("");
    if (enable){
        //ENABLE bindings
        clearErrorsView();
        bindingsEnabled = true;
        $("#buttonPayment").hide();
        $("#buttonBindingPayment2").show();
        // $.mask.definitions['h'] = "^.{0,19}$";
        $('.visual-pan-js').mask("?hhhh hhhh hhhh hhhh hhh",{placeholder:" "});
        // $("#bindingIdSelect [value='"+bindings[selectdItem.value].value+"']").attr("selected", "selected");
        // $(".select-month .ui-selectmenu-status").text($("#month option[value='"+bindings[selectdItem.value].month+"']").text());
        // $(".select-year .ui-selectmenu-status").text($("#year option[value='20"+bindings[selectdItem.value].year+"']").text());
        $("#iTEXT").val("");
        document.getElementById("iTEXT").disabled=true;
    } else {
        //DISABLE bindings
        bindingsEnabled = false;
        $("#buttonPayment").show();
        $("#buttonBindingPayment2").hide();
        $('.visual-pan-js').mask("?9999 9999 9999 9999 999",{placeholder:" "});
        document.getElementById("iTEXT").disabled=false;
    }
}
var errorFields = [
    {id:'#iTEXT',  borderId:'.name-card-js',       message:'Владелец карты указан неверно'},
    {id:'#iPAN',   borderId:'.number-selection-js',message:'Номер карты указан неверно'},
    {id:'#iCVC',   borderId:'.code-js',            message:'CVC указан неверно'},
    {id:'#year',   borderId:'.choice-date-js',     message:'Срок действия карты указан неверно'},
    {id:'#iAgree', borderId:'.agreeBox',        message:'Укажите Ваше согласие с условиями'}
];
function updateErrors(){
    $('#errorBlock1').empty();
    errorFields.forEach(function(element){
        if ($(element.id).hasClass("invalid")){
            $(element.borderId).addClass('error');
            // без дополнительных текстов, только стили
            // $('#errorBlock1').append('<p class = "errorField">'+element.message+'</p>');
        } else {
            $(element.borderId).removeClass('error');
        }
    });
}
function clearErrorsView(){
    $('#errorBlock').empty();
    $('#errorBlock1').empty();
    errorFields.forEach(function(element){
        $(element.borderId).removeClass('error');
    });
}
function rebuildMonthSelect(){
    $('#month').find('option').each(function(){
        var option = $(this);
        option.text(option.attr('value'));
    });
}
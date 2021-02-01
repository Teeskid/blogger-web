function buildHttpRequest() {
    var mXHR;
    if (window.XMLHttpRequest) {
        mXHR =  new XMLHttpRequest();
    } else {
        try {
            mXHR = new ActiveXObject("MSXML2.XMLHTTP.3.0");
        } catch (error) {
            console.error("Neither XHR or ActiveX are supported!");
            mXHR = null;
        }
    }
    return mXHR;
}
function serializeForm(form) {
    var data = {};
    form.querySelectorAll("[name]").forEach(function(elem){
        if(elem.disabled)
            return;
        if(elem.type === "submit")
            return;
        if(elem.type === "radio" || elem.type === "checkbox") {
            data[elem.name] = elem.checked;
            return;
        }
        data[elem.name] = elem.value;
    });
    return data;
}
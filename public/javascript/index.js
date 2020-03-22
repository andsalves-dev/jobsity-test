function includeJs(path) {
    var script = document.createElement('script');
    script.src = path;
    script.async = false;
    script.defer = false;
    document.body.appendChild(script);
}

includeJs('/javascript/vendor/moment.min.js');
includeJs('/javascript/vendor/axios.min.js');
includeJs('/javascript/vendor/vue.min.js');
includeJs('/javascript/requests.js');
includeJs('/javascript/chat.js');
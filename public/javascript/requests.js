function fetchCurrentMessages() {
    var params = {};

    if (localStorage.getItem('last_login_at')) {
        params.from_date = localStorage.getItem('last_login_at');
    } else {
        params.from_date = moment().subtract(15, 'minutes').format();
    }

    return axios.get('/api/messages', {params}).catch(function (error) {
        return {
            data: {messages: []},
        };
    });
}

function sendMessage(message) {
    var data = {
        text: message,
        is_bot: false,
    }

    return axios.post('/api/messages', data).catch(function (error) {
        if (error.response && error.response.data['response_message']) {
            // using response message from api
            return error.response;
        }

        return {
            data: {
                // default error message
                response_message: {
                    text: 'Sorry, I could not understand your request. Could you please try other keywords?',
                    is_bot: true,
                    date: moment().format(),
                }
            },
        };
    });
}

function performLogin(data) {
    return axios.post('/api/auth/login', data);
}

function performRegister(data) {
    return axios.post('/api/user', data);
}
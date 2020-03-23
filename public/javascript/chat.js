var getJtwToken = function () {
    return localStorage.getItem('jwt');
}

var useJwtToken = function (token) {
    axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
}

Vue.filter('time', function (dateStr) {
    return moment(dateStr).format('h:mm a');
});

if (localStorage.getItem('jwt')) {
    useJwtToken(localStorage.getItem('jwt'));
    }

var welcomeMessage = {
    text: "Welcome! How can I help you?",
    is_bot: true,
    date: moment().format(),
}

var loginRequiredMessage = {
    text: 'Authentication required. Please start by typing your username and hit enter.',
    is_bot: true,
    date: moment().format(),
}

var loginFailedMessage = {
    text: 'Login failed, please check your credentials and try again. Type your password and hit enter:.',
    is_bot: true,
    date: moment().format(),
};

var defaultErrorMessage = {
    text: 'Sorry, I could not understand your request. Could you please try other keywords?',
    is_bot: true,
    date: moment().format(),
}

var vueApp = new Vue({
    el: '#main-content',
    data() {
        return {
            fieldValue: '',
            messages: [],
            getMessageClass: function (message) {
                return [
                    'list-group-item message-item mb-3',
                    {'active author-message': !message['is_bot']},
                ];
            },
            getMessageAuthorName: function (message) {
                return message['is_bot'] ? 'Bot' : 'You';
            },
            isLogged: Boolean(getJtwToken()),
            usernameForLogin: null,
        };
    },
    methods: {
        sendMessage() {
            var self = this;

            if (self.handlePreRecognizedMessage()) {
                self.fieldValue = '';
                return null;
            }

            self.messages.push({
                text: this.fieldValue,
                is_bot: false,
                date: moment().format(),
            });

            self.scrollToBottom();

            sendMessage(this.fieldValue).then(function (response) {
                if (!response.data || !response.data['response_message']) {
                    self.messages.push(defaultErrorMessage);
                } else {
                    self.messages.push(response.data['response_message']);
                }

                self.scrollToBottom();
            });

            self.fieldValue = '';
        },
        scrollToBottom() {
            setTimeout(function () {
                var el = document.getElementById('messages-list');
                el.scrollTo({top: el.scrollHeight, behavior: 'smooth'})
            }, 200);
        },
        // Specific messages handled or partially handled by the frontend
        handlePreRecognizedMessage() {
            var self = this;

            switch (true) {
                // clears the messages list. can be undone by refreshing the page
                case String(self.fieldValue).toLowerCase() === 'clear':
                    Vue.set(self, 'messages', [welcomeMessage]);
                    return true;
                // empty message: does nothing
                case !self.fieldValue:
                    return true;
                // handling login interaction
                case !self.isLogged && !self.usernameForLogin:
                    self.setUsername(self.fieldValue);
                    return true;
                case Boolean(!self.isLogged && self.usernameForLogin):
                    self.login();
                    return true;
                case ['logout', 'quit', 'exit'].includes(String(self.fieldValue).toLowerCase()):
                    self.logout();
                    return true;
            }

            return false;
        },
        setUsername(username) {
            var self = this;
            Vue.set(self, 'usernameForLogin', username);
            self.messages.push({
                text: username,
                is_bot: false,
                date: moment().format(),
            });
            self.messages.push({
                text: 'Now, type your password:',
                is_bot: true,
                date: moment().format(),
            });

            self.scrollToBottom();
        },
        login() {
            var self = this;
            var data = {
                username: self.usernameForLogin,
                password: self.fieldValue,
            };
            self.usernameForLogin = null;

            performLogin(data).then(function (response) {
                if (response.data['jwt']) {
                    localStorage.setItem('jwt', response.data['jwt']);
                    useJwtToken(response.data['jwt']);
                    Vue.set(self, 'messages', [welcomeMessage]);
                    self.isLogged = true;
                    alert('Logged in successfully!');
                } else {
                    throw new Error();
                }
            }).catch(function () {
                Vue.set(self, 'messages', [loginFailedMessage]);
                self.scrollToBottom();
            });
        },
        logout() {
            localStorage.removeItem('jwt');
            useJwtToken(null);
            alert('Logged out!');
            Vue.set(this, 'messages', [loginRequiredMessage]);
        }
    },
    created() {
        var self = this;

        if (self.isLogged) {
            fetchCurrentMessages().then(function (response) {
                if (!response.data || response.data.messages.length === 0) {
                    var defaultMessage = self.isLogged ? welcomeMessage : loginRequiredMessage;
                    Vue.set(self, 'messages', [defaultMessage]);
                } else {
                    Vue.set(self, 'messages', response.data.messages);
                }

                self.scrollToBottom();
            });
        } else {
            Vue.set(self, 'messages', [loginRequiredMessage]);
        }
    },
    computed: {
        getFieldType() {
            if (!this.isLogged && this.usernameForLogin) {
                return 'password';
            }

            return 'text';
        }
    }
});

axios.interceptors.response.use(null, function (error) {
    if ([401, 403].includes(error.status)) {
        return vueApp.isLogged = false;
    }

    return Promise.reject(error);
});
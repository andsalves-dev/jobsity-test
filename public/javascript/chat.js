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

axios.interceptors.response.use(null, function (error) {
    if ([401, 403].includes(error.response.status)) {
        vueApp.isLogged = false;
        localStorage.removeItem('jwt');
        axios.defaults.headers.common['Authorization'] = null;
    }

    return Promise.reject(error);
});

var buildMessage = function (text, is_bot = true) {
    return {
        text,
        is_bot,
        date: moment().format(),
    }
};

var welcomeMessage = buildMessage("Welcome! How can I help you?");

var loginRequiredMessage = buildMessage(
    "Authentication required. Type one of the available actions: 'login' or 'register'."
);

var registerMessages = {
    name: 'Please type your name:',
    username: 'Now type your username. Make to not include spaces or special characters:',
    email: 'Type your email address:',
    default_currency: 'Type your default currency for operations: (example: USD)',
    password: 'To finish, provide a password (min. of 4 characters):',
};

var loginMessages = {
    username: 'Please type your username:',
    password: 'Now please type your password:',
};

var loginFailedMessage = buildMessage(
    "Login failed, please check your credentials and try again. Type 'login' or 'register' to continue."
);

var defaultErrorMessage = buildMessage(
    'Sorry, I could not understand your request. Could you please try other keywords?'
);

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
            isLoggingIn: false,
            dataForLogin: {},
            isRegistering: false,
            dataForRegister: {},
            showHelper: false,
        };
    },
    methods: {
        sendMessage() {
            var self = this;

            if (self.handlePreRecognizedMessage()) {
                self.fieldValue = '';
                self.scrollToBottom();
                return null;
            }

            if (self.isLogged) {
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
            } else {
                self.messages.push(buildMessage("Invalid command. Try 'login' or 'register'."));
                self.scrollToBottom();
            }

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
            var lowerCasedText = String(self.fieldValue).toLowerCase();

            switch (true) {
                // clears the messages list. can be undone by refreshing the page
                case lowerCasedText === 'clear':
                    Vue.set(self, 'messages', [welcomeMessage]);
                    return true;
                // empty message: does nothing
                case !self.fieldValue:
                    return true;
                // handling login interaction
                case !self.isLogged && self.isLoggingIn:
                    self.handleProcess('login');
                    return true;
                case !self.isLogged && self.isRegistering:
                    self.handleProcess('register');
                    return true;
                case lowerCasedText === 'register':
                    self.startRegister();
                    return true;
                case lowerCasedText === 'login':
                    self.startLogin();
                    return true;
                case self.isLogged && ['logout', 'quit', 'exit'].includes(lowerCasedText):
                    self.logout();
                    return true;

            }

            return false;
        },
        startLogin() {
            this.isLoggingIn = true;
            this.messages.push(buildMessage(loginMessages.username));
        },
        startRegister() {
            this.isRegistering = true;
            this.messages.push(buildMessage(registerMessages.name));
        },
        handleProcess(process) {
            if (!['login', 'register'].includes(process)) {
                return null
            }

            var processField = process === 'login' ? 'dataForLogin' : 'dataForRegister';
            var messagesList = process === 'login' ? loginMessages : registerMessages;

            var fields = Object.keys(messagesList);
            var self = this;

            for (var i = 0; i < fields.length; i++) {
                if (!self[processField][fields[i]]) {
                    Vue.set(self[processField], fields[i], self.fieldValue)
                    self.messages.push(buildMessage(self.fieldValue, false));
                    fields[i + 1] && self.messages.push(buildMessage(messagesList[fields[i + 1]]));
                    self.scrollToBottom();
                    break;
                }
            }

            if (fields.length !== Object.keys(self[processField]).length) {
                return null;
            }

            if (process === 'login') {
                performLogin(self[processField]).then(function (response) {
                    if (response.data['jwt']) {
                        localStorage.setItem('jwt', response.data['jwt']);
                        useJwtToken(response.data['jwt']);
                        Vue.set(self, 'messages', [welcomeMessage]);
                        self.isLogged = true;
                        alert('Logged in successfully!');
                        self.clearProcesses();
                    } else {
                        throw new Error();
                    }
                }).catch(function () {
                    Vue.set(self, 'messages', [loginFailedMessage]);
                    self.scrollToBottom();
                    self.clearProcesses();
                })
            } else {
                performRegister(self[processField]).then(function () {
                    alert('Registered successfully! You can now use your credentials to login.');
                    self.clearProcesses();
                    Vue.set(self, 'messages', [loginRequiredMessage]);
                }).catch(function (error) {
                    var errorMessage = 'Failed to register. ';

                    if (error.response && error.response.data.message) {
                        errorMessage += error.response.data.message;
                    }

                    errorMessage += ". Type 'register' or 'login' to continue.";

                    Vue.set(self, 'messages', [buildMessage(errorMessage)]);
                    self.scrollToBottom();
                    self.clearProcesses();
                });
            }
        },
        clearProcesses() {
            this.dataForRegister = {};
            this.isRegistering = false;
            this.dataForLogin = {};
            this.isLoggingIn = false;
        },
        logout() {
            this.isLogged = false;
            this.clearProcesses()
            localStorage.removeItem('jwt');
            useJwtToken(null);
            alert('Logged out!');
            Vue.set(this, 'messages', [loginRequiredMessage]);
        }
    },
    created() {
        document.getElementById('main-content').style.display = 'block';
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
            if (
                (this.isLoggingIn && this.dataForLogin.username && !this.dataForLogin.password) ||
                (this.isRegistering && this.dataForRegister.default_currency && !this.dataForRegister.password)
            ) {
                return 'password';
            }

            return 'text';
        }
    }
});
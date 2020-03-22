axios.defaults.headers.common['Authorization'] = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MSwidXNlcm5hbWUiOiJhbmRzYWx2ZXMifQ.1-Fsgf0-4zg2f8XgqewSjTfMuCBlIg4PlwZHstnjhKA';

Vue.filter('time', function (dateStr) {
    return moment(dateStr).format('h:mm a');
});

var welcomeMessage = {
    text: "Hello, how can I help you?",
    is_bot: true,
    date: moment().format(),
}

var defaultErrorMessage = {
    text: 'Sorry, I could not understand your request. Could you please try other keywords?',
    is_bot: true,
    date: moment().format(),
}

const app = new Vue({
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
            }
        }
    },
    methods: {
        sendMessage() {
            var self = this;
            if (!self.fieldValue) {
                return null;
            }

            self.messages.push({
                text: this.fieldValue,
                is_bot: false,
                date: moment().format(),
            })

            sendMessage(this.fieldValue).then(function (response) {
                if (!response.data || !response.data['response_message']) {
                    self.messages.push(defaultErrorMessage);
                } else {
                    self.messages.push(response.data['response_message']);
                }

                self.scrollTOBottom();
            });

            self.fieldValue = '';
        },
        scrollTOBottom() {
            setTimeout(function () {
                var el = document.getElementById('messages-list');
                el.scrollTo({top: el.scrollHeight, behavior: 'smooth'})
            }, 200);
        }
    },
    created() {
        var self = this;
        fetchCurrentMessages().then(function (response) {
            if (!response.data || response.data.messages.length === 0) {
                Vue.set(self, 'messages', [welcomeMessage]);
            } else {
                Vue.set(self, 'messages', response.data.messages);
            }

            self.scrollTOBottom();
        });
    },
});

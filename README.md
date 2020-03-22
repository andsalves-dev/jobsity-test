## Jobsity Test
### Chatbot

#### Stack Details
- PHP Framework Used: Symfony
  - Other back-end tools/libraries: Composer, Doctrine ORM, JWT Auth
- Front-end: A single twig template file is used as entry point. 
VueJs, Axios, Bootstrap, and moment.js were also used to facilitate UI interactions and integration.

#### Prerequisites:
- PHP 7.2+
- mysql-server
    - `sudo apt install mysql-server5.7`
       (ubuntu/debian)
- composer
    - https://linuxize.com/post/how-to-install-and-use-composer-on-ubuntu-18-04/
- symfony cli
    - https://symfony.com/download

#### Steps to install and run:

First, create a .env.local file, and put an entry for the database config:
```
# For example:
DATABASE_URL=mysql://root:root@127.0.0.1:3306/jobsity_test?serverVersion=5.7
```

On terminal, run:
```

composer install                              # Installs php dependencies
php bin/console doctrine:database:create      # Creates the working database
php bin/console doctrine:migrations:migrate   # Runs migrations
```
Finally, start the server with
```
composer start
```

On your browser, access `http://localhost:8000` to check the page.

API routes go with the '/api' prefix.

## Bot commands
The bot can execute a few tasks, using the following patterns:
- Deposit an amount - pattern: "Deposit" + numerical amount + currency
  - Examples:
    - Deposit 1000 USD
    - Please deposit 40 USD
    - Can I deposit 1.99 USD, please?
- Withdraw an amount - Follows the same pattern of deposits, but using "withdraw" instead of "deposit".
- Check balance
    - Examples:
        - Show balance
        - Check balance
        - Balance

You can also interact with 'Hello', 'Hi' and 'Hey there'. 

When the bot can't understand your request, it will request you to recheck your words with "Sorry, I could not understand your request. Could you please try other keywords?".


#### Not implemented requisites (as of March 21)
- Login and register through the bot chat
  - a jwt login was implemented, but the front-end only uses a static jwt for now,
    that works for the default user.
- Currency conversion / currency api integration
  - Only USD is being accepted for deposits and withdrawals